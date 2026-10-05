<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class Pep extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        if (isset($this->prm->metodoPep)) {
            $metodo = (isset($this->prm->metodoPep)) ? $this->prm->metodoPep : '';
            switch ($metodo) {
                case 'cambioPla':
                    $this->cargaOpe();
                    break;
                case 'cambioOpe':
                    $this->cargaEsp();
                    break;
                case 'cambioEsp':
                    $this->cargaRsp();
                    break;
                case 'cambioRsp':
                    $this->cambioRsp();
                    break;
            }
            $this->returnJson();
        }
    }

    public static function ponPep()
    {
        $pep = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        $form = '';
        $cf = new WebResponse();
        $preseleccion = '';

        $required = true;
        if (isset($pep->form)) {
            $form = $pep->form;
            $required = isset($pep->required);
        }

        $ponTemplate = (isset($pep->ponTemplate) && is_bool($pep->ponTemplate)) ? $pep->ponTemplate : false;
        $_SESSION[_S]['pep'] = new stdClass();

        $cs = new WebResponse();
        if (!empty($pep->prg_id)) {

            $sql = 'SELECT ope_id, ple_id FROM vin_Programa WHERE prgs_id = :epc';
            $parametros = [':epc' => $pep->prg_id];
            $cf->setSql($sql);
            $cf->conectar();
            $cf->consultar($parametros);
            $cf->desconectar();
            $cf = $cf->extraer();
            $pep->ple_id = $cf['ple_id'];
            $pep->ope_id = $cf['ope_id'];
            //$pep->espe_id = $cf['espe_id'];
            //$pep->rspe_id = $cf['rspe_id'];
            //$_SESSION[_S]['pep']->epc = $pep->epc_id;
        }
        #combo Plan
        $combo = new stdClass();
        $combo->id = 'ple_id';
        $combo->label = 'Plan';
        $combo->mensaje = 'Seleccionar un Plan';
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->enDiv = true;
        $combo->required = $required;
        $combo->horizontal = 3;
        $combo->sql = 'SELECT ple_id,ple_nombre FROM cat_PlanEspecial';
        if (isset($pep->lectura) && $pep->lectura && !empty($pep->ple_id)) {
            $combo->sql .= ' WHERE ple_id = ?;';
            $combo->params = [$pep->ple_id];
            $combo->detonar = false;
        }
        if (isset($pep->ple_id)) {
            $combo->selecciona = $pep->ple_id;
            $_SESSION[_S]['pep']->ple = $pep->ple_id;
            $combo->detonar = false;
        }

        if (isset($pep->disabled)) {
            $combo->disabled = $pep->disabled;
        }
        $plan = $cs->ponCombo($combo);
        if (!is_null($cs->comboValorUnico)) {
            $_SESSION[_S]['pep']->ple = $cs->comboValorUnico;
        }
        #combo Objetivo
        $combo = new stdClass();
        $combo->id = 'ope_id';
        $combo->label = 'Objetivo';
        $combo->comoAtr = 'ple_id,ope_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Objetivo';

        if (!is_null($pep->ple_id)) {
            $params = [$pep->ple_id];
            $combo->sql = 'SELECT ope_id,ope_nombre,ple_id FROM cat_ObjetivoPe WHERE ple_id = ?';
            if (isset($pep->lectura) && $pep->lectura) {
                $combo->sql .= ' AND ope_id = ?;';
                array_push($params, $pep->ope_id);
                $combo->detonar = false;
            }
            if (isset($pep->ope_id)) {
                $combo->selecciona = $pep->ope_id;
                $combo->detonar = false;
                $_SESSION[_S]['pep']->ope = $pep->ope_id;
            }
            if (isset($pep->disabled)) {
                $combo->disabled = $pep->disabled;
            }
            $combo->params = $params;
            $objetivo = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pep']->ope = $cs->comboValorUnico;
            }
        } else {
            $objetivo = $cs->templateCombo($combo);
        }

        #combo Estrategia
        $combo = new stdClass();
        $combo->id = 'espe_id';
        $combo->label = 'Estrategia';
        $combo->comoAtr = 'ple_id,ope_id,espe_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Estrategia';
        if (!is_null($pep->ope_id)) {
            $combo->sql = 'SELECT espe_id,espe_nombre,ple_id,ope_id FROM cat_EstrategiaPe WHERE ple_id = ? AND ope_id = ?';
            $combo->params = [$pep->ple_id, $pep->ope_id];
            if (isset($pep->lectura) && $pep->lectura) {
                $combo->sql .= ' AND espe_id = ?;';
                array_push($combo->params, $pep->espe_id);
                $combo->detonar = false;
            }
            if (isset($pep->espe_id)) {
                $combo->selecciona = $pep->espe_id;
                $combo->detonar = false;
                $_SESSION[_S]['pep']->esp = $pep->espe_id;
            }
            if (isset($pep->disabled)) {
                $combo->disabled = $pep->disabled;
            }
            $estrategia = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pep']->esp = $cs->comboValorUnico;
            }
        } else {
            $estrategia = $cs->templateCombo($combo);
        }

        #combo resultado
        $combo = new stdClass();
        $combo->id = 'rspe_id';
        $combo->label = 'Resultado';
        $combo->comoAtr = 'ple_id,ope_id,espe_id,rspe_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Resultado';
        if (!is_null($pep->espe_id)) {
            $combo->sql = 'SELECT rspe_id,rspe_nombre,ple_id,ope_id,espe_id FROM cat_ResultadoPe WHERE ple_id = ? AND ope_id = ? AND  espe_id = ?';
            $combo->params = [$pep->ple_id, $pep->ope_id, $pep->espe_id];
            if (isset($pep->lectura) && $pep->lectura) {
                $combo->sql .= ' AND rspe_id = ?;';
                array_push($combo->params, $pep->rspe_id);
                $combo->detonar = true;
            }
            if (isset($pep->rspe_id)) {
                $combo->selecciona = $pep->rspe_id;
                $combo->detonar = true;
                $_SESSION[_S]['pep']->rsp = $pep->rspe_id;
            }
            if (isset($pep->disabled)) {
                $combo->disabled = $pep->disabled;
            }
            $resultado = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pep']->rsp = $cs->comboValorUnico;
            }
        } else {
            $resultado = $cs->templateCombo($combo);
        }

        return '
        <div id="row">
            <legend class="col-md-12 col-xs-12">Plan Especial</legend>
                ' . $plan . '
                ' . $objetivo . '

        </div>' . '
        <script language="javascript" >

            $("#ple_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/Pep.php",{
                        metodoPep:"cambioPla",
                        ple_id:$(this).val()
                    });
                }
            });
            $("#ope_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/Pep.php",{
                        metodoPep:"cambioOpe",
                        ple_id:$(this).find("option:selected").attr("ple_id"),
                        ope_id:$(this).val()
                    });
                }
            });
            /*
            $("#espe_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/Pep.php",{
                        metodoPep:"cambioEsp",
                        ple_id:$(this).find("option:selected").attr("ple_id"),
                        ope_id:$(this).find("option:selected").attr("ope_id"),
                        espe_id:$(this).val()
                    });
                }
            });
            $("#rspe_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/Pep.php",{
                        metodoPep:"cambioRsp",
                        ple_id:$(this).find("option:selected").attr("ple_id"),
                        ope_id:$(this).find("option:selected").attr("ope_id"),
                        espe_id:$(this).find("option:selected").attr("espe_id"),
                        rspe_id:$(this).val()
                    });
                }
            });
            */
            ' . $cs->getScript() . '
        </script>';
    }

    private function cargaOpe()
    {
        $_SESSION[_S]['pep']->ple = $this->prm->ple_id;
        $obt = new stdClass();
        $obt->id = 'ope_id';
        // $obt->detonar = false;
        if (isset($this->prm->ope_id) && $this->prm->ope_id != '') {
            $obt->selecciona = $this->prm->ple_id;
        }
        $obt->sql = 'SELECT ope_id,ope_nombre,ple_id FROM cat_ObjetivoPe WHERE ple_id = ?;';
        $obt->params = [$_SESSION[_S]['pep']->ple];
        $obt->comoAtr = 'ple_id,ope_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pep']->ope = $this->comboValorUnico;
        }
    }

    private function cargaEsp()
    {
        $_SESSION[_S]['pep']->ope = $this->prm->ope_id;
        $obt = new stdClass();
        $obt->id = 'espe_id';
        // $obt->detonar = false;
        if (isset($this->prm->espe_id) && $this->prm->espe_id != '') {
            $obt->selecciona = $this->prm->ope_id;
        }
        $obt->sql = 'SELECT espe_id,espe_nombre,ple_id,ope_id FROM cat_EstrategiaPe WHERE ple_id = ? AND ope_id = ? ;';
        $obt->params = [$_SESSION[_S]['pep']->ple, $_SESSION[_S]['pep']->ope];
        $obt->comoAtr = 'ple_id,ope_id,espe_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pep']->esp = $this->comboValorUnico;
        }
    }

    private function cargaRsp()
    {
        $_SESSION[_S]['pep']->esp = $this->prm->espe_id;
        $obt = new stdClass();
        $obt->id = 'rspe_id';
        // $obt->detonar = false;
        if (isset($this->prm->rspe_id) && $this->prm->rspe_id != '') {
            $obt->selecciona = $this->prm->espe_id;
        }
        $obt->sql = 'SELECT rspe_id,rspe_nombre,ple_id,ope_id,espe_id FROM cat_ResultadoPe WHERE ple_id = ? AND ope_id = ? AND espe_id = ?;';
        $obt->params = [$_SESSION[_S]['pep']->ple, $_SESSION[_S]['pep']->ope, $_SESSION[_S]['pep']->esp];
        $obt->comoAtr = 'ple_id,ope_id,espe_id,rspe_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pep']->rsp = $this->comboValorUnico;
        }
    }

    private function cambioRsp()
    {
        $_SESSION[_S]['pep']->rsp = $this->prm->rspe_id;
        $this->setSql('SELECT epc_id FROM enc_Pep WHERE ope_id= ? AND espe_id= ? AND rspe_id= ?;');
        $parametros = [$_SESSION[_S]['pep']->ope, $_SESSION[_S]['pep']->esp, $_SESSION[_S]['pep']->rsp];
        $this->conectar();
        $this->consultar($parametros);
        $pep = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['pep']->epc = $pep['epc_id'];
    }
}
//-------------------------------//
if (isset($_POST['metodoPep'])) {
    new Pep();
}
//---------------------------------//
