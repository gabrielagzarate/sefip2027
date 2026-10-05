<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class Ped extends WebResponse
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
                    $this->cargaODS();
                    $this->cargaEst();
                    break;
                case 'cambioODS':
                    $this->cargaODS();
                    break;
                case 'cambioEst':
                    $this->cargaLac();
                    break;
                case 'cambioLac':
                    $this->cambioLac();
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
        $ped = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $form = '';
        $cf = new WebResponse();
        $preseleccion = '';
        $_SESSION[_S]['ped'] = new stdClass();

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

        if (!empty($ped->epe_id)) {
            $cf->conectar();
            $parametros = [$ped->epe_id];
            $cf->setSql('SELECT eje_id, tem_id, obj_id, est_id, lac_id FROM enc_Ped WHERE epe_id = ?;');
            $cf->consultar($parametros);
            $datos = $cf->extraer();
            $cf->desconectar();
            $ped->eje_id = $datos['eje_id'];
            $ped->tem_id = $datos['tem_id'];
            $ped->obj_id = $datos['obj_id'];
            $ped->est_id = $datos['est_id'];
            $ped->lac_id = $datos['lac_id'];
            $_SESSION[_S]['ped']->epe = $ped->epe_id;
        }

        $required = true;
        if (isset($ped->form)) {
            $form = $ped->form;
            $required = isset($ped->required);
        }

        $cs = new WebResponse();
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
        if ((isset($ped->lectura) && $ped->lectura && !empty($ped->eje_id)) || (isset($ped->lecturapr) && $ped->lecturapr)) {
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

        if (isset($ped->lecturapr) && $ped->lecturapr) {
            $combo->disabled = 'disabled';
        }
        $eje = $cs->ponCombo($combo);
        if (!is_null($cs->comboValorUnico)) {
            $_SESSION[_S]['ped']->eje = $cs->comboValorUnico;
        }

        #combo Tema
        $combo = new stdClass();
        $combo->id = 'tem_id';
        $combo->label = 'Tema';
        $combo->comoAtr = 'eje_id,tem_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Tema';
        if (!empty($ped->eje_id)) {
            $combo->sql = 'SELECT tem_id,tem_nombre,eje_id FROM cat_Tema WHERE eje_id = ' . $ped->eje_id . '';
            if ((isset($ped->lectura) && $ped->lectura) || (isset($ped->lecturapr) && $ped->lecturapr)) {
                $combo->sql .= ' AND tem_id = ?;';
                $combo->params = [$ped->tem_id];
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

            if (isset($ped->lecturapr) && $ped->lecturapr) {

                $combo->disabled = 'disabled';
            }

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
        $parametros = [];
        if (!empty($ped->obj_id)) {
            $combo->sql = 'SELECT obj_id, obj_nombre,eje_id,tem_id FROM cat_Objetivo WHERE eje_id = :eje AND  tem_id = :tem ';
            $parametros['eje'] = $ped->eje_id;
            $parametros['tem'] = $ped->tem_id;
            if ((isset($ped->lectura) && $ped->lectura) || (isset($ped->lecturapr) && $ped->lecturapr)) {
                $combo->sql .= ' AND obj_id = :obj ;';
                $parametros['obj'] = $ped->obj_id;
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($ped->obj_id)) {
                $combo->selecciona = $ped->obj_id;
                $_SESSION[_S]['ped']->obj = $ped->obj_id;
                $combo->detonar = false;
            }

            if (isset($ped->disabled)) {
                $combo->disabled = $ped->disabled;
            }

            if (isset($ped->lecturapr) && $ped->lecturapr) {

                $combo->disabled = 'disabled';
            }
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

        ##COMBO ODS
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
            $combo->sql = "SELECT ods_id, ods_nombre
                FROM cat_ODS
                WHERE ods_id = '{$ped->ods_id}';";
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

        #combo Estrategia
        $combo = new stdClass();
        $combo->id = 'est_id';
        $combo->label = 'Estrategia';
        $combo->comoAtr = 'eje_id,tem_id,obj_id,est_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;

        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Estrategia';
        $parametros = [];
        if (!empty($ped->obj_id)) {
            $combo->sql = 'SELECT est_id,est_nombre,eje_id,tem_id,obj_id FROM cat_Estrategia WHERE eje_id = :eje AND tem_id= :tem AND  obj_id = :obj ';
            $parametros['eje'] = $ped->eje_id;
            $parametros['tem'] = $ped->tem_id;
            $parametros['obj'] = $ped->obj_id;
            if (isset($ped->lectura) && $ped->lectura && isset($ped->est_id)) {
                $combo->sql .= ' AND est_id = :est;';
                $parametros['est'] = $ped->est_id;
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($ped->est_id)) {
                $combo->selecciona = $ped->est_id;
                $_SESSION[_S]['ped']->est = $ped->est_id;
                $combo->detonar = false;
            }

            if (isset($ped->disabled)) {
                $combo->disabled = $ped->disabled;
            }

            $estrategia = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['ped']->est = $cs->comboValorUnico;
            }
        } else {
            $estrategia = $cs->templateCombo($combo);
        }

        #combo Linea Accion
        $combo = new stdClass();
        $combo->id = 'lac_id';
        $combo->label = 'Líneas de Acción';
        $combo->comoAtr = 'eje_id,tem_id,obj_id,est_id,lac_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Línea de Acción';
        $parametros = [];
        if (!empty($ped->est_id)) {
            $combo->sql = 'SELECT lac_id,lac_nombre,eje_id,tem_id,obj_id,est_id FROM cat_LineaAccion WHERE eje_id = :eje AND tem_id= :tem AND  obj_id = :obj AND est_id = :est ';
            $parametros['eje'] = $ped->eje_id;
            $parametros['tem'] = $ped->tem_id;
            $parametros['obj'] = $ped->obj_id;
            $parametros['est'] = $ped->est_id;
            if (isset($ped->lectura) && $ped->lectura && isset($ped->lac_id)) {
                $combo->sql .= ' AND lac_id = :lac;';
                $parametros['lac'] = $ped->lac_id;
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($ped->lac_id)) {
                $combo->selecciona = $ped->lac_id;
                $_SESSION[_S]['ped']->lac_id = $ped->lac_id;
                $combo->detonar = false;
            }

            if (isset($ped->disabled)) {
                $combo->disabled = $ped->disabled;
            }

            $lineaaccion = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['ped']->lac = $cs->comboValorUnico;
            }

        } else {
            $lineaaccion = $cs->templateCombo($combo);
        }

        return '
        <div id="row">
            <legend class="col-md-12 col-xs-12">PED</legend>
                ' . $eje . '
                ' . $tema . '
                ' . $objetivo . '
                ' . $estrategia . '
                ' . $lineaaccion . '
            <legend class="col-md-12 col-xs-12">ODS</legend>
                ' . $ods . '

        </div>' . '
        <script language="javascript" >
            $("#eje_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/Ped.php",{
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
                    comunica("srv/Ped.php",{
                        metodoPed:"cambioObj",
                        eje_id:$(this).find("option:selected").attr("eje_id"),
                        tem_id:$(this).find("option:selected").attr("tem_id"),
                        obj_id:$(this).val()
                    });
                }
            });
            $("#est_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/Ped.php",{
                        metodoPed:"cambioEst",
                        eje_id:$(this).find("option:selected").attr("eje_id"),
                        tem_id:$(this).find("option:selected").attr("tem_id"),
                        obj_id:$(this).find("option:selected").attr("obj_id"),
                        est_id:$(this).val()
                    });
                }
            });
            $("#lac_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/Ped.php",{
                        metodoPed:"cambioLac",
                        eje_id:$(this).find("option:selected").attr("eje_id"),
                        tem_id:$(this).find("option:selected").attr("tem_id"),
                        obj_id:$(this).find("option:selected").attr("obj_id"),
                        est_id:$(this).find("option:selected").attr("est_id"),
                        lac_id:$(this).val()
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
        $obt->detonar = false;

        if (isset($this->prm->tem_id) && $this->prm->tem_id != '') {
            $obt->selecciona = $this->prm->tem_id;
        }

        $obt->sql = '
            SELECT tem_id,tem_nombre,eje_id
            FROM cat_Tema
            WHERE eje_id= ?';
        $obt->comoAtr = 'eje_id,tem_id';
        $obt->params = [$_SESSION[_S]['ped']->eje];
        $this->recargaCombo($obt);

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->tem = $this->comboValorUnico;
            $this->cargaObj();
        }
    }

    private function cargaObj()
    {
        $_SESSION[_S]['ped']->eje = $this->prm->eje_id;
        $obt = new stdClass();
        $obt->id = 'obj_id';

        if (isset($this->prm->obj_id) && $this->prm->obj_id != '') {
            $obt->selecciona = $this->prm->obj_id;
        }

        $obt->sql = '
            SELECT  obj_id,obj_nombre,eje_id,tem_id
            FROM cat_Objetivo
            WHERE eje_id=' . $_SESSION[_S]['ped']->eje;

        if (isset($_SESSION[_S]['ped']->tem) && $_SESSION[_S]['ped']->tem != '') {
            $obt->sql .= '  AND tem_id=' . $_SESSION[_S]['ped']->tem;
        }

        $obt->comoAtr = 'eje_id,tem_id,obj_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->obj = $this->comboValorUnico;
            $this->cargaEst();
        }
    }

    private function cargaODS()
    {
        $_SESSION[_S]['ped']->obj = $this->prm->obj_id;
        $params = [$_SESSION[_S]['ped']->tem, $_SESSION[_S]['ped']->eje, $_SESSION[_S]['ped']->obj];
        $this->setSql('SELECT epo_id
            FROM enc_PedObjetivo
            WHERE tem_id = ?
                AND eje_id = ?
                AND obj_id = ?;
        ');
        $this->conectar();
        $this->consultar($params);
        $ped = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['ped']->epo = $ped['epo_id'];

        $params = [$_SESSION[_S]['ped']->epo];
        $this->setSql('SELECT epods_id, epo_id, ods_id
        FROM enc_PedODS
        WHERE epo_id = ? LIMIT 1');
        $this->conectar();
        $this->consultar($params);
        $ped = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['ped']->epods_id = $ped['epods_id'];
        $obt = new stdClass();
        $obt->id = 'ods_id';

        if (isset($this->prm->tem_id) && $this->prm->tem_id != '') {
            $obt->selecciona = $ped['ods_id'];
        }

        $parametro = [$ped['ods_id']];
        $obt->sql = 'SELECT ods_id, ods_nombre
            FROM cat_ODS
            WHERE ods_id=?';
        $obt->comoAtr = 'ods_id';
        $obt->params = $parametro;
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->ods = $this->comboValorUnico;
        }

    }

    private function cargaEst()
    {
        $_SESSION[_S]['ped']->obj = (empty($this->prm->obj_id)) ? 0 : $this->prm->obj_id;
        $obt = new stdClass();
        $obt->id = 'est_id';
        $parametros = [];
        if (isset($this->prm->est_id) && $this->prm->est_id != '') {
            $obt->selecciona = $this->prm->est_id;
        }

        $obt->sql = '
            SELECT est_id,est_nombre,tem_id,eje_id,obj_id
            FROM cat_Estrategia
            WHERE eje_id= :eje ';
        $parametros['eje'] = $_SESSION[_S]['ped']->eje;
        if (isset($_SESSION[_S]['ped']->tem) && $_SESSION[_S]['ped']->tem != '') {
            $obt->sql .= '  AND tem_id= :tem';
            $parametros['tem'] = $_SESSION[_S]['ped']->tem;
        }

        if (isset($_SESSION[_S]['ped']->tem) && $_SESSION[_S]['ped']->tem != '') {
            $obt->sql .= '  AND obj_id= :obj';
            $parametros['obj'] = $_SESSION[_S]['ped']->obj;
        }
        $obt->comoAtr = 'eje_id,tem_id,obj_id,est_id';
        $obt->params = $parametros;
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->est = $this->comboValorUnico;
        }

        $this->cargaLac();
    }

    private function cargaLac()
    {
        $_SESSION[_S]['ped']->est = (empty($this->prm->est_id)) ? 0 : $this->prm->est_id;
        $obt = new stdClass();
        $obt->id = 'lac_id';
        if (isset($this->prm->lac_id) && $this->prm->lac_id != '') {
            $obt->selecciona = $this->prm->est_id;
        }

        $obt->sql = '
            SELECT  lac_id,lac_nombre,eje_id,tem_id,obj_id,est_id
            FROM cat_LineaAccion
            WHERE eje_id= ?
                AND tem_id= ?
                AND obj_id= ?
                AND est_id= ?
        ';
        $obt->params = [$_SESSION[_S]['ped']->eje, $_SESSION[_S]['ped']->tem, $_SESSION[_S]['ped']->obj, $_SESSION[_S]['ped']->est];
        $obt->comoAtr = 'eje_id,tem_id,obj_id,est_id,lac_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['ped']->lac = $this->comboValorUnico;
        }

    }

    private function cambioLac()
    {
        $_SESSION[_S]['ped']->lac = $this->prm->lac_id;
        $parametros = [$_SESSION[_S]['ped']->tem, $_SESSION[_S]['ped']->eje, $_SESSION[_S]['ped']->obj, $_SESSION[_S]['ped']->est, $_SESSION[_S]['ped']->lac];
        $this->setSql('
            SELECT epe_id
            FROM enc_Ped
            WHERE tem_id=? AND eje_id=? AND obj_id=? AND est_id=? AND lac_id= ?;
        ');
        $this->conectar();
        $this->consultar($parametros);
        $ped = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['ped']->epe = $ped['epe_id'];
    }
}
if (isset($_POST['metodoPed'])) {
    new Ped();
}
