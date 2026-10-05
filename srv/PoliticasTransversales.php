<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class PoliticasT extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        if (isset($this->prm->metodoPoliticasT)) {
            $metodo = (isset($this->prm->metodoPoliticasT)) ? $this->prm->metodoPoliticasT : '';
            switch ($metodo) {
                case 'cambioEjeTra':
                    $this->cargaEstrT();
                    break;
                case 'cambioEstT':
                    $this->cargaLinAccT();
                    break;
                case 'cambioLinAccT':
                    $this->cambioLinAccT();
                    break;
            }
            $this->returnJson();
        }
    }

    public static function ponPoliticasT()
    {
        $politicasT = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $form = '';
        $cf = new WebResponse();
        $preseleccion = '';

        $required = true;
        if (isset($politicasT->form)) {
            $form = $politicasT->form;
            $required = isset($politicasT->required);
        }

        $ponTemplate = (isset($politicasT->ponTemplate) && is_bool($politicasT->ponTemplate)) ? $politicasT->ponTemplate : false;
        $_SESSION[_S]['politicasT'] = new stdClass();

        $cs = new WebResponse();
        if (!empty($politicasT->etla_id)) {
            $parametros = [$politicasT->etla_id];
            $cf->conectar();
            $cf->setSql('SELECT ejt_id, eje_id, etra_id, lact_id FROM enc_EjeTransLineaAcc WHERE etla_id = ?;');
            $cf->consultar($parametros);
            $datos = $cf->extraer();
            $cf->desconectar();
            $politicasT->ejt_id = $datos['ejt_id'];
            $politicasT->eje_id = $datos['eje_id'];
            $politicasT->etra_id = $datos['etra_id'];
            $politicasT->lact_id = $datos['lact_id'];
            $_SESSION[_S]['politicasT']->etla = $politicasT->etla_id;
        }
        #combo Eje PED
        $combo = new stdClass();
        $combo->id = 'eje_id';
        $combo->comoAtr = 'eje_id';
        $combo->label = 'Eje PED';
        $combo->mensaje = 'Seleccionar un Eje PED';
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->enDiv = true;
        $combo->required = $required;
        $combo->sql = 'SELECT eje_id, eje_nombre FROM cat_Eje';

        if (isset($politicasT->lectura) && $politicasT->lectura && !empty($politicasT->eje_id)) {
            $combo->sql .= ' WHERE eje_id = :eje;';
            $combo->params = [':eje' => $politicasT->eje_id];
            $combo->detonar = false;
        }
        if (isset($politicasT->eje_id)) {
            $combo->selecciona = $politicasT->eje_id;
            $_SESSION[_S]['politicasT']->ejeP = $politicasT->eje_id;
            $combo->detonar = false;
        }

        if (isset($politicasT->disabled)) {
            $combo->disabled = $politicasT->disabled;
        }

        $ejePED = $cs->ponCombo($combo);
        if (!is_null($cs->comboValorUnico)) {
            $_SESSION[_S]['politicasT']->ejeP = $cs->comboValorUnico;
            $politicasT->eje_id = $_SESSION[_S]['politicasT']->ejeP;
        }

        #combo Eje Transversal
        $combo = new stdClass();
        $combo->id = 'ejt_id';
        $combo->label = 'Eje Transversal';
        $combo->comoAtr = 'ejt_id';
        $combo->enDiv = true;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Eje Transversal';

        if ($ponTemplate) {
            $ejeTransversal = $cs->templateCombo($combo);
        } else {
            $combo->sql = '
                SELECT ejt_id, ejt_nombre
                FROM cat_EjeTransversal
            ';
            if (isset($politicasT->ueEjeT) && $politicasT->ueEjeT) {
                $combo->sql .= ' INNER JOIN enc_EjeTransSupueDos USING (ejt_id)';
                $combo->sql .= ' WHERE gpo_id = ?';
                $combo->sql .= ' AND ur_id = ?';
                $combo->sql .= ' AND ue_id = ?';
                $combo->params = [$_SESSION[_S]['gpo_id'], $_SESSION[_S]['ur_id'], $_SESSION[_S]['ue_id']];
                $combo->detonar = false;
            }
            if (isset($politicasT->ejt_id) || $politicasT->ueEjeT) {
                $combo->selecciona = $politicasT->ejt_id;
                $combo->detonar = false;
                $_SESSION[_S]['politicasT']->ejeT = $politicasT->ejt_id;
            }
            if (isset($politicasT->disabled)) {
                $combo->disabled = $politicasT->disabled;
            }

            $ejeTransversal = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['politicasT']->ejeT = $cs->comboValorUnico;
                $politicasT->ejt_id = $_SESSION[_S]['politicasT']->ejeT;
            }

        }

        #combo Estrategia Transversal
        $combo = new stdClass();
        $combo->id = 'etra_id';
        $combo->label = 'Estrategia';
        $combo->comoAtr = 'ejt_id,eje_id,etra_id';
        $combo->enDiv = true;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Estrategia';
        $parametros = [];
        if (isset($politicasT->eje_id) && isset($politicasT->ejt_id)) {
            $combo->sql = '
                SELECT DISTINCT etra_id, etra_Clave, etra_nombre, ejt_id, eje_id
                FROM cat_EstrategiaTransversal
                INNER JOIN enc_EstrategiaTransversalSupDos USING (etra_id)
                WHERE ejt_id = :ejt
                AND eje_id = :eje
            ';
            $parametros['ejt'] = $politicasT->ejt_id;
            $parametros['eje'] = $politicasT->eje_id;
            if (isset($politicasT->ueEtraT) && $politicasT->ueEtraT) {
                $combo->sql .= ' AND gpo_id = :gpo';
                $combo->sql .= ' AND ur_id = :ur';
                $combo->sql .= ' AND ue_id = :ue';
                $parametros['gpo'] = $_SESSION[_S]['gpo_id'];
                $parametros['ur'] = $_SESSION[_S]['ur_id'];
                $parametros['ue'] = $_SESSION[_S]['ue_id'];
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($politicasT->etra_id) || $politicasT->ueEtraT) {
                $combo->selecciona = $politicasT->etra_id;
                $combo->detonar = false;
                $_SESSION[_S]['politicasT']->etraT = $politicasT->etra_id;
            }
            if (isset($politicasT->disabled)) {
                $combo->disabled = $politicasT->disabled;
            }

            $estrategiaTransversal = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['politicasT']->etraT = $cs->comboValorUnico;
                $politicasT->etra_id = $_SESSION[_S]['politicasT']->etraT;
            }

        } else {
            $estrategiaTransversal = $cs->templateCombo($combo);
        }

        #combo linea de acción transversal
        $combo = new stdClass();
        $combo->id = 'lact_id';
        $combo->label = 'Linea de Acción';
        $combo->comoAtr = 'ejt_id,eje_id,etra_id,lact_id';
        $combo->enDiv = true;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Linea de Acción';
        $parametros = [];
        if (isset($politicasT->etra_id)) {
            $combo->sql = '
                SELECT DISTINCT lact_id, lact_Clave, lact_nombre, ejt_id, eje_id, etra_id
                FROM enc_EjeTransLineaAcc
                INNER JOIN cat_LineaAccionTransversal using (lact_id)
                WHERE ejt_id = :ejt
                AND eje_id = :eje
                AND  etra_id = :etra
            ';
            $parametros['ejt'] = $politicasT->ejt_id;
            $parametros['eje'] = $politicasT->eje_id;
            $parametros['etra'] = $politicasT->etra_id;
            if (isset($politicasT->ueLinAccT) && $politicasT->ueLinAccT) {
                $combo->sql .= ' AND gpo_id = :gpo';
                $combo->sql .= ' AND ur_id = :ur';
                $combo->sql .= ' AND ue_id = :ue';
                $parametros['gpo'] = $_SESSION[_S]['gpo_id'];
                $parametros['ur'] = $_SESSION[_S]['ur_id'];
                $parametros['ue'] = $_SESSION[_S]['ue_id'];
                $combo->detonar = true;
            }
            $combo->params = $parametros;
            if (isset($politicasT->lact_id)) {
                $combo->selecciona = $politicasT->lact_id;
                $combo->detonar = true;
                $_SESSION[_S]['politicasT']->lact = $politicasT->lact_id;
            }
            if (isset($politicasT->disabled)) {
                $combo->disabled = $politicasT->disabled;
            }

            $lineaAccTransversal = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['politicasT']->lact = $cs->comboValorUnico;
                $politicasT->lact_id = $_SESSION[_S]['politicasT']->lact;
            }

        } else {
            $lineaAccTransversal = $cs->templateCombo($combo);
        }

        return '
            <div id="row">
                ' . $ejePED . '
                ' . $ejeTransversal . '
                ' . $estrategiaTransversal . '
                ' . $lineaAccTransversal . '
            </div>' . '
            <script language="javascript" >

                $("#ejt_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/PoliticasTransversales.php",{
                            metodoPoliticasT:"cambioEjeTra",
                            eje_id:$("#eje_id").find("option:selected").attr("eje_id"),
                            ejt_id:$(this).val()
                        });
                    }
                });
                $("#etra_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/PoliticasTransversales.php",{
                            metodoPoliticasT:"cambioEstT",
                            ejt_id:$(this).find("option:selected").attr("ejt_id"),
                            eje_id:$(this).find("option:selected").attr("eje_id"),
                            etra_id:$(this).val()
                        });
                    }
                });
                $("#lact_id").change(function(){
                    if($(this).val()!=""){
                        comunica("srv/PoliticasTransversales.php",{
                            metodoPoliticasT:"cambioLinAccT",
                            ejt_id:$(this).find("option:selected").attr("ejt_id"),
                            eje_id:$(this).find("option:selected").attr("eje_id"),
                            etra_id:$(this).find("option:selected").attr("etra_id"),
                            lact_id:$(this).val()
                        });
                    }
                });

                ' . $cs->getScript() . '
                ' . $preseleccion . '
            </script>';
    }

    private function cargaEstrT()
    {
        $_SESSION[_S]['politicasT']->ejeT = $this->prm->ejt_id;
        $_SESSION[_S]['politicasT']->ejeP = $this->prm->eje_id;
        $obt = new stdClass();
        $obt->id = 'etra_id';

        if (isset($this->prm->etra_id) && $this->prm->etra_id != '') {
            $obt->selecciona = $this->prm->etra_id;
        }

        $obt->sql = '
            SELECT DISTINCT etra_id, etra_Clave, etra_nombre, ejt_id, eje_id
            FROM cat_EstrategiaTransversal
            INNER JOIN enc_EstrategiaTransversalSupDos USING (etra_id)
            WHERE ejt_id=' . $_SESSION[_S]['politicasT']->ejeT . '
            AND eje_id=' . $_SESSION[_S]['politicasT']->ejeP . ' ;
        ';
        $obt->comoAtr = 'ejt_id,eje_id,etra_id';
        $this->recargaCombo($obt);
    }

    private function cargaLinAccT()
    {
        $_SESSION[_S]['politicasT']->etraT = $this->prm->etra_id;
        $obt = new stdClass();
        $obt->id = 'lact_id';
        if (isset($this->prm->lact_id) && $this->prm->lact_id != '') {
            $obt->selecciona = $this->prm->etra_id;
        }

        $obt->sql = '
            SELECT DISTINCT lact_id, lact_Clave, lact_nombre, ejt_id, eje_id, etra_id
            FROM enc_EjeTransLineaAcc
            INNER JOIN cat_LineaAccionTransversal using (lact_id)
            where ejt_id = ?
            AND eje_id = ?
            AND etra_id = ?
        ';
        $obt->comoAtr = 'ejt_id,eje_id,etra_id';
        $obt->params = [$_SESSION[_S]['politicasT']->ejeT, $_SESSION[_S]['politicasT']->ejeP, $_SESSION[_S]['politicasT']->etraT];
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['politicasT']->lact = $this->comboValorUnico;
        }

    }

    private function cambioLinAccT()
    {
        $_SESSION[_S]['politicasT']->lact = $this->prm->lact_id;
        $parametros = [$_SESSION[_S]['politicasT']->ejeP, $_SESSION[_S]['politicasT']->ejeT, $_SESSION[_S]['politicasT']->etraT, $_SESSION[_S]['politicasT']->lact];
        $this->setSql('
            SELECT etla_id
            FROM enc_EjeTransLineaAcc
            WHERE eje_id = ?
            AND ejt_id = ?
            AND etra_id = ?
            AND lact_id = ?
        ');
        $this->conectar();
        $this->consultar($parametros);
        $encLineAcc = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['politicasT']->etla = $encLineAcc['etla_id'];
    }
}
//-------------------------------//
if (isset($_POST['metodoPoliticasT'])) {
    new PoliticasT();
}

//---------------------------------//
