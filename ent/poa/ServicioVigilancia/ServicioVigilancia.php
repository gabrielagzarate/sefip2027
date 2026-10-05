<?php
require_once __DIR__ . "/../../Entidad.php";

class ServicioVigilancia extends Entidad
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
    protected $tct_id;
    protected $area;       //mapea a la columna svg_area
    protected $unidades;   //calendario de unidades por partida
    protected $montos;     //calendario de costos por partida
    protected $usr_id;

    public function __construct()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(["tabla" => "poa_ServicioVigilancia"]);

        $this->ccd_id = new Atributo("numero", "NO", "PRI", "ccd_id");
        $this->ced_id = new Atributo("numero", "NO", "PRI", "ced_id");
        $this->gpo_id = new Atributo("numero", "NO", "MUL", "gpo_id");
        $this->ur_id  = new Atributo("numero", "NO", "", "ur_id");
        $this->ue_id  = new Atributo("numero", "NO", "", "ue_id");
        $this->epg_id = new Atributo("numero", "NO", "", "epg_id");
        $this->pg_id  = new Atributo("numero", "NO", "MUL", "pg_id");
        $this->pe_id  = new Atributo("numero", "NO", "", "pe_id");
        $this->ecf_id = new Atributo("numero", "NO", "", "ecf_id");
        $this->tct_id = new Atributo("numero", "NO", "MUL", "tct_id");
        $this->area   = new Atributo("cadena", "NO", "", "svg_area");
        $this->unidades = new Meses(["prefix" => "uni_"]);
        $this->montos   = new Meses(["prefix" => "mon_"]);
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        // Cédula de Servicio de Vigilancia = 7; partida específica 328
        $this->setCcd_id(7);
        $this->setPe_id(328);

        if ($prm) {
            $this->fill($prm, true);
        }
    }

    public static function abc($p)
    {
        $svg = new ServicioVigilancia();
        $svg->fill($p, false);

        $svg->setObservacion($p->bit_observacion);
        switch ($p->abc) {
            case "a":
                $svg->insert();
                return $svg->getUltimoId();
            case "b":
                $svg->delete();
                break;
            case "c":
                $svg->update();
                break;
        }
    }
}
