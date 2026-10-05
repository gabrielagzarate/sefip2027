<?php

/**
 * Description of ControladorBancosUr
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 30-Sep-2019
 * Comment: -----
 * Objective:
 * Regla de Negocio:
 * Permits:
 * Tiene permisos de lectura y escritura de SCP->Catálogos->Bancos Ur., y escritura y lectura de
 * SCG->Banco->Banco Ur.
 *Changes: De la tabla ctrl2023Bitacora.bit_BancosUr hacemos referencia ahora en BD_CTR.LBitacora.bit_ModificacionBancosUr
 **/

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../ent/BancosUr.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/PlanCuentas.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/CuentaBancariaBeneficiario.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/BeneficiarioMunicipio.php';
class ControladorBancosUr extends WebResponse
{
    private $altaCuentasBancarias;
    private $autorizacionCuentasBancarias;
    private $contabilidad;
    private $contabilidadGdaCta;
    private $generalizacionDeCuentas;
    private $consultaCuentasBancarias;
    private $deshabilitar;
    private $modificacionTipoClcCuentasBancarias;
    private $usr_id;
    private $ip;
    private $s23_nvj;
    private $gpo_id;
    private $ur_id;

    public function __construct()
    {
        parent::__construct();
        $this->altaCuentasBancarias = isset($_SESSION[_S]['prm']['S']['6']['2']['10']) ? true : false;
        $this->autorizacionCuentasBancarias = (isset($_SESSION[_S]['prm']['S']['6']['2']['11']) || isset($_SESSION[_S]['prm']['T']['6']['4']['11'])) ? true : false;
        $this->contabilidad = isset($_SESSION[_S]['prm']['C']['8']['1']['1']) ? true : false; //El permiso de lectura le asignamos a la variable contabilidad
        $this->contabilidadGdaCta = isset($_SESSION[_S]['prm']['C']['8']['1']['2']) ? true : false;
        $this->generalizacionDeCuentas = (isset($_SESSION[_S]['prm']['S']['6']['2']['12']) || isset($_SESSION[_S]['prm']['T']['6']['4']['12'])) ? true : false;
        $this->consultaCuentasBancarias = (isset($_SESSION[_S]['prm']['S']['6']['2']['13']) || isset($_SESSION[_S]['prm']['T']['6']['4']['13'])) ? true : false;
        $this->deshabilitar = (isset($_SESSION[_S]['prm']['S']['6']['2']['81']) || isset($_SESSION[_S]['prm']['T']['6']['4']['81'])) ? true : false;
        $this->modificacionTipoClcCuentasBancarias = (isset($_SESSION[_S]['prm']['S']['6']['2']['63']) || isset($_SESSION[_S]['prm']['T']['6']['4']['63'])) ? true : false;
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->s23_nvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->gpo_id = $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['ur_id'];
        $this->pantalla = 'scp/Catalogos/ControladorBancosUr.php';

        switch ($this->s23_nvj) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7: //Nivel UR y unidad Eje3cutora para alta de folios modulo unidad resposable
                switch ($this->metodo) {
                    case 'ponTablaListadoCuentas':
                        $this->ponTablaListadoCuentas();
                        break;
                    case 'addModCuenta':
                    case 'autorizacuentaur':
                        $this->addModCuenta();
                        break;
                    case 'getRazonSocialMunicipio':
                        $this->getRazonSocialMunicipio();
                        break;
                    case 'consultaBanco':
                        $this->consultaBanco();
                        break;
                    case 'consultaPlaza':
                        $this->consultaPlaza();
                        break;
                    case 'determinarSiProveedorEsFisico':
                        $this->determinarSiProveedorEsFisico();
                        break;
                    case 'abcBancos':
                        $this->abcBancos();
                        break;
                    case 'DeshabilitarCta':
                        $this->DeshabilitarCta();
                        break;
                    case 'historialCuenta':
                        $this->historialCuenta();
                        break;
                    case 'llenaTablaBancos':
                        $this->llenaTablaBancos();
                        break;
                    case 'checaCuenta':
                        $this->checaCuenta();
                        break;
                    case 'EnviaraConta':
                        $this->EnviaraConta();
                        break;
                    case 'EnviarAsefin':
                        $this->EnviarAsefin();
                        break;
                    case 'ValidarAutRec':
                        $this->ValidarAutRec();
                        break;
                    case 'GuardaCuentaContable':
                        $this->GuardaCuentaContable();
                        break;
                    case 'ConsultaCuentas':
                        $this->ConsultaCuentas();
                        break;
                    case 'llenaTablaConsultaCta':
                        $this->llenaTablaConsultaCta();
                        break;
                    case 'ValidaUr':
                        $this->ValidaUr();
                        break;
                    case 'MuestraTipoClc':
                        $this->MuestraTipoClc();
                        break;
                    case 'LlenaTablaModTipoClc':
                        $this->LlenaTablaModTipoClc();
                        break;
                    case 'ModificaUsoTipoClc':
                        $this->ModificaUsoTipoClc();
                        break;
                    case 'ComboTcB':
                        $this->ComboTcB();
                        break;
                    case 'AgregaBeneficiario':
                        $this->AgregaBeneficiario();
                        break;

                    case 'buscaCuentaContable':
                        $this->buscaCuentaContable();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }
    //*******Metodo que muestra las opciones segun los permisos, nivel y perfil del usuario, UR y SEFIN
    private function main()
    {
        $this->titulo('Cuentas Bancarias');
        $cards = [];

        if ($this->altaCuentasBancarias && ($this->s23_nvj == 1 || $this->s23_nvj == 2 || $this->usr_id == '119')) {
            $c = new stdClass();
            $c->id = 'altacuentaur';
            $c->class = 'altacuentaur';
            $c->titulo = 'Unidad Responsable';
            $c->img = 'img/img.php?img=agregacuenta';
            $c->label = 'Alta de Cuentas Bancarias UR';
            $c->title = 'Alta de cuentas bancarias';
            $cards[] = $c;
        }

        //Boton de Autorización de Cuentas Bancarias Ur
        if ($this->autorizacionCuentasBancarias && ($this->s23_nvj == 4 || $this->s23_nvj == 3)) {
            $c = new stdClass();
            $c->id = 'autorizacuentaur';
            $c->class = 'autorizacuentaur';
            $c->titulo = 'SEFIN';
            $c->img = 'img/autorizacuenta.png';
            $c->label = 'Autorización de Cuentas Bancarias UR';
            $c->title = 'Autorización de cuentas bancarias UR';
            $cards[] = $c;
        }

        //Boton de Vinculacion Contable de Cuentas Bancarias Ur
        if ($this->contabilidad) {
            $c = new stdClass();
            $c->id = 'vinculacuentaur';
            $c->class = 'vinculacuentaur';
            $c->titulo = 'UR';
            $c->img = 'img/consultacuenta.png';
            $c->label = 'Vinculación Contable de Cuentas Bancarias UR';
            $c->title = 'Vinculacion contable de cuentas bancarias UR';
            $cards[] = $c;
        }

        //Boton de Generalización de Cuentas o Cambio de Titulares
        if ($this->generalizacionDeCuentas && ($this->s23_nvj == 4 || $this->s23_nvj == 3)) {
            $c = new stdClass();
            $c->id = 'generalizacuentaur';
            $c->class = 'generalizacuentaur';
            $c->titulo = 'SEFIN';
            $c->img = 'img/generalizacuenta.png';
            $c->label = 'Generalización de Cuentas Bancarias UR';
            $c->title = 'Generalización de cuentas o cambio de Titulares';
            $cards[] = $c;
        }

        //Boton de Consulta de Cuentas Bancarias Ur
        if ($this->consultaCuentasBancarias && ($this->s23_nvj == 4 || $this->s23_nvj == 3)) {
            $c = new stdClass();
            $c->id = 'consultacuentaur';
            $c->class = 'consultacuentaur';
            $c->titulo = 'SEFIN';
            $c->img = 'img/consultacuenta.png';
            $c->label = 'Consulta de Cuentas Bancarias UR';
            $c->title = 'Consulta de Cuentas Bancarias Ur';
            $cards[] = $c;
        }

        //Boton de modificación de tipode Clc de las Cuentas Bancarias Ur
        if ($this->modificacionTipoClcCuentasBancarias && ($this->s23_nvj == 4 || $this->s23_nvj == 3)) {
            $c = new stdClass();
            $c->id = 'MuestraTipoClc';
            $c->class = 'MuestraTipoClc';
            $c->titulo = 'MODIFICACIÓN';
            $c->img = 'img/documentosnormativos.png';
            $c->label = 'Tipo de CLC de Cuentas Bancarias';
            $c->title = 'Tipo de CLC de Cuentas Bancarias';
            $cards[] = $c;
        }

        $radios = '';
        $this->agregaTools($radios);
        $this->mixHtml($this->ponCards($cards));
        $this->mixScript('
            $(".altacuentaur").click(function(){
                comunica("' . $this->pantalla . '",{
                    "metodo":"ponTablaListadoCuentas",
                    "tarea":"addModCuenta"
                });
                return false;
            });

            $(".autorizacuentaur").click(function(){
                comunica("' . $this->pantalla . '",{
                    "metodo":"ponTablaListadoCuentas",
                    "tarea":"autorizacuentaur"
                });
                return false;
            });

            $(".vinculacuentaur").click(function(){
                comunica("' . $this->pantalla . '",{
                    "metodo":"ponTablaListadoCuentas",
                    "tarea":"vinculacuentaur"
                });
                return false;
            });

            $(".generalizacuentaur").click(function(){
                comunica("' . $this->pantalla . '",{
                    "metodo":"ponTablaListadoCuentas",
                    "tarea":"generalizaCuenta"
                });
                return false;
            });
            
            $(".consultacuentaur").click(function(){
                comunica("' . $this->pantalla . '",{
                    "metodo":"ConsultaCuentas",
                    "tarea":"ConsultaCuentas"
                });
                return false;
            });

            $("#MuestraTipoClc").click(function(){
                comunica("' . $this->pantalla . '",{
                    "metodo":"MuestraTipoClc"
                });
                return false;
            });
        ');
    }
    //**********Dibuja la lista de cuentas bancarias registradas*******************/
    private function ponTablaListadoCuentas()
    {
        //$checked = "";
        $this->conectar();

        $sql = 'SELECT eba_id, eba_nombre, eba_color FROM cat_EstadoBancos';
        $wh = '';
        switch ($this->prm->tarea) {
            case 'addModCuenta':
                break;
            case 'autorizacuentaur':
                $wh .= (strlen($wh) > 0) ? ' and ' : ' WHERE ';
                $wh .= ' eba_id  =  4 ';
                break;
            case 'ConsultaCuentas':
                $wh .= (strlen($wh) > 0) ? ' and ' : ' WHERE ';
                $wh .= ' eba_id  IN  (6,7) ';
                break;
            case 'generalizaCuenta':
                $wh .= (strlen($wh) > 0) ? ' and ' : ' WHERE ';
                $wh .= ' eba_id  =  6 ';
                break;
            case 'vinculacuentaur':
                $wh .= (strlen($wh) > 0) ? ' and eba_id IN ( 2, 6 )' : ' WHERE eba_id IN ( 2, 6 ) ';
                break;
            default:
                echo 'Error';
                break;
        }

        $inputNumCta = new stdClass();
        $inputNumCta->id = 'inputNumCta';
        $inputNumCta->label = 'Número de Cuenta';
        $inputNumCta->placeholder = '000000000000000000';
        $inputNumCta->enDiv = true;
        $inputNumCta->class = 'limpiar';
        $inputNumCta->required = true;
        $inputNumCta->maxLength = 18;
        $inputNumCta->formato = 'entero';

        $inputUr = new stdClass();
        $inputUr->id = 'inputUr';
        $inputUr->label = 'UR';
        $inputUr->placeholder = '000';
        $inputUr->enDiv = true;
        $inputUr->class = 'limpiar';
        $inputUr->required = true;
        $inputUr->value = (isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '';
        $inputUr->maxLength = 3;
        $inputUr->formato = 'entero';

        $btnBuscarCta = new stdClass();
        $btnBuscarCta->id = 'btnBuscarCta';
        $btnBuscarCta->class = 'mt-3 Buscar';
        $btnBuscarCta->text = 'Buscar';
        $btnBuscarCta->value = 'Buscar';
        $btnBuscarCta->icon = 'search me-2';
        $btnBuscarCta->color = 'gris';

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->class = 'mt-3';
        $clean->id = 'clean';
        $clean->color = 'cielo';
        $clean->icon = 'refresh me-2';

        $radios = '
            <form id="myForm"  class="row mb-4">
                        <table style="width:100%">
                            <tr>';

        $this->setSql($sql . $wh);
        $this->consultar();
        $vars = $this->extraer();
        $radios .= '
            <td id="radios_afectacion">
                <div style="display:block; height:auto; text-align:center";>
                    <input class= "rad_afectacion" type="radio" checked name="CuentasBancarias" id="0" usoDeCuenta=0 onClick="LlenaTabla()"/>
                        <label class ="rad_afectacion" for="all">TODOS<label>
                </div>
            <td>';

        do {
            $radios .= '
                <td id="radios_afectacion">
                    <div style="display:block; height:auto; text-align:center";>
                        <input class= "rad_afectacion" type="radio" name="CuentasBancarias" id="' . $vars['eba_id'] . '" usoDeCuenta=0 onClick="LlenaTabla(\'' . $vars['eba_id'] . '\')"/>
                            <label class ="rad_afectacion" for="' . $vars['eba_id'] . '">' . $vars['eba_nombre'] . '<label>
                    </div>
                <td>';
        } while ($vars = $this->extraer());

        $radios .= '</table>';

        if ($this->prm->tarea == 'autorizacuentaur') {

            $radios = '<form id="myForm" class="row  mb-4">
                            <table style="width:100%"><tr>';
            $radios .= '<div class="col-md-5 col-xs-5">';
            $radios .= $this->ponInput($inputNumCta);
            $radios .= '</div>';
            $radios .= '<div class="col-md-4 col-xs-4">';
            $radios .= $this->ponInput($inputUr);
            $radios .= '</div>';
            $radios .= '<div class="col-md-3 col-xs-3">';
            $radios .= $this->ponBoton($btnBuscarCta) . ' ' . $this->ponBoton($clean);
            $radios .= '</div>';
            $radios .= '</table>';
        }

        if ($this->prm->tarea == 'generalizaCuenta') {

            $radios = '<form id="myForm" class="row mb-4">
                            <table style="width:100%"><tr>';
            $radios .= '<div class="col-md-5 col-xs-5" mb-4>';
            $radios .= $this->ponInput($inputNumCta);
            $radios .= '</div>';
            $radios .= '<div class="col-md-4 col-xs-4">';
            $radios .= $this->ponInput($inputUr);
            $radios .= '</div>';
            $radios .= '<div class="col-md-3 col-xs-3">';
            $radios .= $this->ponBoton($btnBuscarCta) . ' ' . $this->ponBoton($clean);
            $radios .= '</div>';
            $radios .= '</table>';
        }

        $radios .= '</form>';

        $this->desconectar();

        $nuevaCuenta = new stdClass();
        $nuevaCuenta->id = 'addCuenta';
        $nuevaCuenta->icon = 'solid fa-plus me-2';
        $nuevaCuenta->class = 'addCuenta me-1';
        $nuevaCuenta->value = 'Nueva Cuenta Bancaria';
        $nuevaCuenta->color = 'azul';

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->icon = 'fa-solid fa-circle-left';
        $btnRegresarIndex->class = 'regresar me-2';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->text = 'Regresar';

        $tabla = new stdClass();
        $tabla->id = 'tbl_Cuentas';
        $tabla->tools = $radios;
        if ($this->prm->tarea == 'addModCuenta') {
            $tabla->titulo = 'Cuentas bancarias de la Unidad Responsable';
        }
        if ($this->prm->tarea == 'autorizacuentaur') {
            $tabla->titulo = 'Autorización de Cuentas bancarias';
        }
        if ($this->prm->tarea == 'generalizaCuenta') {
            $tabla->titulo = 'Generalización de Cuentas bancarias';
        }
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaBancos';
        $tabla->comunica->eba_id = '{$(\'input[name="CuentasBancarias"]:checked\').attr("id")}';
        $tabla->comunica->bur_id = '{$(\'#inputNumCta\').val()}';
        $tabla->comunica->ur_clave = '{$(\'#inputUr\').val()}';
        $tabla->comunica->usoDeCuenta = '{$(\'input[name="CuentasBancarias"]:checked\').attr("usoDeCuenta")}';
        $tabla->comunica->tarea = $this->prm->tarea;

        $tr = [];
        //Ver/Editar
        $th = new stdClass();
        $th->width = '6%';
        $th->align = 'left';
        $th->texto = 'Ver';
        if ($this->s23_nvj <= 2) {
            $th->texto = 'Ver / Editar';
        }
        array_push($tr, $th);

        if ($this->prm->tarea == 'autorizacuentaur') {
            //Ur
            $th = new stdClass();
            $th->width = '8%';
            $th->align = 'left';
            $th->texto = 'Ur';
            array_push($tr, $th);
        }
        if ($this->prm->tarea == 'generalizaCuenta') {
            //Ur
            $th = new stdClass();
            $th->width = '6%';
            $th->align = 'left';
            $th->texto = 'Ur';
            array_push($tr, $th);
        }
        //Clabe
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Clabe';
        array_push($tr, $th);
        //Cuenta bancaria
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Cuenta';
        array_push($tr, $th);
        //Nombre del Banco
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Banco';
        array_push($tr, $th);

        if ($this->prm->tarea != 'autorizacuentaur') {
            //Cuenta contable
            $th = new stdClass();
            $th->width = '18%';
            $th->align = 'center';
            $th->texto = 'Cta. Contable';
            array_push($tr, $th);
        }
        //Estado
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Estado';
        array_push($tr, $th);

        $trbody = [];    // se inicia el cuerpo de la tabla

        /*ver editar*/
        $td = new stdClass();
        switch ($this->prm->tarea) {
            case 'addModCuenta':
                $td->casos = new stdClass();
                $td->casos->campo = 'eba_id';
                $td->casos->tipoCondicion = '||';
                $td->casos->valor = '1,5';
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->class = 'EditaCuenta';
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->icono = 'pencil';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->bur_id = 'bur_id';
                $td->casos->verdadero->attr->eba_id = 'eba_id';
                $td->casos->verdadero->attr->gpo_id = 'gpo_id';
                $td->casos->verdadero->attr->ur_id = 'ur_id';

                $td->casos->falso = new stdClass();
                $td->casos->falso->class = 'EditaCuenta';
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->icono = 'eye';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->bur_id = 'bur_id';
                $td->casos->falso->attr->eba_id = 'eba_id';
                $td->casos->falso->attr->gpo_id = 'gpo_id';
                $td->casos->falso->attr->ur_id = 'ur_id';
                break;
            case 'autorizacuentaur':
                $td->casos = new stdClass();
                $td->casos->campo = 'eba_id';
                $td->casos->valor = '4';
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->class = 'EditaCuenta';
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->icono = 'pencil';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->bur_id = 'bur_id';
                $td->casos->verdadero->attr->eba_id = 'eba_id';
                $td->casos->verdadero->attr->gpo_id = 'gpo_id';
                $td->casos->verdadero->attr->ur_id = 'ur_id';

                $td->casos->falso = new stdClass();
                $td->casos->falso->class = 'EditaCuenta';
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->icono = 'eye';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->bur_id = 'bur_id';
                $td->casos->falso->attr->eba_id = 'eba_id';
                $td->casos->falso->attr->gpo_id = 'gpo_id';
                $td->casos->falso->attr->ur_id = 'ur_id';
                break;
            case 'generalizaCuenta':
                $td->casos = new stdClass();
                $td->casos->campo = 'eba_id';
                $td->casos->valor = '6';
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->class = 'EditaCuenta';
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->icono = 'pencil';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->bur_id = 'bur_id';
                $td->casos->verdadero->attr->eba_id = 'eba_id';
                $td->casos->verdadero->attr->gpo_id = 'gpo_id';
                $td->casos->verdadero->attr->ur_id = 'ur_id';

                $td->casos->falso = new stdClass();
                $td->casos->falso->class = 'EditaCuenta';
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->icono = 'eye';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->bur_id = 'bur_id';
                $td->casos->falso->attr->eba_id = 'eba_id';
                $td->casos->falso->attr->gpo_id = 'gpo_id';
                $td->casos->falso->attr->ur_id = 'ur_id';
                break;
            case 'vinculacuentaur':
                $td = new stdClass();
                $td->class = 'EditaCuenta';
                $td->tipo = 'boton';
                $td->icono = 'pencil';
                $td->attr = new stdClass();
                $td->attr->bur_id = 'bur_id';
                $td->attr->eba_id = 'eba_id';
                $td->attr->gpo_id = 'gpo_id';
                $td->attr->ur_id = 'ur_id';
                break;
            default:
                echo 'Error';
                break;
        }
        array_push($trbody, $td);

        if ($this->prm->tarea == 'autorizacuentaur') {
            /*ur_Clave */
            $td = new stdClass();
            $td->campoTxt = 'ur_clave';
            array_push($trbody, $td);
        }
        if ($this->prm->tarea == 'generalizaCuenta') {
            /*ur_Clave */
            $td = new stdClass();
            $td->campoTxt = 'ur_clave';
            array_push($trbody, $td);
        }
        /*clabe */
        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        array_push($trbody, $td);
        /*Cuenta */
        $td = new stdClass();
        $td->campoTxt = 'bur_cuentaBancaria';
        array_push($trbody, $td);
        /*banco */
        $td = new stdClass();
        $td->campoTxt = 'iba_nombre';
        array_push($trbody, $td);
        if ($this->prm->tarea != 'autorizacuentaur') {
            /*cta c */
            $td = new stdClass();
            $td->campoTxt = 'CuentaContable';
            array_push($trbody, $td);
        }
        /*estado */
        $td = new stdClass();
        $td->campoTxt = 'eba_nombre';
        $td->tipo = 'bolita';
        $td->color = 'eba_color';
        $td->lbl = 'eba_nombre';
        $td->linea = 'eba_color';
        $td->align = 'center';

        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('<div class="col-md-12 col-xs-12 mb-4">' . $this->wrPonTabla($tabla) . '</div>');

        $botones = '<button id="btnRegresarIndex" type="button" class="btn btn-outline-secondary me-1" title="Regresar al Menú Principal">
                        <i class="fa-solid fa-circle-left me-2"></i>Regresar</button>';
        switch ($this->prm->tarea) {
            case 'addModCuenta':
                $botones .= '<button id="addCuenta" type="button" class="btn btn-outline-primary me-1" title="Registrar nueva Cuenta Bancaria"><i class="fa-solid fa-plus me-2"></i>Nueva Cuenta Bancaria</button>';
                $this->mixScript('
                    $("#addCuenta").click (function(){
                        comunica("' . $this->pantalla . '",{
                            "metodo":"addModCuenta",
                            "tarea":"' . $this->prm->tarea . '"
                        });
                        return false;
                    });
                ');
                // no break
            case 'ConsultaCuentas':
            case 'autorizacuentaur':
            case 'generalizaCuenta':
                break;
        }
        $this->agregaTools(
            $botones . '
                <input type="search" width=50 id="filtroTabla" style="display: inline; width:300px; height: 33px;" class="form-control me-1" placeholder="Filtrar Cuenta Bancaria"/>'
        );
        $this->mixScript($this->wrLlamaLlenaTabla('tbl_Cuentas'));
        $this->mixScript('
            $("#btnBuscarCta").click(function() {
                if ($("#inputUr").val() != "" || $("#inputNumCta").val() != "") {
                    ' . $this->wrLlamaLlenaTabla('tbl_Cuentas') . '
                }
            });
            function LlenaTabla(eba_id = 0) {
                var obj = {
                    "metodo": "llenaTablaBancos",
                    "tarea": "' . $this->prm->tarea . '"
                };
                if (eba_id != null || eba_id != 0)
                    obj.eba_id = eba_id;
                ' . $this->wrLlamaLlenaTabla('tbl_Cuentas') . '
                return false;
            }
            $("#btnRegresarIndex").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo: "main"
                });
                $("#btnRegresarIndex").remove();
                $("#filtroTabla").remove();
                return false;
            });
            setTimeout(function() {
                $("#filtroTabla").focus()
            }, 300);
            $("#clean").click(function() {
                $(".limpiar").val("");
                comunica("' . $this->pantalla . '", {
                    metodo: "ponTablaListadoCuentas",
                    tarea: "' . $this->prm->tarea . '"
                });
                return false;
            });
            if ($(".ui-dialog").length > 0) {
                $(".ui-dialog").remove();
            }
        ');
    }
    //*******************Funcion que agrega o modifica una cuenta bancaria**************************/
    public function addModCuenta()
    {
        $abc = 'a';
        $tmp = '';
        $tmp1 = '';
        $tmp2 = '';
        /*Urs del grupo 2, de la tabla 2 del anexo 2 del documento Reglas de operación de CLC's, afectaciones y numeros de control*/
        $urs = ['105', '530', '531', '540'];
        $gpo8 = ['901', '902'];
        $ur_id = $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'];

        $inputClabe = new stdClass();
        $inputCuenta = new stdClass();
        $comboInsBan = new stdClass();
        $btnVincularCta = new stdClass();
        $UsodeCta = [];
        $inputPlaza = new stdClass();
        $inputSucursal = new stdClass();
        $inputAlias = new stdClass();
        $comboUsoCta = new stdClass();
        $inputUrSolic = new stdClass();
        $inputBeneficiario = new stdClass();
        $inputTitular1 = new stdClass();
        $inputcargo1 = new stdClass();
        $inputTitular2 = new stdClass();
        $inputcargo2 = new stdClass();
        $inputCtaContable = new stdClass();
        $inputNivelReg = new stdClass();

        $editables = '';
        $mensaje = '';
        $generalizarCuenta = '';
        $cambioTitulares = '';

        $tipoClc = new stdClass();
        $tipoClc->id = 'tcl_id';
        $tipoClc->name = 'tcl_id';
        $tipoClc->required = true;
        $tipoClc->enDiv = true;
        $tipoClc->camposVal = 'tcl_id';
        $tipoClc->agrupaPor = 'tcl_id';
        $tipoClc->campoLabelAgrupacion = 'tcl_nombre';
        $tipoClc->mensaje = 'Selecciona';
        $tipoClc->label = 'Uso de la cuenta';
        $tipoClc->comoAtr = 'tcl_id';
        $tipoClc->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc WHERE tcl_id in("C","M","L");';
        $bloqueo = '';

        $comboUE = new stdClass(); // clase para mostrar el combo de municipios cuando la cuenta a registrar pertence al ur de 902
        $comboUE->id = 'ue_id';
        $comboUE->sql = 'SELECT ue_id, ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id={gpo_id} AND ur_id={ur_id};';
        $comboUE->label = 'Municipio beneficiario';
        $comboUE->enDiv = true;
        $comboUE->required = true;
        $comboUE->search = true;

        if (isset($this->prm->bur_id)) {
            /*vamos a establecer instrucciones para obtener los datos del registro de la base de datos */
            $abc = 'c';
            $Bancos = new BancosUr($this->prm);

            $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria WHERE ucb_id in(1,4)';
            $comboUsoCta->selecciona = $Bancos->getUcb_id();
            $inputUrSolic->readonly = 'readonly'; // establece que no se puede editar el input
            $inputUrSolic->value = (($Bancos->getUcb_id() == 1 || $Bancos->getUcb_id() == 4 || $Bancos->getUcb_id() == 2) ? $Bancos->getGpo_id() . $Bancos->getUr_id() : $Bancos->getBur_gpo_id() . $Bancos->getBur_ur_id());

            if ($Bancos->getGer_id() == '') {
                $Bancos->setGer_id(1);
                $Bancos->setCgp_id(1);
                $Bancos->setRub_id(1);
                $Bancos->setCgcu_id(3);
                $Bancos->setCgsc_id(1);
            }
            $sql = "SELECT tcl_id,tcl_nombre FROM vin_BancosUrTipoClc INNER JOIN cat_TipoClc USING(tcl_id) WHERE bur_id = ? AND tcl_id in('C','M','L');";
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->prm->bur_id]);
            if ($this->getNumeroRegistros() > 0) {
                $r = $this->extraer();
                $tipoClc->selecciona = $r['tcl_id'];
            }
            $this->desconectar();
            // si es edicion o actualizacion  pondremos el texto Actualizar en el boton de guardar
            $inputClabe->readonly = 'readonly';

            $cuenta = new PlanCuentas();

            if ($Bancos->getCta_id() != '') {
                $cuenta->setGer_id($Bancos->getGer_id());
                $cuenta->setCgp_id($Bancos->getCgp_id());
                $cuenta->setRub_id($Bancos->getRub_id());
                $cuenta->setCgcu_id($Bancos->getCgcu_id());
                $cuenta->setCgsc_id($Bancos->getCgsc_id());
                $cuenta->staCta_gpo_id($Bancos->getCta_gpo_id());
                $cuenta->staCta_ur_id($Bancos->getCta_ur_id());
                $cuenta->setCta_ue_id($Bancos->getCta_ue_id());
                $cuenta->setCgc_id($Bancos->getCgc_id());
                $cuenta->setCta_id($Bancos->getCta_id());
                $cuenta->select();
            }

            $inputCtaContable->value = $cuenta->getCuenta() . ' - ' . $cuenta->getNombre();
            $poner = true;
            $poner1 = true;
            $poner3 = true;
            $bloqueo = '';
            $bloqueobton = ''; // GEMMA .- $bloqueobton .-para inhabilitar el botón de actualizar el uso de la cuenta (btnUpdUso) si la Clabe Bancaria es propia de fondo rotatorio.

            switch ($Bancos->getEba_id()) {
                case 5:
                    $sql = 'SELECT bit_observacion FROM ' . BD_BITACORA . ".bit_ModificacionBancosUr WHERE bur_id = ? and bur_tipoCambio = 'R' order by usr_fecha desc limit 1";
                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar([$this->prm->bur_id]);
                    $this->desconectar();
                    if ($this->getNumeroRegistros() > 0) {
                        $r = $this->extraer();
                        $Obsv = strtoupper($r['bit_observacion']);
                        $mensaje = '
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ' . $Obsv . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
                    }

                    break;
                case 1:
                    break;
                case 2:
                case 6:
                    /*segun la consulta, solo deberian llegar a generalizar la cuenta, en el estado 6*/
                    switch ($this->prm->tarea) {
                        case 'vinculacuentaur':
                            $tmp1 = '';
                            $poner = false;
                            $bloqueo = 'disabled = \"disabled\"';
                            $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria';
                            $comboUsoCta->disabled = 'disabled';
                            $comboUsoCta->lectura = false;
                            break;
                        case 'addModCuenta':
                            $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria';
                            $comboUsoCta->disabled = 'disabled';

                            /*04/06/14 Se modificó  que cuando el Uso de la Cuenta sea COMPARTIDA se visualice (input UR a compartir y N° de oficio ) en las Cuentas autorizadas.*/
                            switch ($Bancos->getUcb_id()) {

                                case 1:
                                    $comboUsoCta->disabled = 'disabled';
                                    break;
                                case 2:
                                    $tmp = '';
                                    $tmp1 = '';
                                    $tmp2 = '';
                                    $poner = false;
                                    $bloqueo = 'disabled = \"disabled\"';

                                    $sql = 'SELECT bur_tarjeta FROM ' . BD_BITACORA . ".bit_ModificacionBancosUr WHERE bur_id = ? and bur_tipoCambio='G'
                                            ORDER BY bit_bur desc";
                                    $this->conectar();
                                    $this->setSql($sql);
                                    $this->consultar([$this->prm->bur_id]);
                                    $this->desconectar();

                                    $tarjetaOfiUcb = $this->extraer();

                                    $generalizarCuenta =
                                        '<div class="container ">
                                            <div class="col-md-3 col-xs-4 form-group mb-4">
                                                <label  for="inputTarjetaOficio">No. tarjeta/oficio</label>
                                                <input  ' . $bloqueo . ' ' . $tmp . ' value = "' . $tarjetaOfiUcb['bur_tarjeta'] . '" type="text" class="form-control" required="1" id="inputTarjetaOficio" maxlength="20" placeholder="No. tarjeta/oficio">
                                            </div>
                                            <div class="col-md-3 col-xs-4 form-group mb-4">
                                                <label  for="inputURcomp">UR Compartida</label>
                                                <input  ' . $bloqueo . ' ' . $tmp1 . ' type="text" class="form-control" required="1" id="inputURcomp" maxlength="3" placeholder="000" disabled="1" ' . (($Bancos->getUcb_id() == 2) ? 'value="' . $Bancos->getBur_gpo_id() . $Bancos->getBur_ur_id() . '"' : '') . ' >
                                            </div>' . (($poner) ? '
                                            <div class="col-md-3 col-xs-4 mb-4">
                                                <button  ' . $bloqueo . ' ' . $tmp2 . ' id="btnUpdUso"  type="button" class="btn btn-outline-success">
                                                    <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar uso de cuenta
                                                </button>
                                            </div>' : '') . '
                                        </div>';
                                    break;
                            }
                            break;
                        case 'ConsultaCuentas':
                            $comboUE->disabled = 'disabled'; // deshabilitamos el campo de municipios

                            $ur_id = $Bancos->getGpo_id() . $Bancos->getUr_id();
                            $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria';

                            $where = '';
                            switch ($Bancos->getUcb_id()) {
                                case 1:
                                    $poner = false;
                                    $poner1 = false;
                                    $comboUsoCta->disabled = 'disabled';
                                    $comboUsoCta->lectura = true;
                                    $inputUrSolic->readonly = 'readonly';
                                    break;
                                case 2:
                                    $comboUsoCta->disabled = 'disabled';
                                    $inputUrSolic->disabled = 'disabled';
                                    break;
                                case 3:
                                    $inputUrSolic->value = $Bancos->getBur_gpo_id() . $Bancos->getBur_ur_id();
                                    // no break
                                case 4:
                                    $poner = false;
                                    $comboUsoCta->disabled = 'disabled';
                                    break;
                            }
                            $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria ' . $where . '';
                            $poner = false;
                            $bloqueo = 'disabled = \"disabled\"';
                            $inputTitular1->disabled = 'disabled';
                            $inputTitular2->disabled = 'disabled';

                            // no break
                        case 'generalizaCuenta':
                            /*Se desconoce el motivo por el cual se prefiere el grupo y ur de la cuenta contable al de la cuenta bancaria*/
                            if ($Bancos->getUcb_id() <> 4) {
                                $ur_id = $Bancos->getCta_gpo_id() . $Bancos->getCta_ur_id();
                            } else {
                                $ur_id = $Bancos->getGpo_id() . $Bancos->getUr_id();
                            }

                            $where = '';
                            switch ($Bancos->getUcb_id()) {
                                case 1:
                                    if ($Bancos->getBur_fondoRotatorio() == 'S') {
                                        $where = ' where ucb_id IN(1,2)';
                                    } else {
                                        $where = ' where ucb_id!=4 ';
                                    }
                                    $inputUrSolic->disabled = 'disabled';
                                    $poner1 = false;
                                    break;
                                case 2:
                                    if ($Bancos->getBur_fondoRotatorio() == 'S') {
                                        $where = ' where ucb_id in (2) ';
                                        $bloqueobton = 'disabled = \"disabled\"';
                                        $comboUsoCta->disabled = 'disabled';
                                        $inputUrSolic->disabled = 'disabled';
                                        $tmp = 'disabled = \"disabled\"';
                                        $tmp1 = 'disabled = \"disabled\"';
                                        $tmp2 = 'disabled = \"disabled\"';
                                    } else {
                                        $where = ' where ucb_id in (2,3) ';
                                    }
                                    break;
                                case 3:
                                    $comboUsoCta->disabled = 'disabled';
                                    $inputUrSolic->disabled = 'disabled';
                                    $tmp = 'disabled = \"disabled\"';
                                    $tmp1 = 'disabled = \"disabled\"';
                                    $tmp2 = 'disabled = \"disabled\"';
                                    // GEMMA .- $bloqueobton .-para inhabilitar el botón de actualizar el uso de la cuenta (btnUpdUso) si la Clabe Bancaria es Generica de fondo rotatorio.
                                    if ($Bancos->getBur_fondoRotatorio() == 'S') {
                                        $bloqueobton = 'disabled = \"disabled\"';
                                    }
                                    break;
                                case 4:
                                    $comboUsoCta->disabled = 'disabled';
                                    $comboUsoCta->lectura = true;
                                    $inputUrSolic->disabled = 'disabled';
                                    $tmp = 'disabled = \"disabled\"';
                                    $tmp1 = 'disabled = \"disabled\"';
                                    $tmp2 = 'disabled = \"disabled\"';
                                    break;
                            }

                            $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria ' . $where . '';
                            unset($inputUrSolic->readonly);

                            $sql = 'SELECT bur_tarjeta FROM ' . BD_BITACORA . ".bit_ModificacionBancosUr WHERE bur_id = ? and bur_tipoCambio='G'
                                    ORDER BY bit_bur desc";
                            $this->conectar();
                            $this->setSql($sql);
                            $this->consultar([$this->prm->bur_id]);
                            $this->desconectar();

                            $tarjetaOfiUcb = $this->extraer();

                            $generalizarCuenta =
                                '<div id="princ" class="container ">';
                            if ($poner1 && $Bancos->getUcb_id() != 4) {
                                $generalizarCuenta .= '
                                    <div id="uno" class="col-md-3 col-xs-4 form-group mb-4">
                                        <label for="inputTarjetaOficio">No. tarjeta/oficio</label>
                                        <input ' . $bloqueo . ' ' . $tmp . '  value ="' . $tarjetaOfiUcb['bur_tarjeta'] . '" type="text" class="form-control" required="1" id="inputTarjetaOficio" maxlength="20" placeholder="No. tarjeta/oficio" />
                                    </div>';
                                if ($Bancos->getUcb_id() == 2) {
                                    $generalizarCuenta .= '        
                                        <div id="dos" class="col-md-3 col-xs-4 form-group">
                                            <label  for="inputURcomp">UR Compartida</label>
                                            <input  ' . $bloqueo . ' ' . $tmp1 . ' type="text" class="form-control" required="1" id="inputURcomp" maxlength="3" placeholder="000" disabled="1" ' . (($Bancos->getUcb_id() == 2) ? 'value="' . $Bancos->getBur_gpo_id() . $Bancos->getBur_ur_id() . '"' : '') . ' >
                                        </div>';
                                }
                            }
                            /* GEMMA .- $bloqueobton .-para inhabilitar el botón de actualizar el uso de la cuenta (btnUpdUso)si la Clabe Bancaria es propia de fondo rotatorio.*/
                            if ($poner && $this->prm->tarea == 'generalizaCuenta') {
                                $generalizarCuenta .= '<div id="comboTipoClc" class="col-md-3 col-xs-3 mb-4" style="display:none;">' . $this->ponCombo($tipoClc) . '</div>';
                                $generalizarCuenta .= '
                                    <div class="col-md-3 col-xs-4 mb-4">
                                        <button  ' . $bloqueo . ' ' . $bloqueobton . ' ' . $tmp2 . ' id="btnUpdUso" ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '" gpo_id= "' . $_SESSION[_S]['clasAdm-gpo'] . '" type="button" class="btn btn-outline-success">
                                            <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar uso de cuenta
                                        </button>
                                    </div>';
                            }

                            $generalizarCuenta .= '</div>';

                            $sql = 'SELECT bur_tarjeta 
                                    FROM ' . BD_BITACORA . ".bit_ModificacionBancosUr 
                                    WHERE bur_id = ? and bur_tipoCambio='T'
                                    ORDER BY bit_bur desc";

                            $this->conectar();
                            $this->setSql($sql);
                            $this->consultar([$this->prm->bur_id]);
                            $this->desconectar();

                            $tarjetaOfiTit = $this->extraer();

                            $tarjeta = isset($tarjetaOfiTit['bur_tarjeta']) ? $tarjetaOfiTit['bur_tarjeta'] : '';
                            $cambioTitulares = '
                                <div class="container ">
                                    <div class="form-inline">
                                        <div class="col-md-3 col-xs-6 form-group mb-4">
                                            <label for="inputTarjetaOficio">No. tarjeta/oficio</label>
                                            <input ' . $bloqueo . '  value = "' . $tarjeta . '" type="text" class="form-control" required="1" id="inputTarjeta"     maxlength="20" placeholder="No. tarjeta" />
                                        </div>' . (($poner && $this->prm->tarea == 'generalizaCuenta') ? '
                                        <div class="col-md-3 col-xs-6 mb-4">
                                            <button id="btnUpdTitulares" type="button" class="btn btn-outline-success">
                                                <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar titulares
                                            </button>
                                        </div>' : '') . '
                                    </div>
                                </div>';
                            break;

                        default:
                            $inputTitular1->readonly = 'readonly';
                            $inputTitular2->readonly = 'readonly';
                            break;
                    }
                    $inputcargo1->readonly = 'readonly';
                    $inputcargo2->readonly = 'readonly';
                    $comboInsBan->disabled = 'disabled';
                    $inputCuenta->readonly = 'readonly';
                    $inputPlaza->readonly = 'readonly';
                    $inputSucursal->readonly = 'readonly';
                    $inputAlias->readonly = 'readonly';
                    $inputBeneficiario->readonly = 'readonly';
                    $inputBeneficiario->readonly = 'readonly';
                    $editables = " disabled='disabled' ";
                    $comboUE->disabled = 'disabled'; // deshabilitamos el campo de municipios
                    if (!$this->contabilidad) {
                        $inputNivelReg->readonly = 'readonly';
                    }
                    break;
                case 4:
                    $comboUsoCta->disabled = 'disabled';

                    if ($Bancos->getUcb_id() <> 4) {
                        $ur_id = $Bancos->getCta_gpo_id() . $Bancos->getCta_ur_id();
                    } else {
                        $ur_id = $Bancos->getGpo_id() . $Bancos->getUr_id();
                    }
                    // no break
                default:
                    $comboInsBan->disabled = 'disabled';
                    $inputCuenta->readonly = 'readonly';
                    $inputPlaza->readonly = 'readonly';
                    $inputSucursal->readonly = 'readonly';
                    $inputAlias->readonly = 'readonly';
                    $inputBeneficiario->readonly = 'readonly';
                    $inputTitular1->readonly = 'readonly';
                    $inputcargo1->readonly = 'readonly';
                    $inputTitular2->readonly = 'readonly';
                    $inputcargo2->readonly = 'readonly';
                    $inputNivelReg->readonly = 'readonly';
                    $editables = " disabled='disabled' ";
                    $comboUE->disabled = 'disabled';    // se deshabilita el combo del municipio
                    break;
            }

            $sql = 'SELECT * FROM vin_BancosUrTipoClc WHERE bur_id = ?;';
            $non = new AccesoDatos();
            $non->conectar();
            $non->setSql($sql);
            $non->consultar([$this->prm->bur_id]);
            while ($r = $non->extraer()) {
                array_push($UsodeCta, $r['tcl_id']);
            }
            $non->desconectar();
            unset($non);
        } else {
            /* aqui no establecemos nada porque estamos agregando un nuevo registro  */
            $Bancos = new BancosUr();
            $this->prm->eba_id = 1;
            $comboUsoCta->sql = 'SELECT ucb_id, ucb_nombre FROM cat_UsoCuentaBancaria WHERE ucb_id in (1,4)';
            $inputUrSolic->value = $ur_id; //el valor del input se establece igual a la UR
            $inputUrSolic->readonly = 'readonly';
        }

        $Bancos->getBur_id() > 0 ? $comboUE->selecciona = $Bancos->getUe_id() : ''; // cargamos el combo del municipio con el valor que ya tenia.
        $comboUE->sql = str_replace('{gpo_id}', ($Bancos->getBur_id() > 0 ? $Bancos->getGpo_id() : $this->gpo_id), $comboUE->sql);
        $comboUE->sql = str_replace('{ur_id}', ($Bancos->getBur_id() > 0 ? $Bancos->getUr_id() : $this->ur_id), $comboUE->sql);

        $inputClabe->id = 'bur_id';
        $inputClabe->label = 'Clabe Interbancaria';
        $inputClabe->placeholder = '000000000000000000';
        $inputClabe->enDiv = true;
        $inputClabe->required = true;
        $inputClabe->maxLength = 18;
        $inputClabe->minLength = 18;
        $inputClabe->value = $Bancos->getBur_id();

        $inputCuenta->id = 'cuentaBancaria';
        $inputCuenta->label = 'Cuenta Bancaria';
        $inputCuenta->placeholder = '0000000000';
        $inputCuenta->enDiv = true;
        $inputCuenta->required = true;
        $inputCuenta->maxLength = 11;
        $inputCuenta->minLength = 10;
        $inputCuenta->value = $Bancos->getCuentaBancaria();
        $inputCuenta->readonly = 'readonly';

        $comboInsBan->id = 'iba_id';
        $comboInsBan->sql = '
                    SELECT iba_id, iba_abreviatura 
                    FROM tesoreria.cat_InstitucionBancaria 
                    WHERE iba_id != 0 ';
        $comboInsBan->label = 'Institución Bancaria';
        $comboInsBan->enDiv = true;
        $comboInsBan->disabled = true;
        $comboInsBan->selecciona = $Bancos->getIba_id();

        $inputPlaza->id = 'plaza';
        $inputPlaza->label = 'Plaza';
        $inputPlaza->placeholder = 'OAX';
        $inputPlaza->enDiv = true;
        if (!empty($Bancos->getPlaza())) {
            $this->conectar();
            $this->setSql('SELECT pbn_numero,pbn_nombre FROM tesoreria.cat_PlazaBancaria where pbn_numero = ?;');
            $this->consultar([$Bancos->getPlaza()]);
            $valor_plaza = $this->extraer();
            $this->desconectar();

            $inputPlaza->value = $valor_plaza['pbn_numero'] . '-' . $valor_plaza['pbn_nombre'];
        }
        $inputPlaza->readonly = 'readonly';

        $inputSucursal->id = 'sucursal';
        $inputSucursal->label = 'Sucursal';
        $inputSucursal->placeholder = '00000';
        $inputSucursal->enDiv = true;
        $inputSucursal->maxLength = 5;
        $inputSucursal->value = $Bancos->getSucursal();

        $inputAlias->id = 'alias';
        $inputAlias->label = 'Alias';
        $inputAlias->placeholder = 'SERV.PERS. Y G.OPER.';
        $inputAlias->enDiv = true;
        $inputAlias->required = true;
        $inputAlias->value = $Bancos->getAlias();

        $inputBeneficiario->id = 'beneficiario';
        $inputBeneficiario->label = 'Beneficiario';
        $inputBeneficiario->placeholder = $_SESSION[_S]['ur_nombre'];
        $inputBeneficiario->enDiv = true;
        $inputBeneficiario->required = true;
        $inputBeneficiario->value = $Bancos->getBeneficiario();

        $comboUsoCta->id = 'ucb_id';
        $comboUsoCta->label = 'Uso de Cuenta';
        $comboUsoCta->enDiv = true;
        $comboUsoCta->required = true;
        $comboUsoCta->selecciona = $Bancos->getUcb_id();

        switch ($this->prm->tarea) {
            case 'addModCuenta':

                switch ($Bancos->getEba_id()) {
                    case 1:
                        $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria WHERE ucb_id in (1,4)';
                        break;
                    case 3:
                        $comboUsoCta->readonly = true;
                        $comboUsoCta->disabled = true;
                        break;
                    case 4:
                        $comboUsoCta->readonly = true;
                        $comboUsoCta->disabled = true;
                        break;
                    case 5:
                        switch ($Bancos->getUcb_id()) {
                            case 1:
                                unset($comboUsoCta->readonly);
                                unset($comboUsoCta->disabled);
                                $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria WHERE ucb_id in (1,4)';
                                break;
                            case 4:
                                unset($comboUsoCta->readonly);
                                unset($comboUsoCta->disabled);
                                // $comboUsoCta->selecciona=$Bancos->getUcb_id();
                                $comboUsoCta->sql = 'SELECT ucb_id,ucb_nombre FROM cat_UsoCuentaBancaria WHERE ucb_id in (1,4)';
                                break;
                            default:
                                $comboUsoCta->readonly = true;
                                $comboUsoCta->disabled = true;
                                break;
                        }
                        break;
                    default:
                        if ($Bancos->getUcb_id() == 4) {
                            $comboUsoCta->sql = 'SELECT ucb_id, ucb_nombre FROM cat_UsoCuentaBancaria WHERE ucb_id =4';
                        }
                        break;
                }
                break;
            default:
                if ($Bancos->getUcb_id() == 4) {
                    $comboUsoCta->sql = 'SELECT ucb_id, ucb_nombre FROM cat_UsoCuentaBancaria WHERE ucb_id =4';
                }
                break;
        }

        $lectura = 'true';
        switch ($Bancos->getEba_id()) {
            case 1:
            case 5:
                if ($_SESSION[_S]['nvj_id'] <= 2) {
                    $lectura = 'false';
                } //escritura
                break;
            case 4:
            case 6:
            case 2:
            case 3:
                break;
            default:
                /*cuando se genera una nueva cuenta, debe ser de escritura*/
                if ($_SESSION[_S]['nvj_id'] <= 2) {
                    $lectura = 'false';
                } //escritura
                break;
        }
        $params = [$_SESSION[_S]['gpo_id'], $_SESSION[_S]['ur_id']];
        $this->conectar();
        $this->setSql('SELECT ur_nombre FROM cat_UnidadResponsable WHERE gpo_id = ? AND ur_id = ?');
        $this->consultar($params);
        $nombreUR = $this->extraer();
        $this->desconectar();

        // Dibujamos la pantalla que permite buscar un proveedor
        $this->mixHtml(DibujaPantallaBuscarProveedorHTML());
        $this->mixScript(DibujaPantallaBuscarProveedorJS());

        $this->mixScript('
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ\u00f1\u00d1\d]/;
            var permitirCaracteresNumericos = /\d/;

            function numericos(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127))
                    return true;
                return permitirCaracteresNumericos.test(String.FROMCharCode(keynum));
            }

            $("#sucursal").on("keypress", function(e) {
                return numericos(e);
            });

            $("#inputRFC_id").blur(function() {
                if ($("#inputRFC_id").val() != "") {
                    comunica("' . $this->pantalla . '", {
                        metodo: "AgregaBeneficiario",
                        rfc_id: $("#inputRFC_id").val()
                    });
                }
            });

            $("#ucb_id").change(function() {
                comunica("' . $this->pantalla . '", {
                    "metodo": "ComboTcB",
                    combo: $("#ucb_id").find("option:selected").val(),
                    tcb_id: "tcb_id"
                });

                switch ($(this).val()) {
                    case "1":
                        $("#div_rfc").hide();
                        $("#div_ursolic").show();

                        if ("' . $this->usr_id . '" == "0") {
                            $("#rad_E").attr("disabled", "disabled");
                        } else if ("' . $Bancos->getEba_id() . '" != "1" && "' . $Bancos->getEba_id() . '" != "") {
                            $("#rad_E").attr("disabled", "disabled");
                        } else if ("' . $Bancos->getEba_id() . '" == "") {
                            $("#rad_E").removeAttr("disabled");
                        }

                        if ("' . $Bancos->getEba_id() . '" == "5" || "' . $Bancos->getEba_id() . '" == "1") {
                            $("#rad_E").removeAttr("disabled");
                        }

                        $("#uno").remove();
                        $("#dos").remove();
                        $("#inputURcomp").attr("disabled", "disabled");
                        $("#inputURcomp").val("");
                        $("#inputUrSolic").val(' . $Bancos->getGpo_id() . $Bancos->getUr_id() . ');
                         ' . (($ur_id == 902 || $ur_id == 901) ? '' : '$("#beneficiario").val("' . str_replace('"', '\"', (empty($Bancos->getBeneficiario()) ? $nombreUR['ur_nombre'] : $Bancos->getBeneficiario())) . '");') . '
                        $("#beneficiario").attr("readonly", "readonly");
                        $("#inputUrSolic").attr("readonly", "readonly");
                        $("#comboTipoClc").css("display", "none");
                        $("#tcl_id").removeAttr("required");
                        $("#inputRFC_id").removeAttr("required");
                        var lectura = "' . $lectura . '";
                        var UsodeCta = Array("' . (implode('","', $UsodeCta)) .
            '");

                if (lectura == "false") {
                    if (document.getElementById("rad_A"))
                        document.getElementById("rad_A").checked = false;
                    if (document.getElementById("rad_B"))
                        document.getElementById("rad_B").checked = false;
                    if (document.getElementById("rad_F"))
                        document.getElementById("rad_F").checked = false;
                    if (UsodeCta.indexOf("A") !== -1)
                        document.getElementById("rad_A").checked = true;
                    if (UsodeCta.indexOf("B") !== -1)
                        document.getElementById("rad_B").checked = true;
                    if (UsodeCta.indexOf("F") !== -1)
                        document.getElementById("rad_F").checked = true;
                }
                break;
                case "2":
                $("#div_rfc").hide();
                $("#div_ursolic").show();

                $("#uno").remove();
                $("#dos").remove();
                $("#inputRFC_id").removeAttr("required");
                $("#princ").prepend("<div id=\"uno\" class=\"col-md-3 col-xs-4 form-group mb-4\"><label  for=\"inputTarjetaOficio\">No. tarjeta/oficio</label><input  ' . $bloqueo . ' ' . $tmp . '  value =\"' . (isset($tarjetaOfiUcb['bur_tarjeta']) ? $tarjetaOfiUcb['bur_tarjeta'] : '') . '\" type=\"text\" class=\"form-control\" required=\"1\" id=\"inputTarjetaOficio\" maxlength=\"20\" placeholder=\"No. tarjeta/oficio\"></div><div id=\"dos\" class=\"col-md-3 col-xs-4 form-group\"><label  for=\"inputURcomp\">UR Compartida</label><input  ' . $bloqueo . ' ' . $tmp1 . ' type=\"text\" class=\"form-control\" required=\"1\" id=\"inputURcomp\" maxlength=\"3\" placeholder=\"000\" disabled=\"1\" ></div>");
                $("#comboTipoClc").css("display", "block");
                $("#tcl_id").attr("required", "1");
                if ("' . $Bancos->getUcb_id() . '" != 2) {
                    $("#inputURcomp").removeAttr("disabled", "1");
                } else {
                    $("#inputURcomp").attr("disabled", "1");
                }
                $("#inputURcomp").val(' . (($Bancos->getUcb_id() == 2) ? $Bancos->getBur_gpo_id() . $Bancos->getBur_ur_id() : ' ') . ');
                $("#inputUrSolic").attr("disabled", "disabled");

                var permitirCaracteresNumericos = /^[\d]/;
                $("#inputURcomp").keypress(function(e) {
                    var keynum = window.event ? window.event.keyCode : e.which;
                    if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                        return true;
                    return permitirCaracteresNumericos.test(String.FROMCharCode(keynum));
                });

                $("#inputURcomp").blur(function() {
                    if ($("#inputURcomp").val() != "" && $("#inputURcomp").val().length == 3) {
                        comunica("' . $this->pantalla . '", {
                            metodo: "ValidaUr",
                            Ur: $(this).val()
                        });
                    } else {
                        notify("UR no Válida", "error");
                    }
                });
                break;

                case "3":
                $("#div_rfc").hide();
                $("#div_ursolic").show();
                $("#uno").remove();
                $("#dos").remove();
                $("#comboTipoClc").css("display", "none");
                $("#tcl_id").removeAttr("required");
                $("#princ").prepend("<div id=\"uno\" class=\"col-md-3 col-xs-4 form-group mb-4\"><label  for=\"inputTarjetaOficio\">No. tarjeta/oficio</label><input  ' . $bloqueo . ' ' . $tmp . '  value =\"' . (isset($tarjetaOfiUcb['bur_tarjeta']) ? $tarjetaOfiUcb['bur_tarjeta'] : '') . '\" type=\"text\" class=\"form-control\" required=\"1\" id=\"inputTarjetaOficio\" maxlength=\"20\" placeholder=\"No. tarjeta/oficio\"></div>");
                $("#inputUrSolic").removeAttr("disabled");
                $("#inputUrSolic").removeAttr("readonly");
                $("#inputURcomp").attr("disabled", "disabled");
                $("#inputURcomp").val("");
                $("#inputRFC_id").removeAttr("required");
                break;

                case "4":
                $("#div_rfc").show();
                $("#div_ursolic").hide();

                $("#rad_E").attr("disabled", "disabled");
                $("#rad_E").removeAttr("checked");

                $("#inputRFC_id").attr("required");
                $("#inputRFC_id").attr("placeholder", "F1: Catalogo");
                $("#inputRFC_id").addClass("F1Catalogo");
                $("#inputRFC_id").attr("maxlength", 13);
                $("#inputURcomp").attr("disabled", "disabled");
                $("#inputURcomp").val("");
                $("#tcl_id").removeAttr("required");
                $("#comboTipoClc").css("display", "none");
                $("#vincula_cuenta").css("display", "none");

                var lectura = "' . $lectura . '";
                var UsodeCta = Array("' . (implode('","', $UsodeCta)) . '");
                if (lectura === "false") {
                    $("#rad_A").removeAttr("disabled");
                    $("#rad_B").removeAttr("disabled");
                    $("#rad_F").removeAttr("disabled");
                    if (document.getElementById("rad_A"))
                        document.getElementById("rad_A").checked = false;
                    if (document.getElementById("rad_B"))
                        document.getElementById("rad_B").checked = false;
                    if (document.getElementById("rad_F"))
                        document.getElementById("rad_F").checked = false;
                    if (UsodeCta.indexOf("A") !== -1)
                        document.getElementById("rad_A").checked = true;
                    if (UsodeCta.indexOf("B") !== -1)
                        document.getElementById("rad_B").checked = true;
                    if (UsodeCta.indexOf("F") !== -1)
                        document.getElementById("rad_F").checked = true;
                }
            $("#inputRFC_id").focus(function(e) {
                rfcCampo = "inputRFC_id";
                rfcBeneficiario = "beneficiario";
                tipoBeneficiario = {
                    campo1: "titular1",
                    campo2: "titular_2"
                };
            });
            ' . $this->ponF1Proveedores('inputRFC_id', ['beneficiario']) . '
            break;
            default:
                $("#rad_A").removeAttr("disabled"); $("#rad_B").removeAttr("checked"); $("#rad_B").removeAttr("disabled"); $("#rad_F").removeAttr("disabled");
                break;
            }
            });
        ');

        $inputUrSolic->id = 'inputUrSolic';
        $inputUrSolic->label = 'UR Solicitante';
        $inputUrSolic->placeholder = '000';
        $inputUrSolic->enDiv = true; // lo pone en un contenedor con estilo
        $inputUrSolic->required = true; // required significa que no puede quedarse vacio a la hora de guardar

        $inputRFC_id = new stdClass();
        $inputRFC_id->id = 'inputRFC_id';
        $inputRFC_id->name = 'inputRFC_id';
        $inputRFC_id->label = 'RFC';

        $inputRFC_id->autocomplete = true;
        $inputRFC_id->tabla = 'cat_Proveedor ';
        $inputRFC_id->tablas = 'INNER JOIN sinpres.cat_Proveedores USING ( rfc_id )';
        $inputRFC_id->campoVal = 'rfc_id';
        $inputRFC_id->camposText = 'rfc_nombre';
        $inputRFC_id->desdecaracter = 3;

        $inputRFC_id->placeholder = 'Ingresar RFC';
        $inputRFC_id->maxLength = 13;
        $inputRFC_id->enDiv = true;
        if ($abc == 'c' && $Bancos->getUcb_id() == 4) {
            $inputRFC_id->required = true;
        }
        $inputRFC_id->value = $Bancos->getRfc_id();

        $comboTipoCta = new stdClass();
        $comboTipoCta->id = 'tcb_id';
        if ($_SESSION[_S]['clasAdm-gpo'] == 1 && $_SESSION[_S]['clasAdm-ur'] == 16) {
            $comboTipoCta->sql = 'SELECT tcb_id,tcb_nombre FROM cat_TipoCuentaBancaria';
            $comboTipoCta->selecciona = $Bancos->getTcb_id();
        } else {
            $comboTipoCta->sql = 'SELECT tcb_id,tcb_nombre FROM cat_TipoCuentaBancaria WHERE tcb_id = 1';
        }
        $comboTipoCta->label = 'Tipo de Cuenta';
        $comboTipoCta->enDiv = true;
        $comboTipoCta->required = true;

        $inputTitular1->id = 'titular1';
        $inputTitular1->label = 'Titular 1';
        $inputTitular1->placeholder = 'LIC.EMMANUEL LÓPEZ';
        $inputTitular1->maxLength = 100;
        $inputTitular1->enDiv = true;
        $inputTitular1->required = true;
        $inputTitular1->value = $Bancos->getTitular1();

        $inputcargo1->id = 'cargo1';
        $inputcargo1->label = 'Cargo 1';
        $inputcargo1->placeholder = 'PRESIDENTE DE LA JUNTA DE COORDINACIÓN';
        $inputcargo1->maxLength = 100;
        $inputcargo1->enDiv = true;
        $inputcargo1->required = true;
        $inputcargo1->value = $Bancos->getCargo1();

        $inputTitular2->id = 'titular_2';
        $inputTitular2->label = 'Titular 2';
        $inputTitular2->placeholder = 'LIC.ANSELMO GARCÍA';
        $inputTitular2->maxLength = 100;
        $inputTitular2->enDiv = true;
        $inputTitular2->required = true;
        $inputTitular2->value = $Bancos->getTitular_2();

        $inputcargo2->id = 'cargo2';
        $inputcargo2->label = 'Cargo 2';
        $inputcargo2->placeholder = 'TESORERO';
        $inputcargo2->maxLength = 100;
        $inputcargo2->enDiv = true;
        $inputcargo2->required = true;
        $inputcargo2->value = $Bancos->getCargo2();

        $inputCtaNiv1 = new stdClass();
        $inputCtaNiv1->id = 'inputCtaNiv1';
        $inputCtaNiv1->label = 'Nivel de Mayor';
        $inputCtaNiv1->enDiv = true;
        $inputCtaNiv1->readonly = 'readonly';
        $inputCtaNiv1->attr = new StdClass();
        $inputCtaNiv1->attr->niv1 = $Bancos->getGer_id() . $Bancos->getCgp_id() .
            $Bancos->getRub_id() . $Bancos->getCgcu_id() . $Bancos->getCgsc_id();
        $inputCtaNiv1->value = $Bancos->getGer_id() .
            $Bancos->getCgp_id() .
            $Bancos->getRub_id() .
            $Bancos->getCgcu_id() .
            $Bancos->getCgsc_id() . ' - BANCO MONEDA NACIONAL';

        $inputUnidadRes = new stdClass();
        $inputUnidadRes->id = 'cta_ur_id';
        $inputUnidadRes->label = 'Unidad Responsable';
        $inputUnidadRes->enDiv = true;
        $inputUnidadRes->attr = new StdClass();
        $inputUnidadRes->readonly = 'readonly';

        // SI es usu del cuenta 2 o  3 y el estado es 6 {
        $inputUnidadEje = new stdClass();
        $inputUnidadEje->id = 'cta_ue_id';
        $inputUnidadEje->label = 'Unidad Ejecutora';
        $inputUnidadEje->enDiv = true;
        $inputUnidadEje->attr = new StdClass();
        $inputUnidadEje->attr->ue_id = 'ue_id';

        if ($this->contabilidad && ($Bancos->getUcb_id() == 1 || $Bancos->getUcb_id() == 2 || $Bancos->getUcb_id() == 3)) {
            $params = [$this->prm->gpo_id, $this->prm->ur_id];
            $sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND eue_id=6 ';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            if ($this->getNumeroRegistros() == 0) {
                $inputUnidadEje->sql = 'SELECT "NO HAY UNIDADES EJECUTORAS VALIDADAS CONSULTE A SU ANALISTA"';
                $params = [];
            } else {
                $params = [$this->prm->gpo_id, $this->prm->ur_id];
                $inputUnidadEje->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND eue_id=6 ';
            }
            $this->desconectar();
            $inputUnidadEje->required = true;
            $inputUnidadEje->selecciona = $Bancos->getCta_ue_id();
        } else {
            $inputUnidadEje->value = $Bancos->getCta_ue_id();
            $inputUnidadEje->readonly = 'readonly';
        }
        $inputUnidadEje->params = $params;

        $gpotmp = 0;
        $urtmp = 0;

        if ($Bancos->getUcb_id() == 4 || ($_SESSION[_S]['gpo_id'] == 1 && $_SESSION[_S]['ur_id'] == 16) || in_array($ur_id, $gpo8)) {
            $gpotmp = $Bancos->getGpo_id();
            $urtmp = $Bancos->getUr_id();
            $ur_id = $Bancos->getGpo_id() . $Bancos->getUr_id();
        } elseif ($Bancos->getUcb_id() != 4 && !in_array($Bancos->getGpo_id() . $Bancos->getUr_id(), $gpo8) && $Bancos->getGpo_id() . $Bancos->getUr_id() != 116) {
            $gpotmp = $Bancos->getCta_gpo_id();
            $urtmp = $Bancos->getCta_ur_id();
            $ur_id = $Bancos->getCta_gpo_id() . $Bancos->getCta_ur_id();
            $gpotmp = $Bancos->getGpo_id();
            $urtmp = $Bancos->getUr_id();
            $ur_id = $Bancos->getGpo_id() . $Bancos->getUr_id();
        } else {
            $gpotmp = $Bancos->getGpo_id();
            $urtmp = $Bancos->getUr_id();
            $ur_id = $Bancos->getGpo_id() . $Bancos->getUr_id();
        }

        if ($abc == 'a') {
            $ur_id = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        }

        $params = [];
        $sql =
            ' SELECT ur_clave, ur_nombre, ue_clave, ue_id, ue_nombre
            FROM  cat_UnidadResponsable a 
            JOIN  cat_UnidadEjecutora b on a.gpo_id = b.gpo_id and a.ur_id = b.ur_id and a.ur_ueContable = b.ue_id ';
        if (($this->prm->eba_id == 4 || $this->prm->eba_id == 6) && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] != 116 || in_array($ur_id, $gpo8))) {
            $sql .= 'WHERE a.gpo_id = ? and a.ur_id = ?';
            $params = [$gpotmp, $urtmp];
        } else {
            $sql .= 'WHERE a.gpo_id = ? and a.ur_id = ? ';
            $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
        }

        $this->setSql($sql);
        $this->conectar();
        $this->consultar($params);
        if ($r = $this->extraer()) {
            $inputUnidadRes->value = $r['ur_clave'] . ' - ' . $r['ur_nombre'];
            $inputUnidadRes->attr->ur = $r['ur_clave'];

            $inputUnidadEje->value = $r['ue_id'] . ' - ' . $r['ue_nombre'];
            $inputUnidadEje->attr = new StdClass();
            $inputUnidadEje->attr->ue = $r['ue_id'];
        }
        $this->desconectar();

        $inputNivelReg->id = 'cta_id';
        $inputNivelReg->label = 'Nivel de Registro';
        $inputNivelReg->placeholder = '0000';
        $inputNivelReg->enDiv = true;
        $inputNivelReg->maxLength = 4;
        $inputNivelReg->minLength = 4;
        $inputNivelReg->value = $Bancos->getCta_id();
        $inputNivelReg->required = true;

        $inputCtaContable->id = 'cta_cuenta';
        $inputCtaContable->label = 'Cuenta Contable';
        $inputCtaContable->enDiv = true;
        $inputCtaContable->readonly = 'readonly';

        $btnGuardaCtaCont = new stdClass();
        $btnGuardaCtaCont->id = 'btnGuardaCtaCont';
        $btnGuardaCtaCont->class = 'GuardarCta';
        $btnGuardaCtaCont->text = 'Guardar Cuenta Contable';
        $btnGuardaCtaCont->value = 'Guardar Cuenta Contable';
        $btnGuardaCtaCont->color = 'verde';
        $btnGuardaCtaCont->icon = 'fa-solid fa-floppy-disk me-2';

        switch ($this->prm->tarea) {
            case 'ConsultaCuentas':
                $this->mixScript('
                    $("#btnRegresaIndex").click (function(){
                        comunica("' . $this->pantalla . '",{
                        "metodo":"ConsultaCuentas",
                        "tarea":"' . $this->prm->tarea . '",
                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                        }
                    );
                        return false;
                    });
                ');
                break;
            default:
                $this->mixScript('
                    $("#btnRegresaIndex").click (function(){
                        comunica("' . $this->pantalla . '",
                        {"metodo":"ponTablaListadoCuentas",
                        "tarea":"' . $this->prm->tarea . '",
                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                        });
                        return false;
                    });
                ');
                break;
        }

        $form = new stdClass();
        $form->id = 'bancos';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcBancos';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->tarea = ((isset($this->prm->tarea)) ? $this->prm->tarea : '');
        if (isset($this->prm->bur_id)) {
            $form->datosExtra->bur_id = $this->prm->bur_id;
        }
        $formulario = new form($form);
        $radcheck = '';

        if (in_array($ur_id, $urs)) {

            $radcheck .= '
                <table style="width:100%" border="0">
                    <tr>'
                . '<td id="radios_afectacion"><input type="checkbox" id="rad_A" class = "tipoClcReq" name="UsodeCta[]" value="A" ' . ((in_array('A', $UsodeCta)) ? 'checked' : '') . $editables . ' /><label for="rad_A">Servicios Personales</label></td>'
                . '<td id="radios_afectacion"><input type="checkbox" id="rad_B" class = "tipoClcReq" name="UsodeCta[]" value="B" ' . ((in_array('B', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_B">Gastos de Operación</label></td>'
                . '<td id="radios_afectacion"><input type="checkbox" id="rad_F" class = "tipoClcReq" name="UsodeCta[]" value="F" ' . ((in_array('F', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_F">Inversión Pública</label>'
                . '</tr>
                </table>';
        } elseif ($ur_id == '116') {

            $radcheck .= '
                <table style="width:100%" border="0">
                    <tr>'
                . '<td id="radios_afectacion"><input type="checkbox" id="rad_J"  class = "tipoClcReq" name="UsodeCta[]" value="J" ' . ((in_array('J', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_J">Deuda Publica</label></td>'
                . '<td id="radios_afectacion"><input type="checkbox" id="rad_K"  class = "tipoClcReq" name="UsodeCta[]" value="K" ' . ((in_array('K', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_K">Bursatilizacion</label></td>'
                . '<td id="radios_afectacion"><input type="checkbox" id="rad_L"  class = "tipoClcReq" name="UsodeCta[]" value="L" ' . ((in_array('L', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_L">Transferencias</label>'
                . '<td id="radios_afectacion"><input type="checkbox" id="rad_M"  class = "tipoClcReq" name="UsodeCta[]" value="M" ' . ((in_array('M', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_M">Pagos a Terceros</label>'
                . '</tr>
                </table>';
        } elseif ($ur_id == '901') {
            $radcheck .=
                '<table style="width:100%" border="0">
                    <tr>'
                . '<td id="radios_afectacion"><input type="radio" id="rad_I" class = "tipoClcReq" name="UsodeCta" value="I" ' . ((in_array('I', $UsodeCta)) ? 'checked' : '') . $editables . '/><br/><label for="rad_I">Municipios</td>'
                . '</tr>
                </table>';
        } else {
            $radcheck .= '
                <table style="width:100%" border="0">
                    <tr>'
                . '<td id="radios_afectacion"><input type="radio" id="rad_A" class = "tipoClcReq" required="required" name="UsodeCta" value="A" ' . ((in_array('A', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_A">Servicios Personales</label></td>'
                . '<td id="radios_afectacion"><input type="radio" id="rad_B" class = "tipoClcReq" required="required" name="UsodeCta" value="B" ' . ((in_array('B', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_B">Gastos de Operación</label></td>'
                . '<td id="radios_afectacion"><input type="radio" id="rad_F" class = "tipoClcReq" required="required" name="UsodeCta"value="F" ' . ((in_array('F', $UsodeCta)) ? 'checked' : '') . $editables . '/><label for="rad_F">Inversión Pública</label>'
                . '</tr>
                </table>';
        }

        $CheckFonRot = '
            <div>
                <caption style="font-size:14px; text-align:left; margin-left:30px;  font-weight:bold;">
                    <label for="rad_E"><b>Fondo Rotatorio</b></label>
                </caption>';
        $CheckFonRot .= '
            <tr>
                <td  id="CheckFonRot" class = "tipoClcReq" style="padding:16px; font-size:14px; border-left:1px solid #EAEAEA;" >
                    <input type="checkbox" style="margin-left:12px;  text-align:left;" id="rad_E" name="tipoClcE" value="E" ' . ($Bancos->getBur_fondoRotatorio() == 'S' ? 'checked' : '') . $editables . ' />
                </td>
            </tr>';
        //SI SE BORRA DEL ENCUADRE EL TIPO DE CLC =E ENTONCES SE BORRA LA CONDICION IN_ARRAY('E',$UsodeCta) de la linea 1344  JAPHET
        $CheckFonRot .= '
            </div>';

        $combomunicipios = ($ur_id == 902 || $ur_id == 901) ? '<div class="col-md-12 col-xs-12 ">' . $this->ponCombo($comboUE) . '</div>' : '';

        $formulario->mixHtml(
            $mensaje .
                '<legend>Datos generales de la cuenta</legend>'
                . '<div class= "row">'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputClabe) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputCuenta) . '</div>'
                . '<div class="col-md-12 col-xs-12 mb-4">' . $this->ponCombo($comboInsBan) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputPlaza) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputSucursal) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputAlias) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputBeneficiario) . '</div>'
                . $combomunicipios
                . '<div class="col-md-3 col-xs-3 mb-4">' . $this->ponCombo($comboUsoCta) . '</div>'
                . '<div class="col-md-3 col-xs-3 mb-4">'
                . '<div id="div_ursolic" >' . $this->ponInput($inputUrSolic) . '</div>'
                . '<div id="div_rfc" style="display:none">' . $this->ponInput($inputRFC_id) . '</div>' . '</div>'
                . '<div class="col-md-3 col-xs-3 mb-4">' . $this->ponCombo($comboTipoCta) . '</div>'
                . '<div class="col-md-3 col-xs-3 mb-4">'
                . $CheckFonRot
                . '</div>'
                . $generalizarCuenta
                . '<legend></legend>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputTitular1) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputcargo1) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputTitular2) . '</div>'
                . '<div class="col-md-6 col-xs-6 mb-4">' . $this->ponInput($inputcargo2) . '</div>'
                . $cambioTitulares
                . '<div class="col-md-12 col-xs-12 mb-4">'
                . '<legend class="mb-4">Tipo de CLC asignado a la Cuenta Bancaria</legend>'
                . $radcheck
                . '</td>'
                . '</div></div>'
        );

        /****LLEVARA TODOS LOS BOTONES DE LA PANTALLA SEGUN SEAN EVALUADOS LOS CASOS [TAREAS,ESTADOCUENTABANCARIA, USO DE CUENTA,...] ***/
        $tmp = '';
        /**** BOTON REGRESAR  ***/
        $tmp .= '
            <button id="btnRegresaIndex" type="button" class="btn btn-outline-secondary me-1" title="Regresar">
                <i class="fa-solid fa-circle-left me-2"></i>Regresar
            </button>';
        /**** BOTON REGRESAR  ***/
        $modalRechazo = '';
        $modalHistorial = '';
        if ($abc == 'c') {

            $thtable = [];
            $trtable = [];

            $th = new stdClass();
            $th->texto = 'Clabe';
            array_push($thtable, $th);
            $th = new stdClass();
            $th->texto = 'Cuenta';
            array_push($thtable, $th);
            $th = new stdClass();
            $th->texto = 'Banco';
            array_push($thtable, $th);
            $th = new stdClass();
            $th->texto = 'Motivo de rechazo';
            array_push($thtable, $th);
            $th = new stdClass();
            $th->texto = 'Fecha de rechazo';
            array_push($thtable, $th);

            $td = new stdClass();
            $td->campoTxt = 'bur_id';
            array_push($trtable, $td);
            $td = new stdClass();
            $td->campoTxt = 'bur_cuentaBancaria';
            array_push($trtable, $td);
            $td = new stdClass();
            $td->campoTxt = 'iba_nombre';
            array_push($trtable, $td);
            $td = new stdClass();
            $td->campoTxt = 'bit_observacion';
            array_push($trtable, $td);
            $td = new stdClass();
            $td->campoTxt = 'bit_fecha';
            array_push($trtable, $td);

            $tablaHistorial = new stdClass();
            $tablaHistorial->id = 'tbl_historialCuenta';
            $tablaHistorial->titulo = 'Historial de la cuenta';
            $tablaHistorial->encabezado = [$thtable];
            $tablaHistorial->cuerpo = $trtable;
            $tablaHistorial->comunica = new stdClass();
            $tablaHistorial->comunica->metodo = 'historialCuenta';
            $tablaHistorial->comunica->tarea = $this->prm->tarea;
            $tablaHistorial->comunica->bur_id = $this->prm->bur_id;
            $tablaHistorial->comunica->pantalla = $this->pantalla;

            $modalHistorial = '
                <!-- Modal -->
                <div class="modal" id="historialModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="historialModalLabel">Bancos UR - Historial de la cuenta</h4>
                                <button type="button" class="btn-close btn-outline-danger" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="contenedorHistorial">
                                ' . $this->wrPonTabla($tablaHistorial) . '
                            </div>
                            <div class="modal-footer ">
                                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                                    <i class="fa fa-times me-2"></i>Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            ';

            //vincular a cuenta contable
            $html = '
                <div id="vincula_cuenta" style="display:' . ($Bancos->getCta_id() <> '' ? 'block' : 'none') . ';">'/*Así estaba en 2015*/
                . '<div style="width:40%">'
                . '<div class="row mb-4">'
                . '<div class="col-md-12">'
                . '<legend>Cuenta Contable</legend>'
                . '</div>'
                . '</div>'
                . $this->ponInput($inputCtaNiv1)
                . $this->ponInput($inputUnidadRes)
                . (($this->contabilidadGdaCta) ? $this->ponCombo($inputUnidadEje) : $this->ponInput($inputUnidadEje))
                . $this->ponInput($inputNivelReg)
                . $this->ponInput($inputCtaContable)
                . '<br/>'
                . (($Bancos->getEba_id() == 1 || $Bancos->getEba_id() == 5 || ($Bancos->getEba_id() == 2 || $Bancos->getEba_id() == 6) && $this->contabilidadGdaCta) ? $this->ponBoton($btnGuardaCtaCont) : '')
                . '</div>
                </div>';

            /********************** SE EVALUA EL EL ESTADO DE LA CUENTA BANCARIA ***************************/
            switch ($Bancos->getEba_id()) {
                case 5:
                    if ($this->prm->tarea == 'addModCuenta') {
                        switch ($Bancos->getUcb_id()) {
                            case 1:
                                $btnGuardarCuenta = '
                                    <button id="btnGuardarCuenta" type="submit" form="bancos" class="btn btn-outline-success me-1"  title="Actualizar Cuenta Bancaria">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar
                                    </button>';

                                $this->mixScript(' $("#rad_E").removeAttr("disabled"); ');

                                if ($Bancos->getUcb_id() == 4 || ($_SESSION[_S]['clasAdm-gpo'] == 1 && $_SESSION[_S]['clasAdm-ur'] == 16) || in_array($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'], $gpo8)) {
                                    $tmp .= '
                                        <button id="btnGuardarCuenta" type="submit" form="bancos" class="btn btn-outline-success me-1"  title="Actualizar Cuenta Bancaria">
                                            <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar
                                        </button>';
                                    $tmp .= '
                                        <button id="btnEnviaSefin" type="button" form="EnvíaSefin" class="btn btn-outline-info me-1"  title="Enviar Cuenta Bancaria a SEFIN">
                                            <i class="fa fa-paper-plane me-2"></i>Enviar a SEFIN
                                        </button>';
                                    $this->mixScript('
                                        $("#btnEnviaSefin").click(function(){
                                            comunica("' . $this->pantalla . '",{
                                                "metodo":"EnviarAsefin",
                                                "tarea":"' . $this->prm->tarea . '",
                                                "bur_id":"' . $this->prm->bur_id . '"
                                                
                                            });
                                        });
                                    ');
                                } else {
                                    $btnVincularCta = '
                                        <button id="btnVincularCta" type="button" form="Vincular" class="btn btn-outline-info me-1" title="Vincular con Cuenta Contable">
                                            <i class="fa fa-resize-small me-2"></i>Vincular con Cuenta Contable
                                        </button>';
                                    $btnEnviarAContA = '
                                        <button id="btnEnviarAContA" type="button" form="Vincular" class="btn btn-outline-success me-1"  title="Enviar a Contabilidad de la UR">
                                            <i class="fa fa-paper-plane me-2"></i>Enviar a Contabilidad de la UR
                                        </button>';
                                    $btnHistorial = '
                                        <button id="btnHistorial" type="button" form="MostrarHistorial" class="btn btn-outline-secondary" title="Mostrar Historial de la Cuenta Bancaria">
                                            <i class="fa fa-list-alt me-2"></i>Mostrar Historial
                                        </button>';
                                    $tmp .= $btnGuardarCuenta . (($Bancos->getCta_id() <> '') ? '' : $btnVincularCta . $btnEnviarAContA) . "<div class='pull-right'>" . $btnHistorial . '</div>';
                                }
                                break;

                            case 4:
                                $btnGuardarCuenta = '
                                    <button id="btnGuardarCuenta" type="submit" form="bancos" class="btn  btn-outline-success me-1"  title="Actualizar Cuenta Bancaria">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar
                                    </button>';

                                $btnHistorial = '
                                    <button id="btnHistorial" type="button" form="MostrarHistorial" class="btn btn-outline-secondary" title="Mostrar Historial de la Cuenta Bancaria">
                                        <i class="fa fa-list-alt me-2"></i>Mostrar Historial
                                    </button>';
                                $tmp .= $btnGuardarCuenta . "<div class='pull-right'>" . $btnHistorial . '</div>';

                                $this->mixScript('
                                    $("#btnEnviaSefin").click(function(){
                                        comunica("' . $this->pantalla . '",{
                                            "metodo":"EnviarAsefin",
                                            "tarea":"' . $this->prm->tarea . '",
                                            "bur_id":"' . $this->prm->bur_id . '"
                                        });
                                    });
                                ');
                                break;
                        }
                    }
                    break;
                    //En el estado elaborada (1) puede editarse el Uso de Cuenta Bancaria y aparecerá el boton Enviar a Sefin directo cuando el ucb_ sea = 4
                case 1:
                    $tmp .= '
                        <button id="btnGuardarCuenta" type="button" form="bancos" class="btn btn-outline-success me-1"  title="Actualizar Cuenta Bancaria">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar
                        </button>';

                    if ($Bancos->getUcb_id() == 4 || ($_SESSION[_S]['clasAdm-gpo'] == 1 && $_SESSION[_S]['clasAdm-ur'] == 16) || in_array($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'], $gpo8)) {
                        $tmp .= '<button id="btnEnviaSefin" type="button" form="EnvíaSefin" class="btn btn-outline-info "  title="Enviar Cuenta Bancaria a SEFIN"><i class="fa fa-paper-plane me-2"></i>Enviar a SEFIN</button>';
                        $this->mixScript('
                            $("#btnEnviaSefin").click(function(){
                                comunica("' . $this->pantalla . '",{
                                    "metodo":"EnviarAsefin",
                                    "tarea":"' . $this->prm->tarea . '",
                                    "bur_id":"' . $this->prm->bur_id . '"
                                });
                            });
                       ');
                    } else {
                        $btnVincularCta = '
                            <button id="btnVincularCta" type="button" form="Vincular" class="btn btn-outline-primary me-1" title="Vincular con Cuenta Contable">
                                <i class="fa-solid fa-link me-2"></i>Vincular con Cuenta Contable
                            </button>';
                        $btnEnviarAContA = '
                            <button id="btnEnviarAContA" type="button" form="Vincular" class="btn btn-outline-info" title="Enviar a Contabilidad de la UR">
                                <i class="fa fa-paper-plane me-2"></i>Enviar a Contabilidad de la UR
                            </button>';
                        $tmp .= $btnVincularCta . $btnEnviarAContA;
                    }
                    break;
                case 2:
                case 6:
                    $btnDeshabilitarCta = '
                        <button id="btnDeshabilitarCta" type="button" form="Deshabilitar" class="btn btn-outline-danger" title="Deshabilitar Cuenta Bancaria">
                            <i class="fa-solid fa-toggle-off me-2"></i>Deshabilitar
                        </button>';
                    $btnRechazaCta = '
                        <button id="btnRechazaCtaModal" type="button" form="Rechazar" class="btn btn-outline-danger" title="Rechazar Cuenta Bancaria">
                            <i class="fa fa-times me-2"></i>Rechazar
                        </button>';

                    if ($this->deshabilitar && ($this->s23_nvj == 4 || $this->s23_nvj == 3)) {   // 567
                        $modalRechazo = '
                        <!-- Modal -->
                        <div class="modal fade" style="top:150px" id="rechazaCtaModal" tabindex="-1" role="dialog" aria-labelledby="rechazaCtaModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header style="background-color: #9d2449;"">
                                    <h4 class="modal-title" id="rechazaCtaModalLabel">Bancos UR - Motivo de rechazo</h4>
                                    <button type="button" class="btn-close btn-outline-danger" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                </div>
                                <div class="modal-body">
                                    <textarea id="motivoRechazo" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="modal-footer ">
                                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                                        <i class="fa fa-times me-2"></i>Cancelar
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btnRechazaCta">
                                        Aceptar
                                    </button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->';

                        if (!$poner) {
                            $tmp .= $btnDeshabilitarCta;
                        }

                        $this->mixHtml($modalRechazo);
                        $this->mixScript('
                            $("#btnDeshabilitarCta").click(function(){

                                alertify.confirm("DESHABILITAR CUENTA BANCARIA","¿Esta seguro de Deshabilitar la Cuenta Bancancaria",
                                        function(){
                                            comunica("' . $this->pantalla . '",{
                                                metodo:"DeshabilitarCta",
                                                "tarea":"' . $this->prm->tarea . '",
                                                bur_id:"' . $this->prm->bur_id . '"
                                            });
                                        },
                                        function(){}
                                ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                            });
                        ');
                    }

                    break;
                case 3:
                    if ($this->prm->tarea == 'addModCuenta') {
                        $tmp .= '
                            <button id="btnEnviaSefin" type="button" form="EnvíaSefin" class="btn btn-outline-success"  title="Enviar Cuenta Bancaria a SEFIN">
                                <i class="fa fa-paper-plane me-2"></i>Enviar a SEFIN
                            </button>';
                    }
                    break;
                case 4:
                    if ($this->prm->tarea == 'autorizacuentaur') {
                        if ($this->autorizacionCuentasBancarias && $this->s23_nvj == 4 || $this->s23_nvj == 3 && $this->prm->tarea == 'autorizacuentaur') {

                            $btnAutorizaCta = '
                                <button id="btnAutorizaCta" type="button" form="Autorizar" class="btn btn-test btn-outline-success me-1" title="Autorizar Cuenta Bancaria">
                                    <i class="fa fa-check me-2"></i>Autorizar
                                </button>';
                            $btnRechazaCta = '
                                <button id="btnRechazaCtaModal" type="button" form="Rechazar" class="btn btn-test btn-outline-danger" title="Rechazar Cuenta Bancaria">
                                    <i class="fa fa-times me-2"></i>Rechazar
                                </button>';
                            $tmp .= $btnAutorizaCta . $btnRechazaCta;
                        }
                        $modalRechazo = '
                        <!-- Modal -->
                        <div class="modal fade" style="top:150px" id="rechazaCtaModal" tabindex="-1" role="dialog" aria-labelledby="rechazaCtaModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: #9d2449;">
                                    <h4 class="modal-title" id="rechazaCtaModalLabel">Bancos UR - Motivo de rechazo</h4>
                                    <button type="button" class="btn-close btn-outline-danger" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <textarea id="motivoRechazo" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="modal-footer ">
                                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                                        <i class="fa fa-times me-2"></i>Cancelar
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btnRechazaCta" >
                                        Aceptar
                                    </button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                        ';
                    }
                    break;
                case 7:
                    if ($this->deshabilitar && ($this->s23_nvj == 4 || $this->s23_nvj == 3)) {
                        $btnHabilitaCuenta = '
                            <button id="btnHabilitaCta" type="button" class="btn btn-outline-success me-1"  title="Habilitar Cuenta Bancaria">
                                <i class="fa-solid fa-toggle-off me-2"></i>Habilitar
                            </button>';
                        $btnGuardarCuentaHabilitada = '
                            <button id="btnGuardarCtaHabilitada" type="button" class="btn btn-outline-success me-1"  title="Guardar" style="display:none">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
                            </button>';
                        $tmp .= $btnHabilitaCuenta . $btnGuardarCuentaHabilitada;
                    }
            }
        } else {
            $tmp .= '
                <button id="btnGuardarCuenta" type="button" form="bancos" class="btn btn-outline-success me-1"  title="Guardar Cuenta Bancaria">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
                </button>';
        }
        $this->agregaTools($tmp);

        $this->mixHtml($formulario->ponForm()); // esto pone el formulario en la pantalla
        $this->mixScript('
            $("#btnGuardarCuenta").click(function(){
                var ur=' . $ur_id . ';// ur que da alta la cuenta
                var municipio = $("#ue_id").find("option:selected").val();// municipio beneficiario
                $(this).hide().attr("disabled","disabled");

                uso = false;
                $(".tipoClcReq").each(function(){
                    if( $(this).is(":checked") )
                        uso = true
                });

                if((ur==902 || ur==901) && municipio==""){
                    notify("No selecciono el municipio beneficiario ","error");    
                    $(this).show().removeAttr("disabled");
                    return false;
                }

                if(!uso ) {
                    notify("No selecciono un tipo de CLC ","error");    
                    $(this).show().removeAttr("disabled");
                    return false;
                    return false;
                }
                if($("#ucb_id").val()==4){
                    $("#alias").val($("#beneficiario").val());
                }

                if($("#bur_id").val().length == 18 ){
                    $("#iba_id").attr("disabled",false);
                    $("#iba_id").selectpicker("refresh");
                    $("#bancos").submit();
                }else{
                    $(this).show().removeAttr("disabled");
                    notify("La CLABE no tiene los dígitos necesarios (18)", "error");
                }
            });
        ');

        if ($abc == 'c') {
            $this->mixHtml($html . $modalHistorial);

            switch ($Bancos->getEba_id()) {
                case 5:
                    $this->mixScript('
                        $("#btnGuardaCtaCont").click(function(){
                            uso = false;
                            $(".tipoClcReq").each(function(){
                                if( $(this).is(":checked") )
                                    uso = true
                            });

                            if(!uso ) {
                                notify("No selecciono un tipo de CLC ","error");    
                                return false;
                            }

                            if($("#bur_id").val().length == 18 ){
                                $("#bancos").submit();
                            }else{
                                notify("La CLABE no tiene los dígitos necesarios (18)", "error");
                            }
                        });

                        $("#btnHistorial").click(function(){
                            ' . $this->wrLlamaLlenaTabla('tbl_historialCuenta') . '
                            $("#historialModal").modal("show");
                        });
                    ');
                    // no break
                case 1:
                    $this->mixScript('
                        $("#cta_id").on("input", function () {
                            let valor = $(this).val();

                            if (valor.length === 4) {
                                comunica("' . $this->pantalla . '",{
                                    metodo:"buscaCuentaContable",
                                    nivel1:$("#inputCtaNiv1").attr("niv1"),
                                    cta_ur_id:$("#cta_ur_id").attr("ur"),
                                    cta_ue_id:$("#cta_ue_id").attr("ue"),
                                    cgc_id:$("#iba_id").val(),
                                    cta_id:pad($("#cta_id").val(),4,"0")
                                });
                            }
                        });

                        $("#btnGuardaCtaCont").click(function(){
                            if ($("#cta_cuenta").val().trim() == "") {
                                notify("La cuenta contable no existe, no es posible vincular", "error");
                                return false;
                            }
                            comunica("' . $this->pantalla . '",{
                            "metodo":"GuardaCuentaContable",
                            "bur_id":"' . $this->prm->bur_id . '",
                            ' . ($ur_id == 902 ? '"ue_id":$("#ue_id").find("option:selected").val(),' : '') . '
                            "eba_id":3,
                            "nivel1":$("#inputCtaNiv1").attr("niv1"),
                            "cta_ur_id":$("#cta_ur_id").attr("ur"),
                            "cta_ue_id":$("#cta_ue_id").attr("ue"),
                            "cgc_id":$("#iba_id").val(),
                            "cta_id":pad($("#cta_id").val(),4,"0")
                            },true);
                        });

                        $("#btnVincularCta").click(function(){
                            $("#vincula_cuenta").css("display","block");
                        });

                        $("#cuentaBancaria").keypress(function(e){
                            if(e.which>47 && e.which <58 ){
                                return true;
                            }
                            if (e.which!=0 && e.which!=8){
                                return false;
                            }
                        });

                        $("#cta_id").blur(function(){
                            var cuenta = "";
                            cuenta+=$("#inputCtaNiv1").attr("niv1")+".";
                            cuenta+=$("#cta_ur_id").attr("ur")+".";
                            cuenta+=$("#cta_ue_id").attr("ue")+".";
                            cuenta+=$("#iba_id").find("option:selected").val()+".";
                            cuenta+=pad($(this).val(),4,"0");
                        });
                        $("#cta_id").blur();

                        $("#btnEnviarAContA").click(function(){
                            alertify.confirm("ENVIAR CUENTA BANCARIA PARA SU VINCULACIÓN", "¿Esta seguro de enviar la Cta. Bancancaria al Área de Contabilidad de la propia UR para que se realice unicamente la Vinculación Contable? \n Recuerde que si la UR aún NO cuenta con Usuarios para el Módulo de Contabilidad, la vinculación quedará pendiente hasta que existan usuarios que la realicen.", 
                                function(){ 
                                    comunica("' . $this->pantalla . '",{
                                        metodo : "EnviaraConta",
                                        bur_id:"' . $this->prm->bur_id . '",
                                        "tarea":"addModCuenta"
                                    }); 
                                },
                                function(){}
                            ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                            const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                if (btnAceptar) {
                                    btnAceptar.classList.remove("btn-primary");
                                    btnAceptar.classList.add("btn-success");
                                }
                        });
                    ');
                    break;
                case 2:
                case 6:
                    $this->mixScript('
                        $("#btnUpdUso").click(function(){
                            if( $("#inputTarjetaOficio").val() == ""){
                                notify("Debe capturar un No. de tarjeta/oficio", "warning");
                            }else if($("#inputURcomp").length == 1 && $("#inputURcomp").val() == ""){
                                notify("Debe capturar una UR", "warning");
                            }else if($("#tcl_id").val() == "" && $("#tcl_id").attr("required") == "1" ){
                                notify("Debe seleccionar un tipo de uso", "warning");
                            }else{
                                comunica("' . $this->pantalla . '",{
                                    "metodo":"abcBancos","abc":"c",
                                    "tarea":"' . $this->prm->tarea . '",
                                    "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '",
                                    "gpo_id" : "' . ((isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '') . '",
                                    "ur_id" : "' . ((isset($this->prm->ur_id)) ? $this->prm->ur_id : '') . '",
                                    "bur_id":"' . $this->prm->bur_id . '",
                                    "ucb_id":$("#ucb_id").val(),
                                    "inputUrSolic":$("#inputUrSolic").val(),
                                    "tarjeta":$("#inputTarjetaOficio").val(),
                                    "inputURcomp":$("#inputURcomp").val(),
                                    "usodeCta":$("#tcl_id").val()
                                    
                                });
                            }
                        });

                        $("#btnUpdTitulares").click(function(){
                            if( $("#inputTarjeta").val() != ""){
                                comunica("' . $this->pantalla . '",{"metodo":"abcBancos","abc":"c",
                                    "tarea":"' . $this->prm->tarea . '",
                                    "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '",
                                    "bur_id":"' . $this->prm->bur_id . '",
                                    "titular1":$("#titular1").val(),
                                    "titular_2":$("#titular_2").val(),
                                    "tarjeta":$("#inputTarjeta").val(),
                                    "ucb_id":$("#ucb_id").val(),
                                    "inputRFC_id": $("#inputRFC_id").val(),
                                    "actuliza_titulares": true
                                });
                            } else {
                                notify("Debe capturar un No. de tarjeta", "warning");
                            }
                        });

                        ' . ($Bancos->getUcb_id() <> 4 ? '$("#vincula_cuenta").css("display","block");' : '') . '
                        ' . ($Bancos->getBur_id() > 0 ? ($Bancos->getGpo_id() . $Bancos->getUr_id() == '901' ? '$("#vincula_cuenta").css("display","none")' : '') : '') . '
                        ' . ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116 ? '$("#vincula_cuenta").css("display","none")' : '') . '
                    ');

                    if ($this->contabilidadGdaCta) {
                        $this->mixScript('
                            $("#cta_id").on("input", function () {
                                if ($(this).val().length === 4) {
                                    comunica("' . $this->pantalla . '",{
                                        metodo:"buscaCuentaContable",
                                        nivel1:$("#inputCtaNiv1").attr("niv1"),
                                        cta_ur_id:$("#cta_ur_id").attr("ur"),
                                        cta_ue_id:$("#cta_ue_id").val(),
                                        cgc_id:$("#iba_id").val(),
                                        cta_id:pad($("#cta_id").val(),4,"0")
                                    });
                                }
                            });

                            $("#btnGuardaCtaCont").click(function(){
                                if ($("#cta_cuenta").val().trim() == "") {
                                    notify("La cuenta contable no existe, no es posible vincular", "error");
                                    return false;
                                }
                                comunica("' . $this->pantalla . '",{
                                "metodo":"GuardaCuentaContable",
                                "bur_id":"' . $this->prm->bur_id . '",
                                ' . ($ur_id == 902 ? '"ue_id":$("#ue_id").find("option:selected").val(),' : '') . '
                                "eba_id":' . ($Bancos->getEba_id() == 2 ? '3' : '6') . ',
                                "gpo_id":"' . ((isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '') . '",
                                "ur_id":"' . ((isset($this->prm->ur_id)) ? $this->prm->ur_id : '') . '",
                                "nivel1":$("#inputCtaNiv1").attr("niv1"),
                                "cta_ur_id":$("#cta_ur_id").attr("ur"),
                                "cta_ue_id":$("#cta_ue_id").val(),
                                "cgc_id":$("#iba_id").val(),
                                "cta_id":pad($("#cta_id").val(),4,"0")
                                },true);
                            });

                            $("#cta_id").blur(function(){
                                var cuenta = "";
                                cuenta+=$("#inputCtaNiv1").attr("niv1")+".";
                                cuenta+=$("#cta_ur_id").attr("ur")+".";
                                cuenta+=$("#cta_ue_id").val()+".";
                                cuenta+=$("#iba_id").find("option:selected").val()+".";
                                cuenta+=pad($(this).val(),4,"0");
                            });

                            $("#cta_ue_id").change(function(){
                                var cuenta = "";
                                cuenta+=$("#inputCtaNiv1").attr("niv1")+".";
                                cuenta+=$("#cta_ur_id").attr("ur")+".";
                                cuenta+=$("#cta_ue_id").val()+".";
                                cuenta+=$("#iba_id").find("option:selected").val()+".";
                                cuenta+=pad($(cta_id).val(),4,"0");
                            });
                            $("#cta_id").blur();
                        ');
                    } else {
                        $this->mixScript('
                            $("#cta_id").on("input", function () {
                                if ($(this).val().length === 4) {
                                    comunica("' . $this->pantalla . '",{
                                        metodo:"buscaCuentaContable",
                                        nivel1:$("#inputCtaNiv1").attr("niv1"),
                                        cta_ur_id:$("#cta_ur_id").attr("ur"),
                                        cta_ue_id:$("#cta_ue_id").attr("ue"),
                                        cgc_id:$("#iba_id").find("option:selected").val(),
                                        cta_id:pad($("#cta_id").val(),4,"0")
                                    });
                                }
                            });

                            $("#btnGuardaCtaCont").click(function(){
                                if ($("#cta_cuenta").val().trim() == "") {
                                    notify("La cuenta contable no existe, no es posible vincular", "error");
                                    return false;
                                }
                                comunica("' . $this->pantalla . '",{
                                "metodo":"GuardaCuentaContable",
                                "bur_id":"' . $this->prm->bur_id . '",
                                ' . ($ur_id == 902 ? '"ue_id":$("#ue_id").find("option:selected").val(),' : '') . '
                                "eba_id":' . ($Bancos->getEba_id() == 2 ? '3' : '6') . ',
                                "gpo_id":"' . ((isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '') . '",
                                "ur_id":"' . ((isset($this->prm->ur_id)) ? $this->prm->ur_id : '') . '",
                                "nivel1":$("#inputCtaNiv1").attr("niv1"),
                                "cta_ur_id":$("#cta_ur_id").attr("ur"),
                                "cta_ue_id":$("#cta_ue_id").attr("ue"),
                                "cgc_id":$("#iba_id").find("option:selected").val(),
                                "cta_id":pad($("#cta_id").val(),4,"0")
                                },true);
                            });

                            $("#cta_id").blur(function(){
                                var cuenta = "";
                                cuenta+=$("#inputCtaNiv1").attr("niv1")+".";
                                cuenta+=$("#cta_ur_id").attr("ur")+".";
                                cuenta+=$("#cta_ue_id").attr("ue")+".";
                                cuenta+=$("#iba_id").find("option:selected").val()+".";
                                cuenta+=pad($(this).val(),4,"0");
                            });
                            $("#cta_id").blur();
                            
                        ');
                    }
                    break;
                case 3:
                    $this->mixScript('
                        $("#btnEnviaSefin").click(function(){
                            comunica("' . $this->pantalla . '",{
                                "metodo":"EnviarAsefin",
                                "tarea":"' . $this->prm->tarea . '",
                                "bur_id":"' . $this->prm->bur_id . '"
                            });
                        });
                    ');
                    break;
                case 4:
                    $this->mixHtml($modalRechazo);
                    $this->mixScript('
                        $("#btnAutorizaCta").click(function(){
                            alertify.confirm("AUTORIZAR CUENTA BANCARIA", "¿Esta seguro de Autorizar la Cuenta Bancaria para su Uso?<h5 style=\'display:inline\'></h5> ", 
                                function(){ 
                                    comunica("' . $this->pantalla . '",{
                                        "metodo" : "ValidarAutRec",
                                        "tarea":"' . $this->prm->tarea . '",
                                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '",
                                        "gpo_id":"' . ((isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '') . '",
                                        "ur_id":"' . ((isset($this->prm->ur_id)) ? $this->prm->ur_id : '') . '",
                                        "Autorizado":"si",
                                        "bur_id":"' . $this->prm->bur_id . '"
                                    }); 
                                },
                                function(){}
                                ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                        if (btnAceptar) {
                                            btnAceptar.classList.remove("btn-primary");
                                            btnAceptar.classList.add("btn-success");
                                        }
                        });
                        $("#btnRechazaCtaModal").click(function(){
                            $("#rechazaCtaModal").modal("show");
                        });
                        $("#btnRechazaCta").click(function(){
                            $("#rechazaCtaModal").modal("hide");
                            $(".modal-backdrop").remove();
                            comunica("' . $this->pantalla . '",{
                                "metodo":"ValidarAutRec",
                                "motivo":$("#motivoRechazo").val(),
                                "tarea":"' . $this->prm->tarea . '",
                                "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '",
                                "Autorizado":"no",
                                "bur_id":"' . $this->prm->bur_id . '"});
                        });
                    ');
                    break;
                case 7:
                    $this->mixScript('
                        $("#btnHabilitaCta").click(function(){
                            $("#alias").removeAttr("readonly");
                            $("#beneficiario").removeAttr("readonly");
                            $("#sucursal").removeAttr("readonly");
                            $("#titular1").removeAttr("readonly");
                            $("#cargo1").removeAttr("readonly");
                            $("#titular_2").removeAttr("readonly");
                            $("#cargo2").removeAttr("readonly");
                            $(".tipoClcReq").removeAttr("disabled");
                            $("#btnGuardarCtaHabilitada").css("display","");
                            $("#btnHabilitaCta").css("display","none");
                            $("#btnGuardarCtaHabilitada").click(function(){
                                uso = false;
                                $(".tipoClcReq").each(function(){
                                    if( $(this).is(":checked") )
                                        uso = true;
                                });
                                if(!uso) {
                                    notify("Debe seleccionar el Tipo de CLC asignado a la Cuenta Bancaria","error");
                                    return false;
                                }
                                $("#bancos").submit();
                            });
                        });
                    ');
                    break;
            }
        } else {
            // @pcaCI=Permitir caracteres alfanumericos para clabe interbancaria
            $this->mixScript('
                var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
                var pcaCI = /^[\d]/;
                $("#bur_id").keyup(function(e){
                    escribe();
                });
                $("#bur_id").keypress(function(e) {
                    var keynum = window.event ? window.event.keyCode : e.which;
                    if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                        return true;
                    return pcaCI.test(String.FROMCharCode(keynum));
                });
                $("#bur_id").blur(function(){
                    if ($(this).val().length !=18 ){
                        notify("La CLABE no tiene los dígitos necesarios (18)", "error");
                    }
                    else{
                        comunica("' . $this->pantalla . '",{metodo:"checaCuenta","clabe":$(this).val()});
                    }
                });
            ');
        }

        $this->mixScript('
            if("' . $this->usr_id . '" =="2006"  ){
                $("#rad_E").attr("disabled","disabled");
            }
            $("#rad_E").click(function(){
                if($(this).is(":checked")){
                    $("#rad_A,#rad_F").removeAttr("checked");
                    $("#rad_A,#rad_F").attr("disabled","1");
                }else{
                    $("#rad_A,#rad_F,#rad_B").removeAttr("disabled");
                }
            });
            $("#cuentaBancaria").blur(function(){
                if ($(this).val().length <10 ){
                    notify("La CUENTA BANCARIA no tiene los digitos necesarios (10)", "error");
                }
            });
            function escribe(){
                if(document.getElementById("bur_id").value.length == 3){
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaBanco",
                        param: $("#bur_id").val()
                    });
                }
                if(document.getElementById("bur_id").value.length == 6){
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaPlaza",
                        param: $("#bur_id").val().substring(3)
                    });
                }
            }
            if($("#ue_id").length>0){
                $("#ue_id").on("change",function(e){
                    var getMunicipio=$("#ue_id option:selected").text();
                    var separar=getMunicipio.split("-");
                    // $("#beneficiario").val(separar[1].trim());
                    comunica("' . $this->pantalla . '",{
                        metodo:"getRazonSocialMunicipio",
                        nombreMunicipio: separar[1].trim(),
                        mun_id: separar[0].trim()
                    });
                });
            }
        ');
    }

    private function getRazonSocialMunicipio()
    {
        $this->prm->nombreMunicipio = (isset($this->prm->nombreMunicipio)) ? $this->prm->nombreMunicipio : '';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('SELECT rfc_nombre FROM sinpres.cat_Proveedores WHERE mun_id = ? AND rfc_nombre LIKE ?  AND soc_id=0;');
        $conexion->consultar([$this->prm->mun_id, '%' . $this->prm->nombreMunicipio . '%']);
        $row = $conexion->extraer();
        $conexion->desconectar();
        if (!empty($row)) {
            $this->mixScript('$("#beneficiario").val("' . $row['rfc_nombre'] . '");');
        } else {
            $this->mixScript('notify("No se encontro la razón social del municipio seleccionado","warning");
                $("#beneficiario").val("' . $this->prm->nombreMunicipio . '");
            ');
        }
    }

    private function consultaBanco()
    {
        /* el # debe ser removido cuando el modelo de bancos UR ya esté en la base de datos 2020*/
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('SELECT iba_id FROM tesoreria.cat_InstitucionBancaria where iba_numero = ?;');
        $conexion->consultar([$this->prm->param]);
        $valor = $conexion->extraer();
        $conexion->desconectar();
        if ($valor) {
            $this->mixScript('
            jQuery("#iba_id").val("' . $valor['iba_id'] . '"); 
            $("#iba_id").selectpicker("refresh");
        ');
        } else {
            $this->mixScript('notify("Institucion Bancaria no Registrada","error");
            jQuery("#iba_id").val("");
            $("#iba_id").selectpicker("refresh");
            ');
            return false;
        }
    }

    private function consultaPlaza()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('SELECT pbn_numero,pbn_nombre FROM tesoreria.cat_PlazaBancaria where pbn_numero = ?;');
        $conexion->consultar([$this->prm->param]);
        $valor = $conexion->extraer();
        $conexion->desconectar();
        if ($valor) {
            $this->mixScript('$("#plaza").val("' . $valor['pbn_numero'] . '-' . $valor['pbn_nombre'] . '");');
        }

    }

    private function determinarSiProveedorEsFisico()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            SELECT 
                scp.tcy_id AS tcy
            FROM cat_Proveedor cp
            INNER JOIN sinpres.cat_Proveedores scp using( rfc_id )
            INNER JOIN sinpres.cat_SociedadProveedor scsp  USING (soc_id)
            WHERE scp.rfc_id LIKE ? AND 
                sts_id = 1 AND scp.tcy_id=1
        ');
        $conexion->consultar([$this->prm->param . '%']);

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript('
                $("#cargo1").val("Titular 1");
                $("#cargo2").val("Titular 2");
            ');
        }

        $conexion->desconectar();
    }

    //*********Función para recargar el tipo de cuenta Bancaria dependiendo del Uso de Cta.***************/
    private function ComboTcB()
    {
        $combo = new stdClass();
        $combo->id = 'tcb_id';
        $combo->attr = 'ucb_id';
        $combo->value = 'ucb_id';

        /*si es Pago Directo Ucb_id=4 el Tcb_id = 2 (Inproductivo) si es Propia ucb=1 el tcb_id=1(Productivo)*/
        if (isset($this->prm->combo) && $this->prm->combo != '') {
            switch ($this->prm->combo) {
                case 1:
                    $combo->sql = 'SELECT tcb_id,tcb_nombre FROM cat_TipoCuentaBancaria WHERE tcb_id = 1';
                    break;
                case 2:
                    $combo->sql = 'SELECT tcb_id,tcb_nombre FROM cat_TipoCuentaBancaria WHERE tcb_id = 1';
                    break;
                case 3:
                    $combo->sql = 'SELECT tcb_id,tcb_nombre FROM cat_TipoCuentaBancaria WHERE tcb_id = 1';
                    break;
                case 4:
                    $combo->sql = 'SELECT tcb_id,tcb_nombre FROM cat_TipoCuentaBancaria WHERE tcb_id = 2';
                    break;
            }

            $this->recargaCombo($combo);
            if ($this->prm->combo == 4 || $this->prm->combo == 1) {
                $this->mixScript('$("#beneficiario").attr("readonly",true)');
            } else {
                $this->mixScript('$("#beneficiario").removeAttr("readonly"); ');
            }
        }
    }
    //**********Función abc para el controlador***************/
    public function abcBancos()
    {
        #***********************************************
        #Ojo: Se quita el log, que se generaba en el archivo
        #ya que el log de afectaciones se guarda
        #actualmente en BD_CTRL.Bitacora.bit_BancosUr
        #***********************************************

        if (strlen(trim($this->prm->bur_id)) != 18) {
            $this->mixScript('
                $("#iba_id").attr("disabled",true);
                $("#iba_id").selectpicker("refresh");
                $("#btnGuardarCuenta").show().removeAttr("disabled");
                notify("La CLABE debe de tener 18 Digitos","error");
            ');
            return false;
        }
        if (isset($this->prm->cuentaBancaria)) {
            if (strlen(trim($this->prm->cuentaBancaria)) < 10 || strlen(trim($this->prm->cuentaBancaria)) > 11) {
                $this->mixScript('
                    $("#iba_id").attr("disabled",true);
                    $("#iba_id").selectpicker("refresh");
                    $("#btnGuardarCuenta").show().removeAttr("disabled");
                    notify("La cuenta debe de tener entre 10 y 11  Digitos","error");
                ');
                return false;
            }
        }

        if (isset($this->prm->ucb_id) && $this->prm->ucb_id == 4 && $this->prm->abc != 'b') {
            if (!isset($this->prm->inputRFC_id) || $this->prm->inputRFC_id == '') {
                $this->mixScript('
                    $("#iba_id").attr("disabled",true);
                    $("#iba_id").selectpicker("refresh");
                    $("#btnGuardarCuenta").show().removeAttr("disabled");
                    notify("Proveedor Invalido o vacio","error");
                ');
                return false;
            }
            $res = $this->validaRfc($this->prm->inputRFC_id);

            if (is_string($res)) {
                $this->mixScript('
                    $("#iba_id").attr("disabled",true);
                    $("#iba_id").selectpicker("refresh");
                    notify("' . $res . '","error")
                ');
                return false;
            }
            $this->prm->beneficiario = $this->AgregaBeneficiario($this->prm->inputRFC_id);
        }

        if (isset($this->prm->tipoClcE) && $this->prm->tipoClcE != '') {
            if ($this->prm->UsodeCta[0] != 'B') {
                $this->mixScript('
                    $("#iba_id").attr("disabled",true);
                    $("#iba_id").selectpicker("refresh");
                    notify("Una cuenta marcada como Fondo Rotatorio, solo puede ser usada en Gastos de Operación","error");
                ');
                return false;
            }
            $this->prm->bur_fondoRotatorio = 'S';
        } else {
            $this->prm->bur_fondoRotatorio = 'N';
        }

        switch ($this->prm->abc) {
            case 'a': //para guardar un nuevo registro
                $valido = (bool) $this->checaCuenta($this->prm->bur_id);
                if ($valido === false) {
                    $this->mixScript('
                        $("#iba_id").attr("disabled",true);
                        $("#iba_id").selectpicker("refresh");
                        $("#btnGuardarCuenta").show().removeAttr("disabled");
                        notify("La CLABE bancaria ' . $this->prm->bur_id . ' ya existe en la base de datos :: ABC","error");
                    ');
                    return false;
                }

                if (isset($this->prm->tipoClcE) && $this->prm->tipoClcE != '') {
                    $this->prm->bur_fondoRotatorio = 'S';
                } else {
                    $this->prm->bur_fondoRotatorio = 'N';
                }

                $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
                $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
                $this->prm->sts_id = 1;
                $this->prm->eba_id = 1;
                $this->prm->bur_gpo_id = substr($this->prm->inputUrSolic, 0, 1);
                $this->prm->bur_ur_id = substr($this->prm->inputUrSolic, 1, 2);

                if ($this->prm->iba_id == '') {
                    $this->mixScript('
                        $("#iba_id").attr("disabled",true);
                        $("#iba_id").selectpicker("refresh");
                        $("#btnGuardarCuenta").show().removeAttr("disabled");
                        notify("Ingresa una Institución Bancaria","error");
                    ');
                    return false;
                }

                if ($this->prm->titular1 == '' || $this->prm->titular_2 == '') {
                    $this->mixScript('
                        $("#iba_id").attr("disabled",true);
                        $("#iba_id").selectpicker("refresh");
                        $("#btnGuardarCuenta").show().removeAttr("disabled");
                        notify("Ingresa los Titulares de la Cuenta Bancaria","error");
                    ');
                    return false;
                }

                if ($this->prm->cargo1 == '' || $this->prm->cargo2 == '') {
                    $this->mixScript('
                        $("#iba_id").attr("disabled",true);
                        $("#iba_id").selectpicker("refresh");
                        $("#btnGuardarCuenta").show().removeAttr("disabled");
                        notify("Ingresa los Cargos de los Titulares ","error");
                    ');
                    return false;
                }

                if ($this->prm->sucursal == '') {
                    $this->mixScript('
                        $("#iba_id").attr("disabled",true);
                        $("#iba_id").selectpicker("refresh");
                        $("#btnGuardarCuenta").show().removeAttr("disabled");
                        notify("Ingresa información del Banco ","error");
                    ');
                    return false;
                }

                if ($this->prm->beneficiario == '' || $this->prm->alias == '') {
                    $this->mixScript('
                        $("#iba_id").attr("disabled",true);
                        $("#iba_id").selectpicker("refresh");
                        $("#btnGuardarCuenta").show().removeAttr("disabled");
                        notify("Ingresa la información de la Cuenta ","error");
                    ');
                    return false;
                }

                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('SELECT pbn_numero FROM tesoreria.cat_PlazaBancaria where pbn_numero = ?;');
                $conexion->consultar([substr($this->prm->bur_id, 3, 3)]);
                $extra = $conexion->extraer();
                $ClabePlaza = substr($this->prm->bur_id, 3, 3);

                if ($conexion->getNumeroRegistros() == 0) {
                    $this->mixScript('
                        $("#iba_id").attr("disabled",true);
                        $("#iba_id").selectpicker("refresh");
                        $("#btnGuardarCuenta").show().removeAttr("disabled");
                        notify("Clabe Interbancaria No Válida. El Número ' . $ClabePlaza . ' no se reconoce.", "warning");
                    ');
                    return 0;
                } else {
                    $this->prm->plaza = $extra['pbn_numero'];
                }

                BancosUr::abc($this->prm);

                if ($this->prm->ucb_id == 4) {
                    /*     SI ES PGO DIRECTO A PROVEEDOR ENTONCES
                        INSERTAMOS EN LA VINCULACION DE CUENTA BANCARIA PROVEEDOR
                    */
                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql('
                        SET @bit_observacion = ?, 
                        @usr_ip = INET_ATON( ?),
                        @usr_id = ?
                    ');
                    $params = ['Se inserta la vinculación desde el Modulo de Bancos',  $this->ip, $this->usr_id];
                    $con->consultar($params);
                    $con->setSql('INSERT IGNORE INTO vin_BancosUrProveedor ( bur_id,rfc_id,sts_id, usr_id) VALUES (?,?,?,?)
                    ');
                    $params = [$this->prm->bur_id, $this->prm->inputRFC_id, 2, $this->usr_id];
                    $con->consultar($params);
                    $con->desconectar();
                }

                // CUANDO SE APLIQUE NO SE INSERTARA EN EL ENC BASTARA CON COMETAR LA LINEA 1839  $this->consultar();
                if (isset($this->prm->UsodeCta)) {
                    $this->conectar();
                    if (is_array($this->prm->UsodeCta) && count($this->prm->UsodeCta)) {
                        foreach ($this->prm->UsodeCta as $key => $value) {
                            if ($value == 'F') {
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES (?, ?, ?);');
                                $params = [$this->prm->bur_id, 'F', $this->usr_id];
                                $this->consultar($params);
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?); ');
                                $params = [$this->prm->bur_id, 'G', $this->usr_id];
                                $this->consultar();
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?); ');
                                $params = [$this->prm->bur_id, 'H', $this->usr_id];
                                $this->consultar($params);

                                if ($this->prm->gpo_id == 9 && $this->prm->ur_id == 02) {
                                    $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?); ');
                                    $params = [$this->prm->bur_id, 'I', $this->usr_id];
                                    $this->consultar($params);
                                } // se agrega el tipo I para la ur 902
                            } else {
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?); ');
                                $params = [$this->prm->bur_id, $value, $this->usr_id];
                                $this->consultar($params);
                            }
                        }
                    } else {
                        if ($this->prm->UsodeCta == 'F') {
                            $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?)');
                            $params = [$this->prm->bur_id, 'F', $this->usr_id];
                            $this->consultar($params);
                            $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?)');
                            $params = [$this->prm->bur_id, 'G', $this->usr_id];
                            $this->consultar($params);
                            $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?)');
                            $params = [$this->prm->bur_id, 'H', $this->usr_id];
                            $this->consultar($params);
                            if ($this->prm->gpo_id == 9 && $this->prm->ur_id == 02) {
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?); ');
                                $params = [$this->prm->bur_id, 'I', $this->usr_id];
                                $this->consultar($params);
                            } // se agrega el tipo I para la ur 902
                        } else {
                            $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES(?, ?, ?)');
                            $params = [$this->prm->bur_id, $this->prm->UsodeCta, $this->usr_id];
                            $this->consultar($params);
                        }
                    }
                    $this->desconectar();
                }
                $this->mixScript('
                    notify ("El registro se guardo exitosamente");
                    $("#toolsPantalla").html("");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"ponTablaListadoCuentas",
                        "tarea":"addModCuenta"
                    });
                ');

                break;

            case 'b': //para eliminar  un registro
                $this->mixScript('notify("El registro se eliminó exitosamente")');
                break;

            case 'c': //actualiza un registro
                $banco = new BancosUr();
                $banco->setBur_id($this->prm->bur_id);
                $banco->select();

                $antUcb_id = $banco->getUcb_id();
                $nuevoUcb_id = $this->prm->ucb_id;

                if ($banco->getEba_id() == 5) {
                    $this->prm->eba_id = 3;
                }

                if ($banco->getEba_id() == 7) {
                    if ($banco->getUcb_id() == 4) {
                        $conexionParaElEstado7 = new AccesoDatos();
                        $conexionParaElEstado7->conectar();
                        $conexionParaElEstado7->setSql("
                            SELECT CONCAT(IF(p.tcy_id=4 || p.tcy_id=1,CONCAT(p.rfc_nombre,' ',p.rfc_paterno,' ',p.rfc_materno),CONCAT(p.rfc_nombre,' ',s.soc_nombre))) beneficiario 
                            FROM sinpres.cat_Proveedores p
                            INNER JOIN sinpres.cat_SociedadProveedor s USING(soc_id)
                            WHERE rfc_id = ?;
                        ");
                        $conexionParaElEstado7->consultar([$this->prm->inputRFC_id]);
                        $beneficiario = $conexionParaElEstado7->extraer();

                        $this->prm->beneficiario = trim(str_replace(['   ', '  '], ' ', $this->prm->beneficiario));
                        $beneficiario['beneficiario'] = trim(str_replace(['   ', '  '], ' ', $beneficiario['beneficiario']));

                        if ($beneficiario['beneficiario'] == $this->prm->beneficiario) {
                            if ($this->prm->beneficiario == $this->prm->alias) {
                                $conexionParaElEstado7->setSql('
                                    SELECT CONCAT(c.gpo_id, c.ur_id) ur, c.clc_id, c.ecl_id,e.ecl_nombre 
                                    FROM ' . BD_CTRL . '.scp_Clc c
                                    INNER JOIN ' . BD_CTRL . '.cat_EstadoClc e USING(ecl_id)
                                    WHERE bur_id = ? AND ecl_id NOT IN(16,17,18,19);
                                ');
                                $conexionParaElEstado7->consultar([$this->prm->bur_id]);
                                if ($conexionParaElEstado7->getNumeroRegistros() > 0) {
                                    $clcsEnProceso = 'Las siguines CLCs estan en proceso, por tal motivo no puede habilitarse la cuenta bancaria.<br>';
                                    while ($row = $conexionParaElEstado7->extraer()) {
                                        $clcsEnProceso .= 'La CLC: ' . $row['clc_id'] . ' de la UR: ' . $row['ur'] . ' en estado: ' . $row['ecl_id'] . '(' . $row['ecl_nombre'] . ') <br>';
                                    }
                                    $this->mixScript('
                                        bootbox.dialog({
                                            message: "' . $clcsEnProceso . '",
                                            title: "INFORMACIÓN",
                                            buttons: {
                                                cancelar : { label: "Aceptar", className: "btn-outline-primary" }
                                            }
                                        });
                                        
                                    ');
                                    return 0;
                                } else {
                                    $this->prm->eba_id = 6;
                                    $conexionParaElEstado7->desconectar();
                                }
                            } else {
                                $this->mixScript('notify("El beneficiario debe ser igual al alias","error");');
                                $conexionParaElEstado7->desconectar();
                                return 0;
                            }
                        } else {
                            $this->mixScript('notify("El beneficiario no coincide con el proveedore del RFC ' . $this->prm->inputRFC_id . '","error");');
                            $conexionParaElEstado7->desconectar();
                            return 0;
                        }
                    }
                }

                unset($banco);
                $ucb = isset($this->prm->ucb_id) ? $this->prm->ucb_id : '';

                if ($ucb == 4) {
                    $this->prm->ger_id = 'NULL';
                    $this->prm->cgp_id = 'NULL';
                    $this->prm->rub_id = 'NULL';
                    $this->prm->cgcu_id = 'NULL';
                    $this->prm->cgsc_id = 'NULL';
                    $this->prm->cta_gpo_id = 'NULL';
                    $this->prm->cta_ur_id = 'NULL';
                    $this->prm->cta_ue_id = 'NULL';
                    $this->prm->cgc_id = '';
                    $this->prm->cta_id = '';
                }
                $conexion = new AccesoDatos();
                $ClabePlaza = substr($this->prm->bur_id, 3, 3);
                $conexion->conectar();
                $conexion->setSql('SELECT pbn_numero FROM tesoreria.cat_PlazaBancaria where pbn_numero = ?;');
                $conexion->consultar([$ClabePlaza]);
                $valor_plaza = $conexion->extraer();


                if ($conexion->getNumeroRegistros() == 0) {
                    $this->mixScript('notify("Clabe Interbancaria No Válida. El Número ' . $ClabePlaza . ' no se reconoce.", "warning");');
                    return 0;
                } else {
                    $this->prm->plaza = $valor_plaza['pbn_numero'];
                }

                if ($ucb == 2 && isset($this->prm->inputURcomp) && $this->prm->inputURcomp != '') {
                    $grupo = substr($this->prm->inputURcomp, 0, 1);
                    $Ur = substr($this->prm->inputURcomp, 1, 2);

                    $consultaUr = 'SELECT gpo_id FROM cat_UnidadResponsable WHERE gpo_id = ? and ur_id = ?';

                    $conexion->conectar();
                    $conexion->setSql($consultaUr);
                    $conexion->consultar([$grupo, $Ur]);
                    $data = $conexion->extraer();
                    $conexion->desconectar();

                    if ($data['gpo_id'] == '') {
                        $this->mixScript('notify("Ingresar una UR válida","error");');
                        return 0;
                    }
                }

                if ($ucb == 2 && isset($this->prm->inputURcomp) && $this->prm->inputURcomp != '') {

                    $this->prm->bur_gpo_id = substr($this->prm->inputURcomp, 0, 1);
                    $this->prm->bur_ur_id = substr($this->prm->inputURcomp, 1, 2);
                    if ($this->prm->usodeCta <> '') {

                        $consultaVin = '
                            SELECT * FROM vin_BancosUrTipoClc
                            WHERE bur_id = ? 
                            AND tcl_id = ?';
                        $params = [$this->prm->bur_id, $this->prm->usodeCta];
                        $this->conectar();
                        $this->setSql($consultaVin);
                        $this->consultar($params);

                        if ($this->getNumeroRegistros() == 0) {
                            $sqltmp = '';
                            $params = [];
                            if ($this->prm->usodeCta == 'Z') {
                                $sqltmp = '
                                    INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) 
                                    VALUES
                                        (?,?,?)
                                        (?,?,?)
                                        (?,?,?)';
                                $params = [$this->prm->bur_id, 'F', $this->usr_id, $this->prm->bur_id, 'G', $this->usr_id, $this->prm->bur_id, 'H', $this->usr_id];
                            } else {
                                $sqltmp = '
                                    INSERT INTO vin_BancosUrTipoClc ( bur_id, tcl_id, usr_id ) VALUES(?,?,?)';
                                $params = [$this->prm->bur_id, $this->prm->usodeCta, $this->usr_id];
                            }
                            $this->conectar();
                            $this->setSql($sqltmp);
                            $this->consultar($params);
                        } else {
                            $this->mixScript('
                                notify("La vinculación ya se encuentra registrada", "warning", true);
                            ');
                            return 0;
                        }
                        $this->desconectar();
                    }
                }

                /*si viene el prm tarjeta, se actualiza la bitacora*/
                if (isset($this->prm->tarjeta)) {
                    $bancos = new BancosUr();
                    $bancos->setBur_id($this->prm->bur_id);
                    $bancos->select();

                    $bur_tipoCambio = (isset($this->prm->actuliza_titulares)) ? 'T' : ((isset($this->prm->ucb_id)) ? 'G' : 'T');
                    $params = [];
                    switch ($bur_tipoCambio) {
                        case 'G':
                            if ($ucb == 2 && $this->prm->inputURcomp != '') {
                                $this->prm->bur_gpo_id = substr($this->prm->inputURcomp, 0, 1);
                                $this->prm->bur_ur_id = substr($this->prm->inputURcomp, 1, 2);
                                $bit_observacion = 'USOCTAANT:' . $bancos->getUcb_id() . ' USOCTANUEVO:' . $this->prm->ucb_id;
                                $sql = ' INSERT INTO ' . BD_BITACORA . '.bit_ModificacionBancosUr(bur_id, bur_tipoCambio, bur_tarjeta,  bit_observacion, bur_ur_solicitud, usr_ip, usr_id) 
                                values(?,?,?,?,?,INET_ATON( ? ), ?)';
                                $params = [$bancos->getBur_id(), $bur_tipoCambio, $this->prm->tarjeta, $bit_observacion, $this->prm->inputURcomp, $this->ip, $this->usr_id];
                            } else {
                                $this->prm->bur_gpo_id = substr($this->prm->inputUrSolic, 0, 1);
                                $this->prm->bur_ur_id = substr($this->prm->inputUrSolic, 1, 2);
                                $bit_observacion = 'USOCTAANT:' . $bancos->getUcb_id() . ' USOCTANUEVO:' . $this->prm->ucb_id;
                                $sql = ' INSERT INTO ' . BD_BITACORA . '.bit_ModificacionBancosUr(bur_id, bur_tipoCambio, bur_tarjeta, bit_observacion, bur_ur_solicitud, usr_ip, usr_id) 
                                values (?,?,?,?,?,INET_ATON( ? ), ?)';
                                $params = [$bancos->getBur_id(), $bur_tipoCambio, $this->prm->tarjeta, $bit_observacion, $this->prm->inputUrSolic, $this->ip, $this->usr_id];
                            }
                            break;
                        case 'T':
                            $bit_observacion = 'ANT TITULAR1:' . $bancos->getTitular1() . ' NUEVO TITULAR1:' . $this->prm->titular1;
                            $bit_observacion .= ' ANT TITULAR_2:' . $bancos->getTitular_2() . ' NUEVO TITULAR_2:' . $this->prm->titular_2;
                            $sql = ' INSERT INTO ' . BD_BITACORA . '.bit_ModificacionBancosUr(bur_id, bur_tipoCambio, bur_tarjeta, bit_observacion, bur_ur_solicitud, usr_ip, usr_id) 
                                values (?,?,?,?,?,INET_ATON( ? ), ?)';
                            $params = [$bancos->getBur_id(), $bur_tipoCambio, $this->prm->tarjeta, $bit_observacion, 0, $this->ip, $this->usr_id];
                            break;
                        default:
                            $sql = '';
                            break;
                    }

                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar($params);
                    $this->desconectar();
                }

                if ($this->prm->tarea == 'addModCuenta') {
                    if (isset($this->prm->tipoClcE) && $this->prm->tipoClcE != '') {
                        $this->prm->bur_fondoRotatorio = 'S';
                    } else {
                        $this->prm->bur_fondoRotatorio = 'N';
                    }
                }

                BancosUr::abc($this->prm);

                if ($antUcb_id == 4 && $nuevoUcb_id != 4) {
                    $params = ['Eliminar la vinculación del proveedor desde la pantalla de Bancos Ur.', $this->usr_id];
                    $this->conectar();
                    $this->setSql('set @bit_observacion = ?, @usr_id = ?, @usr_ip=sinpres.fn_get_ip();');
                    $this->consultar($params);
                    $this->setSql('DELETE FROM vin_BancosUrProveedor WHERE bur_id = ?;');
                    $this->consultar([$this->prm->bur_id]);
                } else {
                    if ($this->prm->ucb_id == 4) {
                        /*     SI ES PAGO DIRECTO A PROVEEDOR Y YA TIENE REGISTRO DE RFC ENTONCES
                                ACTUALIZAMOS EN LA VINCULACION DE CUENTA BANCARIA PROVEEDOR
                            */
                        $conexion;

                        $sql = ' SELECT * FROM vin_BancosUrProveedor WHERE bur_id = ?';
                        $conexion = new AccesoDatos();
                        $conexion->conectar();
                        $conexion->setSql($sql);
                        $conexion->consultar([$this->prm->bur_id]);

                        if ($conexion->getNumeroRegistros() > 0) {
                            $params = ['Se inserta la vinculación desde el Modulo de Bancos', $this->ip, $this->usr_id];
                            $conexion->setSql('SET @bit_observacion = ?, @usr_ip = INET_ATON( ? ), @usr_id = ?');
                            $conexion->consultar($params);
                            $params = [$this->prm->inputRFC_id, $this->prm->bur_id];
                            $sqlvin = 'UPDATE vin_BancosUrProveedor set rfc_id = ? WHERE bur_id = ? ';
                            $conexion->setSql($sqlvin);
                            $conexion->consultar($params);
                            $conexion->desconectar();
                        } else {

                            /*     SI ES PAGO DIRECTO A PROVEEDOR ENTONCES
                                    INSERTAMOS EN LA VINCULACION DE CUENTA BANCARIA PROVEEDOR
                            */

                            $con = new AccesoDatos();
                            $con->conectar();
                            $params = ['Se inserta la vinculación desde el Modulo de Bancos', $this->ip, $this->usr_id];
                            $con->setSql('SET @bit_observacion = ?, @usr_ip = INET_ATON( ? ), @usr_id = ?');
                            $con->consultar($params);
                            $params = [$this->prm->bur_id, $this->prm->inputRFC_id, 2, $this->usr_id];
                            $con->setSql('INSERT INTO vin_BancosUrProveedor ( bur_id,rfc_id,sts_id, usr_id) VALUES (?, ?, ?, ?)');
                            $con->consultar($params);
                            $con->desconectar();
                        }
                    }
                }

                if (isset($this->prm->UsodeCta)) {
                    //siempre se borran las cuentas para poder hacer una insercion
                    $this->conectar();
                    $this->setSql('DELETE FROM vin_BancosUrTipoClc WHERE bur_id = ? ');
                    $this->consultar([$this->prm->bur_id]);

                    //solo se insertan las que viene marcadas
                    if (is_array($this->prm->UsodeCta) && count($this->prm->UsodeCta)) {
                        foreach ($this->prm->UsodeCta as $key => $value) {
                            if ($value == 'F') {
                                $params = [$this->prm->bur_id, 'F', $this->usr_id];
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                                $this->consultar($params);
                                $params = [$this->prm->bur_id, 'G', $this->usr_id];
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES  ( ?, ?, ?)');
                                $this->consultar($params);
                                $params = [$this->prm->bur_id, 'H', $this->usr_id];
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES  ( ?, ?, ?)');
                                $this->consultar($params);
                                if ($_SESSION[_S]['clasAdm-gpo'] == 9 && $_SESSION[_S]['clasAdm-ur'] == 02) {
                                    $params = [$this->prm->bur_id, 'I', $this->usr_id];
                                    $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                                    $this->consultar($params);
                                } // se agrega el tipo I para la ur 902
                            } else {
                                $params = [$this->prm->bur_id, $value, $this->usr_id];
                                $this->setSql(' INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                                $this->consultar($params);
                            }
                        }
                    } else {
                        if ($this->prm->UsodeCta == 'F') {
                            $params = [$this->prm->bur_id, 'F', $this->usr_id];
                            $this->setSql('INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                            $this->consultar($params);
                            $params = [$this->prm->bur_id, 'G', $this->usr_id];
                            $this->setSql('INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                            $this->consultar($params);
                            $params = [$this->prm->bur_id, 'H', $this->usr_id];
                            $this->setSql('INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                            $this->consultar($params);
                            if ($_SESSION[_S]['clasAdm-gpo'] == 9 && $_SESSION[_S]['clasAdm-ur'] == 02) {
                                $params = [$this->prm->bur_id, 'I', $this->usr_id];
                                $this->setSql('INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                                $this->consultar($params);
                            } // se agrega el tipo I para la ur 902
                        } else {
                            $params = [$this->prm->bur_id, $this->prm->UsodeCta, $this->usr_id];
                            $this->setSql('INSERT INTO vin_BancosUrTipoClc ( bur_id , tcl_id, usr_id ) VALUES ( ?, ?, ?)');
                            $this->consultar($params);
                        }
                    }
                    $this->desconectar();
                }

                $metodo = '';
                $tarea = '';
                if (isset($this->prm->tarea)) {
                    switch ($this->prm->tarea) {
                        case 'ConsultaCuentas':
                            $metodo = 'ConsultaCuentas';
                            break;
                        default:
                            $metodo = 'ponTablaListadoCuentas';
                            break;
                    }
                    $this->mixScript('
                        notify("El registro se actualizó exitosamente");
                        comunica("' . $this->pantalla . '",{"metodo":"' . $metodo . '",
                        "tarea":"' . $this->prm->tarea . '",
                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                        }); ');
                } else {
                    $this->mixScript('
                        notify("El registro se actualizó exitosamente"); 
                        comunica("' . $this->pantalla . '",{"metodo":"ponTablaListadoCuentas",
                        "tarea":"addModCuenta",
                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                        }); ');
                }
                break;
        }
    }
    //********Function que llena la lista de cuentas**********/
    public function llenaTablaBancos()
    {
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $this->prm->eba_id = (isset($this->prm->eba_id) && !empty($this->prm->eba_id)) ? $this->prm->eba_id : '';
        $this->prm->bur_id = (isset($this->prm->bur_id)) ? $this->prm->bur_id : '';
        $this->prm->ur_clave = (isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '';
        $this->prm->usoDeCuenta = (isset($this->prm->usoDeCuenta) && !empty($this->prm->usoDeCuenta)) ? $this->prm->usoDeCuenta : '';

        $wh = '';
        $params = [];
        $sql = '
            SELECT 
            bur_id,concat(gpo_id, ur_id) ur_clave,gpo_id,ur_id, bur_cuentaBancaria, iba_nombre,concat(cta_cuenta, " - " ,cta_nombre) CuentaContable,
            eba_id, eba_nombre,eba_color ' . ($this->prm->tarea == 'ConsultaCuentas' ? ' substring(FECHAAuto, 1, 10) as FECHAAuto,' : '') . '
            FROM  cat_BancosUr b
            JOIN cat_InstitucionesBancarias USING(iba_id)
            JOIN cat_EstadoBancos USING (eba_id)
            LEFT JOIN vin_BancosUrPlanCuentas USING(bur_id)
            LEFT JOIN cg_PlanCuentas USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
        ';

        switch ($this->prm->tarea) {
            case 'addModCuenta':
                if ($this->prm->eba_id != '') {
                    $wh .= (strlen($wh) > 0) ? ' AND' : 'WHERE ';
                    $wh .= ' eba_id = ?';
                    array_push($params, $this->prm->eba_id);
                }
                if ($this->usr_id == 2006) {
                    $wh .= (strlen($wh) > 0) ? ' AND' : 'WHERE ';
                    $wh .= ' b. usr_id = ?';
                    array_push($params, 2006);
                }
                $wh .= (strlen($wh) > 0) ? ' AND' : 'WHERE ';
                $wh .= ' gpo_id = ? AND ur_id = ?';
                $wh .= ' OR (gpo_id!=bur_gpo_id AND ur_id !=bur_ur_id AND bur_gpo_id = ? AND bur_ur_id = ? ' . (isset($this->prm->eba_id) && $this->prm->eba_id <> '' ? ' AND eba_id=' . $this->prm->eba_id : '') . ') ';
                array_push($params, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                break;

            case 'autorizacuentaur':
                $wh .= (strlen($wh) > 0) ? ' AND' : 'WHERE ';
                $wh .= ' eba_id = 4';
                $wh .= (strlen($this->prm->ur_clave) == 3) ? ' AND gpo_id = ? AND ur_id = ?' : '';
                (strlen($this->prm->ur_clave) == 3) ? array_push($params, substr($this->prm->ur_clave, 0, 1), substr($this->prm->ur_clave, 1, 2)) : '';
                $wh .= (strlen($this->prm->bur_id) > 0) ? ' AND (bur_id = ? or bur_cuentaBancaria like ?)' : null;
                (strlen($this->prm->bur_id) > 0) ? array_push($params, $this->prm->bur_id, '%' . $this->prm->bur_id . '%') : null;
                break;

            case 'generalizaCuenta':
                $wh .= (strlen($wh) > 0) ? ' AND ' : 'WHERE ';
                $wh .= ' eba_id = 6 ';
                if ($wh == 'WHERE ') {
                    $wh .= (strlen($this->prm->ur_clave) == 3) ? ' gpo_id = ? AND ur_id = ?' : '';
                    (strlen($this->prm->ur_clave) == 3) ? array_push($params, substr($this->prm->ur_clave, 0, 1), substr($this->prm->ur_clave, 1, 2)) : null;
                } else {
                    $wh .= (strlen($this->prm->ur_clave) == 3) ? ' AND gpo_id = ? AND ur_id = ?' : '';
                    (strlen($this->prm->ur_clave) == 3) ? array_push($params, substr($this->prm->ur_clave, 0, 1), substr($this->prm->ur_clave, 1, 2)) : null;
                }
                if ($wh == 'WHERE ') {
                    $wh .= (strlen($this->prm->bur_id) > 0) ? " (bur_id = ? or bur_cuentaBancaria like ?')" : '';
                    (strlen($this->prm->bur_id) > 0) ? array_push($params, $this->prm->bur_id, '%' . $this->prm->bur_id . '%') : null;
                } else {
                    $wh .= (strlen($this->prm->bur_id) > 0) ? ' AND (bur_id = ? or bur_cuentaBancaria like ?)' : '';
                    (strlen($this->prm->bur_id) > 0) ? array_push($params, $this->prm->bur_id, '%' . $this->prm->bur_id . '%') : null;
                }
                break;

            case 'vinculacuentaur':
                $wh = ' WHERE ((gpo_id = ? AND ur_id = ?)';
                array_push($params, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                $wh .= ' OR (bur_gpo_id = ? AND bur_ur_id = ?))';
                array_push($params, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                $wh .= $this->prm->eba_id != '' ? ' AND eba_id = ? ' : ' AND eba_id IN ( 2, 6 ) ';
                $this->prm->eba_id != '' ? array_push($params, $this->prm->eba_id) : null;
                $wh .= $this->prm->usoDeCuenta != '' ? ' AND ucb_id = ?' : '';
                $this->prm->usoDeCuenta != '' ? array_push($params, $this->prm->usoDeCuenta) : null;
                break;
            default:
                echo 'Error';
                break;
        }

        $wh .= (strlen($wh) > 0) ? ' AND' : 'WHERE ';
        $wh .= ' bur_id != 0';

        $sql = $sql . $wh . ' GROUP BY bur_id ORDER BY eba_id, bur_id';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_Cuentas';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaCuenta").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addModCuenta",
                    tarea:"' . $this->prm->tarea . '",
                    "ur_clave" : "' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '",
                    "gpo_id" : "' . ((isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '') . '",
                    "ur_id" : "' . ((isset($this->prm->ur_id)) ? $this->prm->ur_id : '') . '",
                    "bur_id" : $(this).attr("bur_id"),
                    "eba_id" : $(this).attr("eba_id"),
                    "gpo_id" : $(this).attr("gpo_id"),
                    "ur_id" : $(this).attr("ur_id")
                });
                return false;
            });

            $(document).ready(function(){
                $("#tabla-tbl_Cuentas").filterTable({
                    inputSelector: "#filtroTabla"
                });
            });
        ');
    }
    //**************Función para Deshabilitar una Cuenta Bancaria.*******************/
    public function DeshabilitarCta()
    {
        $sql = 'SELECT  * FROM  scp_Clc WHERE bur_id = ?';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->bur_id]);
        $datos = $this->getNumeroRegistros();
        $this->desconectar();
        if ($datos > 0) {
            $this->mixScript('notify("La CLABE bancaria ' . $this->prm->bur_id . ' no puede deshabilitarse debido a que ya fue usada","error");');
        } else {
            /* Borrrar del encuadre del uso de CLC vin_BancosUrTipoClc esta cuenta  */
            /* Cambiar el estado de la cuenta Bancaria  */
            $this->conectar();
            $bit_observacion = 'Eliminar tipo clc de la cuenta bancaria ' . $this->prm->bur_id;
            $params = [$this->ip, $this->usr_id, $bit_observacion];
            $this->setSql('SET @usr_ip =INET_ATON( ? ), @usr_id= ? , @bit_observacion = ?;');
            $this->consultar($params);

            $sql = 'DELETE FROM vin_BancosUrTipoClc WHERE bur_id = ?';
            $this->setSql($sql);
            $this->consultar([$this->prm->bur_id]);

            $bit_observacion = 'Actualizar estado de cuenta bancaria ' . $this->prm->bur_id . ' a deshabilitada';
            $params = [$this->ip, $this->usr_id, $bit_observacion];
            $this->setSql('SET @usr_ip =INET_ATON( ? ), @usr_id = ? , @bit_observacion = ?;');
            $this->consultar($params);

            $sql = 'UPDATE cat_BancosUr set eba_id = 7 WHERE bur_id = ?';
            $this->setSql($sql);
            $this->consultar([$this->prm->bur_id]);
            $this->desconectar();
            if ($this->prm->tarea == 'ConsultaCuentas') {
                $this->mixScript('
                    notify("La cuenta ha sido deshabilitada","error");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"ConsultaCuentas",
                        "tarea":"' . $this->prm->tarea . '",
                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                        });
                ');
            } else {
                $this->mixScript('
                    notify("La cuenta ha sido deshabilitada","error");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"ponTablaListadoCuentas",
                        "tarea":"' . $this->prm->tarea . '"
                        });
                ');
            }
        }
    }
    //**************Function que llena la el historial de una CuentaBancaria*****************/
    public function historialCuenta()
    {
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $this->prm->eba_id = (isset($this->prm->eba_id)) ? $this->prm->eba_id : '';
        $params = [$this->prm->bur_id];
        $sql = '
            SELECT 
                bur_id, bur_cuentaBancaria, iba_nombre, bit_observacion, date(' . BD_CTRL . 'Bitacora.bit_ModificacionBancosUr.usr_fecha) bit_fecha
            FROM  cat_BancosUr
                JOIN cat_InstitucionesBancarias USING(iba_id)
                join ' . BD_CTRL . 'Bitacora.bit_ModificacionBancosUr using( bur_id )            
            WHERE
                bur_id = ?';

        switch ($this->prm->tarea) {
            case 'addModCuenta':
                if ($this->prm->eba_id != '') {
                    $sql .= ' AND bur_tipoCambio = ? ';
                    array_push($params, 'R');
                }
                break;
            case 'autorizacuentaur':
                $sql .= ' and eba_id = ?';
                array_push($params, 4);
                break;
        }

        $sql = $sql . ' ORDER BY bit_fecha desc, bit_bur';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_historialCuenta';
        $this->wrLlenaTabla($nav);
    }
    //************Verifica que la cuenta bancaria (CLABE) no exista ya en la base de datos *****************/
    private function checaCuenta($bur = null)
    {
        $params = [];
        $sql = 'SELECT * FROM cat_BancosUr WHERE bur_id like ?';
        $bur === null ? $params = ['%' . $this->prm->clabe] : $params = ['%' . $bur];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $datos = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() == 1 && $bur !== null) {
            return false;
        }
        if ($this->getNumeroRegistros() == 1 && $bur === null) {
            $this->mixScript('notify("La CLABE bancaria ' . $this->prm->clabe . ' ya existe en la base de datos ::METHOD ","error");');
        } else {
            $params = [];
            $this->conectar();
            $this->setSql('SELECT iba_id FROM tesoreria.cat_InstitucionBancaria where iba_numero = ?;');
            $bur === null ? $params = [substr($this->prm->clabe, 0, 3)] : $params = [substr($bur, 0, 3)];
            $this->consultar($params);
            $valor = $this->extraer();

            $this->setSql('SELECT pbn_numero,pbn_nombre FROM tesoreria.cat_PlazaBancaria where pbn_numero = ?;');
            $bur === null ? $params = [substr($this->prm->clabe, 3, 3)] : $params = [substr($bur, 3, 3)];
            $this->consultar($params);
            $valor_plaza = $this->extraer();
            $this->desconectar();

            $cuenta = substr(($bur === null ? $this->prm->clabe : $bur), -12, -1);
            if ($cuenta && $valor && $valor_plaza) {
                $this->mixScript('
                $("#cuentaBancaria").val("' . $cuenta . '");
                $("#iba_id > option[value=\'' . $valor['iba_id'] . '\']").attr("selected", "selected");
                $("#iba_id").selectpicker("refresh");
                $("#plaza").val("' . $valor_plaza['pbn_numero'] . '-' . $valor_plaza['pbn_nombre'] . '");
            ');

            }
        }

        return true;
    }
    //**********************************Function que permite envíar la Cuenta Bancaria para su vinculación en el área contable de la UR*******/
    private function EnviaraConta()
    {
        $bit_observacion = 'REGISTRO INSERTADO ';
        $params = [$this->ip, $this->usr_id, $bit_observacion];
        $this->conectar();
        $this->setSql(' SET @usr_ip =INET_ATON( ? ), @usr_id = ? , @bit_observacion = ?;');
        $this->consultar($params);

        $sql = 'UPDATE cat_BancosUr set eba_id = ? WHERE gpo_id = ? AND ur_id = ? AND bur_id = ?';
        $params = [2, $_SESSION[_S]['gpo_id'], $_SESSION[_S]['ur_id'], $this->prm->bur_id];

        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $this->mixScript('notify("La cuenta ha sido enviada a contabilidad para su vinculación");
            comunica("' . $this->pantalla . '",{
                "metodo":"ponTablaListadoCuentas",
                "tarea":"' . $this->prm->tarea . '"
            });
        ');
    }
    //**********Function que permite envíar la Cuenta Bancaria para su Autorización por parte de la SEFIN*************/
    private function EnviarAsefin()
    {
        $bit_observacion = 'REGISTRO INSERTADO ';
        $params = [$this->ip, $this->usr_id, $bit_observacion];
        $this->conectar();
        $this->setSql(' SET @usr_ip =INET_ATON( ? ), @usr_id = ? , @bit_observacion = ?;');
        $this->consultar($params);

        $sql = 'UPDATE cat_BancosUr set eba_id = ? WHERE gpo_id = ? AND ur_id = ?  AND bur_id = ? ';
        $params = [4, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->prm->bur_id];
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $this->mixScript('notify("La cuenta ha sido enviada SEFIN para su autorización");
            comunica("' . $this->pantalla . '",{
                "metodo":"ponTablaListadoCuentas",
                "tarea":"' . $this->prm->tarea . '",
            });
        ');
    }
    /****************Función que rechaza o autoriza las Cuentas Bancarias ****************/
    private function ValidarAutRec()
    {
        $Bancos = new BancosUr();
        $Bancos->setBur_id($this->prm->bur_id);
        $Bancos->select();
        /*Para que no se autorice una Cta. Bancaria ya autorizada*/

        if ($Bancos->getEba_id() == 6 && $Bancos->getBur_fondoRotatorio() == 'N') {
            $this->mixScript('notify("No puede modificar una cuenta autorizada","error");');
            return false;
        } else {
            $sql = 'SELECT  * FROM  scp_Clc WHERE bur_id = ?';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->prm->bur_id]);
            $datos = $this->getNumeroRegistros();
            $this->desconectar();
            if ($datos > 0) {
                $this->mixScript('notify("La CLABE bancaria ' . $this->prm->bur_id . ' no se puede rechazar debido a que ya fue usada en una CLC","error");');
                return false;
            }
        }

        if ($this->prm->Autorizado == 'no') {
            $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
            $banco = new BancosUr($this->prm);

            $MotRech = strtoupper($this->prm->motivo);
            $UrSolicitud = $banco->getGpo_id() . $banco->getUr_id();
            $params = [$banco->getBur_id(), 'R', $UrSolicitud, $MotRech, $this->ip, $this->usr_id];
            $sql = ' INSERT INTO ' . BD_BITACORA . '.bit_ModificacionBancosUr(bur_id, bur_tipoCambio,bur_ur_solicitud, bit_observacion, usr_ip,usr_id) VALUES ( ?, ?, ?, ?,INET_ATON( ? ), ?)';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $this->desconectar();
        } else {
            $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
            $banco = new BancosUr($this->prm);
            $UrSolicitud = $banco->getGpo_id() . $banco->getUr_id();

            $params = [$banco->getBur_id(), 'A', $UrSolicitud, 'CUENTA AUTORIZADA', $this->ip, $this->usr_id];
            $this->conectar();
            $sql = 'INSERT INTO ' . BD_BITACORA . '.bit_ModificacionBancosUr(bur_id, bur_tipoCambio, bur_ur_solicitud, bit_observacion, usr_ip, usr_id) VALUES ( ?, ?, ?, ?, INET_ATON( ? ) ,? )';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $this->desconectar();
        }

        $sql = 'SELECT  * FROM  scp_Clc WHERE bur_id = ? ';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->bur_id]);
        $datos = $this->getNumeroRegistros();
        $this->desconectar();

        if ($datos > 0) {
            $this->mixScript('notify("La CLABE bancaria ' . $this->prm->bur_id . ' no se puede rechazar debido a que ya fue usada en una CLC","error");');
        } else {

            $bit_observacion = 'ACTUALIZAR REGISTRO PARA ' . ($this->prm->Autorizado == 'si' ? 'AUTORIZARLO' : 'RECHAZARLO');
            $params = [$this->ip, $this->usr_id, $bit_observacion];
            $this->conectar();
            $this->setSql(' SET @usr_ip =INET_ATON( ? ), @usr_id= ? , @bit_observacion = ? ;');
            $this->consultar($params);

            $sql = 'UPDATE cat_BancosUr set eba_id = ? WHERE bur_id = ?';
            $this->prm->Autorizado == 'si' ? $params = ['6', $this->prm->bur_id] : $params = ['5', $this->prm->bur_id];
            $this->setSql($sql);
            $this->consultar($params);

            /*
        * Consulta de la cuenta bancaria a autorizar
        */
            $sql = 'SELECT * FROM ' . BD_CTRL . '.cat_BancosUr WHERE bur_id = ?';
            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql($sql);
            $con->consultar([$this->prm->bur_id]);

            if ($fila = $con->extraer()) {
                /**
                 * Cambia estado de la vinculación de ctrl2023.vin_BancosUrProovedor si la cuenta fue autorizada
                 * y es de uso 4 (Pago directo)
                 */
                if ($fila['ucb_id'] == 4 && $fila['eba_id'] == 6) {
                    $con2 = new AccesoDatos();
                    $params = [$this->ip, $this->usr_id, 'CAMBIO DE ESTATUS DE LA VINCULACION D LA CUENTA CON EL PROVEEDOR'];
                    $sqlObs = 'SET @usr_ip = INET_ATON( ? ), @usr_id = ?, @bit_observacion = ?;';
                    $con2->conectar();
                    $con2->setSql($sqlObs);
                    $con2->consultar($params);

                    $sqlvin = 'UPDATE ' . BD_CTRL . '.vin_BancosUrProveedor SET sts_id = 1 WHERE bur_id = ?';
                    $con2->setSql($sqlvin);
                    $con2->consultar([$this->prm->bur_id]);
                }

                /**
                 * Si la cuenta fue autorizada y es de la UR 902, la inserta en
                 * tesoreria.ban_CuentaBancariaBeneficiario
                 */
                if ($fila['gpo_id'] == 9 && $fila['ur_id'] == 2 && $fila['eba_id'] == 6) {

                    $sucursal = '0';
                    if (is_numeric($fila['bur_sucursal'])) {
                        $sucursal = $fila['bur_sucursal'];
                    }

                    $fechaAlta = date('Y-m-d');

                    if (empty($fila['bur_fechaAlta'])) {
                        $fechaAlta = $fila['usr_fecha'];
                    }

                    $cbb = new stdClass();
                    $cbb->abc = 'a';
                    $cbb->cbb_id = $fila['bur_id'];
                    $cbb->cuenta = $fila['bur_cuentaBancaria'];
                    $cbb->fechaApertura = $fechaAlta;
                    $cbb->anio = APP_EJERCICIO;
                    $cbb->sucursal = $sucursal;
                    $cbb->nombre = $fila['bur_beneficiario'];
                    $cbb->alias = substr($fila['bur_alias'], 0, 19);
                    $cbb->cliente = '0';
                    $cbb->iba_id = $fila['iba_id'];
                    $cbb->pbn_id = $fila['pbn_id'];
                    $cbb->tbe_id = 'MUN';
                    $cbb->eba_id = 6;
                    $cbb->observacion = 'ALTA DE CUENTA DESDE MODULO SCP';
                    $cbb->plazaBancaria = $fila['pbn_id'];

                    $vbm = new stdClass();
                    $vbm->abc = 'a';
                    $vbm->mun_id = $fila['ue_id'];
                    $vbm->tfn_id = 9;
                    $vbm->cbb_id = $cbb->cbb_id;

                    BeneficiarioMunicipio::abc($vbm);
                }

                /**
                 * Nueva regla para la UR: 901
                 * Si la cuenta fue autorizada y es de la UR 901, la inserta en
                 * tesoreria.ban_CuentaBancariaBeneficiario
                 */
                if ($fila['gpo_id'] == 9 && $fila['ur_id'] == 1 && $fila['eba_id'] == 6) {
                    $vbm = new stdClass();
                    $vbm->abc = 'a';
                    $vbm->mun_id = $fila['ue_id'];
                    $vbm->tfn_id = 9;
                    $vbm->cbb_id = $fila['bur_id'];

                    BeneficiarioMunicipio::abc($vbm);
                }
            }

            if ($this->prm->tarea == 'ConsultaCuentas') {

                $this->mixScript('
                    notify("La cuenta ha sido ' . ($this->prm->Autorizado == 'si' ? ' autorizada' : 'rechazada') . '","error");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"ConsultaCuentas",
                        "tarea":"' . $this->prm->tarea . '",
                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                    });
                ');
            } else {

                $this->mixScript('
                    notify("La cuenta ha sido ' . ($this->prm->Autorizado == 'si' ? ' autorizada' : 'rechazada') . '");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"ponTablaListadoCuentas",
                        "tarea":"' . $this->prm->tarea . '",
                        "ur_clave":"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                    });
                ');
            }
        }
    }
    //*************Consulta cuentas bancarias Autorizadas y Deshabilitadas****************/
    private function ConsultaCuentas()
    {
        $this->titulo('Consulta de cuentas Bancarias');
        $inputNumCta = new stdClass();
        $inputNumCta->id = 'inputNumCta';
        $inputNumCta->label = 'Número de Cuenta';
        $inputNumCta->placeholder = '000000000000000000';
        $inputNumCta->enDiv = true;
        $inputNumCta->class = 'limpiar';
        $inputNumCta->required = true;
        $inputNumCta->maxLength = 18;
        $inputNumCta->formato = 'entero';

        $inputUr = new stdClass();
        $inputUr->id = 'inputUr';
        $inputUr->label = 'UR';
        $inputUr->placeholder = '000';
        $inputUr->enDiv = true;
        $inputUr->value = (isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '';
        $inputUr->class = 'limpiar';
        $inputUr->required = true;
        $inputUr->maxLength = 3;
        $inputUr->formato = 'entero';

        $btnBuscarCta = new stdClass();
        $btnBuscarCta->id = 'btnBuscarCta';
        $btnBuscarCta->class = 'mt-3 Buscar';
        $btnBuscarCta->text = 'Buscar';
        $btnBuscarCta->value = 'Buscar ';
        $btnBuscarCta->color = 'gris';
        $btnBuscarCta->icon = 'search me-2';

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->class = 'mt-3';
        $clean->id = 'clean';
        $clean->color = 'cielo';
        $clean->icon = 'refresh me-2';

        $filtro = '<div class="row">';
        $filtro .= '<div class="col-md-5 col-xs-5 mb-4">';
        $filtro .= $this->ponInput($inputNumCta);
        $filtro .= '</div>';
        $filtro .= '<div class="col-md-4 col-xs-4 mb-4">';
        $filtro .= $this->ponInput($inputUr);
        $filtro .= '</div>';
        $filtro .= '<div class="col-md-3 col-xs-3">';
        $filtro .= $this->ponBoton($btnBuscarCta) . ' ' . $this->ponBoton($clean);
        $filtro .= '</div>';
        $filtro .= '</div>';
        $tablaConsultaCta = new stdClass();
        $tablaConsultaCta->id = 'tbl_Cuentas';
        $tablaConsultaCta->titulo = 'Consulta de Cuentas Bancarias';
        $tablaConsultaCta->tools = $filtro;
        $tablaConsultaCta->comunica = new stdClass();
        $tablaConsultaCta->comunica->pantalla = $this->pantalla;
        $tablaConsultaCta->comunica->metodo = 'llenaTablaConsultaCta';
        $tablaConsultaCta->comunica->bur_id = '{$(\'#inputNumCta\').val()}';
        $tablaConsultaCta->comunica->ur_clave = '{$(\'#inputUr\').val()}';
        $tablaConsultaCta->comunica->tarea = $this->prm->tarea;

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->width = '6%';
        $th->align = 'center';
        $th->texto = 'Ver';
        array_push($tr, $th);
        //Ur
        $th = new stdClass();
        $th->width = '5%';
        $th->align = 'center';
        $th->texto = 'Ur';
        array_push($tr, $th);
        //Clabe
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Clabe';
        array_push($tr, $th);
        //Cuenta bancaria
        $th = new stdClass();
        $th->width = '9%';
        $th->align = 'center';
        $th->texto = 'Cuenta';
        array_push($tr, $th);
        //Nombre del Banco
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Banco';
        array_push($tr, $th);

        $th = new stdClass();
        $th->width = '20%';
        $th->align = 'center';
        $th->texto = 'Alias';
        array_push($tr, $th);
        //Cuenta contable
        $th = new stdClass();
        $th->width = '24%';
        $th->align = 'center';
        $th->texto = 'Cta. Contable';
        array_push($tr, $th);
        //Estado
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Estado';
        array_push($tr, $th);
        //Fecha de Autorización
        $th = new stdClass();
        $th->width = '8%';
        $th->align = 'center';
        $th->texto = 'Fecha de Autorizacion';
        array_push($tr, $th);

        $trbody = [];    // se inicia el cuerpo de la tablaConsultaCta

        /*ver editar*/
        $td = new stdClass();
        $td->icono = 'eye';
        $td->class = 'EditaCuenta';
        $td->tipo = 'boton';
        $td->attr = new stdClass();
        $td->attr->bur_id = 'bur_id';
        $td->attr->eba_id = 'eba_id';
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        array_push($trbody, $td);

        /*ur_Clave */
        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        $td->align = 'center';
        array_push($trbody, $td);

        /*clabe */
        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        array_push($trbody, $td);
        /*Cuenta */
        $td = new stdClass();
        $td->campoTxt = 'bur_cuentaBancaria';
        array_push($trbody, $td);
        /*banco */
        $td = new stdClass();
        $td->campoTxt = 'iba_nombre';
        array_push($trbody, $td);
        /*banco */
        $td = new stdClass();
        $td->campoTxt = 'bur_alias';
        array_push($trbody, $td);
        /*cta c */
        $td = new stdClass();
        $td->campoTxt = 'CuentaContable';
        array_push($trbody, $td);
        /*estado */
        $td = new stdClass();
        $td->campoTxt = 'eba_nombre';
        $td->tipo = 'bolita';
        $td->color = 'eba_color';
        $td->lbl = 'eba_nombre';
        $td->linea = 'eba_color';
        $td->align = 'center';
        array_push($trbody, $td);
        /*Fecha de Autorización */
        $td = new stdClass();
        $td->campoTxt = 'FECHAAuto';
        $td->align = 'center';
        array_push($trbody, $td);

        $tablaConsultaCta->encabezado = [$tr];
        $tablaConsultaCta->cuerpo = $trbody;

        $this->mixHtml('<div class="col-md-12 col-xs-12 mb-4">' . $this->wrPonTabla($tablaConsultaCta) . '</div>');
        $Reporte = '
            <a id="imprimir" target="_blank" class="me-1" title="Imprimir Cuentas Bancarias" style="padding: 0">
                <button type="button" class="btn btn-outline-danger " >
                    <i class="fa fa-file-pdf me-2"></i> PDF
                </button>
            </a>';
        $ReporteExcel = '
            <a id="btnPrintExcel" target="_blank" class="me-1" title="Imprimir Cuentas Bancarias" style="padding: 0">
                <button type="button" class="btn btn-outline-success " >
                    <i class="fa fa-file-excel me-2"></i> Excel
                </button>
            </a>';
        $botones = '
            <button id="btnRegresarIndex" type="button" class="btn btn-outline-secondary me-1" title="Regresar al Menú Principal">
                <i class="fa-solid fa-circle-left me-2"></i>Regresar
            </button>';
        $this->agregaTools(
            $botones . $Reporte . $ReporteExcel . '<input type="search" width=50 id="filtroTabla" style="display: inline; width:300px; height: 33px;" class="form-control me-1"  placeholder="Filtrar Cuenta Bancaria"/>'
        );

        $this->mixScript($this->wrLlamaLlenaTabla('tbl_Cuentas') . '
            $("#btnBuscarCta").click (function(){
                if( $("#inputUr").val() != "" || $("#inputNumCta").val() != ""){
                    ' . $this->wrLlamaLlenaTabla('tbl_Cuentas') . '
                }
            });
            $("#clean").click(function(){
                $(".limpiar").val("");
                comunica("' . $this->pantalla . '",{
                    metodo:"ConsultaCuentas",
                    tarea:"' . $this->prm->tarea . '"
                    });
                return false;
                });
            $("#btnRegresarIndex").click (function(){
                comunica("' . $this->pantalla . '",{
                metodo:"main",
                ur_clave:"' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '"
                });
                $("#btnRegresarIndex").remove();
                $("#filtroTabla").remove();
                $("#imprimir").remove();
                return false;
            });

            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );
            $("#imprimir").click(function(){
                inputUr=$("#inputUr").val();
                var form = $("<form>")
                    .attr("action", "rep/doc/scp/catalogos/rep_BancosUr.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "inputUr").attr("value", inputUr))

                $("body").append(form);
                form.submit();
                form.remove();
            });
            $("#btnPrintExcel").click(function(){
                inputUr=$("#inputUr").val();
                var form = $("<form>")
                    .attr("action", "rep/excel/scp/catalogos/exp_BancosUr.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "inputUr").attr("value", inputUr))

                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }
    //*********LLena la tabla de la Consulta cuentas bancarias Autorizadas y Deshabilitadas*************/
    public function llenaTablaConsultaCta()
    {
        $this->prm->eba_id = (isset($this->prm->eba_id)) ? $this->prm->eba_id : '';
        $this->prm->bur_id = (isset($this->prm->bur_id)) ? $this->prm->bur_id : '';
        $this->prm->ur_clave = (isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '';

        $wh = '';
        $params = [];
        $sql = '
            SELECT 
            DISTINCT bur_id,concat(gpo_id, ur_id) ur_clave, bur_cuentaBancaria, iba_nombre, bur_alias, eba_id, eba_nombre,eba_color,substring(FECHAAuto, 1, 10) as FECHAAuto,
            concat(cta_cuenta, " - " ,cta_nombre) CuentaContable
            FROM  cat_BancosUr
            JOIN cat_InstitucionesBancarias USING(iba_id)
            JOIN cat_EstadoBancos USING (eba_id)
            LEFT JOIN vin_BancosUrPlanCuentas USING(bur_id)
            LEFT JOIN cg_PlanCuentas USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)';

        switch ($this->prm->tarea) {
            case 'ConsultaCuentas':
                $sql .= 'LEFT JOIN (SELECT bur_id, MAX(usr_fecha) as FECHAAuto 
                        FROM ' . BD_CTRL . 'Bitacora.bit_ModificacionBancosUr
                        WHERE bur_tipoCambio ="A"
                        GROUP BY bur_id) a 
                        USING(bur_id)';
                $wh .= (strlen($wh) > 0) ? ' AND ' : 'WHERE ';
                if (strlen($this->prm->bur_id) == 0) {
                    $wh .= ' eba_id IN (6,7) ';
                }
                if ($wh == 'WHERE ') {
                    $wh .= (strlen($this->prm->ur_clave) == 3) ? ' gpo_id = ? AND ur_id = ?' : '';
                    (strlen($this->prm->ur_clave) == 3) ? array_push($params, substr($this->prm->ur_clave, 0, 1), substr($this->prm->ur_clave, 1, 2)) : null;
                } else {
                    $wh .= (strlen($this->prm->ur_clave) == 3) ? ' AND gpo_id = ? AND ur_id = ? ' : '';
                    (strlen($this->prm->ur_clave) == 3) ? array_push($params, substr($this->prm->ur_clave, 0, 1), substr($this->prm->ur_clave, 1, 2)) : null;
                }
                if ($wh == 'WHERE ') {
                    $wh .= (strlen($this->prm->bur_id) > 0) ? ' (bur_id = ? or bur_cuentaBancaria like ?)' : '';
                    (strlen($this->prm->bur_id) > 0) ? array_push($params, $this->prm->bur_id, '%' . $this->prm->bur_id . '%') : null;
                } else {
                    $wh .= (strlen($this->prm->bur_id) > 0) ? ' AND (bur_id = ? or bur_cuentaBancaria like ?)' : '';
                    (strlen($this->prm->bur_id) > 0) ? array_push($params, $this->prm->bur_id, '%' . $this->prm->bur_id . '%') : null;
                }
                break;
        }

        $wh .= (strlen($wh) > 0) ? ' AND' : 'WHERE ';
        $wh .= ' bur_id != 0';

        $sql = $sql . $wh . ' ORDER BY eba_id, bur_id';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_Cuentas';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaCuenta").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addModCuenta",
                    tarea:"' . $this->prm->tarea . '",
                    "bur_id":$(this).attr("bur_id"),
                    "eba_id":$(this).attr("eba_id"),
                    "ur_clave" : "' . ((isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '') . '",
                    "gpo_id"   : "' . ((isset($this->prm->gpo_id)) ? $this->prm->gpo_id : $_SESSION[_S]['gpo_id']) . '",
                    "ur_id"    : "' . ((isset($this->prm->ur_id)) ? $this->prm->ur_id : $_SESSION[_S]['ur_id']) . '"
                });
                return false;
            });

            $(document).ready(function(){
                $("#tabla-tbl_Cuentas").filterTable({
                    inputSelector: "#filtroTabla"
                });
            });
        ');
    }
    //*************Función agrega la Cuenta Contable al catálogo**************/
    private function GuardaCuentaContable()
    {
        $Bancos = new BancosUr();
        $Bancos->setBur_id($this->prm->bur_id);
        $Bancos->select();

        $this->prm->ger_id = substr($this->prm->nivel1, 0, 1);
        $this->prm->cgp_id = substr($this->prm->nivel1, 1, 1);
        $this->prm->rub_id = substr($this->prm->nivel1, 2, 1);
        $this->prm->cgcu_id = substr($this->prm->nivel1, 3, 1);
        $this->prm->cgsc_id = substr($this->prm->nivel1, 4, 2);

        $this->prm->cta_gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->prm->cta_ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->prm->cta_ue_id = $this->prm->cta_ue_id;

        if ($this->prm->cta_ue_id == '') {
            $this->mixScript('
                notify("Unidad Ejecutora vacia");
            ');
        }
        $params = [$this->prm->ger_id, $this->prm->cgp_id, $this->prm->rub_id, $this->prm->cgcu_id, $this->prm->cgsc_id, $this->prm->cta_gpo_id, $this->prm->cta_ur_id, $this->prm->cta_ue_id, $this->prm->cgc_id, $this->prm->cta_id];
        $sql = 'SELECT cta_nombre FROM cg_PlanCuentas
                    WHERE ger_id = ? and cgp_id = ? and rub_id = ? and cgcu_id = ? and cgsc_id = ? and cta_gpo_id = ? and cta_ur_id = ? and cta_ue_id = ? and cgc_id = ? and cta_id = ?';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $datos = $this->extraer();
        $this->desconectar();

        if (!isset($this->prm->cta_nombre)) {
            // Se evalua $datos (capturado antes de desconectar) porque getNumeroRegistros() no es
            // confiable despues de desconectar() y provocaba un INSERT duplicado en cg_PlanCuentas.
            // Si la cuenta contable ya existe con estas llaves, la CLABE ya se encuentra vinculada.
            if (!empty($datos)) {
                $this->mixScript('
                    notify("La CLABE ya se encuentra vinculada", "warning");
                ');
                return;
            }

            $Bancos = new BancosUr();
            $Bancos->setBur_id($this->prm->bur_id);
            $Bancos->select();

            // Dar de alta la cuenta Contable en cg_PlanCuentas
            $this->prm->abc = 'a';
            $this->prm->agc_id = 'GRAL';
            $this->prm->cta_nombre = 'CTA ' . $this->prm->bur_id . ' - ' . $Bancos->getAlias();
            PlanCuentas::abc($this->prm);

            $this->prm->abc = 'c';
            $bancos = new BancosUr();
            $bancos->setBur_id($this->prm->bur_id);
            $bancos->select();

            if ($bancos->getEba_id() == 5) {
                $this->prm->eba_id = 3;
            }

            $this->prm->guardaCuenta = true;

            BancosUr::abc($this->prm);
            if ($this->contabilidad) {
                $this->mixScript('
                    notify("El registro se actualizó exitosamente");
                    comunica("' . $this->pantalla . '",{"metodo":"ponTablaListadoCuentas","tarea":"vinculacuentaur"});
                ');
            } else {
                $this->mixScript('
                    notify("El registro se actualizó exitosamente");
                    comunica("' . $this->pantalla . '",{"metodo":"ponTablaListadoCuentas","tarea":"addModCuenta"});
                ');
            }
        } else {
            if ($this->getNumeroRegistros()) {
                $this->mixScript('
                    $("#cta_cuenta").val($("#cta_cuenta").val()+ "-' . $datos['cta_nombre'] . '")');
            } else {
                $this->mixScript('
                    $("#cta_cuenta").val($("#cta_cuenta").val()+" CTA-"+$("#cuentaBancaria").val())');
            }
        }
    }
    //**********************************Función que valida si la Ur que ingresa el usuario para compartir una cuenta, exite*******************/
    private function ValidaUr()
    {
        $grupo = substr($this->prm->Ur, 0, 1);
        $Ur = substr($this->prm->Ur, 1, 2);

        $consultaUr = 'SELECT gpo_id FROM cat_UnidadResponsable WHERE gpo_id = ? and ur_id = ?';
        $this->conectar();
        $this->setSql($consultaUr);
        $this->consultar([$grupo, $Ur]);
        $data = $this->extraer();
        $this->desconectar();

        if ($data['gpo_id'] == '') {
            $this->mixScript('
                notify("Ingresar una UR válida","error");
            ');
        }
    }
    //**********************************Tabla que muestra los usos de Clcs que tienen las Cuentas Bancarias***********************************/
    private function MuestraTipoClc()
    {
        $inputNumCta = new stdClass();
        $inputNumCta->id = 'inputNumCta';
        $inputNumCta->label = 'Número de Cuenta';
        $inputNumCta->placeholder = '000000000000000000';
        $inputNumCta->enDiv = true;
        $inputNumCta->class = 'limpiar';
        $inputNumCta->required = true;
        $inputNumCta->maxLength = 18;
        $inputNumCta->formato = 'entero';

        $inputUr = new stdClass();
        $inputUr->id = 'inputUr';
        $inputUr->label = 'UR';
        $inputUr->placeholder = '000';
        $inputUr->enDiv = true;
        $inputUr->class = 'limpiar';
        $inputUr->required = true;
        $inputUr->maxLength = 3;
        $inputUr->formato = 'entero';

        $btnBuscarCta = new stdClass();
        $btnBuscarCta->id = 'btnBuscarCta';
        $btnBuscarCta->class = 'mt-3 Buscar';
        $btnBuscarCta->text = 'Buscar';
        $btnBuscarCta->value = 'Buscar ';
        $btnBuscarCta->color = 'gris';
        $btnBuscarCta->icon = 'search me-2';

        $clean = new stdClass();
        $clean->class = 'mt-3';
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->color = 'cielo';
        $clean->icon = 'refresh me-2';

        $filtro = '<div class="row mb-4">';
        $filtro .= '<div class="col-md-5 col-xs-5">';
        $filtro .= $this->ponInput($inputNumCta);
        $filtro .= '</div>';
        $filtro .= '<div class="col-md-4 col-xs-4">';
        $filtro .= $this->ponInput($inputUr);
        $filtro .= '</div>';
        $filtro .= '<div class="col-md-3 col-xs-3">';
        $filtro .= $this->ponBoton($btnBuscarCta) . ' ' . $this->ponBoton($clean);
        $filtro .= '</div>';
        $filtro .= '</div>';
        $botones = '<button id="btnRegresarIndex" type="button" class="btn btn-outline-secondary me-1" title="Regresar al Menú Principal"><i class="fa-solid fa-circle-left me-2"></i>Regresar</button>';
        $this->agregaTools($botones . '
            <input type="search" width=50 id="filtroTabla" style="display: inline; width:300px; height: 33px;" class="form-control me-1" placeholder="Filtrar Cuenta Bancaria"/>
        ');

        $tabla = new stdClass();
        $tabla->id = 'tbl_CuentasModTipClc';
        $tabla->tools = $filtro;
        $tabla->titulo = 'Modificar Tipo de CLC Asignado a una Cuenta Bancaria ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'LlenaTablaModTipoClc';
        $tabla->comunica->bur_id = '{$(\'#inputNumCta\').val()}';
        $tabla->comunica->ur_clave = '{$(\'#inputUr\').val()}';

        $tr = [];

        /* Ur */
        $th = new stdClass();
        $th->width = '6%';
        $th->align = 'center';
        $th->texto = 'Ur';
        array_push($tr, $th);

        /* Clabe */
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Clabe';
        array_push($tr, $th);

        /*Cuenta bancaria*/
        $th = new stdClass();
        $th->width = '12%';
        $th->align = 'center';
        $th->texto = 'Cuenta';
        array_push($tr, $th);

        /*Estado*/
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Estado';
        array_push($tr, $th);

        /*SERV. PERS*/
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'A-Serv.Pers.';
        array_push($tr, $th);

        /*GTOS. OPE*/
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'B- Gto. Oper.';
        array_push($tr, $th);

        /*INVERSIÓN*/
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'C- Pag. a Ter.';
        array_push($tr, $th);

        /*PAGOS A TER*/
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'I- Inversión';
        array_push($tr, $th);

        /*PAG.A TER(OTROS)*/
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'M- Pag. a Ter. O.';
        array_push($tr, $th);

        /*TRANSFERENCIAS*/
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'L- Transferencias';
        array_push($tr, $th);

        // se inicia el cuerpo de la tablaConsultaCta
        $trbody = [];

        $tabla->template = '
            <tr class="odd">
                <!--Columna de Ur-->
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${ur_clave}</label>
                </td>
                <!--Columna de Clabe-->
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${bur_id}</label>
                </td>
                  <!--Columna de Cuenta-->
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${bur_cuentaBancaria}</label>
                </td>
                <!--Columna de Estado-->
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${eba_nombre}</label>
                </td>
                <!--Columna de A (SERV. PERSO.)-->
                <td class="sorting_1">
                    {{if A =="FALSE"}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImprimir" class="btn btn-outline-success btn-sm agregacta" bur_id="${bur_id}" accion="agrega" tipo ="A">
                                <i class="fa fa-plus me-1"></i>Agrega 
                            </button>
                        </div>
                    {{else}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImprimir" class="btn btn-outline-danger btn-sm button btn-sm agregacta " bur_id="${bur_id}" accion="quita" tipo="A">
                                <i class="fa fa-times me-1"></i> Quitar  
                            </button>
                        </div>
                    {{/if}}
                <!--Columna de B (GTOS. OPER.)-->
                <td class="sorting_1">
                    {{if B =="FALSE"}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-success btn-sm agregacta" bur_id="${bur_id}" accion="agrega" tipo="B">
                                <i class="fa fa-plus me-1"></i>Agrega 
                            </button>
                        </div>
                    {{else}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-danger btn-sm agregacta" bur_id="${bur_id}" accion="quita" tipo="B">
                                <i class="fa fa-times me-1"></i>Quitar
                            </button>
                        </div>
                    {{/if}}
                </td>
                <!--Columna de C (PAGOS A TERCEROS)-->
                <td class="sorting_1">
                    {{if C =="FALSE"}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-success btn-sm agregacta" bur_id="${bur_id}" accion="agrega" tipo="C">
                                <i class="fa fa-plus me-1"></i>Agrega 
                            </button>
                        </div>
                    {{else}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-danger btn-sm agregacta" bur_id="${bur_id}" accion="quita" tipo="C">
                                <i class="fa fa-times me-1"></i>Quitar
                            </button>
                        </div>
                    {{/if}}
                </td>
                <!--Columna de I (INVERSIÓN)-->
                <td class="sorting_1">
                    {{if I =="FALSE"}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-success btn-sm agregacta" bur_id="${bur_id}" accion="agrega" tipo="I">
                                <i class="fa fa-plus me-1"></i>Agrega 
                            </button>
                        </div>
                    {{else}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-danger btn-sm agregacta" bur_id="${bur_id}" accion="quita" tipo="I">
                                <i class="fa fa-times me-1"></i>Quitar
                            </button>
                        </div>
                    {{/if}}
                </td>
                <!--Columna de M (PAG. A TERC. "OTROS" )-->
                <td class="sorting_1">
                    {{if M =="FALSE"}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-success btn-sm agregacta" bur_id="${bur_id}" accion="agrega" tipo="M">
                                <i class="fa fa-plus me-1"></i>Agrega 
                            </button>
                        </div>
                    {{else}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-danger btn-sm agregacta" bur_id="${bur_id}" accion="quita" tipo="M">
                                <i class="fa fa-times me-1"></i>Quitar
                            </button>
                        </div>
                    {{/if}}
                </td>
                <!--Columna de L (PAG. A TERC. "OTROS" )-->
                <td class="sorting_1">
                    {{if L =="FALSE"}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-success btn-sm agregacta" bur_id="${bur_id}" accion="agrega" tipo="L">
                                <i class="fa fa-plus me-1"></i>Agrega 
                            </button>
                        </div>
                    {{else}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button title="" id="btnImpClc" class="btn btn-outline-danger btn-sm agregacta" bur_id="${bur_id}" accion="quita" tipo="L">
                                <i class="fa fa-times me-1"></i>Quitar
                            </button>
                        </div>
                    {{/if}}
                </td>
            </tr>
        ';

        $tabla->encabezado = [$tr];
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript($this->wrLlamaLlenaTabla('tbl_CuentasModTipClc') . '

            $("#btnBuscarCta").click (function(){
                if( $("#inputUr").val() != "" || $("#inputNumCta").val() != ""){
                    ' . $this->wrLlamaLlenaTabla('tbl_CuentasModTipClc') . '
                }
            });

            $("#clean").click(function(){
                $(".limpiar").val("");
                    comunica("' . $this->pantalla . '",{
                    metodo:"MuestraTipoClc"
                    });
                return false;
            });

            $("#btnRegresarIndex").click (function(){
                comunica("' . $this->pantalla . '",{
                metodo:"main"
                });
                $("#btnRegresarIndex").remove();
                $("#filtroTabla").remove();
                return false;
            });

            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );
        ');
    }
    //***LLena la tabla de la Modificación del Tipo de Uso de Clc de la Cta. Bancaria***/
    private function LlenaTablaModTipoClc()
    {
        $this->prm->bur_id = (isset($this->prm->bur_id)) ? $this->prm->bur_id : '';
        $this->prm->ur_clave = (isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '';

        $wh = '';
        $params = [];
        $wh .= (strlen($this->prm->ur_clave) == 3) ? ' AND gpo_id = ? AND ur_id = ? ' : '';
        (strlen($this->prm->ur_clave) == 3) ? array_push($params, substr($this->prm->ur_clave, 0, 1), substr($this->prm->ur_clave, 1, 2)) : null;
        $wh .= (strlen($this->prm->bur_id) > 0) ? ' AND (bur_id = ? or bur_cuentaBancaria like ?)' : '';
        (strlen($this->prm->bur_id) > 0) ? array_push($params, $this->prm->bur_id, '%' . $this->prm->bur_id . '%') : null;
        $sql = '
            SELECT CONCAT(gpo_id,ur_id) AS ur_clave, bur_id,bur_cuentaBancaria,eba_nombre,
                IF(A IS NULL,"FALSE","A") AS A, 
                IF(B IS NULL,"FALSE","B") AS B, 
                IF(C IS NULL,"FALSE","C") AS C, 
                IF(I IS NULL,"FALSE","I") AS I, 
                IF(M IS NULL,"FALSE","M") AS M,
                IF(L IS NULL,"FALSE","M") AS L
            FROM
                vin_BancosUrTipoClc
                INNER JOIN cat_BancosUr USING(bur_id)
                INNER JOIN cat_EstadoBancos USING(eba_id)
                LEFT JOIN
                (
                    SELECT tcl_id AS A,bur_id FROM vin_BancosUrTipoClc
                    INNER JOIN cat_TipoClc  USING(tcl_id)WHERE tcl_id = "A"
                )A USING(bur_id)
                LEFT JOIN
                (
                    SELECT tcl_id AS B,bur_id FROM vin_BancosUrTipoClc
                    INNER JOIN cat_TipoClc  USING(tcl_id)WHERE tcl_id = "B"
                )B USING(bur_id)
                LEFT JOIN
                (
                    SELECT tcl_id AS C,bur_id FROM vin_BancosUrTipoClc
                    INNER JOIN cat_TipoClc  USING(tcl_id)WHERE tcl_id = "C"
                )C USING(bur_id)
                LEFT JOIN
                (
                    SELECT IF(tcl_id IS NULL,"FALSE","OK") AS I,bur_id FROM vin_BancosUrTipoClc
                    INNER JOIN cat_TipoClc  USING(tcl_id)WHERE tcl_id IN("F","G","H","I")
                    GROUP BY I, bur_id
                )I USING(bur_id)
                LEFT JOIN
                (
                    SELECT tcl_id AS M,bur_id FROM vin_BancosUrTipoClc
                    INNER JOIN cat_TipoClc  USING(tcl_id)WHERE tcl_id = "M"
                )M USING(bur_id)
                LEFT JOIN
                (
                    SELECT tcl_id AS L,bur_id FROM vin_BancosUrTipoClc
                    INNER JOIN cat_TipoClc  USING(tcl_id)WHERE tcl_id = "L"
                )L USING(bur_id)
            WHERE eba_id=6 AND bur_fondoRotatorio="N"
        ';

        $sql = $sql . $wh . ' GROUP BY I,bur_id ';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_CuentasModTipClc';
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(document).ready(function(){
                $("#tabla-tbl_CuentasModTipClc").filterTable({
                    inputSelector: "#filtroTabla"
                });
                
            });

            $(".agregacta").click(function(){
                comunica("' . $this->pantalla . '",{
                "metodo":"ModificaUsoTipoClc",
                "accion":$(this).attr("accion"),
                "tipo":$(this).attr("tipo"),
                "bur_id":$(this).attr("bur_id"),
                "ur_clave":"' . $this->prm->ur_clave . '"
                });
            });
        ');
    }
    //**********Agrega, actualiza o elimina el Tipo de Uso de Clc de la Cta. Bancaria**************/
    private function ModificaUsoTipoClc()
    {
        $bur_id = $this->prm->bur_id;
        $tipo = '';
        $sql = '';
        if ($this->prm->accion == 'quita') {
            $sql = 'SELECT COUNT(*) AS N FROM vin_BancosUrTipoClc WHERE bur_id= ?';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->prm->bur_id]);
            $data = $this->extraer();
            $this->desconectar();

            if ($data['N'] == 1) {
                $this->mixScript('
                    notify("El uso no se puede eliminar por que la cuenta debe tener como mínimo un uso","error");');
                return;
            }

            if ($this->prm->tipo == 'I' && $data['N'] <= 3) {
                $this->mixScript('
                    notify("El uso no se puede eliminar por que la cuenta debe tener como mínimo un uso","error");
                ');
                return;
            }

            $sql = ' SELECT COUNT(*) AS N FROM scp_Clc WHERE bur_id = ?';
            $params = [$this->prm->bur_id];
            if ($this->prm->tipo == 'I') {
                $sql .= ' AND (tcl_id = ? OR tcl_id = ? OR tcl_id = ?)';
                array_push($params, 'F', 'G', 'H');
            } else {
                $sql .= ' AND tcl_id = ?';
                array_push($params, $this->prm->tipo);
            }

            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $data = $this->extraer();
            $this->desconectar();
            if ($data['N'] > 0) {
                $this->mixScript('
                    notify("La Cuenta Bancaria ya ha sido utilizada en ' . $data['N'] . ' CLC(s)","error");');
                return;
            }
        }
        $params = [];
        if ($this->prm->accion == 'agrega') {

            if ($this->prm->tipo == 'I' && $this->prm->ur_clave == '902') { // se agrega
                $params = [$bur_id, 'F', $this->usr_id, $bur_id, 'G', $this->usr_id, $bur_id, 'H', $this->usr_id, $bur_id, 'I', $this->usr_id];
                $sql = 'INSERT INTO vin_BancosUrTipoClc (bur_id, tcl_id, usr_id) VALUES(?,?,?),(?,?,?),(?,?,?),(?,?,?)';
            } elseif ($this->prm->tipo == 'I') {
                $params = [$bur_id, 'F', $this->usr_id, $bur_id, 'G', $this->usr_id, $bur_id, 'H', $this->usr_id];
                $sql = 'INSERT INTO vin_BancosUrTipoClc (bur_id, tcl_id, usr_id) VALUES(?,?,?),(?,?, ?),(?,?, ?)';
            } else {
                $params = [$bur_id, $this->prm->tipo, $this->usr_id];
                $sql = 'INSERT INTO vin_BancosUrTipoClc (bur_id, tcl_id, usr_id) VALUES(?,?,?)';
            }
        } else {
            if ($this->prm->tipo == 'I') {
                $sql = 'DELETE FROM vin_BancosUrTipoClc  WHERE bur_id = ? AND tcl_id IN("F","H","G")';
                $params = [$bur_id];
            } else {
                $sql = 'DELETE FROM vin_BancosUrTipoClc  WHERE bur_id = ? AND tcl_id = ?';
                $params = [$bur_id, $this->prm->tipo];
            }
        }

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();

        /******************* LOG DE LAS AFECTACIONES********************************/
        $rutalog = 'log/CambiodeUsoClc-' . $this->usr_id . '.txt';
        $file = fopen($rutalog, 'a');
        $tempdata = '';
        $hoy = date('Y-m-d H-i-s');
        foreach ($this->prm as $field => $value) {
            if (!is_object($value)) {
                $tempdata .= '|' . $field . '=' . $value;
            }
        }

        $tempdata .= '|IPADDRESS=' . $this->ip;
        $tempdata .= '|USRID=' . $this->usr_id;
        $tempdata .= '|CONSULTA=' . $sql . '';

        fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);

        $peso = filesize($rutalog);
        $peso = ($peso / 1024) / 1024;

        if ($peso > 8) {
            $rutamodificada = 'log/CambiodeUsoClc-' . $this->usr_id . $hoy . '.txt';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }

        /******************* fin del log************************************/
        $this->mixScript('
            notify("El Tipo de Clc ha sido modificado","success")
            comunica("' . $this->pantalla . '",{metodo:"LlenaTablaModTipoClc",bur_id:' . $bur_id . '})
        ');
    }

    //** FUNCION QUE CONSULTA LA EXISTENCIA DEL PROVEEDOR EN LA BASE DE DATOS DEL AÑO **/
    private function consultaProveedor($rfc)
    {
        if (empty($rfc) || strlen($rfc) > 20) {
            return false;
        }
        $consulta = ' SELECT * FROM cat_Proveedor WHERE rfc_id = ?';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($consulta);
        $conexion->consultar([$rfc]);
        $conexion->desconectar();
        if ($conexion->getNumeroRegistros() == 0) {
            return false;
        } else {
            return true;
        }
    }
    //** FUNCION QUE VALIDA LA EXISTENCIA DEL PROVEEDOR EN LA BASE DE DATOS DEL AÑO **/
    private function validaRfc($rfc)
    {
        $conexion = '';
        $sql = ' SELECT * FROM sinpres.cat_Proveedores WHERE rfc_id = ?';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar([$rfc]);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() == 0) {
            return ' EL Registro NO existe, por favor darlo de ALTA';
        }

        $sql2 = ' SELECT * FROM cat_Proveedor WHERE rfc_id = ?';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql2);
        $conexion->consultar([$rfc]);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() == 0) {
            return ' El Proveedor NO esta HABILITADO, por favor habilitarlo en el Catalogo de Proveedores';
        }
        return true;
    }

    //** FUNCION AGREGA EL NOMBRE DEL PROVEEDOR EN EL BENEFICIARIO **/
    private function AgregaBeneficiario($rfc_id = null)
    {
        if ($rfc_id == null) {
            $rfc = $this->prm->rfc_id;
        } else {
            $rfc = $rfc_id;
        }

        $existe = $this->validaRfc($rfc);

        if (is_string($existe)) {
            if ($rfc_id == null) {
                $this->mixScript('notify("' . $existe . '","error")');
            } else {
                return false;
            }

            return false;
        }

        if ($rfc != '') {
            $consulta = ' 
                SELECT rfc_nombre,rfc_paterno,rfc_materno,soc_nombre,CONCAT(rfc_nombre,CASE TRIM(IFNULL(rfc_paterno,"")) 
                WHEN "" 
                THEN ""
                ELSE 
                concat(" ",rfc_paterno )END,
                CASE TRIM(IFNULL(rfc_materno,"")) WHEN "" 
                THEN ""
                ELSE 
                concat(" ",rfc_materno ) END,
                CASE TRIM(IFNULL(soc_nombre,"")) WHEN "" 
                THEN ""
                ELSE 
                concat(" ",soc_nombre ) END
                )AS benecifiario FROM sinpres.cat_Proveedores
                INNER JOIN sinpres.cat_SociedadProveedor using(soc_id) 
                where rfc_id = ?';
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($consulta);
            $conexion->consultar([$rfc]);
            $conexion->desconectar();
            $extr = $conexion->extraer();

            if ($extr['benecifiario'] != '' && $rfc_id == null) {
                $this->mixScript('$("#beneficiario").val("' . $extr['benecifiario'] . '");');
            } else {
                return $extr['benecifiario'];
            }
        }
    }

    private function buscaCuentaContable()
    {
        $sql = "
            SELECT CONCAT(cta_cuenta, ' - ', cta_nombre) as cuenta
            FROM cg_PlanCuentas 
            WHERE cta_gpo_id = ?
            AND cta_ur_id = ?
            AND cta_ue_id = ?
            AND cgc_id = ?
            AND cta_id = ?
        ";

        $params = [
            substr($this->prm->cta_ur_id, 0, 1),
            substr($this->prm->cta_ur_id, 1, 2),
            $this->prm->cta_ue_id,
            $this->prm->cgc_id,
            $this->prm->cta_id,
        ];

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {
            $this->mixScript('
                $("#cta_cuenta").val("");
                notify("La cuenta contable no existe", "error");
            ');
        } else {
            $cuenta = $this->extraer();
            $this->mixScript('
                $("#cta_cuenta").val("' . $cuenta['cuenta'] . '");
            ');
        }
    }
}

if (
    isset($_SESSION[_S]['prm']['S']['6']['2']['1']) || isset($_SESSION[_S]['prm']['C']['8']['1']['1'])
    || isset($_SESSION[_S]['prm']['T']['6']['4']['1'])
) { // Permisos del usuario que puede ver este controlador.
    new ControladorBancosUr();
} else {
    WebResponse::sinPermisos();
}
