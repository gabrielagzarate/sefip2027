<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (! isset($_SESSION[_S])) {
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
                    $this->cargaLape();
                    break;
                case 'cambioLape':
                    $this->cambioLape();
                    break;
            }
            $this->returnJson();
        }
    }

    public static function ponPep()
    {
        $pep = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
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
        if (! empty($pep->epc_id)) {
            $sql = 'SELECT ple_id,ope_id,espe_id,lape_id FROM enc_Pep WHERE epc_id = :epc';
            $parametros = [':epc' => $pep->epc_id];
            $cf->setSql($sql);
            $cf->conectar();
            $cf->consultar($parametros);
            $cf->desconectar();
            $cf = $cf->extraer();
            $pep->ple_id = $cf['ple_id'];
            $pep->ope_id = $cf['ope_id'];
            $pep->espe_id = $cf['espe_id'];
            $pep->lape_id = $cf['lape_id'];
            $_SESSION[_S]['pep']->epc = $pep->epc_id;
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
        if (isset($pep->lectura) && $pep->lectura && ! empty($pep->ple_id)) {
            $combo->sql .= ' WHERE ple_id = :ple;';
            $combo->params = [':ple' => $pep->ple_id];
            $combo->detonar = false;
        }
        if (isset($pep->ple_id)) {
            $combo->selecciona = $pep->ple_id;
            $_SESSION[_S]['pep']->ple = $pep->ple_id;
            $combo->detonar = false;
        }

        if (isset($pep->disabled) || isset($pep->planDisabled)) {
            $combo->disabled = true;
        }
        $plan = $cs->ponCombo($combo);
        if (! is_null($cs->comboValorUnico)) {
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
        $parametros = [];
        if (! is_null($pep->ple_id)) {
            $combo->sql = 'SELECT ope_id,ope_nombre,ple_id FROM cat_ObjetivoPe WHERE ple_id = :ple ';
            $parametros[':ple'] = $pep->ple_id;
            if (isset($pep->lectura) && $pep->lectura) {
                $combo->sql .= ' AND ope_id = :ope;';
                $parametros[':ope'] = $pep->ope_id;
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($pep->ope_id)) {
                $combo->selecciona = $pep->ope_id;
                $combo->detonar = false;
                $_SESSION[_S]['pep']->ope = $pep->ope_id;
            }
            if (isset($pep->disabled) || isset($pep->objetivoDisabled)) {
                $combo->disabled = true;
            }
            $objetivo = $cs->ponCombo($combo);
            if (! is_null($cs->comboValorUnico)) {
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
        $parametros = [];
        if (! is_null($pep->ope_id)) {
            $combo->sql = 'SELECT espe_id,espe_nombre,ple_id,ope_id FROM cat_EstrategiaPe WHERE ple_id = :ple AND ope_id = :ope';
            $parametros[':ple'] = $pep->ple_id;
            $parametros[':ope'] = $pep->ope_id;
            if (isset($pep->lectura) && $pep->lectura) {
                $combo->sql .= ' AND espe_id = :espe;';
                $parametros[':espe'] = $pep->espe_id;
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($pep->espe_id)) {
                $combo->selecciona = $pep->espe_id;
                $combo->detonar = false;
                $_SESSION[_S]['pep']->esp = $pep->espe_id;
            }
            if (isset($pep->disabled)) {
                $combo->disabled = $pep->disabled;
            }

            $estrategia = $cs->ponCombo($combo);
            if (! is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pep']->esp = $cs->comboValorUnico;
            }
        } else {
            $estrategia = $cs->templateCombo($combo);
        }

        #combo linea de accion
        $combo = new stdClass();
        $combo->id = 'lape_id';
        $combo->label = 'Línea de acción';
        $combo->comoAtr = 'ple_id,ope_id,espe_id,lape_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Línea de acción';
        $parametros = [];
        if (isset($pep->espe_id)) {
            $combo->sql = 'SELECT lape_id,lape_nombre,ple_id,ope_id,espe_id 
                FROM cat_LineaAccionPe WHERE ple_id = :ple  
                AND ope_id = :ope AND espe_id = :espe';
            $parametros[':ple'] = $pep->ple_id;
            $parametros[':ope'] = $pep->ope_id;
            $parametros[':espe'] = $pep->espe_id;
            if (isset($pep->lectura) && $pep->lectura) {
                $combo->sql .= ' AND lape_id = :lape;';
                $parametros[':lape'] = $pep->lape_id;
                $combo->detonar = true;
            }
            $combo->params = $parametros;
            if (isset($pep->lape_id)) {
                $combo->selecciona = $pep->lape_id;
                $combo->detonar = true;
                $_SESSION[_S]['pep']->lape = $pep->lape_id;
            }
            if (isset($pep->disabled)) {
                $combo->disabled = $pep->disabled;
            }

            $lineaAccion = $cs->ponCombo($combo);
            if (! is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pep']->lape = $cs->comboValorUnico;
            }
        } else {
            $lineaAccion = $cs->templateCombo($combo);
        }

        return '
        <div id="row">
            <legend class="col-md-12 col-xs-12">Plan Especial</legend>
                ' . $plan . '
                ' . $objetivo . '
                ' . $estrategia . '
                ' . $lineaAccion . '
        </div>' . '
        <script language="javascript" >

            $("#ple_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/CatPep.php",{
                        metodoPep:"cambioPla",
                        ple_id:$(this).val()
                    });
                }
            });
            $("#ope_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/CatPep.php",{
                        metodoPep:"cambioOpe",
                        ple_id:$(this).find("option:selected").attr("ple_id"),
                        ope_id:$(this).val()
                    });
                }
            });
            $("#espe_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/CatPep.php",{
                        metodoPep:"cambioEsp",
                        ple_id:$(this).find("option:selected").attr("ple_id"),
                        ope_id:$(this).find("option:selected").attr("ope_id"),
                        espe_id:$(this).val()
                    });
                }
            });
            $("#lape_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/CatPep.php",{
                        metodoPep:"cambioLape",
                        ple_id:$(this).find("option:selected").attr("ple_id"),
                        ope_id:$(this).find("option:selected").attr("ope_id"),
                        espe_id:$(this).find("option:selected").attr("espe_id"),
                        lape_id:$(this).val()
                    });
                }
            });

            ' . $cs->getScript() . '
            ' . $preseleccion . '
        </script>';
    }

    private function cargaOpe()
    {
        $_SESSION[_S]['pep']->ple = $this->prm->ple_id;
        $obt = new stdClass();
        $obt->id = 'ope_id';

        if (isset($this->prm->ope_id) && $this->prm->ope_id != '') {
            $obt->selecciona = $this->prm->ple_id;
        }

        $obt->sql = '
            SELECT ope_id,ope_nombre,ple_id
            FROM cat_ObjetivoPe
            WHERE ple_id=' . $_SESSION[_S]['pep']->ple . ';
        ';
        $obt->comoAtr = 'ple_id,ope_id';
        $this->recargaCombo($obt);
        if (! is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pep']->ope = $this->comboValorUnico;
        }
    }

    private function cargaEsp()
    {
        $_SESSION[_S]['pep']->ope = $this->prm->ope_id;
        $obt = new stdClass();
        $obt->id = 'espe_id';

        if (isset($this->prm->espe_id) && $this->prm->espe_id != '') {
            $obt->selecciona = $this->prm->ope_id;
        }

        $obt->sql = '
            SELECT espe_id,espe_nombre,ple_id,ope_id
            FROM cat_EstrategiaPe
            WHERE ple_id = :ple
            AND ope_id = :ope;
        ';

        $obt->params = [
            ':ple' => $_SESSION[_S]['pep']->ple,
            ':ope' => $_SESSION[_S]['pep']->ope,
        ];

        $obt->comoAtr = 'ple_id,ope_id,espe_id';
        $this->recargaCombo($obt);
        if (! is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pep']->esp = $this->comboValorUnico;
        }
    }

    private function cargaLape()
    {
        $_SESSION[_S]['pep']->esp = $this->prm->espe_id;
        $obt = new stdClass();
        $obt->id = 'lape_id';

        if (isset($this->prm->lape_id) && $this->prm->lape_id != '') {
            $obt->selecciona = $this->prm->espe_id;
        }

        $obt->sql = '
            SELECT lape_id,lape_nombre,ple_id,ope_id,espe_id
            FROM cat_LineaAccionPe
            WHERE ple_id = :ple
            AND ope_id = :ope
            AND espe_id = :esp;
        ';
        $obt->params = [
            ':ple' => $_SESSION[_S]['pep']->ple,
            ':ope' => $_SESSION[_S]['pep']->ope,
            ':esp' => $_SESSION[_S]['pep']->esp,
        ];
        $obt->comoAtr = 'ple_id,ope_id,espe_id,lape_id';
        $this->recargaCombo($obt);
        if (! is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pep']->lape = $this->comboValorUnico;
        }
    }

    private function cambioLape()
    {
        $_SESSION[_S]['pep']->lape = $this->prm->lape_id;
        $this->setSql('
            SELECT epc_id
            FROM enc_Pep
            WHERE ple_id = :ple
            AND ope_id = :ope
            AND espe_id = :esp
            AND lape_id = :lape;
        ');

        $parametros = [
            ':ple'  => $_SESSION[_S]['pep']->ple,
            ':ope'  => $_SESSION[_S]['pep']->ope,
            ':esp'  => $_SESSION[_S]['pep']->esp,
            ':lape' => $_SESSION[_S]['pep']->lape,
        ];
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
