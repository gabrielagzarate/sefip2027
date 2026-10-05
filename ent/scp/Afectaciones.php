<?php

require_once __DIR__ . '/../Entidad.php';

class Afectacion extends Entidad
{
    protected $gpo_id;                //Grupo de la UR. FK scp_Clc / cat_UnidadEjecutora
    protected $ur_id;                 //Unidad Responsable. FK scp_Clc / cat_UnidadEjecutora
    protected $clc_id;                //CLC a la que pertenece la Afectación. FK scp_Clc
    protected $afe_id;                //Identificador de la Afectación
    protected $epg_id;                //Encuadre Programático. FK enc_Programatico
    protected $ecf_id;                //Encuadre de Clave de Financiamiento. FK enc_ClaveFinanciamiento
    protected $ue_id;                 //Unidad Ejecutora. FK cat_UnidadEjecutora
    protected $tcp_id;                //Tipo de compra. FK cat_TipoCompra
    protected $tcl_id;                //Tipo de CLC. FK cat_SubtipoClc
    protected $stc_id;                //Sub-Tipo de CLC. FK cat_SubtipoClc
    protected $tno_id;                //Tipo de Nómina. FK cat_TipoNomina
    protected $mno_id;                //Modalidad de Nómina. FK cat_SubmodalidadNomina
    protected $smn_id;                //Sub-Modalidad de Nómina. FK cat_SubmodalidadNomina
    protected $afe_percepcion;        //Monto total de percepciones
    protected $afe_retencion;         //Importe total de retenciones
    protected $afe_fechaInicio;       //Fecha de inicio del periodo (si aplica)
    protected $afe_fechaFin;          //Fecha de fin del periodo (si aplica)
    protected $afe_fechaElaboracion;  //Fecha de elaboración de la Afectación
    protected $afe_clc;               //Número de la CLC
    protected $tem_carga;             //Indica si la Afectación fue cargada por carga masiva
    protected $ocl_id;                //Origen de la CLC. FK cat_OrigenClc
    protected $afe_fondoRotatorio;
    protected $eaf_id;
    protected $afe_periodo;
    protected $afe_fechaAutorizado;
    protected $afe_fechaCancelado;
    protected $afe_ipu_id;
    protected $usr_id;

    public function __construct()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(['tabla' => 'scp_Afectacion']);

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->clc_id = new Atributo('numero', 'NO', 'PRI', 'clc_id');
        $this->afe_id = new Atributo('numero', 'NO', 'PRI', 'afe_id');
        $this->epg_id = new Atributo('numero', 'YES', 'MUL', 'epg_id');
        $this->ecf_id = new Atributo('numero', 'NO', 'MUL', 'ecf_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->tcp_id = new Atributo('cadena', 'YES', 'MUL', 'tcp_id');
        $this->tcl_id = new Atributo('cadena', 'NO', 'MUL', 'tcl_id');
        $this->stc_id = new Atributo('numero', 'NO', '', 'stc_id');
        $this->tno_id = new Atributo('numero', 'YES', 'MUL', 'tno_id');
        $this->mno_id = new Atributo('numero', 'YES', 'MUL', 'mno_id');
        $this->smn_id = new Atributo('numero', 'YES', '', 'smn_id');
        $this->afe_percepcion = new Atributo('numero', 'NO', '', 'afe_percepcion');
        $this->afe_retencion = new Atributo('numero', 'NO', '', 'afe_retencion');
        $this->afe_fechaInicio = new Atributo('fecha', 'YES', '', 'afe_fechaInicio');
        $this->afe_fechaFin = new Atributo('fecha', 'YES', '', 'afe_fechaFin');
        $this->afe_fechaElaboracion = new Atributo('fecha', 'NO', '', 'afe_fechaElaboracion');
        $this->afe_clc = new Atributo('numero', 'NO', '', 'afe_clc');
        $this->tem_carga = new Atributo('cadena', 'NO', '', 'tem_carga');
        $this->ocl_id = new Atributo('numero', 'NO', 'MUL', 'ocl_id');
        $this->afe_fondoRotatorio = new Atributo('cadena', 'YES', 'MUL', 'afe_fondoRotatorio');
        $this->eaf_id = new Atributo('cadena', 'NO', 'MUL', 'eaf_id');
        $this->afe_periodo = new Atributo('numero', 'NO', 'MUL', 'afe_periodo');
        $this->afe_fechaAutorizado = new Atributo('fecha', 'YES', '', 'afe_fechaAutorizado');
        $this->afe_fechaCancelado = new Atributo('fecha', 'YES', '', 'afe_fechaCancelado');
        $this->afe_ipu_id = new Atributo('numero', 'YES', '', 'afe_ipu_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if ($prm) {
            $this->fill($prm, true);
            if ($this->getGpo_id() != '' && $this->getUr_id() != '' && $this->getClc_id() != '' && $this->getAfe_id() != '') {
                $this->select();
            }
        }
    }

    /*
     * Accesores conservados: ControladorAfectacion los invoca con este nombre y no
     * coinciden con la columna, por lo que el magic __call de Entidad no los cubre.
     */
    public function getFechaInicio()
    {
        return $this->afe_fechaInicio->getVal();
    }

    public function getFechaFin()
    {
        return $this->afe_fechaFin->getVal();
    }

    public function getFondoRotatorio()
    {
        return $this->afe_fondoRotatorio->getVal();
    }

    public static function abc($p)
    {
        $p = (object) $p;

        // El módulo de afectaciones envía llaves "cortas"; se normalizan al nombre de columna.
        $cortos = ['percepcion', 'retencion', 'fechaInicio', 'fechaFin', 'fechaElaboracion', 'clc', 'fondoRotatorio', 'periodo', 'fechaAutorizado', 'fechaCancelado'];
        foreach ($cortos as $corto) {
            if (isset($p->$corto)) {
                $p->{'afe_' . $corto} = $p->$corto;
            }
        }
        if (isset($p->carga)) {
            $p->tem_carga = $p->carga;
        }

        $afe = new Afectacion();
        $afe->fill($p, false);

        switch ($p->abc) {
            case 'a':
                $afe->insert();
                $afe->setSql('SELECT @afe_id as afe_id;');
                $afe->consultar();
                $afe_id = $afe->extraer();
                $afe->desconectar();
                return $afe_id['afe_id'];
            case 'b':
                $afe->delete();
                break;
            case 'c':
                $afe->update();
                break;
        }
    }
}
