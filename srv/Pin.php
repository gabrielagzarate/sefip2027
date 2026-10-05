<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class Pin extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        if (isset($this->prm->metodoPin)) {
            $metodo = (isset($this->prm->metodoPin)) ? $this->prm->metodoPin : '';
            switch ($metodo) {
                case 'cambioPi':
                    $this->cargaObjpi();
                    break;
                case 'cambioObjpi':
                    $this->cambioObjpi();
                    break;
                case 'cambioObjpi2':
                    $this->cambioObjpi2();
                    break; //Renombrar para facilitar lectura
            }
            $this->returnJson();
        }
    }

    public static function ponPin()
    {
        $pin = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        $form = '';
        $cf = new WebResponse();
        $preseleccion = '';

        $required = true;
        if (isset($pin->form)) {
            $form = $pin->form;
            $required = isset($pin->required);
        }

        $ponTemplate = (isset($pin->ponTemplate) && is_bool($pin->ponTemplate)) ? $pin->ponTemplate : false;

        $_SESSION[_S]['pin'] = new stdClass();

        $cs = new WebResponse();
        if (!empty($pin->epi_id)) {
            $sql = 'SELECT pi_id, objpi_id FROM enc_PlanInstitucional WHERE epi_id = ?';
            $parametros = [$pin->epi_id];
            $cf->setSql($sql);
            $cf->conectar();
            $cf->consultar($parametros);
            $cf->desconectar();
            $cf = $cf->extraer();
            $pin->pi_id = $cf['pi_id'];
            $pin->objpi_id = $cf['objpi_id'];
            $_SESSION[_S]['pin']->epi = $pin->epi_id;
        }

        #combo Plan Institucional
        $combo = new stdClass();
        $combo->id = 'pi_id';
        $combo->label = 'Plan';
        $combo->mensaje = 'Seleccionar un Plan';
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->enDiv = true;
        $combo->required = $required;
        $combo->horizontal = 0;
        $combo->sql = 'SELECT pi_id, pi_nombre FROM cat_PlanInstitucional';
        if (isset($pin->lectura) && $pin->lectura && !empty($pin->pi_id)) {
            $combo->sql .= ' WHERE pi_id = ?;';
            $combo->params = [$pin->pi_id];
            $combo->detonar = false;
        }
        if (isset($pin->pi_id)) {
            $combo->selecciona = $pin->pi_id;
            $_SESSION[_S]['pin']->pi = $pin->pi_id;
            $combo->detonar = false;
        }

        if (isset($pin->disabled)) {
            $combo->disabled = $pin->disabled;
        }
        $plan = $cs->ponCombo($combo);

        if (!is_null($cs->comboValorUnico)) {
            $_SESSION[_S]['pin']->pi = $cs->comboValorUnico;
        }

        #combo Objetivo PI
        $combo = new stdClass();
        $combo->id = 'objpi_id';
        $combo->label = 'Objetivo PI';
        $combo->comoAtr = 'objpi_id, pi_id';
        $combo->enDiv = true;
        $combo->horizontal = 0;
        if ($form != '') {
            $combo->form = $form;
        }
        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Objetivo';
        if (!is_null($pin->objpi_id)) {
            $combo->sql = 'SELECT objpi_id, descripcion FROM cat_ObjetivoPI WHERE pi_id = ? AND objpi_id = ?';
            $combo->params = [$pin->pi_id, $pin->objpi_id];
            if (isset($pin->lectura) && $pin->lectura) {
                $combo->sql .= ' AND objpi_id = ?;';
                array_push($combo->params, $pin->objpi_id);
                $combo->detonar = true;
            }
            if (isset($pin->objpi_id)) {
                $combo->selecciona = $pin->objpi_id;
                $combo->detonar = true;
                $_SESSION[_S]['pin']->objpi = $pin->objpi_id;
            }
            if (isset($pin->disabled)) {
                $combo->disabled = $pin->disabled;
            }
            $objetivo = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pin']->objpi = $cs->comboValorUnico;
            }
        } else {
            $objetivo = $cs->templateCombo($combo);
        }


        return '
        <div id="row">
            <legend class="col-md-12 col-xs-12">Plan Institucional</legend>
                ' . $plan . '
                ' . $objetivo . '
        </div>' . '
            <script language="javascript" >
                $("#pi_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/Pin.php",{
                            metodoPin:"cambioPi",
                            pi_id:$(this).val()
                        });
                    }
                });
                $("#objpi_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/Pin.php",{
                            metodoPin:"cambioObjpi2",
                            pi_id:$(this).find("option:selected").attr("pi_id"),
                            objpi_id:$(this).val()
                        });
                    }
                });
                ' . $cs->getScript() . '
                ' . $preseleccion . '
            </script>';
    }

    private function cargaObjpi()
    {
        $_SESSION[_S]['pin']->pi = $this->prm->pi_id;
        $obt = new stdClass();
        $obt->id = 'objpi_id';

        if (isset($this->prm->objpi_id) && $this->prm->objpi_id != '') {
            $obt->selecciona = $this->prm->objpi_id;
        }

        $obt->sql = 'SELECT objpi_id,descripcion,pi_id FROM cat_ObjetivoPI WHERE pi_id = ?';
        $obt->comoAtr = 'pi_id,objpi_id';
        $obt->params = [$_SESSION[_S]['pin']->pi];
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pin']->objpi = $this->comboValorUnico;
        }
        $this->cambioObjpi();
    }

    private function cambioObjpi()
    {
        $_SESSION[_S]['pin']->objpi = (empty($this->prm->objpi_id)) ? 0 : $this->prm->objpi_id;
        $_SESSION[_S]['pin']->pi = (empty($this->prm->pi_id)) ? 0 : $this->prm->pi_id;

        $obt = new stdClass();
        $obt->id = 'objpi_id';

        if (isset($this->prm->objpi_id) && $this->prm->objpi_id != '') {
            $obt->selecciona = $this->prm->objpi_id;
        }
        $obt->sql = 'SELECT objpi_id,descripcion,pi_id FROM cat_ObjetivoPI WHERE pi_id = ?;';
        $obt->params = [$_SESSION[_S]['pin']->pi];
        $obt->comoAtr = 'pi_id,objpi_id';
        $this->recargaCombo($obt);

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pin']->objpi = $this->comboValorUnico;
        }
    }

    private function cambioObjpi2()
    {
        $_SESSION[_S]['pin']->objpi = $this->prm->objpi_id;
        $this->setSql('SELECT epi_id FROM enc_PlanInstitucional WHERE pi_id = ? AND objpi_id = ?');
        $parametros = [$_SESSION[_S]['pin']->pi, $_SESSION[_S]['pin']->objpi];
        $this->conectar();
        $this->consultar($parametros);
        $pin = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['pin']->epi = $pin['epi_id'];
    }
}
//-------------------------------//
if (isset($_POST['metodoPin'])) {
    new Pin();
}
//---------------------------------//
