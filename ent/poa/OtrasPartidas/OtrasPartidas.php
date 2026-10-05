<?php

//require_once dirname(__FILE__) . '/../../../lib/constantes.php';
require_once __DIR__ . '/../../Entidad.php';
//require_once dirname(__FILE__) . '/../../../lib/Meses.php';

class OtrasPartidas extends Entidad
{
    protected $ccd_id;
    protected $ced_id;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $epg_id;
    protected $pg_id;
    protected $pe_id;
    protected $ecf_id;
    protected $otp_archivo;
    protected $otp_justificacion;
    protected $otp_unidadMedida; //texto libre solo cuando umv_id corresponde a OTRO
    protected $umv_id;           //FK al catálogo cat_UnidadMedidaVariable
    protected $otp_costo;
    protected $unidades;   //calendario de unidades por partida
    protected $montos;     //calendario de costos por partida
    protected $usr_id;

    public function __construct()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(['tabla' => 'poa_OtrasPartidas']);

        $this->ccd_id = new Atributo('numero', 'NO', 'PRI', 'ccd_id');
        $this->ced_id = new Atributo('numero', 'NO', 'PRI', 'ced_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', 'MUL', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', '', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->otp_archivo = new Atributo('cadena', 'NO', '', 'otp_archivo');
        $this->otp_justificacion = new Atributo('cadena', 'NO', '', 'otp_justificacion');
        $this->otp_unidadMedida = new Atributo('cadena', 'SI', '', 'otp_unidadMedida');
        $this->umv_id = new Atributo('numero', 'NO', '', 'umv_id');
        $this->otp_costo = new Atributo('numero', 'NO', '', 'otp_costo');
        $this->unidades = new Meses(['prefix' => 'uni_']);
        $this->montos = new Meses(['prefix' => 'mon_']);
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        // Cédula de Otras Partidas = 9
        $this->setCcd_id(9);

        if ($prm) {
            $this->fill($prm, true);
            if ($this->getCcd_id() != '' && $this->getCed_id() != '') {
                $this->select();
            }
        }
    }

    public static function abc($p)
    {
        $otp = new OtrasPartidas();
        $otp->fill($p, false);

        $otp->setObservacion($p->bit_observacion);
        switch ($p->abc) {
            case 'a':
                $otp->insert();
                return $otp->getUltimoId();
            case 'b':
                $otp->delete();
                break;
            case 'c':
                $otp->update();
                break;
        }
    }
}
