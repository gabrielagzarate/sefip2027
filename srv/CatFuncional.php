<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class CatalogoFuncional extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        $metodo = (isset($this->prm->metodoCatFuncional)) ? $this->prm->metodoCatFuncional : '';
        switch ($metodo) {
            case 'cambioFin':
                $this->cargaFun();
                break;
            case 'cambioFun':
                $this->cargaSfu();
                break;
            case 'cambioSfu':
                $this->cambioSfu();
                break;
        }
        $this->returnJson();
    }

    public static function ponCatalogoFuncional()
    {
        $catFun = ((func_num_args() > 0) ?
            (((is_object(func_get_arg(0))) ?
                func_get_arg(0)
                : (is_array(func_get_arg(0)))) ?
                (object) func_get_arg(0)
                : null)
            : null);

        $form = '';
        $required = true;
        if (!empty($catFun->form)) {
            $form = $catFun->form;
            $required = isset($catFun->required);
        }
        $_SESSION[_S]['catfuncional'] = new stdClass();
        $cf = new WebResponse();

        if (!empty($catFun->efn_id)) {
            $cf->conectar();
            $parametros = [$catFun->efn_id];
            $cf->setSql('SELECT fin_id,fun_id,sfu_id FROM enc_Funcional WHERE efn_id = ?;');
            $cf->consultar($parametros);
            $datos = $cf->extraer();
            $cf->desconectar();
            $catFun->fin_id = $datos['fin_id'];
            $catFun->fun_id = $datos['fun_id'];
            $catFun->sfu_id = $datos['sfu_id'];
            $_SESSION[_S]['catfuncional']->efn = $catFun->efn_id;
        } elseif (isset($catFun->lectura) && $catFun->lectura) {
            $catFun->fin_id = 'null';
            $catFun->fun_id = 'null';
            $catFun->sfu_id = 'null';
        }

        $combo = new stdClass();
        $combo->id = 'fin_id';
        $combo->sql = 'SELECT fin_id,fin_nombre FROM cat_Finalidad';
        if (isset($catFun->lectura) && $catFun->lectura && !empty($catFun->fin_id)) {
            $combo->sql .= ' WHERE fin_id = ?;';
            $combo->params = [$catFun->fin_id];
            $combo->detonar = false;
        }
        $combo->label = 'Finalidad';
        if (isset($catFun->fin_id)) {
            $combo->selecciona = $catFun->fin_id;
            $_SESSION[_S]['catfuncional']->fin = $catFun->fin_id;
            $combo->detonar = false;
        }
        $combo->enDiv = true;
        if ($form != '') {
            $combo->form = 'Seleccionar una Función';
        }

        $combo->required = $required;
        $combo->horizontal = 3;
        $combo->mensaje = 'Seleccionar una Función';
        if (isset($catFun->disabled)) {
            $combo->disabled = $catFun->disabled;
        }

        $finalidad = $cf->ponCombo($combo);
        $combo = new stdClass();
        $combo->id = 'fun_id';
        $combo->label = 'Función';
        $combo->comoAtr = 'fin_id,fun_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = 'Seleccionar una Función';
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Función';
        if (!empty($catFun->fin_id)) {
            $combo->sql = 'SELECT fun_id,fun_nombre,fin_id FROM cat_Funcion WHERE fin_id = ' . $catFun->fin_id . '';
            if (isset($catFun->lectura) && $catFun->lectura) {
                $combo->sql .= ' AND fun_id = ?;';
                $combo->params = [$catFun->fun_id];
                $combo->detonar = false;
            }
            if (isset($catFun->fun_id)) {
                $combo->selecciona = $catFun->fun_id;
                $_SESSION[_S]['catfuncional']->fun = $catFun->fun_id;
                $combo->detonar = false;
            }
            if (isset($catFun->disabled)) {
                $combo->disabled = $catFun->disabled;
            }

            $funcion = $cf->ponCombo($combo);
        } else {
            $funcion = $cf->templateCombo($combo);
        }

        $combo = new stdClass();
        $combo->id = 'sfu_id';
        $combo->label = 'Subfunción';
        $combo->comoAtr = 'fin_id,fun_id,sfu_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = 'Seleccionar una Función';
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Función';
        $combo->horizontal = 3;

        if (!empty($catFun->fin_id) && !empty($catFun->fun_id)) {
            $combo->sql = 'SELECT sfu_id,sfu_nombre,fun_id,fin_id FROM cat_Subfuncion WHERE fin_id=' . $catFun->fin_id . ' AND fun_id=' . $catFun->fun_id . '';
            if (isset($catFun->lectura) && $catFun->lectura) {
                $combo->sql .= ' AND sfu_id = ?;';
                $combo->params = [$catFun->sfu_id];
            }
            if (!empty($catFun->sfu_id)) {
                $combo->selecciona = $catFun->sfu_id;
                $_SESSION[_S]['catfuncional']->sfu = $catFun->sfu_id;
            }
            if (isset($catFun->disabled)) {
                $combo->disabled = $catFun->disabled;
            }

            $subfuncion = $cf->ponCombo($combo);
        } else {
            $subfuncion = $cf->templateCombo($combo);
        }
        return '
            <div class="row">
                <legend class="col-md-12 col-xs-12">Clasificación Funcional</legend>
                    ' . $finalidad . '
                    ' . $funcion . '
                    ' . $subfuncion . '
            </div>
            <script language="javascript" >
                $("#fin_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/CatalogoFuncional.php",{
                            metodoCatFuncional:"cambioFin",
                            fin_id:$(this).val()
                        });
                    }
                });
                $("#fun_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/CatalogoFuncional.php",{
                            metodoCatFuncional:"cambioFun",
                            fin_id:$(this).find("option:selected").attr("fin_id"),
                            fun_id:$(this).find("option:selected").attr("fun_id")
                        });
                    }
                });
                $("#sfu_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/CatalogoFuncional.php",{
                            metodoCatFuncional:"cambioSfu",
                            fin_id:$(this).find("option:selected").attr("fin_id"),
                            fun_id:$(this).find("option:selected").attr("fun_id"),
                            sfu_id:$(this).find("option:selected").attr("sfu_id")
                        });
                    }
                });
                ' . $cf->getScript() . '
            </script>
        </div>';
    }

    private function cargaFun()
    {
        $_SESSION[_S]['catfuncional']->fin = $this->prm->fin_id;
        $this->prm->fun_id = 0;
        $fun = new stdClass();
        $fun->id = 'fun_id';
        $fun->sql = 'SELECT fun_id,fun_nombre,fin_id FROM cat_Funcion WHERE fin_id= ?;';
        $fun->comoAtr = 'fin_id,fun_id';
        $fun->params = [$this->prm->fin_id];
        $this->recargaCombo($fun);
        if (!empty($this->comboValorUnico)) {
            $this->prm->fun_id = $this->comboValorUnico;
        }

        $this->cargaSfu($fun);
    }

    private function cargaSfu()
    {
        if (isset($this->prm->fun_id)) {
            $_SESSION[_S]['catfuncional']->fun = $this->prm->fun_id;
            $_SESSION[_S]['catfuncional']->sfu = 0;
            $fun = new stdClass();
            $fun->id = 'sfu_id';
            $fun->sql = 'SELECT sfu_id,sfu_nombre,fun_id,fin_id FROM cat_Subfuncion WHERE fin_id= ? AND fun_id= ?;';
            $fun->params = [$this->prm->fin_id, $this->prm->fun_id];
            $fun->comoAtr = 'fin_id,fun_id,sfu_id';

            $this->recargaCombo($fun);
        }
    }

    private function cambioSfu()
    {
        if (isset($this->prm->sfu_id)) {
            $_SESSION[_S]['catfuncional']->sfu = $this->prm->sfu_id;
            $parametros = [
                ':fin' => $_SESSION[_S]['catfuncional']->fin,
                ':fun' => $_SESSION[_S]['catfuncional']->fun,
                ':sfu' => $_SESSION[_S]['catfuncional']->sfu,
            ];
            $this->setSql('SELECT efn_id FROM enc_Funcional WHERE fin_id = :fin AND fun_id = :fun AND  sfu_id = :sfu;');
            $this->conectar();
            $this->consultar($parametros);
            $funcional = $this->extraer();
            $this->desconectar();
            $_SESSION[_S]['catfuncional']->efn = $funcional['efn_id'];
        }
    }
}
//-------------------------------//
if (isset($_POST['metodoCatFuncional'])) {
    new CatalogoFuncional();
}
//---------------------------------//
