<?php
require_once __DIR__ . "/../../Entidad.php";

class Arrendamiento extends Entidad
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
    protected $uso;
    protected $ubicacion;
    protected $inicioPeriodo;
    protected $terminoPeriodo;
    protected $rentaMensual;
    protected $totalUnidades;
    protected $observaciones;
    protected $unidades;
    protected $montos;
    protected $usr_id;

    public function __construct()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(["tabla" => "poa_Arrendamiento"]);

        $this->ccd_id = new Atributo("numero", "NO", "PRI", "ccd_id");
        $this->ced_id = new Atributo("numero", "NO", "PRI", "ced_id");
        $this->gpo_id = new Atributo("numero", "NO", "MUL", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "", "ur_id");
        $this->ue_id = new Atributo("numero", "NO", "", "ue_id");
        $this->epg_id = new Atributo("numero", "NO", "", "epg_id");
        $this->pg_id = new Atributo("numero", "NO", "MUL", "pg_id");
        $this->pe_id = new Atributo("numero", "NO", "", "pe_id");
        $this->ecf_id = new Atributo("numero", "NO", "", "ecf_id");
        $this->uso = new Atributo("cadena", "NO", "", "arr_uso");
        $this->ubicacion = new Atributo("cadena", "NO", "", "arr_ubicacion");
        $this->inicioPeriodo = new Atributo("fecha", "NO", "", "arr_inicioPeriodo");
        $this->terminoPeriodo = new Atributo("fecha", "NO", "", "arr_terminoPeriodo");
        $this->rentaMensual = new Atributo("numero", "NO", "", "arr_rentaMensual");
        $this->totalUnidades = new Atributo("numero", "NO", "", "arr_totalUnidades");
        $this->observaciones = new Atributo("cadena", "NO", "", "arr_observaciones");
        $this->unidades = new Meses(array("prefix" => "uni_"));
        $this->montos = new Meses(array("prefix" => "mon_"));
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        // Cédula de Arrendamiento = 5; partidas específicas 310 y 311
        $this->setCcd_id(5);
        $this->setPe_id(310);

        if ($prm) {
            $this->fill($prm, true);
        }
    }

    public static function abc($p)
    {
        $arr = new Arrendamiento();
        $arr->fill($p, false);

        $arr->setObservacion($p->bit_observacion);
        switch ($p->abc) {
            case "a":
                $arr->insert();
                return $arr->getUltimoId();
            case "b":
                $arr->delete();
                break;
            case "c":
                $arr->update();
                break;
        }
    }
}
