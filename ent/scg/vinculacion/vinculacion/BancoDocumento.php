<?php

require_once __DIR__ . '/../../../Entidad.php';

class BancoDocumentos extends Entidad
{
    protected $gpo_id;                  //Grupo de la UR. FK cat_UnidadResponsable
    protected $ur_id;                   //Unidad Responsable. FK cat_UnidadResponsable
    protected $bdo_id;                  //Identificador del Documento
    protected $tdf_id;                  //Tipo de Documento fuente
    protected $rfc_id;                  //RFC e identificador de Proveedor
    protected $referencia;              //Referencia o Folio del Documento
    protected $sts_id;                  //Estado del Documento (1: Habilitado, 2: Cancelado)
    protected $fechaDocumento;          //Fecha original del documento con el que se devenga
    protected $total;
    protected $concepto;
    protected $uuid;
    protected $percepcionPresupuestal;
    protected $percepcionContable;
    protected $retencionContable;
    protected $estadoSat;               //Validación SAT (1: pendiente, 2: validado, 3: no validable)
    protected $limitadoTipoClc;         //0: cualquier tipo de CLC, 1: Obra o Administración Directa
    protected $bdo_bdo_id;
    protected $rectificaciones;
    protected $fondoRotatorio;          //Facturas utilizadas en fondo rotatorio
    protected $origen;                  //1: captura manual, 2: desde XML, 3: carga masiva
    protected $agrupacionGlobal;
    protected $estadoDevengo;           //1: sin devengar, 2: parcial, 3: total
    protected $estadoEjercido;          //1: sin ejercer, 2: parcial, 3: total
    protected $estadoMinistrado;        //1: sin ministrar, 2: parcial, 3: total
    protected $estadoPago;              //1: sin pagar, 2: parcial, 3: total
    protected $estadoRectificado;       //1: sin rectificar, 2: parcial, 3: total
    protected $pdp;                     //Usado en una CLC de Pago Directo a Proveedor
    protected $carga;                   //id de carga temporal
    protected $usr_id;                  //Usuario que captura el registro

    public function __construct()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(['tabla' => 'cat_BancoDocumentos']);

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->bdo_id = new Atributo('numero', 'NO', 'PRI', 'bdo_id');
        $this->tdf_id = new Atributo('cadena', 'NO', 'MUL', 'tdf_id');
        $this->rfc_id = new Atributo('cadena', 'NO', 'MUL', 'rfc_id');
        $this->referencia = new Atributo('cadena', 'NO', '', 'bdo_referencia');
        $this->sts_id = new Atributo('numero', 'NO', 'MUL', 'sts_id');
        $this->fechaDocumento = new Atributo('fecha', 'NO', '', 'bdo_fechaDocumento');
        $this->total = new Atributo('numero', 'NO', '', 'bdo_total');
        $this->concepto = new Atributo('cadena', 'NO', '', 'bdo_concepto');
        $this->uuid = new Atributo('cadena', 'NO', '', 'bdo_uuid');
        $this->percepcionPresupuestal = new Atributo('numero', 'NO', '', 'bdo_percepcionPresupuestal');
        $this->percepcionContable = new Atributo('numero', 'NO', '', 'bdo_percepcionContable');
        $this->retencionContable = new Atributo('numero', 'NO', '', 'bdo_retencionContable');
        $this->estadoSat = new Atributo('numero', 'NO', 'MUL', 'bdo_estadoSat');
        $this->limitadoTipoClc = new Atributo('numero', 'NO', '', 'bdo_limitadoTipoClc');
        $this->bdo_bdo_id = new Atributo('numero', 'YES', 'MUL', 'bdo_bdo_id');
        $this->rectificaciones = new Atributo('numero', 'NO', '', 'bdo_rectificaciones');
        $this->fondoRotatorio = new Atributo('numero', 'NO', '', 'bdo_fondoRotatorio');
        $this->origen = new Atributo('numero', 'NO', 'MUL', 'bdo_origen');
        $this->agrupacionGlobal = new Atributo('cadena', 'NO', '', 'bdo_agrupacionGlobal');
        $this->estadoDevengo = new Atributo('numero', 'NO', '', 'bdo_estadoDevengo');
        $this->estadoEjercido = new Atributo('numero', 'NO', '', 'bdo_estadoEjercido');
        $this->estadoMinistrado = new Atributo('numero', 'NO', '', 'bdo_estadoMinistrado');
        $this->estadoPago = new Atributo('numero', 'NO', 'MUL', 'bdo_estadoPago');
        $this->estadoRectificado = new Atributo('numero', 'NO', '', 'bdo_estadoRectificado');
        $this->pdp = new Atributo('numero', 'NO', '', 'bdo_pdp');
        $this->carga = new Atributo('cadena', 'NO', '', 'tem_carga');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if ($prm) {
            $this->fill($prm, true);
            if ($this->getGpo_id() != '' && $this->getUr_id() != '' && $this->getBdo_id() != '') {
                $this->select();
            }
        }
    }

    public static function abc($p)
    {
        $p = (object) $p;

        $bdo = new BancoDocumentos();
        $bdo->fill($p, false);

        switch ($p->abc) {
            case 'a':
                $bdo->insert();
                return $bdo->getUltimoId();
            case 'b':
                $bdo->delete();
                break;
            case 'c':
                $bdo->update();
                break;
        }
    }
}
