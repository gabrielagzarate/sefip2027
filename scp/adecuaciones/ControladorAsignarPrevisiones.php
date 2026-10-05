<?php

//Inicio de sesion y llamadas a librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scp/PresupuestoVigente.php';

class ControladorAsignarPrevisiones extends WebResponse
{
    private $usuario_id;
    private $ip;
    private $escritura;
    public function __construct()
    { //Constructor
        parent::__construct();
        // Variables de sesión
        $this->usuario_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;
        $this->ip = isset($_SESSION[_S]['ses_ip']) ? $_SESSION[_S]['ses_ip'] : false;
        $this->escritura = isset($_SESSION[_S]['prm']['S']['1']['4'][TAREA_ESCRITURA]) ? true : false;

        switch ($this->metodo) {
            case 'llenaTablaClaves':
                $this->llenaTablaClaves();
                break;
            case 'asignarPartidaPlaneacion':
                $this->asignarPartidaPlaneacion();
                break;
            case 'main':
                $this->main();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $vacio = '';
        $this->mixHtml($this->agregaTools($vacio));
        $this->mixStyle('
            div.estado {float: left; }
            div.estado + span {
                font-size:.8em;
                margin: 0 0 0 10px;
                float:left;
            }
        ');

        $clavePres = new stdClass();
        $clavePres->id = 'clavePres';
        $clavePres->label = 'Previsión';
        $clavePres->placeholder = 'Introducir previsión';
        $clavePres->maxlength = 32;

        $cveFin = new stdClass();
        $cveFin->id = 'cveFin';
        $cveFin->label = 'Clave Financiamiento';
        $cveFin->maxlength = 9;

        $comboFiltro = new stdClass();
        $comboFiltro->id = 'cmbFiltro';
        $comboFiltro->label = 'Asignadas/No Asignadas';
        $comboFiltro->opciones = [
            '' => 'Seleccione una opción de la lista',
            1  => 'Asignadas a planeación',
            2  => 'No asignadas a planeación',
        ];
        $comboFiltro->search = true;
        $comboFiltro->width = '400px';

        $buscar = new stdClass();
        $buscar->id = 'buscar';
        $buscar->value = 'Buscar';
        $buscar->class = 'btn-outline-secondary me-1';
        $buscar->icon = 'search me-2';
        $buscar->color = 'azul';

        $html = '
            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($clavePres) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($cveFin) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboFiltro) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1" style="color:#0431B4"><br/>
                    ' . $this->ponBoton($buscar) . '
                </fieldset>
            </div>
        ';

        $script = $this->getScript();
        $tabla = new stdClass();
        $tabla->titulo = 'Listado de previsiones';
        $tabla->id = 'clavePresupuestal';
        $tabla->tools = $html;
        $tabla->numFilas = 10;
        $tr = [];
        $th = new stdClass();
        $th->texto = 'Previsión';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Asignada a planeación';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Asignar/Quitar';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClaves';
        $tabla->comunica->CvePres = '{(($("#clavePres").val().length>=21) ? $("#clavePres").val():"")}';
        $tabla->comunica->cmbFiltro = '{$("#cmbFiltro").val()}';
        $tabla->comunica->cveFin = '{(($("#cveFin").val().length==9) ? $("#cveFin").val():"")}';
        $tabla->numFilas = 10;
        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['pre_claveCompleta', 'guion', 'tcp_id'];
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'pe_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'asignada';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->color = 'cielo';
        $td->icono = 'check';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->ue_id = 'ue_id';
        $td->attr->epg_id = 'epg_id';
        $td->attr->pg_id = 'pg_id';
        $td->attr->pe_id = 'pe_id';
        $td->attr->ecf_id = 'ecf_id';
        $td->attr->tcp_id = 'tcp_id';
        $td->attr->asignada = 'asignada';
        $td->attr->pre_claveCompleta = 'pre_claveCompleta';
        $td->class = 'asignaPartida';
        if ($this->escritura) {
            array_push($tabla->cuerpo, $td);
        }

        $this->mixHtml($this->wrPonTabla($tabla));

        if (isset($this->prm->regresar) && $this->prm->regresar != '') {
            $this->llenaTablaClaves();
        }

        $this->mixScript($script . '
            $("#buscar").removeClass("btn-primary");
            $("#buscar").click(function(event){
                ' . $this->wrLlamaLlenaTabla('clavePresupuestal') . '
            });
        ');
    }

    private function llenaTablaClaves()
    {
        $and = '';
        $params = [];
        (isset($this->prm->CvePres) && $this->prm->CvePres != '' ? $this->prm->CvePres = str_replace('-', '', $this->prm->CvePres) : '');

        if ((isset($this->prm->CvePres) && strlen($this->prm->CvePres) >= 21) ||
            (isset($this->prm->cveFin) && strlen($this->prm->cveFin) == 9) ||
            (isset($this->prm->cmbFiltro) && $this->prm->cmbFiltro != '')
        ) {

            if (strlen($this->prm->CvePres) >= 21) {

                $and .= ' and ue_id = :ue ';
                $and .= ' and prg_id = :prg ';
                $and .= ' and spr_id = :spr ';
                $and .= ' and py_id = :py ';
                $and .= ' and oa_id = :oa ';
                $and .= ' and p.pg_id = :pg ';
                $and .= ' and p.pe_id = :pe ';

                $params[':ue'] = substr($this->prm->CvePres, 3, 3);
                $params[':prg'] = substr($this->prm->CvePres, 6, 3);
                $params[':spr'] = substr($this->prm->CvePres, 9, 2);
                $params[':py'] = substr($this->prm->CvePres, 11, 3);
                $params[':oa'] = substr($this->prm->CvePres, 14, 3);
                $params[':pg'] = substr($this->prm->CvePres, 17, 3);
                $params[':pe'] = substr($this->prm->CvePres, 20, 3);

                $cff_id = substr($this->prm->CvePres, 23, 1);
                $fte_id = substr($this->prm->CvePres, 24, 1);
                $sft_id = substr($this->prm->CvePres, 25, 1);
                $ori_id = substr($this->prm->CvePres, 26, 1);
                $sor_id = substr($this->prm->CvePres, 27, 1);
                $cfi_id = substr($this->prm->CvePres, 28, 2);
                $cfi_anio = substr($this->prm->CvePres, 30, 2);

                if (strlen($cff_id) == 1) {
                    $and .= ' and cff_id = :ccf ';
                    $params[':ccf'] = substr($this->prm->CvePres, 23, 1);
                }

                if (strlen($fte_id) == 1) {
                    $and .= ' and fte_id = :fte ';
                    $params[':fte'] = substr($this->prm->CvePres, 24, 1);
                }

                if (strlen($sft_id) == 1) {
                    $and .= ' and sft_id = :sft';
                    $params[':sft'] = substr($this->prm->CvePres, 25, 1);
                }

                if (strlen($ori_id) == 1) {
                    $and .= ' and ori_id = :ori';
                    $params[':ori'] = substr($this->prm->CvePres, 26, 1);
                }

                if (strlen($sor_id) == 1) {
                    $and .= ' and sor_id = :sor ';
                    $params[':sor'] = substr($this->prm->CvePres, 27, 1);
                }

                if (strlen($cfi_id) == 2) {
                    $and .= ' and cfi_id = :cfi ';
                    $params[':cfi'] = substr($this->prm->CvePres, 28, 2);
                }

                if (strlen($cfi_anio) == 2) {
                    $and .= ' and cfi_anio = :cfi_anio ';
                    $params[':cfi_anio'] = substr($this->prm->CvePres, 30, 2);
                }


            }

            if (strlen($this->prm->cveFin) == 9) {

                $cff_id = substr($this->prm->cveFin, 0, 1);
                $fte_id = substr($this->prm->cveFin, 1, 1);
                $sft_id = substr($this->prm->cveFin, 2, 1);
                $ori_id = substr($this->prm->cveFin, 3, 1);
                $sor_id = substr($this->prm->cveFin, 4, 1);
                $cfi_id = substr($this->prm->cveFin, 5, 2);
                $cfi_anio = substr($this->prm->cveFin, 7, 2);

                if (strlen($cff_id) == 1) {
                    $and .= ' and p.cff_id = :cff_id ';
                    $params[':cff_id'] = $cff_id ;
                }

                if (strlen($fte_id) == 1) {
                    $and .= ' and p.fte_id = :fte_id ';
                    $params[':fte_id'] = $fte_id;
                }

                if (strlen($sft_id) == 1) {
                    $and .= ' and p.sft_id = :sft_id ';
                    $params[':sft_id'] = $sft_id;
                }

                if (strlen($ori_id) == 1) {
                    $and .= ' and p.ori_id = :ori_id ';
                    $params[':ori_id'] = $ori_id;
                }

                if (strlen($sor_id) == 1) {
                    $and .= ' and p.sor_id = :sor_id ';
                    $params[':sor_id'] = $sor_id;
                }

                if (strlen($cfi_id) == 2) {
                    $and .= ' and p.cfi_id = :cfi_id ';
                    $params[':cfi_id'] = $cfi_id;
                }

                if (strlen($cfi_anio) == 2) {
                    $and .= ' and p.cfi_anio = :cfi_anio ';
                    $params[':cfi_anio'] = $cfi_anio;
                }

            }

            if ($this->prm->cmbFiltro != '') {
                switch ($this->prm->cmbFiltro) {
                    case 1:
                        $and .= ' and pre_planeacion= :pre_plan ';
                        $params[':pre_plan'] = 1;
                        break;
                    case 2:
                        $and .= ' and pre_planeacion= :pre_plan ';
                        $params[':pre_plan'] = 2;
                        break;
                }
            }

            $sql = "
                SELECT pv.pre_planeacion AS asignada, d.pe_nombre, p.pre_claveCompleta, '-' AS guion, p.tcp_id,
                    p.gpo_id, p.ur_id, p.ue_id, p.epg_id, p.pg_id, p.pe_id, p.ecf_id
                FROM " . BD_CTRL . '.presupuesto p
                JOIN ' . BD_CTRL . '.presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN ' . BD_CTRL . ".cat_PartidaEspecifica d USING(pg_id, pe_id)
                WHERE p.gpo_id = :gpo_id AND p.ur_id = :ur_id AND p.tcp_id= :tcp_id $and;";
            $this->setSql($sql);

            $params[':gpo_id'] = 1;
            $params[':ur_id'] = 15;
            $params[':tcp_id'] = 'P';

            $this->setParams($params);
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
            $nav->idTabla = 'clavePresupuestal';

            $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 10;
            if (($this->wrLlenaTabla($nav) == 0) && ((isset($this->prm->tipo) && $this->prm->tipo != '') && $this->prm->gpo_id != '' && $this->prm->ur_id != '')) {
                $this->mixScript('$("#buscar").click();');
            }
        }

        $this->mixScript('
            $(".asignaPartida").click(function(){
                let gp = $(this).attr("gpo_id");
                let ur = $(this).attr("ur_id");
                let ue = $(this).attr("ue_id");
                let epg = $(this).attr("epg_id");
                let pg = $(this).attr("pg_id");
                let pe = $(this).attr("pe_id");
                let ecf = $(this).attr("ecf_id");
                let tcp = $(this).attr("tcp_id");
                let cve = $("#clavePres").val();
                if($(this).attr("asignada")== 1){
                    alertify.confirm("Quitar previsión", "¿Desea quitar la previsión: " + $(this).attr("pre_claveCompleta") + " a planeación?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"asignarPartidaPlaneacion",
                                    gpo_id:gp,
                                    ur_id:ur,
                                    ue_id:ue,
                                    epg_id: epg,
                                    pg_id:pg,
                                    pe_id:pe,
                                    ecf_id:ecf,
                                    tcp_id:tcp,
                                    planeacion:2,
                                    CvePres: cve
                                });
                            },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-success");
                    }
                }
                else{
                    alertify.confirm("Asignar previsión", "¿Desea asignar la previsión: " + $(this).attr("pre_claveCompleta") + " a planeación?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"asignarPartidaPlaneacion",
                                    gpo_id:gp,
                                    ur_id:ur,
                                    ue_id:ue,
                                    epg_id: epg,
                                    pg_id:pg,
                                    pe_id:pe,
                                    ecf_id:ecf,
                                    tcp_id:tcp,
                                    planeacion:1,
                                    CvePres: cve
                                });
                            },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-success");
                    }
                }
            });
        ');
    }

    private function asignarPartidaPlaneacion()
    {
        if (isset($this->prm->planeacion)) {

            $ip = ($this->ip != false && $this->ip != '::1') ?
                $this->ip : '127.0.0.1';

            $a = new AccesoDatos();
            $a->conectar();
            $sql_obs = 'SET @usr_ip =INET_ATON(:ip), @usr_id = :usr, @bit_observacion= :obs ;';
            $params_obs = [':ip' => $ip, ':usr' => $this->usuario_id, ':obs' => 'ACTUALIZA PRE_PLANEACION DESDE PANTALLA ASIGNACION DE PREVISIONES'];
            $a->setSql($sql_obs);
            $a->consultar($params_obs);
            $sql = 'UPDATE ' . BD_CTRL . '.presupuestoVigente SET pre_planeacion = :planeacion
                WHERE gpo_id = :gpo and ur_id = :ur AND ue_id = :ue AND epg_id = :epg AND pg_id = :pg
                AND pe_id = :pe AND ecf_id = :ecf AND tcp_id = :tcp_id ;';
            $params = [':planeacion' => $this->prm->planeacion,
                ':gpo'               => $this->prm->gpo_id,
                ':ur'                => $this->prm->ur_id,
                ':ue'                => $this->prm->ue_id,
                ':epg'               => $this->prm->epg_id,
                ':pg'                => $this->prm->pg_id,
                ':pe'                => $this->prm->pe_id,
                ':ecf'               => $this->prm->ecf_id,
                ':tcp_id'            => $this->prm->tcp_id];
            $a->setSql($sql);
            $a->consultar($params);
            $a->desconectar();
            $h = (($this->prm->planeacion == 1) ? 'habilitado' : 'deshabilitado');

            $this->mixScript('notify("La prevision se ha ' . $h . ' a planeación");');
            $this->mixScript('comunica("' . $this->pantalla . '",{ metodo:"main",regresar:1});');
        }
    }
}

//Verifica si el usuario tiene los permisos
if (isset($_SESSION[_S]['prm']['S']['1']['4'])) {
    new ControladorAsignarPrevisiones();
} else {
    WebResponse::sinPermisos();
}
