<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class ProgramaPresupuestario extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        if (isset($this->prm->metodoPp)) {
            $metodo = (isset($this->prm->metodoPp)) ? $this->prm->metodoPp : '';
            switch ($metodo) {
                case 'cambioTpg':
                    $this->cargaStp();
                    break;
                case 'cambioStp':
                    $this->cargaCtp();
                    break;
                case 'cambioCtp':
                    $this->cambioCtp();
                    break;
            }
            $this->returnJson();
        }
    }

    public static function ponProgramaPres()
    {
        $pp = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        $form = '';
        $cf = new WebResponse();
        $preseleccion = '';

        $required = true;
        if (isset($pp->form)) {
            $form = $pp->form;
            $required = isset($pp->required);
        }

        $ponTemplate = (isset($pp->ponTemplate) && is_bool($pp->ponTemplate)) ? $pp->ponTemplate : false;
        $_SESSION[_S]['pp'] = new stdClass();

        $cs = new WebResponse();
        if (!empty($pp->epp_id)) {
            $sql = 'SELECT tpg_id,stp_id,ctp_id FROM enc_ProgramaPresupuestario WHERE epp_id = :epp';
            $parametros = [':epp' => $pp->epp_id];
            $cs->setSql($sql);
            $cs->conectar();
            $cs->consultar($parametros);
            $cs->desconectar();
            $datos = $cs->extraer();
            $pp->tpg_id = $datos['tpg_id'];
            $pp->stp_id = $datos['stp_id'];
            $pp->ctp_id = $datos['ctp_id'];
            $_SESSION[_S]['pp']->epp = $pp->epp_id;
        } elseif (isset($pp->lectura) && $pp->lectura) {
            $pp->tpg_id = 'null';
            $pp->stp_id = 'null';
            $pp->ctp_id = 'null';
        }

        #combo Objetivo
        $combo = new stdClass();
        $combo->id = 'tpg_id';
        $combo->label = 'Tipología';
        $combo->comoAtr = 'tpg_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Tipología';

        $combo->sql = 'SELECT tpg_id,tpg_nombre FROM cat_Tipologia';
        if (isset($pp->lectura) && $pp->lectura) {
            $combo->sql .= ' WHERE tpg_id = ?;';
            $combo->params = [$pp->tpg_id];
            $combo->detonar = false;
        }
        if (isset($pp->tpg_id)) {
            $combo->selecciona = $pp->tpg_id;
            $_SESSION[_S]['pp']->tpg = $pp->tpg_id;
            $combo->detonar = false;
        }
        if (isset($pp->disabled)) {
            $combo->disabled = $pp->disabled;
        }
        $objetivo = $cs->ponCombo($combo);
        if (!is_null($cs->comboValorUnico)) {
            $_SESSION[_S]['pp']->tpg_id = $cs->comboValorUnico;
        }

        #combo subtipo
        $combo = new stdClass();
        $combo->id = 'stp_id';
        $combo->label = 'Subtipología';
        $combo->comoAtr = 'stp_id,tpg_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Subtipología';
        if (!empty($pp->tpg_id)) {
            $combo->sql = 'SELECT stp_id,stp_nombre,tpg_id FROM cat_SubTipologia WHERE tpg_id = ?';
            $combo->params = [$pp->tpg_id];
            if (isset($pp->lectura) && $pp->lectura) {
                $combo->sql .= ' AND stp_id = ' . $pp->stp_id . ';';
                $combo->detonar = false;
            }
            if (isset($pp->stp_id)) {
                $combo->selecciona = $pp->stp_id;
                $_SESSION[_S]['pp']->stp = $pp->stp_id;
                $combo->detonar = false;
            }
            if (isset($pp->disabled)) {
                $combo->disabled = $pp->disabled;
            }
            $subtipo = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pp']->stp = $cs->comboValorUnico;
            }
        } else {
            $subtipo = $cs->templateCombo($combo);
        }

        #combo clasificacion
        $combo = new stdClass();
        $combo->id = 'ctp_id';
        $combo->label = 'Clasificación Tipología';
        $combo->comoAtr = 'ctp_id,stp_id,tpg_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Clasificacion';
        if (!empty($pp->stp_id)) {
            $params = [$pp->tpg_id, $pp->stp_id];
            $combo->sql = 'SELECT ctp_id,ctp_nombre,stp_id,tpg_id FROM cat_ClasificacionTipologia WHERE tpg_id = ? AND  stp_id = ?';
            if (isset($pp->lectura) && $pp->lectura) {
                $combo->sql .= ' AND ctp_id = ?;';
                $combo->detonar = false;
                array_push($params, $pp->ctp_id);
            }
            if (isset($pp->ctp_id)) {
                $combo->selecciona = $pp->ctp_id;
                $_SESSION[_S]['pp']->ctp = $pp->ctp_id;
                $combo->detonar = false;
            }
            if (isset($pp->disabled)) {
                $combo->disabled = $pp->disabled;
            }
            $combo->params = $params;
            $clasificacion = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pp']->ctp = $cs->comboValorUnico;
            }
        } else {
            $clasificacion = $cs->templateCombo($combo);
        }
        return '
            <div id="row">
                <legend class="col-md-12 col-xs-12">Clasificación Programática</legend>
                    ' . $objetivo . '
                    ' . $subtipo . '
                    ' . $clasificacion . '
            </div>' . '
                <script language="javascript" >
                    $("#tpg_id").change(function(){
                        if($(this).val()!=""){
                            comunica("srv/ProgramaPresupuestario.php",{
                                metodoPp:"cambioTpg",
                                tpg_id:$(this).val()
                            });
                        }
                    });
                    $("#stp_id").change(function(){
                        if($(this).val()!=""){
                            comunica("srv/ProgramaPresupuestario.php",{
                                metodoPp:"cambioStp",
                                tpg_id:$(this).find("option:selected").attr("tpg_id"),
                                stp_id:$(this).val()
                            });
                        }
                    });
                    $("#ctp_id").change(function(){
                        if($(this).val()!=""){
                            comunica("srv/ProgramaPresupuestario.php",{
                                metodoPp:"cambioCtp",
                                tpg_id:$(this).find("option:selected").attr("tpg_id"),
                                stp_id:$(this).find("option:selected").attr("stp_id"),
                                ctp_id:$(this).val()
                            });
                        }
                    });
                    
                    ' . $cs->getScript() . '
                    ' . $preseleccion . '
                </script>';
    }

    private function cargaStp()
    {
        $_SESSION[_S]['pp']->tpg = $this->prm->tpg_id;
        $obt = new stdClass();
        $obt->id = 'stp_id';

        if (isset($this->prm->stp_id) && $this->prm->stp_id != '') {
            $obt->selecciona = $this->prm->stp_id;
        }
        $obt->sql = 'SELECT stp_id,stp_nombre,tpg_id FROM cat_SubTipologia WHERE tpg_id = ? ;';
        $obt->params = [$_SESSION[_S]['pp']->tpg];
        $obt->comoAtr = 'tpg_id,stp_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pp']->stp = $this->comboValorUnico;
        }
        $this->cargaCtp();
    }

    private function cargaCtp()
    { //tpg_id
        $_SESSION[_S]['pp']->stp = (empty($this->prm->stp_id)) ? 0 : $this->prm->stp_id;
        $_SESSION[_S]['pp']->tpg = (empty($this->prm->tpg_id)) ? 0 : $this->prm->tpg_id;
        $obt = new stdClass();
        $obt->id = 'ctp_id';

        if (isset($this->prm->ctp_id) && $this->prm->ctp_id != '') {
            $obt->selecciona = $this->prm->ctp_id;
        }
        $obt->sql = 'SELECT ctp_id,ctp_nombre,stp_id,tpg_id FROM cat_ClasificacionTipologia WHERE tpg_id = ? AND  stp_id = ? ;';
        $obt->params = [$_SESSION[_S]['pp']->tpg, $_SESSION[_S]['pp']->stp];
        $obt->comoAtr = 'tpg_id,stp_id,ctp_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pp']->ctp = $this->comboValorUnico;
        }
    }

    private function cambioCtp()
    {
        $pes = new WebResponse();
        $_SESSION[_S]['pp']->ctp = (empty($this->prm->ctp_id)) ? 0 : $this->prm->ctp_id;
        $sql = 'SELECT epp_id FROM enc_ProgramaPresupuestario WHERE tpg_id = :tpg and stp_id = :stp and ctp_id = :ctp';
        $parametros = [':tpg' => $_SESSION[_S]['pp']->tpg, ':stp' => $_SESSION[_S]['pp']->stp, ':ctp' => $_SESSION[_S]['pp']->ctp];
        $pes->setSql($sql);
        $pes->conectar();
        $pes->consultar($parametros);
        $pes->desconectar();
        $datos = $pes->extraer();
        $_SESSION[_S]['pp']->epp = $datos['epp_id'];
    }
}
//-------------------------------//
if (isset($_POST['metodoPp'])) {
    new ProgramaPresupuestario();
}
//---------------------------------//
