<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';

if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class PedObjetivo extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        if (isset($this->prm->metodoPed)) {
            $metodo = (isset($this->prm->metodoPed)) ? $this->prm->metodoPed : '';
            switch ($metodo) {
                case 'cambioEje':
                    $this->cargaTem();
                    break;
                case 'cambioTem':
                    $this->cargaObj();
                    break;
                case 'cambioObj':
                    $this->cambioObj();
                    break;
                case 'cambioODS':
                    $this->cargaODS();
                    break;
            }
            $this->returnJson();
        }
    }

    public function comboSeleccionaPed()
    {
        $this->cargaTem();
        $this->cargaObj();
    }

    public static function ponPed()
    {
        $ped = (
            func_num_args() > 0 ?
            (is_object(func_get_arg(0)) ?
            func_get_arg(0) : (is_array(func_get_arg(0)) ?
                (object) func_get_arg(0) : null))
        : null
        );

        $form = '';

        $_SESSION[_S]['ped'] = new stdClass();
        $cf = new WebResponse();
        $preseleccion = '';
        $required = true;

        if (isset($ped->form)) {
            $form = $ped->form;
            $required = isset($ped->required);
        }

        $cs = new WebResponse();

        if (!empty($ped->epo_id)) {

            $sql = 'SELECT eje_id, tem_id,obj_id, epo_id FROM enc_PedObjetivo WHERE epo_id = :epo';
            $parametros = [':epo' => $ped->epo_id];
            $cf->setSql($sql);
            $cf->conectar();
            $cf->consultar($parametros);
            $cf->desconectar();
            $cf = $cf->extraer();
            $ped->eje_id = $cf['eje_id'];
            $ped->tem_id = $cf['tem_id'];
            $ped->obj_id = $cf['obj_id'];
            $_SESSION[_S]['ped']->epo = $ped->epo_id;
        }

        #combo eje
        $combo = new stdClass();
        $combo->id = 'eje_id';
        $combo->label = 'Eje';
        $combo->mensaje = 'Seleccionar un Eje';

        if ($form != '') {
            $combo->form = $form;
        }

        $combo->enDiv = true;
        $combo->required = $required;
        $combo->horizontal = 3;
        $combo->sql = 'SELECT eje_id,eje_nombre FROM cat_Eje';

        if (isset($ped->lectura) && $ped->lectura && !empty($ped->eje_id)) {
            $combo->sql .= ' WHERE eje_id = ?;';
            $combo->params = [$ped->eje_id];
            $combo->detonar = false;
        }

        if (isset($ped->eje_id)) {
            $combo->selecciona = $ped->eje_id;
            $_SESSION[_S]['ped']->eje = $ped->eje_id;
            $combo->detonar = false;
        }

        if (isset($ped->disabled)) {
            $combo->disabled = $ped->disabled;
        }

        $eje = $cs->ponCombo($combo);

        if (!is_null($cs->comboValorUnico)) {
            $_SESSION[_S]['ped']->eje = $cs->comboValorUnico;
        }

        #combo Tema
        $combo = new stdClass();
        $combo->id = 'tem_id';
        $combo->label = 'Tema';
        $combo->comoAtr = 'eje_id,tem_id,spe_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;

        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Tema';

        if (!empty($ped->eje_id)) {

            $combo->sql = 'SELECT tem_id,tem_nombre,eje_id FROM cat_Tema WHERE eje_id = ?';
            $params = [$ped->eje_id];
            if (isset($ped->lectura) && $ped->lectura) {
                $combo->sql .= ' AND tem_id = ?;';
                array_push($params, $ped->tem_id);
                $combo->detonar = false;
            }

            if (isset($ped->tem_id)) {
                $combo->selecciona = $ped->tem_id;
                $_SESSION[_S]['ped']->tem = $ped->tem_id;
                $combo->detonar = false;
            }

            if (isset($ped->disabled)) {
                $combo->disabled = $ped->disabled;
            }

            $combo->params = $params;
            $tema = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['ped']->tem = $cs->comboValorUnico;
            }
        } else {
            $tema = $cs->templateCombo($combo);
        }

        #combo Objetivo
        $combo = new stdClass();
        $combo->id = 'obj_id';
        $combo->label = 'Objetivo';
        $combo->comoAtr = 'eje_id,tem_id,obj_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Objetivo';

        if (!empty($ped->obj_id)) {
            $combo->sql = 'SELECT obj_id, obj_nombre,eje_id,tem_id FROM cat_Objetivo WHERE eje_id = ? AND  tem_id = ?';
            $params = [$ped->eje_id, $ped->tem_id];
            if (isset($ped->lectura) && $ped->lectura) {
                $combo->sql .= ' AND obj_id = ?;';
                $combo->detonar = false;
                array_push($params, $ped->obj_id);
            }

            if (isset($ped->obj_id)) {
                $combo->selecciona = $ped->obj_id;
                $_SESSION[_S]['ped']->obj = $ped->obj_id;
                $combo->detonar = false;
            }

            if (isset($ped->disabled)) {
                $combo->disabled = $ped->disabled;
            }
            $combo->params = $params;
            $objetivo = $cs->ponCombo($combo);

            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['ped']->obj = $cs->comboValorUnico;
            }
        } else {
            $objetivo = $cs->templateCombo($combo);
        }

        if (!empty($ped->epods_id)) {
            $cf = new WebResponse();
            $sql = 'SELECT epo_id,ods_id FROM enc_PedODS WHERE epods_id = :epods_id';
            $parametros = [':epods_id' => $ped->epods_id];
            $cf->conectar();
            $cf->setSql($sql);

            $cf->consultar($parametros);
            $cf->desconectar();
            $cf = $cf->extraer();
            $ped->ods_id = $cf['ods_id'];
            $_SESSION[_S]['ped']->epods_id = $ped->epods_id;
        }
        ##COMBO OSD // $ped->epo_id;
        $combo = new stdClass();
        $combo->id = 'ods_id';
        $combo->label = 'Vinculación ODS';
        $combo->comoAtr = 'ods_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un ODS';

        if (!empty($ped->obj_id)) {
            $combo->sql = 'SELECT ods_id, ods_nombre
                FROM cat_ODS 
                WHERE ods_id = ?;';
            $params = [$ped->ods_id];
            $combo->params = $params;
            if (isset($ped->lectura) && $ped->lectura) {
                $combo->detonar = false;
            }

            if (isset($ped->ods_id)) {
                $combo->selecciona = $ped->ods_id;
                $_SESSION[_S]['ped']->epods_id = $ped->epods_id;
                $combo->detonar = false;
            }

            if (isset($ped->disabled)) {
                $combo->disabled = $ped->disabled;
            }
            $ods = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['ped']->ods = $cs->comboValorUnico;
            }
        } else {
            $ods = $cs->templateCombo($combo);
        }


        return '
            <div id="row">
                <legend class="col-md-12 col-xs-12">PED</legend>
                    ' . $eje . '
                    ' . $tema . '
                    ' . $objetivo . '
                    <legend class="col-md-12 col-xs-12">ODS</legend>
                    ' . $ods . '
            </div>' . '
                <script language="javascript" >
                    $("#eje_id").change(function(){
                        if($(this).val()!=""){
                            comunica("srv/PedObjetivo.php",{
                                metodoPed:"cambioEje",
                                eje_id:$(this).val()
                            });
                        }
                    });
                    $("#tem_id").change(function(){
                        if($(this).val()!=""){
                            comunica("srv/PedObjetivo.php",{
                                metodoPed:"cambioTem",
                                eje_id:$(this).find("option:selected").attr("eje_id"),
                                tem_id:$(this).val()
                            });
                        }
                    });
                    $("#obj_id").change(function(){
                        if($(this).val()!=""){
                            comunica("srv/PedObjetivo.php",{
                                metodoPed:"cambioObj",
                                eje_id:$(this).find("option:selected").attr("eje_id"),
                                tem_id:$(this).find("option:selected").attr("tem_id"),
                                obj_id:$(this).val()
                            });
                        }
                    });

                    ' . $cs->getScript() . '
                    ' . $preseleccion . '
                </script>';
    }
    private function cargaTem()
    {

        $_SESSION[_S]['ped']->eje = $this->prm->eje_id;
        $obt = new stdClass();
        $obt->id = 'tem_id';

        if (isset($this->prm->tem_id) && $this->prm->tem_id != '') {
            $obt->selecciona = $this->prm->tem_id;
        }

        $obt->sql = 'SELECT tem_id,tem_nombre,eje_id FROM cat_Tema WHERE eje_id = ?';
        $obt->params = [$_SESSION[_S]['ped']->eje];
        $obt->comoAtr = 'eje_id,tem_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->tem = $this->comboValorUnico;
        }
        $this->cargaObj();
    }

    private function cargaObj()
    {
        $_SESSION[_S]['ped']->obj = (empty($this->prm->obj_id)) ? 0 : $this->prm->obj_id;
        $_SESSION[_S]['ped']->tem = (empty($this->prm->tem_id)) ? 0 : $this->prm->tem_id;
        $_SESSION[_S]['pes']->spe_id = (empty($this->prm->spe_id)) ? 0 : $this->prm->spe_id;

        $obt = new stdClass();
        $obt->id = 'obj_id';

        if (isset($this->prm->obj_id) && $this->prm->obj_id != '') {
            $obt->selecciona = $this->prm->obj_id;
        }

        $obt->sql = 'SELECT obj_id,obj_nombre,eje_id,tem_id FROM cat_Objetivo WHERE eje_id = ? AND tem_id = ?;';
        $obt->params = [$_SESSION[_S]['ped']->eje, $_SESSION[_S]['ped']->tem];
        $obt->comoAtr = 'eje_id,tem_id,obj_id';

        $this->recargaCombo($obt);

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->obj = $this->comboValorUnico;
        }
    }
    private function cambioObj()
    {
        $_SESSION[_S]['ped']->obj = $this->prm->obj_id;
        $this->setSql('SELECT epo_id FROM enc_PedObjetivo WHERE tem_id = ? AND eje_id = ? AND obj_id = ?;');
        $this->conectar();
        $this->consultar([$_SESSION[_S]['ped']->tem, $_SESSION[_S]['ped']->eje, $_SESSION[_S]['ped']->obj]);
        $ped = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['ped']->epo = $ped['epo_id'];
        $this->cargaODS();
    }
    private function cargaODS()
    {
        $this->setSql('SELECT epods_id, epo_id, ods_id FROM enc_PedODS WHERE epo_id = ? LIMIT 1');
        $this->conectar();
        $this->consultar([$_SESSION[_S]['ped']->epo]);
        $ped = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['ped']->epods_id = $ped['epods_id'];
        $obt = new stdClass();
        $obt->id = 'ods_id';

        if (isset($this->prm->tem_id) && $this->prm->tem_id != '') {
            $obt->selecciona = $ped['ods_id'];
        }

        $obt->sql = 'SELECT ods_id, ods_nombre FROM cat_ODS WHERE ods_id = ?';
        $obt->params = [$ped['ods_id']];
        $obt->comoAtr = 'ods_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->ods = $this->comboValorUnico;
        }
    }
}
if (isset($_POST['metodoPed'])) {
    new PedObjetivo();
}
