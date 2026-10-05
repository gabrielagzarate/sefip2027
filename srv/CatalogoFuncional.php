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
        $catFun = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

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
            $cf->setSql('SELECT fin_id,fun_id,sfu_id FROM enc_Funcional WHERE efn_id = ?;');
            $cf->consultar([$catFun->efn_id]);
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
        } else {
            if ($catFun->prgs_id != 0) {
                $cf->conectar();
                $cf->setSql('SELECT fin_id FROM vin_Programa WHERE prgs_id = ?;');
                $cf->consultar([$catFun->prgs_id]);
                $datos = $cf->extraer();
                $cf->desconectar();
                $catFun->fin_id = $datos['fin_id'];
            }
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
            $_SESSION[_S]['catfuncional']->fin = $catFun->fin_id ?? $catFun->fin_id;
            $combo->detonar = false;
        } /*else {
            $combo->selecciona   = 1;
            $_SESSION[_S]['catfuncional']->fin = $catFun->fin_id;
        }*/
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
        if (!isset($catFun->fun_id)) {
            $finalidad = $cf->ponCombo($combo);
        }
        $combo = new stdClass();
        $combo->id = 'fun_id';
        $combo->label = 'Función';
        $combo->comoAtr = 'fin_id,fun_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;

        $_SESSION[_S]['catfuncional']->fin = $catFun->finid ?? $catFun->fin_id;
        if ($form != '') {
            $combo->form = 'Seleccionar una Función';
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Función';
        if (!empty($catFun->finid)) {
            $combo->sql = 'SELECT fun_id,fun_nombre,fin_id FROM cat_Funcion WHERE fin_id = ?';
            $combo->params = [$catFun->finid];

            if (isset($catFun->lectura) && $catFun->lectura) {
                $combo->sql .= ' AND fun_id = ' . $catFun->fun_id . ';';
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
            if ((isset($catFun->sprs_id))) {
                $funcion = $cf->ponCombo($combo); //Solo visible en subprogramas
            }
        } else {
            if ((isset($catFun->sprs_id))) {
                $funcion = $cf->templateCombo($combo); //Solo visible en subprogramas
            }
        }

        $combo = new stdClass();
        $combo->id = 'sfu_id';
        $combo->label = 'Subfunción';
        $combo->comoAtr = 'fin_id,fun_id,sfu_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = 'Seleccionar una SubFunción';
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una SubFunción';
        $combo->horizontal = 3;
        if (!empty($catFun->finid) && !empty($catFun->fun_id)) {
            $combo->sql = 'SELECT sfu_id,sfu_nombre,fun_id,fin_id FROM cat_Subfuncion WHERE fin_id = ? AND fun_id= ? ';
            $combo->params = [$catFun->finid, $catFun->fun_id];
            if (isset($catFun->lectura) && $catFun->lectura) {
                $combo->sql .= ' AND sfu_id = ' . $catFun->sfu_id . ';';
            }
            if (!empty($catFun->sfu_id)) {
                $combo->selecciona = $catFun->sfu_id;
                $catFun->sfu_id;
                $_SESSION[_S]['catfuncional']->sfu = $catFun->sfu_id;
            }
            if (isset($catFun->disabled)) {
                $combo->disabled = $catFun->disabled;
            }
            if ((isset($catFun->sprs_id))) {
                $subfuncion = $cf->ponCombo($combo); //Solo visible en subprogramas
            }
        } else {
            if ((isset($catFun->sprs_id))) {
                $subfuncion = $cf->templateCombo($combo); //Solo visible en subprogramas
            }
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
        ';
    }

    private function cargaFun()
    {
        $_SESSION[_S]['catfuncional']->fin = $this->prm->fin_id;
        $this->prm->fun_id = 0;
        $fun = new stdClass();
        $fun->id = 'fun_id';
        $fun->sql = 'SELECT fun_id,fun_nombre,fin_id FROM cat_Funcion WHERE fin_id = ?;';
        $fun->params = [$this->prm->fin_id];
        $fun->comoAtr = 'fin_id,fun_id';
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
            $_SESSION[_S]['catfuncional']->sfu = 1;
            $fun = new stdClass();
            $fun->id = 'sfu_id';
            $fun->sql = 'SELECT sfu_id,sfu_nombre,fun_id,fin_id FROM cat_Subfuncion WHERE fin_id = ? AND fun_id = ?;';
            $fun->params = [$this->prm->fin_id, $this->prm->fun_id];
            $fun->comoAtr = 'fin_id,fun_id,sfu_id';
            $this->recargaCombo($fun);
        }
    }

    private function cambioSfu()
    {
        if (isset($this->prm->sfu_id)) {
            $_SESSION[_S]['catfuncional']->sfu = $this->prm->sfu_id;
            $this->setSql('SELECT efn_id FROM enc_Funcional WHERE fin_id = ? AND fun_id = ? AND  sfu_id = ?;');
            $this->conectar();
            $this->consultar([$_SESSION[_S]['catfuncional']->fin, $_SESSION[_S]['catfuncional']->fun, $_SESSION[_S]['catfuncional']->sfu]);
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
