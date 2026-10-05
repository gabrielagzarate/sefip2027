<?php

ini_set('display_errors', '0');
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class UbicacionGeografica extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        if (isset($this->prm->metodoUbiGeo)) {
            $metodo = $this->prm->metodoUbiGeo;
            switch ($metodo) {
                case 'cambioCob':
                    $this->cargaCombos();
                    break;
                case 'cambioReg':
                    $this->cambioReg();
                    break;
                case 'cambioMre':
                    $this->cambioMre();
                    break;
                case 'cambioDto':
                    $this->cambioDto();
                    break;
                case 'cambioMun':
                    $this->cambioMun();
                    break;
                case 'cambioLoc':
                    $this->cambioLoc();
                    break;
            }
            $this->returnJson();
        }
    }

    public static function ponUbicacionGeografica()
    {
        $ubiGeo = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $_SESSION[_S]['ubigeo'] = new stdClass();
        $form = '';
        $required = false;
        if (isset($ubiGeo->form)) {
            $form = $ubiGeo->form;
            $required = isset($ubiGeo->required);
        }
        $ug = new WebResponse();
        $preseleccionScript = '';
        $preseleccionHtml = '';
        if (isset($ubiGeo->eug_id)) {
            $preseleccion = new UbicacionGeografica();
            $ug->conectar();
            $parametros = [$ubiGeo->eug_id];
            $ug->setSql('
					SELECT reg_id,dto_id,mun_id,loc_id
					FROM enc_UbicacionGeografica
					WHERE eug_id = ?;
				');

            $ug->consultar($parametros);
            $ug->desconectar();
            $ubicacion = $ug->extraer();
            if ($ubicacion['reg_id'] == 9) {
                $_SESSION[_S]['ubigeo']->cob = 1;
            } elseif ($ubicacion['dto_id'] >= 91) {
                $_SESSION[_S]['ubigeo']->cob = 2;
                $preseleccion->prm->reg_id = $_SESSION[_S]['ubigeo']->reg = $ubicacion['reg_id'];
            } elseif ($ubicacion['mun_id'] >= 901) {
                $_SESSION[_S]['ubigeo']->cob = 4;
                $preseleccion->prm->reg_id = $_SESSION[_S]['ubigeo']->reg = $ubicacion['reg_id'];
                $preseleccion->prm->dto_id = $_SESSION[_S]['ubigeo']->dto = $ubicacion['dto_id'];
            } elseif ($ubicacion['loc_id'] == 0 || $ubicacion['loc_id'] == 9999) {
                $_SESSION[_S]['ubigeo']->cob = 5;
                $preseleccion->prm->reg_id = $_SESSION[_S]['ubigeo']->reg = $ubicacion['reg_id'];
                $preseleccion->prm->dto_id = $_SESSION[_S]['ubigeo']->dto = $ubicacion['dto_id'];
                $preseleccion->prm->mun_id = $_SESSION[_S]['ubigeo']->mun = $ubicacion['mun_id'];
            } else {
                $_SESSION[_S]['ubigeo']->cob = 6;
                $preseleccion->prm->reg_id = $_SESSION[_S]['ubigeo']->reg = $ubicacion['reg_id'];
                $preseleccion->prm->dto_id = $_SESSION[_S]['ubigeo']->dto = $ubicacion['dto_id'];
                $preseleccion->prm->mun_id = $_SESSION[_S]['ubigeo']->mun = $ubicacion['mun_id'];
                $preseleccion->prm->loc_id = $_SESSION[_S]['ubigeo']->loc = $ubicacion['loc_id'];
            }
            $preseleccion->prm->cob_id = $_SESSION[_S]['ubigeo']->cob;

            if (isset($ubiGeo->lectura)) {
                $preseleccion->prm->lectura = $ubiGeo->lectura;
            }

            $preseleccion->cargaCombos();
            $preseleccionHtml = $preseleccion->getHtml();
            $preseleccionScript = $preseleccion->getScript();
        }
        $combo = new stdClass();
        $combo->id = 'comboCob';
        $combo->sql = 'SELECT cob_id,cob_nombre FROM cat_Cobertura where cob_id not in (3)';
        $combo->search = true;
        if (isset($ubiGeo->lectura) && $ubiGeo->lectura) {
            $combo->sql .= ' and cob_id = ? ';
            $combo->params = [$_SESSION[_S]['ubigeo']->cob];
            $combo->detonar = false;
        }
        $combo->label = 'Cobertura';
        if (isset($_SESSION[_S]['ubigeo']->cob)) {
            $combo->detonar = false;
            $combo->selecciona = $_SESSION[_S]['ubigeo']->cob;
        }
        $combo->required = true;
        $combo->enDiv = true;
        $combo->camposText = 'cob_nombre';
        $combo->soloText = true;
        if ($form != '') {
            $combo->form = $form;
        }

        $combo->required = $required;
        $combo->mensaje = 'Seleccionar una Cobertura';

        return '
				<fieldset class="col-md-9 col-xs-12" id="ubicacionGeografica">
					<legend>Ubicación Geográfica</legend>
					' . $ug->ponCombo($combo) . '
					<div id="combosUg">' . $preseleccionHtml . '</div>
				</fieldset>' . '
				<script language="javascript" >
					$("#comboCob").change(function(){
						comunica("srv/UbicacionGeografica.php",{
							metodoUbiGeo:"cambioCob",
							cob_id:$(this).val()
						});
					});
					' . $ug->getScript() . '
					' . $preseleccionScript . '
				</script>';
    }

    private function cargaCombos()
    {
        $this->setContenedor('combosUg');
        $_SESSION[_S]['ubigeo']->cob = $this->prm->cob_id;
        $_SESSION[_S]['ubigeo']->mre = 99;
        switch ($this->prm->cob_id) {
            case 2:
                $this->ponRegion();
                break;
            case 3:
                $this->ponMicroRegion();
                break;
            case 4:
                $this->ponDistrito();
                break;
            case 5:
                $this->ponMunicipio();
                break;
            case 6:
                $this->ponLocalidad();
                break;
            case 1:
            default:
                $this->mixHtml('<p></p>');
                break;
        }
    }

    private function cambioReg()
    {
        $_SESSION[_S]['ubigeo']->reg = (!empty($this->prm->reg_id)) ? $this->prm->reg_id : 0;
        if ($_SESSION[_S]['ubigeo']->cob > 3) {
            $this->prm->dto_id = (isset($this->prm->dto_id)) ? $this->prm->dto_id : 0;
            $this->cargaDistrito();
        } else {
            $_SESSION[_S]['ubigeo']->dto = 0;
            $_SESSION[_S]['ubigeo']->mun = 0;
            $_SESSION[_S]['ubigeo']->loc = 0;
        }
    }

    private function cambioMre()
    {
        $_SESSION[_S]['ubigeo']->mre = (!empty($this->prm->mre_id)) ? $this->prm->mre_id : 99;
        $_SESSION[_S]['ubigeo']->reg = 0;
        $_SESSION[_S]['ubigeo']->dto = 0;
        $_SESSION[_S]['ubigeo']->mun = 0;
        $_SESSION[_S]['ubigeo']->loc = 0;
    }

    private function cambioDto()
    {
        $_SESSION[_S]['ubigeo']->dto = $this->prm->dto_id;
        if ($_SESSION[_S]['ubigeo']->cob > 4) {
            $this->prm->mun_id = 0;
            $this->cargaMunicipio();
        }
    }

    private function cambioMun()
    {
        $_SESSION[_S]['ubigeo']->mun = $this->prm->mun_id;
        if ($_SESSION[_S]['ubigeo']->cob == 6) {
            $this->prm->loc_id = 0;
            $this->cargaLocalidad();
        }
    }

    private function cambioLoc()
    {
        $_SESSION[_S]['ubigeo']->loc = $this->prm->loc_id;
    }

    private function cargaDistrito()
    {
        $_SESSION[_S]['ubigeo']->dto = (!empty($this->prm->dto_id)) ? $this->prm->dto_id : 0;
        $dto = new stdClass();
        $dto->id = 'comboDto';
        $dto->detonar = false;
        $parametros = ['reg' => $_SESSION[_S]['ubigeo']->reg];
        $dto->sql = 'SELECT dto_id,dto_nombre FROM cat_Distrito WHERE dto_nombre NOT like "%cobertura%" AND reg_id= :reg ';
        if (isset($this->prm->lectura) && $this->prm->lectura) {
            $dto->sql .= ' AND dto_id= :dto ;';
            $parametros['dto'] = $_SESSION[_S]['ubigeo']->dto;
        }

        $dto->comoAtr = 'dto_id,dto_nombre';
        $dto->params = $parametros;
        $dto->selecciona = $_SESSION[_S]['ubigeo']->dto;
        $this->recargaCombo($dto);

        if (!empty($this->valorSeleccionado)) {
            $_SESSION[_S]['ubigeo']->dto = $this->valorSeleccionado;
        }

        if (!empty($this->comboValorUnico)) {
            $_SESSION[_S]['ubigeo']->dto = $this->comboValorUnico;
        } elseif (empty($_SESSION[_S]['ubigeo']->dto)) {
            $_SESSION[_S]['ubigeo']->dto = 0;
        }

        if ($_SESSION[_S]['ubigeo']->cob <= 4) {
            $_SESSION[_S]['ubigeo']->mun = 0;
            $_SESSION[_S]['ubigeo']->loc = 0;
        }
    }

    private function cargaMunicipio()
    {
        $_SESSION[_S]['ubigeo']->mun = (!empty($this->prm->mun_id)) ? $this->prm->mun_id : 0;
        $mun = new stdClass();
        $mun->id = 'comboMun';
        $mun->detonar = false;
        $parametros = ['reg' => $_SESSION[_S]['ubigeo']->reg, 'dto' => $_SESSION[_S]['ubigeo']->dto];
        $mun->sql = 'SELECT mun_id,mun_nombre FROM cat_Municipio WHERE mun_nombre NOT like "%cobertura%" AND reg_id= :reg AND dto_id= :dto';
        if (isset($this->prm->lectura) && $this->prm->lectura) {
            $mun->sql .= ' AND mun_id= :mun;';
            $parametros['mun'] = $_SESSION[_S]['ubigeo']->mun;
        }
        $mun->comoAtr = 'mun_id,mun_nombre';
        $mun->params = $parametros;
        $mun->selecciona = $_SESSION[_S]['ubigeo']->mun;
        $this->recargaCombo($mun);
        if (!empty($this->valorSeleccionado)) {
            $_SESSION[_S]['ubigeo']->mun = $this->valorSeleccionado;
        }

        if (!empty($this->comboValorUnico)) {
            $_SESSION[_S]['ubigeo']->mun = $this->comboValorUnico;
        } elseif (empty($_SESSION[_S]['ubigeo']->mun)) {
            $_SESSION[_S]['ubigeo']->mun = 0;
        }

        if ($_SESSION[_S]['ubigeo']->cob < 6) {
            $_SESSION[_S]['ubigeo']->loc = 0;
        }

    }

    private function cargaLocalidad()
    {
        $_SESSION[_S]['ubigeo']->loc = (!empty($this->prm->loc_id)) ? $this->prm->loc_id : 0;
        $loc = new stdClass();
        $loc->id = 'comboLoc';
        $loc->detonar = false;
        $parametros = [
            'reg' => $_SESSION[_S]['ubigeo']->reg,
            'dto' => $_SESSION[_S]['ubigeo']->dto,
            'mun' => $_SESSION[_S]['ubigeo']->mun,
        ];
        $loc->sql = 'SELECT loc_id,loc_nombre FROM cat_Localidad WHERE loc_nombre NOT like "%cobertura%" AND reg_id= :reg AND dto_id= :dto AND mun_id= :mun';
        if (isset($this->prm->lectura) && $this->prm->lectura) {
            $loc->sql .= ' AND loc_id= :loc;';
            $parametros['loc'] = $_SESSION[_S]['ubigeo']->loc;
        }
        $loc->comoAtr = 'loc_id,loc_nombre';
        $loc->params = $parametros;
        $loc->selecciona = $_SESSION[_S]['ubigeo']->loc;
        $this->recargaCombo($loc);
        if (!empty($this->valorSeleccionado)) {
            $_SESSION[_S]['ubigeo']->loc = $this->valorSeleccionado;
        }

        if (!empty($this->comboValorUnico)) {
            $_SESSION[_S]['ubigeo']->loc = $this->comboValorUnico;
        } elseif (empty($_SESSION[_S]['ubigeo']->loc)) {
            $_SESSION[_S]['ubigeo']->loc = 0;
        }

        if (!empty($this->comboValorUnico)) {
            $_SESSION[_S]['ubigeo']->loc = $this->comboValorUnico;
        }

    }

    private function ponMicroRegion()
    {
        $combo = new stdClass();
        $combo->id = 'comboMre';
        $combo->label = 'Microregión';
        $combo->detonar = false;
        $combo->sql = 'SELECT mre_id,mre_nombre FROM cat_MicroRegion';
        $combo->search = true;
        if (isset($this->prm->lectura) && $this->prm->lectura) {
            $combo->sql .= 'AND mre_id= ?;';
            $combo->params = [$_SESSION[_S]['ubigeo']->mre];
        }
        $combo->enDiv = true;
        $combo->required = true;
        $combo->mensaje = 'Seleccionar una Microregión';
        if (!empty($_SESSION[_S]['ubigeo']->mre)) {
            $combo->selecciona = $_SESSION[_S]['ubigeo']->mre;
        }

        $this->mixHtml($this->ponCombo($combo));
        if (!empty($this->comboValorUnico)) {
            $_SESSION[_S]['ubigeo']->mre = $this->comboValorUnico;
        } elseif (empty($_SESSION[_S]['ubigeo']->mre)) {
            $_SESSION[_S]['ubigeo']->mre = 99;
        }

        $this->mixScript('
				$("#comboMre").change(function(){
					comunica("srv/UbicacionGeografica.php",{
						metodoUbiGeo:"cambioMre",
						mre_id:$(this).val()
					});
				});
			');
    }

    private function ponRegion()
    {
        $combo = new stdClass();
        $combo->id = 'comboReg';
        $combo->label = 'Region';
        $combo->detonar = false;
        $combo->sql = 'SELECT reg_id,reg_nombre FROM cat_Region WHERE reg_nombre NOT like "%cobertura%"';
        $combo->search = true;
        if (isset($this->prm->lectura) && $this->prm->lectura) {
            $combo->sql .= 'AND reg_id= ?;';
            $combo->params = [$_SESSION[_S]['ubigeo']->reg];
        }
        $combo->enDiv = true;
        $combo->required = true;
        $combo->mensaje = 'Seleccionar una Región';
        if (!empty($_SESSION[_S]['ubigeo']->reg)) {
            $combo->selecciona = $_SESSION[_S]['ubigeo']->reg;
        }

        $this->mixHtml($this->ponCombo($combo));
        if (!empty($this->comboValorUnico)) {
            $_SESSION[_S]['ubigeo']->reg = $this->comboValorUnico;
        } elseif (empty($_SESSION[_S]['ubigeo']->reg)) {
            $_SESSION[_S]['ubigeo']->reg = 0;
        }

        $this->mixScript('
				$("#comboReg").change(function(){
					comunica("srv/UbicacionGeografica.php",{
						metodoUbiGeo:"cambioReg",
						reg_id:$(this).val()
					});
				});
			');
    }

    private function ponDistrito()
    {
        $this->ponRegion();
        $combo = new stdClass();
        $combo->id = 'comboDto';
        $combo->label = 'Distrito';
        $combo->enDiv = true;
        $combo->required = true;
        $combo->detonar = false;
        $combo->mensaje = 'Seleccionar un Distrito';
        $combo->search = true;
        $this->mixHtml($this->templateCombo($combo));
        $this->mixScript('
				$("#comboDto").change(function(){
					comunica("srv/UbicacionGeografica.php",{
						metodoUbiGeo:"cambioDto",
						dto_id:$(this).val()
					});
				});
			');
        if ($_SESSION[_S]['ubigeo']->reg != 0) {
            $this->cargaDistrito();
        }

    }

    private function ponMunicipio()
    {
        $this->ponDistrito();
        $combo = new stdClass();
        $combo->id = 'comboMun';
        $combo->label = 'Municipio';
        $combo->enDiv = true;
        $combo->required = true;
        $combo->detonar = false;
        $combo->mensaje = 'Seleccionar un Municipio';
        $combo->search = true;
        $this->mixHtml($this->templateCombo($combo));
        $this->mixScript('
				$("#comboMun").change(function(){
					comunica("srv/UbicacionGeografica.php",{
						metodoUbiGeo:"cambioMun",
						mun_id:$(this).val()
					});
				});
			');
        if (isset($_SESSION[_S]['ubigeo']->dto) && $_SESSION[_S]['ubigeo']->dto != 0 && $_SESSION[_S]['ubigeo']->cob > 4) {
            $this->prm->mun_id = (!empty($this->prm->mun_id)) ? $this->prm->mun_id : 0;
            $this->cargaMunicipio();
        }
    }

    private function ponLocalidad()
    {
        $this->ponMunicipio();
        $combo = new stdClass();
        $combo->id = 'comboLoc';
        $combo->label = 'Localidad';
        $combo->enDiv = true;
        $combo->required = true;
        $combo->mensaje = 'Seleccionar una Localidad';
        $combo->search = true;
        $this->mixHtml($this->templateCombo($combo));
        $this->mixScript('
				$("#comboLoc").change(function(){
					comunica("srv/UbicacionGeografica.php",{
						metodoUbiGeo:"cambioLoc",
						loc_id:$(this).val()
					});
				});
			');
        if (isset($_SESSION[_S]['ubigeo']->mun) && $_SESSION[_S]['ubigeo']->mun != 0 && $_SESSION[_S]['ubigeo']->cob == 6) {
            $this->prm->loc_id = (!empty($this->prm->loc_id)) ? $this->prm->loc_id : 0;
            $this->cargaLocalidad();
        }
    }

    public static function getEug_id()
    {
        $eug = new AccesoDatos();
        switch ($_SESSION[_S]['ubigeo']->cob) {
            case 3:
            case 1:
                $_SESSION[_S]['ubigeo']->reg = 9;
                // no break
            case 2:
                $_SESSION[_S]['ubigeo']->dto = 90 + $_SESSION[_S]['ubigeo']->reg;
                // no break
            case 4:
                $_SESSION[_S]['ubigeo']->mun = 900 + $_SESSION[_S]['ubigeo']->dto;
                // no break
            case 5:
                $_SESSION[_S]['ubigeo']->loc = $_SESSION[_S]['ubigeo']->cob == 5 ? 0 : 9999;
                break;
            case 6:

                break;
            default:

                $_SESSION[_S]['ubigeo']->reg = 9;
                $_SESSION[_S]['ubigeo']->dto = 99;
                $_SESSION[_S]['ubigeo']->mun = 999;
                $_SESSION[_S]['ubigeo']->loc = 9999;
                break;
        }
        $parametros = [
            'reg' => $_SESSION[_S]['ubigeo']->reg,
            'dto' => $_SESSION[_S]['ubigeo']->dto,
            'mun' => $_SESSION[_S]['ubigeo']->mun,
            'loc' => $_SESSION[_S]['ubigeo']->loc,
        ];
        $whereReg = '';
        if ($_SESSION[_S]['ubigeo']->cob == 3) {
            $whereReg = ' AND mre_id = :mre';
            $parametros['mre'] = $_SESSION[_S]['ubigeo']->mre;
        }
        $eug->setSql('
				SELECT eug_id
				FROM enc_UbicacionGeografica
				WHERE reg_id = :reg AND
					dto_id = :dto AND
					mun_id = :mun AND
					loc_id = :loc
					  ' . $whereReg . '
			');

        $eug->conectar();
        $eug->consultar($parametros);
        $eug->desconectar();
        $eug = $eug->extraer();
        return $eug['eug_id'];
    }
}
//-------------------------------//
if (isset($_POST['metodoUbiGeo'])) {
    new UbicacionGeografica();
}

//---------------------------------//
