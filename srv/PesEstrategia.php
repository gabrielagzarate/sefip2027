<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class Pes extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        if (isset($this->prm->metodoPes)) {
            $metodo = (isset($this->prm->metodoPes)) ? $this->prm->metodoPes : '';
            switch ($metodo) {
                case 'cambioSec':
                    $this->cambioSec();
                    break;
                case 'cargaObp':
                    $this->cargaObp();
                    break;
                case 'cambioObp':
                    $this->cambioObp();
                    break;
                case 'cambioEst':
                    $this->cambioEst();
                    break;
            }
            $this->returnJson();
        }
    }

    public static function ponPes()
    {
        $pes = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $form = '';
        $cf = new WebResponse();
        $preseleccion = '';

        $required = true;
        if (isset($pes->form)) {
            $form = $pes->form;
            $required = isset($pes->required);
        }

        $ponTemplate = (isset($pes->ponTemplate) && is_bool($pes->ponTemplate)) ? $pes->ponTemplate : false;
        $_SESSION[_S]['pes'] = new stdClass();

        $cs = new WebResponse();
        if (isset($pes->eps_id)) {
            $sql = 'SELECT spe_id, obp_id, esp_id FROM enc_Pes WHERE eps_id = :eps';
            $parametros = [':eps' => $pes->eps_id];
            $cf->setSql($sql);
            $cf->conectar();
            $cf->consultar($parametros);
            $cf->desconectar();
            $cf = $cf->extraer();
            $pes->spe_id = $cf['spe_id'];
            $pes->obp_id = $cf['obp_id'];
            $pes->esp_id = $cf['esp_id'];
            $_SESSION[_S]['pes']->eps = $pes->eps_id;
        }

        #combo Sector
        $combo = new stdClass();
        $combo->id = 'spe_id';
        $combo->label = 'Sector';
        $combo->comoAtr = 'spe_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Sector';

        if ($ponTemplate) {
            $sector = $cs->templateCombo($combo);
        } else {
            $combo->sql = 'SELECT spe_id,spe_nombre FROM cat_SectorPes';
            if (isset($pes->lectura) && $pes->lectura) {
                $combo->sql .= ' WHERE spe_id = :spe;';
                $combo->params = [':spe' => $pes->spe_id];
                $combo->detonar = false;
            }
            if (isset($pes->spe_id)) {
                $combo->selecciona = $pes->spe_id;
                $combo->detonar = false;
                $_SESSION[_S]['pes']->spe_id = $pes->spe_id;
            }
            if (isset($pes->disabled) || isset($pes->sectorDisabled)) {
                $combo->disabled = true;
            }

            $sector = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pes']->spe_id = $cs->comboValorUnico;
            }

        }

        #combo Objetivo
        $combo = new stdClass();
        $combo->id = 'obp_id';
        $combo->label = 'Objetivo';
        $combo->comoAtr = 'spe_id,obp_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar un Objetivo';
        $parametros = [];
        if (!is_null($pes->obp_id) || isset($pes->spe_id)) {
            $combo->sql = 'SELECT obp_id,obp_nombre, spe_id FROM cat_ObjetivoPes WHERE spe_id = :spe';
            $parametros[':spe'] = $pes->spe_id;
            if (isset($pes->lectura) && $pes->lectura) {
                $combo->sql .= ' AND obp_id = :obp;';
                $parametros[':obp'] = $pes->obp_id;
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($pes->obp_id)) {
                $combo->selecciona = $pes->obp_id;
                $combo->detonar = false;
                $_SESSION[_S]['pes']->obp_id = $pes->obp_id;
            }
            if (isset($pes->disabled) || isset($pes->objetivoDisabled)) {
                $combo->disabled = true;
            }

            $objetivo = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pes']->obp_id = $cs->comboValorUnico;
            }

        } else {
            $objetivo = $cs->templateCombo($combo);
        }

        #combo Estrategia
        $combo = new stdClass();
        $combo->id = 'esp_id';
        $combo->label = 'Estrategia';
        $combo->comoAtr = 'spe_id,obp_id,esp_id';
        $combo->enDiv = true;
        $combo->horizontal = 3;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Estrategia';
        $parametros = [];
        if (!empty($pes->spe_id) && !empty($pes->obp_id)) {
            $combo->sql = 'SELECT esp_id,esp_nombre,spe_id,obp_id FROM cat_EstrategiaPes WHERE spe_id = :spe AND obp_id = :obp ';
            $parametros[':spe'] = $pes->spe_id;
            $parametros[':obp'] = $pes->obp_id;
            if (isset($pes->lectura) && $pes->lectura) {
                $combo->sql .= ' AND esp_id = :esp;';
                $parametros[':esp'] = $pes->esp_id;
                $combo->detonar = false;
            }
            $combo->params = $parametros;
            if (isset($pes->esp_id)) {
                $combo->selecciona = $pes->esp_id;
                $combo->detonar = false;
                $_SESSION[_S]['pes']->esp_id = $pes->esp_id;
            }
            if (isset($pes->disabled)) {
                $combo->disabled = $pes->disabled;
            }

            $estrategia = $cs->ponCombo($combo);
            if (!is_null($cs->comboValorUnico)) {
                $_SESSION[_S]['pes']->esp_id = $cs->comboValorUnico;
            }

        } else {
            $estrategia = $cs->templateCombo($combo);
        }

        return '
        <div id="row">
            <legend class="col-md-12 col-xs-12">PES</legend>
                ' . $sector . '
                ' . $objetivo . '
                ' . $estrategia . '
        </div>' . '
        <script language="javascript" >

            $("#spe_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/PesEstrategia.php",{
                        metodoPes:"cambioSec",
                        spe_id:$(this).val()
                    });
                }
            });
            $("#obp_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/PesEstrategia.php",{
                        metodoPes:"cambioObp",
                        spe_id:$("#spe_id option:selected").val(),
                        obp_id:$(this).val()
                    });
                }
            });
            $("#esp_id").change(function(){
                if($(this).val()!=""){
                    comunica("srv/PesEstrategia.php",{
                        metodoPes:"cambioEst",
                        spe_id:$("#spe_id option:selected").val(),
                        obp_id:$("#obp_id option:selected").val(),
                        esp_id:$(this).val()
                    });
                }
            });
            ' . $cs->getScript() . '
            ' . $preseleccion . '
        </script>';
    }

    private function cambioSec()
    {

        $_SESSION[_S]['pes']->spe = $this->prm->spe_id;
        $obt = new stdClass();
        $obt->id = 'obp_id';
        if (isset($this->prm->obp_id) && $this->prm->obp_id != '') {
            $obt->selecciona = $this->prm->obp_id;
        }

        $obt->sql = 'SELECT obp_id,obp_nombre,spe_id
                FROM cat_ObjetivoPes
                WHERE spe_id=:spe;';
        $obt->params = [':spe' => $_SESSION[_S]['pes']->spe];
        $obt->comoAtr = 'spe_id,obp_id';
        $this->recargaCombo($obt);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pes']->obp = $this->comboValorUnico;
        }

        $this->cargaObp();
    }

    private function cargaObp()
    {

        $_SESSION[_S]['pes']->obp = (empty($this->prm->obp_id)) ? 0 : $this->prm->obp_id;
        $_SESSION[_S]['pes']->spe = (empty($this->prm->spe_id)) ? 0 : $this->prm->spe_id;

        $obt = new stdClass();
        $obt->id = 'obp_id';

        if (isset($this->prm->obp_id) && $this->prm->obp_id != '') {
            $obt->selecciona = $this->prm->obp_id;
        }

        $obt->sql = 'SELECT obp_id,obp_nombre,spe_id
            FROM cat_ObjetivoPes
            WHERE spe_id=:spe;';
        $obt->params = [':spe' => $_SESSION[_S]['pes']->spe];
        $obt->comoAtr = 'spe_id,obp_id';
        $this->recargaCombo($obt);

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pes']->obp = $this->comboValorUnico;
        }

        $this->cargaEst();

    }

    private function cambioObp()
    {
        $_SESSION[_S]['pes']->obp = (empty($this->prm->obp_id)) ? 0 : $this->prm->obp_id;
        $_SESSION[_S]['pes']->spe = (empty($this->prm->spe_id)) ? 0 : $this->prm->spe_id;

        $obt = new stdClass();
        $obt->id = 'esp_id';

        if (isset($this->prm->obp_id) && $this->prm->obp_id != '') {
            $obt->selecciona = $this->prm->obp_id;
        }

        $obt->sql = 'SELECT esp_id,esp_nombre,spe_id,obp_id
            FROM cat_EstrategiaPes
            WHERE spe_id=:spe
            AND obp_id=:obp;';
        $obt->params = [':spe' => $_SESSION[_S]['pes']->spe, ':obp' => $_SESSION[_S]['pes']->obp];
        $obt->comoAtr = 'spe_id,obp_id,esp_id';
        $this->recargaCombo($obt);

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pes']->obp = $this->comboValorUnico;
        }
    }

    private function cargaEst()
    {

        $_SESSION[_S]['pes']->obp = (empty($this->prm->obp_id)) ? 0 : $this->prm->obp_id;
        $_SESSION[_S]['pes']->spe = (empty($this->prm->spe_id)) ? 0 : $this->prm->spe_id;
        $_SESSION[_S]['pes']->esp = (empty($this->prm->esp_id)) ? 0 : $this->prm->esp_id;

        $est = new stdClass();
        $est->id = 'esp_id';

        if (isset($this->prm->esp_id) && $this->prm->esp_id != '') {
            $est->selecciona = $this->prm->esp_id;
        }

        $est->sql = 'SELECT esp_id,esp_nombre,spe_id,obp_id
            FROM cat_EstrategiaPes
            WHERE spe_id=:spe
            AND obp_id = :obp;';
        $est->params = [':spe' => $_SESSION[_S]['pes']->spe, ':obp' => $_SESSION[_S]['pes']->obp];
        $est->comoAtr = 'spe_id,obp_id';
        $this->recargaCombo($est);

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['pes']->esp = $this->comboValorUnico;
        }

    }

    private function cambioEst()
    {
        $_SESSION[_S]['pes']->spe = (empty($this->prm->spe_id)) ? 0 : $this->prm->spe_id;
        $_SESSION[_S]['pes']->obp = (empty($this->prm->obp_id)) ? 0 : $this->prm->obp_id;
        $_SESSION[_S]['pes']->esp = (empty($this->prm->esp_id)) ? 0 : $this->prm->esp_id;

        $this->setSql('
            SELECT eps_id
            FROM enc_Pes
            WHERE spe_id = ?
            AND obp_id = ?
            AND esp_id = ?
        ');
        $parametros = [$_SESSION[_S]['pes']->spe, $_SESSION[_S]['pes']->obp, $_SESSION[_S]['pes']->esp];
        $this->conectar();
        $this->consultar($parametros);
        $pes = $this->extraer();
        $this->desconectar();
        $_SESSION[_S]['pes']->eps = $pes['eps_id'];
    }
}
//-------------------------------//
if (isset($_POST['metodoPes'])) {
    new Pes();
}

//---------------------------------//
