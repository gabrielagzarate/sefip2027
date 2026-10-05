<?php

// Marcos: 22/feb/2017
// El usuario ADMIN_DPE tendrá unas excepciones igual que el usuario JAVIERGC, así que su usr_id lo pondremos
// en una constante para ubicarlo más rápido
define("USUARIO_ADMIN_DPE", 1863);


require_once dirname(__FILE__) . "/../lib/constantes.php";
if (!isset($_SESSION[_S])) {
    session_start();
}
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../ent/usuarios/VigenciaUsuario.php';
require_once dirname(__FILE__) . '/../ent/usuarios/BitUsuario.php';
require_once dirname(__FILE__) . '/../ent/usuarios/enc_JerarquiaSefin.php';
require_once dirname(__FILE__) . '/ServicioUsuario.php';

class ControladorUsuarios extends WebResponse
{

    private $screen = 'srv/ControladorUsuarios.php';
    public $parametros;

    public function __construct()
    {
        parent::__construct();
        $this->prm->gpo_id = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : '';
        $this->prm->ur_id = isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : '';

        switch ($this->metodo) {
            case 'main':
                $this->main();
                break;
            case 'tablaUsuarios':
                $this->tablaUsuarios();
                break;
            case 'llenaTablaUsuarios':
                $this->llenaTablaUsuarios();
                break;
            case 'AddModUsuarios':
                $this->AddModUsuarios();
                break;
            case 'abcUsuarios':
                $this->abcUsuarios();
                break;
            case 'Selecciona':
                $this->Selecciona();
                break;
            case 'Subsecretaria':
                $this->Subsecretaria();
                break;
            case 'Direccion':
                $this->Direccion();
                break;
            case 'Unidad':
                $this->Unidad();
                break;
            case 'Departamento':
                $this->Departamento();
                break;
            case 'validaDatosUsuario':
                $this->validaDatosUsuario();
                break; // MIENRTRAS SE USA COMO METODO VALIDADOR
            case 'resetPasswd':
                $this->resetPasswd();
                break; // metodo para reestablecer contraseña de usuario solo aplica para nivel 2 y aquellos usuarios que tengan usuarios dependientes
            case 'cambiarPasswd':
                $this->cambiarPasswd();
                break; //metodo para cambiar contraseña de usuario que se loguea
            case 'actualizaPasswordCaducado':
                $this->actualizaPasswordCaducado();
                break; //metodo para cambiar contraseña de usuario que se loguea y tiene la contraseña caducada
            case 'actualizaPasswordOlvidado':
                $this->actualizaPasswordOlvidado();
                break; //metodo para cambiar contraseña de usuario que se loguea y tiene la contraseña olvidada
            case 'correoInstitucional':
                $this->correoInstitucional();
                break;
            case 'validaNuevoCorreo':
                $this->validaNuevoCorreo();
                break;
            case 'validaNuevoTelefono':
                $this->validaNuevoTelefono();
                break;
        }

        $this->returnJson();
    }

    private function main()
    {        
        $user_id = $_SESSION[_S]['usr_id'];

        // Marcos: 17/jul/2018
        // Parche vergonzoso :(
        // Hay que ir a buscar los valores del encuadre, ya que no están en la tabla, menos en la Entidad
        // se lo quise poner a la entidad pero no se dejó, porque el campo no existe en la tabla ctrlUsuario.cat_Usuario

        // Marcos: 22/oct/2018
        // Que dice Edilberto que ahora ya existe el campo en la base de datos
        // por lo tanto, hay que tomar el encuadre de ctrl2019.cat_Usuario y NO de usuarioPrevio
        $sql = 'SELECT ssc_id, dir_id,uni_id, dep_id FROM
            cat_Usuario
            LEFT JOIN enc_JerarquiaSefin js USING( ejs_id )
            WHERE usr_id = :usr;';
        $parametros = [':usr' => $user_id];

        $this->setSql($sql);
        $this->conectar();
        $this->consultar($parametros);

        $data = $this->extraer();

        $ssc_id = $data['ssc_id'];
        $dir_id = $data['dir_id'];
        $uni_id = $data['uni_id'];
        $dep_id = $data['dep_id'];

        $user = new Usuario();
        $user->setUsr_id($user_id);
        $user->select();

        $userId = new stdClass();
        $userId->id = 'usr_id';
        $userId->label = 'Numero';
        $userId->enDiv = true;
        $userId->value = $user->getUsr_id();
        $userId->readonly = true;

        $userNombre = new stdClass();
        $userNombre->id = 'usr_nombre';
        $userNombre->label = 'Nombre';
        $userNombre->enDiv = true;
        $userNombre->required = true;
        $userNombre->maxlength = 50;
        $userNombre->value = $user->getNombre();
        $userNombre->readonly = true;

        $userPaterno = new stdClass();
        $userPaterno->id = 'usr_paterno';
        $userPaterno->label = 'Apellido paterno';
        $userPaterno->enDiv = true;
        $userPaterno->required = true;
        $userPaterno->maxlength = 50;
        $userPaterno->value = $user->getPaterno();
        $userPaterno->readonly = true;

        $userMaterno = new stdClass();
        $userMaterno->id = 'usr_materno';
        $userMaterno->label = 'Apellido materno';
        $userMaterno->enDiv = true;
        $userMaterno->maxlength = 50;
        $userMaterno->value = $user->getMaterno();
        $userMaterno->readonly = true;

        $userRfc = new stdClass();
        $userRfc->id = 'usr_rfc';
        $userRfc->class = 'rfc';
        $userRfc->label = 'RFC';
        $userRfc->placeholder = 'RFC';
        $userRfc->enDiv = true;
        $userRfc->maxlength = 13;
        $userRfc->value = $user->getRfc();
        $userRfc->readonly = true;

        $userUsuario = new stdClass();
        $userUsuario->id = 'usr_usuario';
        $userUsuario->label = 'Usuario';
        $userUsuario->enDiv = true;
        $userUsuario->required = true;
        $userUsuario->readonly = true;
        $userUsuario->maxlength = 20;
        $userUsuario->value = $user->getUsuario();

        $userTitulo = new stdClass();
        $userTitulo->id = 'usr_titulo';
        $userTitulo->label = 'Título';
        $userTitulo->enDiv = true;
        $userTitulo->maxlength = 10;
        $userTitulo->value = $user->getTitulo();
        $userTitulo->readonly = true;

        $userSexo = new stdClass();
        $userSexo->id = 'usr_sexo';
        $userSexo->opciones = array("H" => "Hombre", "M" => "Mujer");
        $userSexo->label = 'Sexo';
        $userSexo->enDiv = true;
        $userSexo->required = true;
        $userSexo->selecciona = $user->getSexo();
        $userSexo->lectura = true;

        $userCorreo = new stdClass();
        $userCorreo->id = 'usr_correo';
        $userCorreo->label = 'E-mail';
        $userCorreo->placeholder = 'E-mail';
        $userCorreo->enDiv = true;
        $userCorreo->maxlength = 50;
        $userCorreo->value = $user->getCorreo();
        $userCorreo->readonly = true;

        $userNvjNombre = new stdClass();
        $userNvjNombre->sql = 'SELECT nvj_id,nvj_nombre FROM ctrlUsuario.cat_NivelJerarquico where nvj_id="' . $_SESSION[_S]['nvj_id'] . '"';
        $userNvjNombre->id = 'nvj_nom';
        $userNvjNombre->label = 'Jerarquia';
        $userNvjNombre->enDiv = true;
        $userNvjNombre->soloText = true;

        $userPuesto = new stdClass();
        $userPuesto->id = 'usr_puesto';
        $userPuesto->label = 'Puesto';
        $userPuesto->placeholder = 'Puesto';
        $userPuesto->enDiv = true;
        $userPuesto->maxlength = 310;
        $userPuesto->value = $user->getPuesto();
        $userPuesto->readonly = true;

        $userPrf = new stdClass();
        $userPrf->sql = 'SELECT prf_id, prf_nombre FROM ctrlUsuario.cat_Perfil where prf_id="' . $_SESSION[_S]['prf_id'] . '"';
        $userPrf->id = 'prf_id';
        $userPrf->label = 'Perfil';
        $userPrf->enDiv = true;
        $userPrf->soloText = true;
        $userPrf->selecciona = $user->getPrf_id();

        $movil = new stdClass();
        $movil->id = "usr_telefono";
        $movil->label = "Telefono";
        $movil->placeholder = "Telefono";
        $movil->enDiv = true;
        $movil->maxlength = 50;
        $movil->value = $user->getTelefono();
        $movil->readonly = true;

        if ($_SESSION[_S]['nvj_id'] <= 2)
            $usprf = $this->ponCombo($userPrf);
        else
            $usprf = '';

        $botonPass = new stdClass();
        $botonPass->id = 'cambiarPass';
        $botonPass->class = "btn btn-primary";
        $botonPass->icon = 'fas fa-user-lock me-2';
        $botonPass->value = 'Cambiar Contraseña';

        $upDatos = new stdClass();
        $upDatos->id = 'upDatos';
        $upDatos->class = "upDatos btn btn-primary";
        $upDatos->icon = 'fa fa-address-card me-2';
        $upDatos->value = 'Actualizar Teléfono/Correo';

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'save-userId';
        $btnGuardar->icon = 'fa-solid fa-floppy-disk me-2';
        $btnGuardar->class = "btn btn-outline-success";
        $btnGuardar->text = 'Guardar';
        $btnGuardar->type = 'submit';
        $btnGuardar->form = 'userMaster';
        $btnGuardar->value = 'Guardar';

        $estructura = '';
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                if ($user->getUe_id() != '') {
                    $inputUe = new stdClass();
                    $inputUe->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora where gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' and ue_id=' . $user->getUe_id() . '';
                    $inputUe->id = 'inputUe';
                    $inputUe->label = 'Unidad Ejecutora';
                    $inputUe->enDiv = true;
                    $estructura .= $inputUe = $this->ponCombo($inputUe);
                }
                break;
            case 2:

                break;
            default:
                if ($ssc_id != '') {
                    $inputScc = new stdClass();
                    $inputScc->sql = 'SELECT ssc_id,ssc_nombre from cat_Subsecretaria where ssc_id=' . $ssc_id . '';
                    $inputScc->id = 'inputScc';
                    $inputScc->label = 'Subsecretaría';
                    $inputScc->enDiv = true;
                    $inputScc->soloText = true;
                    $estructura .= $inputScc = $this->ponCombo($inputScc);
                }if ($dir_id != '') {
                    $inputDir = new stdClass();
                    $inputDir->sql = 'SELECT dir_id,dir_nombre from cat_Direccion where dir_id=' . $dir_id . '';
                    $inputDir->id = 'inputDir';
                    $inputDir->label = 'Dirección';
                    $inputDir->enDiv = true;
                    $inputDir->soloText = true;
                    $estructura .= $inputDir = $this->ponCombo($inputDir);
                }if ($uni_id != '') {
                    $inputUni = new stdClass();
                    $inputUni->sql = 'SELECT uni_id,uni_nombre from cat_Unidad where uni_id=' . $uni_id . '';
                    $inputUni->id = 'inputUni';
                    $inputUni->label = 'Unidad';
                    $inputUni->enDiv = true;
                    $inputUni->soloText = true;
                    $estructura .= $inputUni = $this->ponCombo($inputUni);
                }if ($dep_id != '') {
                    $inputDep = new stdClass();
                    $inputDep->sql = 'SELECT dep_id,dep_nombre from cat_Departamento where dep_id=' . $dep_id . '';
                    $inputDep->id = 'inputDep';
                    $inputDep->label = 'Departamento';
                    $inputDep->enDiv = true;
                    $inputDep->soloText = true;
                    $estructura .= $inputDep = $this->ponCombo($inputDep);
                }
                break;
        }

        $form = new stdClass();
        $form->id = 'userMaster';
        $form->controlador = $this->screen;
        $form->metodo = 'abcUsuarios';
        $form->metodoValidador = 'validaDatosUsuario';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'c';
        $form = new form($form);

        $form->mixHtml('
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item li" role="presentation">
                    <button class="nav-link active" id="main1-tab" data-bs-toggle="tab" data-bs-target="#main1" type="button" role="tab" aria-controls="home" aria-selected="true">
                        Datos personales
                    </button>
                </li>               
                <li class="nav-item li2" role="presentation">
                    <button class="nav-link" id="main2-tab" data-bs-toggle="tab" data-bs-target="#main2" type="button" role="tab" aria-controls="home" aria-selected="true">
                        Permiso de usuario
                    </button>
                </li>
            </ul>
        ');

        $form->mixHtml(
            '<div class="tab-content container">
                <div class="tab-pane active" id="main1" role="tabpanel">
                    <div class="row">
                    <legend align="center">Datos generales</legend>
                        <div class="row">
                            <div class="col-md-12">
                                ' . $this->ponInput($userTitulo) . '
                            </div>
                        </div>
                        <div class="col-md-6">
                            ' . ($this->ponInput($userNombre)) . '
                            ' . ($this->ponInput($userMaterno)) . '
                            ' . ($this->ponInput($userUsuario)) . '
                            ' . $this->ponInput($userCorreo) . '
                        </div>
                        <div class="col-md-6">
                            ' . ($this->ponInput($userPaterno)) . '
                            ' . $this->ponCombo($userSexo) . '
                            ' . ($this->ponInput($userRfc)) . '
                            ' . $this->ponInput($movil) . '
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <legend>Estructura</legend>
                            ' . $this->ponCombo($userNvjNombre) . '
                            ' . $estructura . '
                            ' . $this->ponInput($userPuesto) . '
                            ' . $usprf . '
                        </div>
                        <div class="col-md-6">
                            <legend>Contraseña</legend>
                            ' . $this->ponBoton($botonPass) . '
                            ' . $this->ponBoton($upDatos) . '
                        </div>
                    </div>
                </div>    
        ');

        $form->mixHtml('<div id="cPasswd"></div>');
        //$this->agregaTools($this->ponBoton($btnCancelar));
        
        $this->mixScript('
            $(".li2").click(function(){
                $("#save-userId").hide();
                $("#cancel-userId").hide();
            });

            $(".li").click(function(){
                $("#save-userId").show();
                $("#cancel-userId").show();
            });

            $(".li3").click(function(){
                $("#save-userId").hide();
                $("#cancel-userId").hide();
            });
        ');
        $this->conectar();
        
        $h = '
                <div class="tab-pane" id="main2" role="tabpanel">
                    <div class="row">
                        <legend align="center">Permisos de usuario</legend>
                        <ul id="browser" class="filetree">
        ';
        $sis = $men = $opc = $tar = $tchk = '';
        $sql = 'SELECT DISTINCT sis_id, men_id, opc_id, sis_nombre, men_titulo, opc_titulo,tar_id,if(sis_id="I","HABILITAR",tar_nombre) tar_nombre, men_orden, opc_orden
            FROM cat_Sistema
            JOIN cat_Menu USING(sis_id)
            JOIN cat_Opcion USING(sis_id,men_id)
            JOIN cat_PermisosUsuarios pu USING(sis_id,men_id,opc_id)
            JOIN cat_Tareas using(tar_id)
            WHERE  pu.usr_id= :usr_id
            ORDER BY sis_id,men_id,men_orden,opc_orden, tar_id';
        
        $parametros = [':usr_id' => $_SESSION[_S]['usr_id']];
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($parametros);
        while ($w = $this->extraer()) {

            if ($sis != $w['sis_id']) {
                if ($sis != '') {
                    if ($men != '') {
                        if ($opc != '') {
                            $h .= '</ul></li>';
                        }
                        $opc = '';
                        $men = '';
                        $h .= '</ul></li>';
                    }
                    $h .= '</ul></li>';
                }

                $h .= '<li><span class="folder">' . $w['sis_nombre'] . '</span><ul>';
                $sis = $w['sis_id'];
            }

            if ($men != $w['men_id']) {
                if ($men != '') {
                    if ($opc != '') {
                        $h .= '</ul></li>';
                    }
                    $opc = '';
                    $h .= '</ul></li>';
                }
                $h .= '<li><span class="folder">' . $w['men_titulo'] . '</span><ul>';
                $men = $w['men_id'];
            }

            if ($opc != $w['opc_id']) {
                if ($opc != '') {
                    $h .= '</ul></li>';
                }
                $h .= '<li><span class="folder">' . $w['opc_titulo'] . '</span><ul>';
                $opc = $w['opc_id'];
            }

            if (isset($w['nanais']) && $w['nanais'] != null) {
                $tchk = 'checked="checked"';
            } else {
                $tchk = '';
            }


            $h .= '<li><span class="file"><input ' . $tchk . ' type="checkbox" readonly="readonly" checked onclick="javascript: return false;" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] . '" />' . $w['tar_nombre'] . '</span></li>';
        }
        $h .= ' </ul></li><!--Opcion-->
                </ul></li> <!--Menu-->
                </ul><!--ul del sistema--></li>
                </ul><!--ul clase filetree-->
                </div>
            </div>';
        if ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T' || $_SESSION[_S]['nvj_id'] >= 4) {
            $h .= '
                <div class="tab-pane" id="main3">
                    <div>
                        <legend align="left">Usuarios Dependientes</legend>
                        <div id="users"> </div>
                    </div>
                </div>
            ';
        }

        $h .= '</div>';

        $this->mixHtml($form->ponForm() . $h);

        $act = '';
        if (isset($this->prm->act) && $this->prm->act != '') {
            $act = '$( "#main1" ).removeClass("active");';
            $act .= '$( "#main3" ).addClass("active");';
            $act .= '$( ".li" ).removeClass("active");';
            $act .= '$( ".li3" ).addClass("active");';
        }

        $this->mixScript('
            ' . $act . '
            $(".li3").click(function(){
                comunica("' . $this->screen . '",{
                metodo:"tablaUsuarios"
                });
            });

           

            $("#browser").treeview({
                animated:"normal",
                persist: "cookie"
            });

            $("#usr_rfc").bind({
                keypress: function(event){
                    tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                    patron = /[A-Za-z0-9&]/;
                    caracter = String.fromCharCode(tecla);
                    if( tecla == 8 || tecla == 13 || tecla == 9 ){
                        return true;
                    }
                    return patron.test(caracter);
                }
            });
            $("#usr_nombre").keypress(function(event){
                tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                    patron = /[A-Za-zñÑ \u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00f1\u00d1\u00FC\u00DC]+$/;
                    caracter = String.fromCharCode(tecla);
                    if( tecla == 8 || tecla == 13 || tecla == 9 ){
                        return true;
                    }
                    return patron.test(caracter);
            });
            $("#usr_paterno,#usr_materno").keypress(function(event){
                tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                    patron = /[A-Za-zñÑ \u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00f1\u00d1\u00FC\u00DC]+$/;
                    caracter = String.fromCharCode(tecla);
                    if( tecla == 8 || tecla == 13 || tecla == 9 ){
                        return true;
                    }
                    return patron.test(caracter);
            });

            $("#cambiarPass").click(function(){
                comunica("' . $this->screen . '",{
                    metodo:"cambiarPasswd",
                    usr_id:' . $user->getUsr_id() . ',
                    bandera: 999
                });
            });

            $("#upDatos").click(function(){
                comunica("' . $this->screen . '",{
                    metodo:"cambiarPasswd",
                    usr_id:' . $user->getUsr_id() . ',
                    bandera: 1
                });
            });
        ');
    }

    private function tablaUsuarios()
    {

        $btnNerwUser = new stdClass();
        $btnNerwUser->id = 'new-user';
        $btnNerwUser->class = 'btn btn-test';
        $btnNerwUser->icon = 'fa-solid fa-plus me-2';
        $btnNerwUser->color = 'azul';
        $btnNerwUser->text = 'Nuevo';
        $btnNerwUser->type = 'button';
        $btnNerwUser->value = 'Nuevo Usuario';

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->class = 'btn btn-test regresar';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->icon = 'fa-fa-solid fa-hand-point-left me-2';

        $this->setContenedor('users');
        $tabla = new stdClass();
        $tabla->id = 'tbl-Usr';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->screen;
        $tabla->comunica->metodo = 'llenaTablaUsuarios';

        $tr = array();
        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                if (isset($_SESSION[_S]['prf_id']) && ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T')) {
                    $th = new stdClass();
                    $th->texto = 'Editar';
                    $th->width = '60px';
                    array_push($tr, $th);
                }
                break;
            case 4: case 5: case 6: case 7: case 8:
                $th = new stdClass();
                $th->texto = 'Editar';
                $th->width = '60px';
                array_push($tr, $th);
                break;
        }

        $th = new stdClass();
        $th->texto = 'Usuario';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Puesto';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nivel';
        array_push($tr, $th);
        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                $texto = '# UR/Unidad Ejecutora';
                break;
            default:
                $texto = '# UR';
                break;
        }
        $th = new stdClass();
        $th->texto = $texto;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Activar/Desactivar';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Status';
        array_push($tr, $th);
        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                if (isset($_SESSION[_S]['prf_id']) && ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T')) {
                    $th = new stdClass();
                    $th->texto = 'Restablecer contraseña';
                    array_push($tr, $th);
                }
                break;
            case 4: case 5: case 6: case 7: case 8:
                $th = new stdClass();
                $th->texto = 'Restablecer contraseña';
                array_push($tr, $th);
                break;
        }

        $trbody = array();

        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                if (isset($_SESSION[_S]['prf_id']) && ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T')) {
                    $td = new stdClass();
                    $td->tipo = "boton";
                    $td->icono = "check";
                    $td->attr = new stdClass();
                    $td->attr->usr_id = "usr_id";
                    $td->attr->gpo_id = "gpo_id";
                    $td->attr->ur_id = "ur_id";
                    $td->attr->ue_id = "ue_id";
                    $td->class = "seleccionaUser";
                    array_push($trbody, $td);
                }
                break;
            case 4: case 5: case 6: case 7: case 8:
                $td = new stdClass();
                $td->tipo = "boton";
                $td->icono = "check";
                $td->attr = new stdClass();
                $td->attr->usr_id = "usr_id";
                $td->attr->gpo_id = "gpo_id";
                $td->attr->ur_id = "ur_id";
                $td->attr->ue_id = "ue_id";
                $td->class = "seleccionaUser";
                array_push($trbody, $td);
                break;
        }

        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_nombreU';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_puesto';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'nivel';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'urue_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->tipo = "boton";
        $td->icono = "check";
        $td->attr = new stdClass();
        $td->attr->usr_id = "usr_id";
        $td->attr->sts_id = "sts_id";
        $td->class = "desactivaUser";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'estado';
        array_push($trbody, $td);
        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                if (isset($_SESSION[_S]['prf_id']) && ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T')) {
                    $td = new stdClass();
                    $td->tipo = "boton";
                    $td->icono = "check";
                    $td->attr = new stdClass();
                    $td->attr->usr_id = "usr_id";
                    $td->class = "resetearPass";
                    array_push($trbody, $td);
                }
                break;
            case 4: case 5: case 6: case 7: case 8:
                $td = new stdClass();
                $td->tipo = "boton";
                $td->icono = "check";
                $td->attr = new stdClass();
                $td->attr->usr_id = "usr_id";
                // Marcos: 22/feb/2017
                // Según el ticket #952717, el usuario tendrá el mismo comportamiento que el de Javier Guzman
                // por lo que... checado con Zuli, agregaremos aquí también ese permiso para el nuevo usuario
                if ($_SESSION[_S]['usr_id'] == 8 || $_SESSION[_S]['usr_id'] == USUARIO_ADMIN_DPE) {
                    $td->attr->gpo_id = "gpo_id";
                    $td->attr->ur_id = "ur_id";
                }
                $td->class = "resetearPass";
                array_push($trbody, $td);
                break;
        }


        $botonNuevo = '';
        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                if (isset($_SESSION[_S]['prf_id']) && ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T')) {
                    $botonNuevo = $this->ponBoton($btnNerwUser);
                    $this->agregaTools($this->ponBoton($btnRegresarIndex) . ' ' . $botonNuevo);
                }
                break;
            case 4: case 5: case 6: case 7: case 8:
                $botonNuevo = $this->ponBoton($btnNerwUser);
                $this->agregaTools($this->ponBoton($btnRegresarIndex) . ' ' . $botonNuevo);
                break;
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaUsuarios();
        $this->mixHtml('<div id="rPasswd"/>');

        $act = '';
        if (isset($this->prm->act) && $this->prm->act != '') {
            $act = '$( "#main1" ).removeClass("active");';
            $act .= '$( "#main3" ).addClass("active");';
            $act .= '$( ".li" ).removeClass("active");';
            $act .= '$( ".li3" ).addClass("active");';
        }
        print_r($act);
        $this->mixScript('' . $this->wrLlamaLlenaTabla('tbl-Usr') . '
            ' . $act . '
            $(".li").click(function(){
                comunica("' . $this->screen . '",{
                    metodo:"main"
                });
            });

            $(".li2").click(function(){
                $("#new-user").remove();
                $("#btnRegresarIndex").remove();
                    comunica("' . $this->screen . '",{
                        metodo:"main2"
                    });
            });

            $("#new-user").click( function (){
                comunica("' . $this->screen . '",{
                    metodo:"AddModUsuarios"
                });
                $("#tituloPantalla").html("");
                $("#tituloPantalla").text("Alta de Usuarios");
                return false;
            });

            $("#btnRegresarIndex").click(function(){
                comunica("' . $this->screen . '",{
                    metodo:"main"
                });
            });

            $(".panel-heading.header").remove();
            $(".panel-footer").remove();

        ');
    }

    private function llenaTablaUsuarios()
    {        
        $where = '';
        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                $where = 'gpo_id=' . $this->prm->gpo_id . ' and ur_id=' . $this->prm->ur_id . ' and ';
                switch ($_SESSION[_S]['prf_id']) {
                    case 'A'://Admin solo dara de alta y podra visualizar a usuarios de nivel 1 y 2
                        $where .= ' prf_id="C" and ';
                        break;
                    case 'T'://Usuarios titulares podrán visualizar y daran de alta usuarios admin.
                        $where .= ' prf_id="A" and ';
                        break;
                }
                break;
            case 4: case 5: case 6: case 7: case 8:
                // Marcos: 22/feb/2017
                // Según el ticket #952717, el usuario tendrá el mismo comportamiento que el de Javier Guzman
                // por lo que... checado con Zuli, agregaremos aquí también ese permiso para el nuevo usuario
                if ($_SESSION[_S]['usr_id'] != 8 && $_SESSION[_S]['usr_id'] != USUARIO_ADMIN_DPE)
                    $where = 'gpo_id=' . $this->prm->gpo_id . ' and ur_id=' . $this->prm->ur_id . ' and ';
                break;
        }

        // Marcos: 22/feb/2017
        // Como en el ticket #952717 piden un usuario ADMIN_DPE como el de Javier, pero los usuarios dependientes de Javier
        // NO tienen ue_id... y éstos si (que son los usuarios ADMIN_SI), la columna URUE me muestra sólo la UE, y pierdo la
        // UR de la que estoy hablando, así que adicionaremos gpo_id + ur_id a la existente ue_id cuando se trate del
        // usuario ADMIN_DPE
        $miCampoUrUe = "if(ue_id!='',ue_id,concat(gpo_id,ur_id)) as urue_id ";
        IF ($_SESSION[_S]['usr_id'] == USUARIO_ADMIN_DPE) {
            $miCampoUrUe = "if(ue_id!='',concat(gpo_id,ur_id,'-',ue_id),concat(gpo_id,ur_id)) as urue_id ";
        }
        $sql = "SELECT u.usr_id,gpo_id,ur_id,ue_id,sts_id,usr_usuario, concat(usr_nombre,' ',usr_paterno,' ',usr_materno) as usr_nombreU,
        usr_puesto,nvj_nombre as nivel, if(sts_id=1,'habilitado','deshabilitado') as estado,
        $miCampoUrUe
        FROM cat_Usuario u
        INNER JOIN ctrlUsuario.cat_NivelJerarquico using(nvj_id)
        where $where usr_usu = '" . $_SESSION[_S]['usr_id'] . "'";
        $this->setSql($sql);
        $nav = ( isset($this->prm->nav) ) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 5;
        $nav->idTabla = 'tbl-Usr';
        $this->wrLlenaTabla($nav);

        // Marcos: 22/feb/2017
        // Según el ticket #952717, el usuario tendrá el mismo comportamiento que el de Javier Guzman
        // por lo que... checado con Zuli, agregaremos aquí también ese permiso para el nuevo usuario
        $ur = ( $_SESSION[_S]['usr_id'] == 8 || $_SESSION[_S]['usr_id'] == USUARIO_ADMIN_DPE ) ? ',g:$(this).attr("gpo_id"),u:$(this).attr("ur_id")' : '';

        if ($_SESSION[_S]['usr_id'] != 8 && $_SESSION[_S]['usr_id'] != USUARIO_ADMIN_DPE) {
            $this->mixScript('
                $(".seleccionaUser").click( function (){
                    comunica("' . $this->screen . '",{
                        metodo:"AddModUsuarios",
                        usr_id: $(this).attr("usr_id"),
                        gpo_id: $(this).attr("gpo_id"),
                        ur_id: $(this).attr("ur_id"),
                        ue_id: $(this).attr("ue_id")
                    });
                    $("#tituloPantalla").html("");
                    $("#tituloPantalla").text("Modificacion de Usuarios");
                    return false;
                });
            ');
        } else {
            $this->mixScript('
                $(".seleccionaUser").click( function (){
                    notify("Su usuario no puede realizar ésta operación, so pena de cambiar el # de UR del usuario", "error");
                });
            ');
        }

        $this->mixScript('
            $(".desactivaUser").click( function (){
                var concepto = ($(this).attr("sts_id")==1) ? "deshabilitar a usuario":"habilitar a usuario";
                if(confirm("Desea "+concepto+" ?")){
                    comunica("' . $this->screen . '",{
                        metodo:"abcUsuarios",
                        usr_id: $(this).attr("usr_id"),
                        abc:"c",
                        desactivar:"true"
                    });
                    $("#tituloPantalla").html("");
                    $("#tituloPantalla").text("Modificacion de Usuarios");
                }
                return false;
            });

            $(".resetearPass").click(function(){
                comunica("' . $this->screen . '",{
                    metodo:"resetPasswd",
                    usr_id: $(this).attr("usr_id")
                    ' . $ur . '
                });
                return false;
            });
        ');
    }

    private function AddModUsuarios()
    {

        $abc = "a";

        $sql = '';
        switch ($_SESSION[_S]['nvj_id']) {
            case 2:
                switch ($_SESSION[_S]['prf_id']) {
                    case 'A':
                        $nj2 = ' nvj_id <= ' . $_SESSION[_S]['nvj_id'] . ' '; //solo usuarios de nivel 1 y 2
                        break;
                    case 'T':
                        $nj2 = ' nvj_id = 2 '; //solo usuarios de nivel 1 y 2
                        break;
                }
                $nj = ' and nvj_id not in (0,3,4,5,6,7,8,9) '; //menos usuarios de nivel 3 en adelante.
                break;
            case 4: case 5: case 6: case 7: case 8:
                $nj2 = ' nvj_id < ' . $_SESSION[_S]['nvj_id'] . ' '; //usuarios de nivel menor al que tenga el usuario que esta dando de alta.
                $nj = ' and nvj_id not in (0,1,2)'; //menos usuarios ur y ue
                break;
        }

        $nvjUser = new stdClass();
        $nvjUser->sql = 'SELECT nvj_id,nvj_nombre from ctrlUsuario.cat_NivelJerarquico where ' . $nj2 . ' ' . $nj . ' ';
        $nvjUser->id = 'nvj_id';
        $nvjUser->name = 'nvj_id';
        $nvjUser->label = 'Nivel de Usuario';
        $nvjUser->required = true;
        $nvjUser->enDiv = true;
        $nvjUser->mensaje = 'Selecciona nivel';
        $nvjUser->width = "200px";
        $nvjUser->soloText = true;

        if (!isset($this->prm->usr_id))
            $usarNvjUser = $this->ponCombo($nvjUser);
        else
            $usarNvjUser = '';

        $user_id = (isset($this->prm->usr_id)) ? $this->prm->usr_id : '';
        $estructura = '';
        if (($user_id != '')) {
            $user = new Usuario();
            $sefin = new EncJerarquiaSefin();
            $user->setUsr_id($user_id);
            $user->select();

            switch ($user->getNvj_id()) {
                case 1:
                    if ($user->getUe_id() != '') {
                        $inputUe = new stdClass();
                        $inputUe->sql = 'SELECT ue_id,ue_nombre from cat_UnidadEjecutora where gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' and ue_id=' . $user->getUe_id() . '';
                        $inputUe->sql .= ( isset($_SESSION[_S]['prm']['X'][2][1]) ) ? ' AND ue_ejerceInversionPlaneacion = 1 ' : '';
                        $inputUe->id = 'inputUe';
                        $inputUe->label = 'Unidad Ejecutora';
                        $inputUe->enDiv = true;                        
                        $estructura .= $inputUe = $this->ponCombo($inputUe);
                    }
                    break;
                case 2:

                    break;
                default:
                    if ($sefin->getSsc_id() != '') {
                        $inputScc = new stdClass();
                        $inputScc->sql = 'SELECT ssc_id,ssc_nombre from cat_Subsecretaria where ssc_id=' . $sefin->getSsc_id() . '';
                        $inputScc->id = 'inputScc';
                        $inputScc->label = 'Subsecretaría';
                        $inputScc->enDiv = true;
                        $inputScc->soloText = true;
                        $estructura .= $inputScc = $this->ponCombo($inputScc);
                    }if ($sefin->getDir_id() != '') {
                        $inputDir = new stdClass();
                        $inputDir->sql = 'SELECT dir_id,dir_nombre from cat_Direccion where dir_id=' . $sefin->getDir_id() . '';
                        $inputDir->id = 'inputDir';
                        $inputDir->label = 'Dirección';
                        $inputDir->enDiv = true;
                        $inputDir->soloText = true;
                        $estructura .= $inputDir = $this->ponCombo($inputDir);
                    }if ($sefin->getUni_id() != '') {
                        $inputUni = new stdClass();
                        $inputUni->sql = 'SELECT uni_id,uni_nombre from cat_Unidad where uni_id=' . $sefin->getUni_id() . '';
                        $inputUni->id = 'inputUni';
                        $inputUni->label = 'Unidad';
                        $inputUni->enDiv = true;
                        $inputUni->soloText = true;
                        $estructura .= $inputUni = $this->ponCombo($inputUni);
                    }if ($sefin->getDep_id() != '') {
                        $inputDep = new stdClass();
                        $inputDep->sql = 'SELECT dep_id,dep_nombre from cat_Departamento where dep_id=' . $sefin->getDep_id() . '';
                        $inputDep->id = 'inputDep';
                        $inputDep->label = 'Departamento';
                        $inputDep->enDiv = true;
                        $inputDep->soloText = true;
                        $estructura .= $inputDep = $this->ponCombo($inputDep);
                    }
                    break;
            }
        } else {
            $user = new Usuario();
        }
        if (isset($this->prm->usr_id) && $this->prm->usr_id != '') {

            $abc = "c";

            $userId = new stdClass();
            $userId->id = 'usr_id';
            $userId->label = 'Numero';
            $userId->enDiv = true;
            $userId->value = $user->getUsr_id();
            $userId->readonly = true;
            $usarId = $this->ponInput($userId);
        } else
            $usarId = '';

        $userNombre = new stdClass();
        $userNombre->id = 'usr_nombre';
        $userNombre->label = 'Nombre';
        $userNombre->placeholder = 'Nombre';
        $userNombre->enDiv = true;
        $userNombre->required = true;
        $userNombre->maxlength = 50;
        $userNombre->value = $user->getNombre();

        $userPaterno = new stdClass();
        $userPaterno->id = 'usr_paterno';
        $userPaterno->label = 'Apellido paterno';
        $userPaterno->enDiv = true;
        $userPaterno->placeholder = 'Apellido paterno';
        $userPaterno->required = true;
        $userPaterno->maxlength = 50;
        $userPaterno->value = $user->getPaterno();

        $userMaterno = new stdClass();
        $userMaterno->id = 'usr_materno';
        $userMaterno->label = 'Apellido materno';
        $userMaterno->placeholder = 'Apellido materno';
        $userMaterno->enDiv = true;
        $userMaterno->maxlength = 50;
        $userMaterno->value = $user->getMaterno();

        $userRfc = new stdClass();
        $userRfc->id = 'usr_rfc';
        $userRfc->class = 'rfc2';
        $userRfc->label = 'RFC';
        $userRfc->placeholder = 'RFC';
        $userRfc->enDiv = true;
        $userRfc->maxlength = 13;        
        $userRfc->value = $user->getRfc();

        $userUsuario = new stdClass();
        $userUsuario->id = 'usr_usuario';
        $userUsuario->label = 'Usuario';
        $userUsuario->placeholder = 'Nombre del usuario';
        $userUsuario->required = true;
        if (isset($this->prm->usr_id))
            $userUsuario->readonly = true;
        $userUsuario->enDiv = true;
        $userUsuario->maxlength = 20;
        $userUsuario->value = $user->getUsuario();

        $userTitulo = new stdClass();
        $userTitulo->id = 'usr_titulo';
        $userTitulo->label = 'Titulo';
        $userTitulo->placeholder = 'Titulo';
        $userTitulo->enDiv = true;
        $userTitulo->maxlength = 10;
        $userTitulo->value = $user->getTitulo();

        $userSexo = new stdClass();
        $userSexo->id = 'usr_sexo';
        $userSexo->name = 'usr_sexo';
        $userSexo->opciones = array("H" => "Hombre", "M" => "Mujer");
        $userSexo->enDiv = true;
        $userSexo->label = 'Sexo';
        $userSexo->required = true;
        $userSexo->selecciona = $user->getSexo();
        $userSexo->disabled = true;

        $userCorreo = new stdClass();
        $userCorreo->id = 'usr_correo';
        $userCorreo->label = 'E-mail';
        $userCorreo->placeholder = 'E-mail';
        $userCorreo->type = 'email';
        $userCorreo->enDiv = true;
        $userCorreo->maxlength = 50;
        $userCorreo->value = $user->getCorreo();

        $userNvjNombre = new stdClass();
        $userNvjNombre->id = 'usr_nvj';
        $userNvjNombre->sql = 'SELECT nvj_id,nvj_nombre from ctrlUsuario.cat_NivelJerarquico where nvj_id = "' . $user->getNvj_id() . '"';
        $userNvjNombre->label = 'Jerarquia';
        $userNvjNombre->enDiv = true;
        $userNvjNombre->required = true;
        $userNvjNombre->soloText = true;
        $usarNvjNombre = '';
        if (isset($this->prm->usr_id)) {
            $usarNvjNombre = $this->ponCombo($userNvjNombre);
        }

        $userPuesto = new stdClass();
        $userPuesto->id = 'usr_puesto';
        $userPuesto->label = 'Puesto';
        $userPuesto->enDiv = true;
        $userPuesto->placeholder = 'Puesto';
        $userPuesto->maxlength = 310;
        $userPuesto->value = $user->getPuesto();

        $userPrf = new stdClass();
        $userPrf->enDiv = true;
        if (isset($this->prm->usr_id)) {
            $sql = 'SELECT prf_id, prf_nombre from cat_Perfil where prf_id="' . $user->getPrf_id() . '"';
        } else {
            switch ($_SESSION[_S]['prf_id']) {
                case 'A':
                    $sql = 'SELECT prf_id, prf_nombre from cat_Perfil where prf_id = "C" '; //Si es perfil Admin podrá dar de alta usuarios con perfil capturista/analista
                    break;
                case 'T':
                    $sql = 'SELECT prf_id, prf_nombre from cat_Perfil where prf_id = "A" '; // si es perfil T podrá dar de alta usuarios con perfil A= ADMIN
                    break;
            }
        }
        $userPrf->sql = $sql;
        $userPrf->id = 'prf_id';
        $userPrf->label = 'Perfil';
        $userPrf->required = true;
        $userPrf->soloText = true;

        $userPrf->value = $user->getPrf_id();

        if ($_SESSION[_S]['nvj_id'] <= 2)
            $usarprf = $this->ponCombo($userPrf);
        else
            $usarprf = '';

        $userPass = new stdClass();
        $userPass->id = 'usr_clave';
        $userPass->label = 'Contraseña';
        $userPass->placeholder = 'maximo 10 caracteres';
        $userPass->enDiv = true;
        $userPass->required = true;
        $userPass->maxlength = 10;
        $userPass->type = 'password';

        $confirmar = new stdClass();
        $confirmar->id = 'confirmar';
        $confirmar->class = 'confirmar';
        $confirmar->label = 'Confirmar contraseña';
        $confirmar->placeholder = 'maximo 10 caracteres';
        $confirmar->required = true;
        $confirmar->maxlength = 10;
        $confirmar->enDiv = true;
        $confirmar->type = 'password';

        if (!isset($this->prm->usr_id)) {
            $btnGuardarU = new stdClass();
            $btnGuardarU->id = 'save-user';
            $btnGuardarU->value = 'Continuar';
            $btnGuardarU->icon = 'fa-solid fa-hand-point-right me-2';
            $btnGuardarU->text = 'Guardar';
            $btnGuardarU->form = 'users';
            $btnGuardarU->type = 'submit';
            $btnGuardarU->class = "btn-test btn-success";
        } else {
            $btnGuardarU = '';
            $btnGuardarU = new stdClass();
            $btnGuardarU->id = 'save-user';
            $btnGuardarU->value = 'Guardar';
            $btnGuardarU->icon = 'fa-floppy-disk me-2';
            $btnGuardarU->text = 'Guardar';
            $btnGuardarU->form = 'users';
            $btnGuardarU->type = 'submit';
            $btnGuardarU->class = "btn-test btn-success";
        }

        $btnCancelarU = new stdClass();
        $btnCancelarU->value = 'Regresar';
        $btnCancelarU->id = 'cancel-user';
        $btnCancelarU->icon = 'fa fa-fa-solid fa-hand-point-left me-2';
        $btnCancelarU->class = 'btn btn-test';

        if (!isset($this->prm->usr_id)) {
            $btnReset = new stdClass();
            $btnReset->value = 'Limpiar';
            $btnReset->class = 'btn btn-test';
            $btnReset->type = 'reset';
            $btnReset->id = 'reset-user';
            $btnReset->form = 'users';
            $usarbtnReset = $this->ponBoton($btnReset);
        } else
            $usarbtnReset = '';

        $form = new stdClass();
        $form->id = 'users';
        $form->controlador = $this->screen;
        $form->metodo = 'abcUsuarios';
        $form->metodoValidador = 'validaDatosUsuario';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->usr_permisos = '"{objetoMeta()}"';
        $form = new form($form);



        $form->mixHtml('
            <ul class="nav nav-tabs">
                <li class="active lii"><a href="#addmod1" data-toggle="tab">Datos personales</a></li>
                <li class="lii2"><a href="#addmod2" data-toggle="tab">Permisos</a></li>
            </ul>
            <div class="tab-content container">
                <div class="tab-pane active" id="addmod1">
                    <div class="row">
                        <legend>Datos generales</legend>
                        <div class="row">
                            <fieldset class="col-md-12">
                                ' . $usarId . '
                            </fieldset>
                        </div>
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($userNombre) . '
                            ' . $this->ponInput($userMaterno) . '
                            ' . $this->ponCombo($userSexo) . '
                            ' . $this->ponInput($userUsuario) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($userPaterno) . '
                            ' . $this->ponInput($userRfc) . '
                            ' . $this->ponInput($userCorreo) . '
                            ' . $this->ponInput($userTitulo) . '
                            <input type="hidden" id="usr_id" value=""/>
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-12">
                            <legend align="left" id="estructura">Estructura</legend>
                            ' . $usarNvjNombre . '
                            ' . $usarNvjUser . '
                                <div id="Uee"/>
                                <div id="Jerarquia">
                                    <div id="Subsecretaria"/>
                                    <div id="Direccion"/>
                                    <div id="Unidad"/>
                                    <div id="Depa"/>
                                </div>
                                ' . $estructura . '
                            ' . $this->ponInput($userPuesto) . '
                            ' . $usarprf . '
                        </fieldset>
                    </div>
        ');

        if (!isset($this->prm->usr_id)) {
            $form->mixHtml('
                        <div class="row">
                            <legend>Contraseña</legend>
                            <fieldset class="col-md-6">
                                ' . $this->ponInput($userPass) . '
                            </fieldset>
                            <fieldset class="col-md-6">
                                ' . $this->ponInput($confirmar) . '
                            </fieldset>
                        </div>
            ');
        }
        $form->mixHtml('
                </div>
            <div class="tab-pane" id="addmod2">
                <fieldset>
                    <legend align="left">Permisos de usuario</legend>
        ');
        $this->conectar();        
        $usuario = (isset($this->prm->usr_id) && $this->prm->usr_id != '') ? ',usr_id:' . $this->prm->usr_id . '' : '';
        if (isset($this->prm->usr_id) && $this->prm->usr_id != '') {
            $this->setSql('
                    SELECT sis_id,sis_nombre, men_id, men_titulo, opc_id, opc_titulo, tar_id, if(sis_id="I","HABILITAR",tar_nombre) tar_nombre, nanais
                    FROM cat_Sistema
                    JOIN cat_Opcion opc USING(sis_id)
                    JOIN cat_Menu USING(sis_id,men_id)
                    JOIN cat_Permisos USING(sis_id,men_id,opc_id)
                    JOIN cat_Tareas USING(tar_id)
                    JOIN (
                        SELECT sis_id,men_id,opc_id,tar_id, usr_id, nanais
                        FROM cat_PermisosUsuarios pu
                        LEFT JOIN (
                            SELECT sis_id,men_id,opc_id,tar_id, usr_id nanais
                            FROM cat_PermisosUsuarios perm
                            WHERE perm.usr_id="' . $this->prm->usr_id . '"
                            ) X USING(sis_id,men_id,opc_id,tar_id)
                        WHERE pu.usr_id="' . $_SESSION[_S]['usr_id'] . '"
                    ) x USING(sis_id,men_id,opc_id,tar_id)
                    where opc.sts_id=1
                    ORDER BY sis_id,men_id,opc_id,tar_id');            
        } else {
            $this->setSql('
                SELECT sis_id,sis_nombre, men_id, men_titulo, opc_id, opc_titulo, tar_id, if(sis_id="I","HABILITAR",tar_nombre) tar_nombre, "" usr_id
                FROM cat_Sistema
                JOIN cat_Opcion opc USING(sis_id)
                JOIN cat_Menu USING(sis_id,men_id)
                JOIN cat_Permisos USING(sis_id,men_id,opc_id)
                JOIN cat_Tareas USING(tar_id)
                JOIN cat_PermisosUsuarios pu USING(sis_id,men_id,opc_id,tar_id)
                    WHERE pu.usr_id=' . $_SESSION[_S]['usr_id'] . ' AND opc.sts_id=1
                ORDER BY sis_id,men_id,opc_id,tar_id'
            );
        }        
        $this->consultar();

        $sis = $men = $opc = $tar = $tchk = '';

        $h = '<ul id="browser" class="filetree">';
        while ($w = $this->extraer()) {

            if ($sis != $w['sis_id']) {
                if ($sis != '') {
                    if ($men != '') {
                        if ($opc != '') {
                            $h .= '</ul></li>';
                        }
                        $opc = '';
                        $men = '';
                        $h .= '</ul></li>';
                    }
                    $h .= '</ul></li>';
                }

                $h .= '<li><span class="folder">' . $w['sis_nombre'] . '</span><ul>';
                $sis = $w['sis_id'];
            }

            if ($men != $w['men_id']) {
                if ($men != '') {
                    if ($opc != '') {
                        $h .= '</ul></li>';
                    }
                    $opc = '';
                    $h .= '</ul></li>';
                }
                $h .= '<li><span class="folder">' . $w['men_titulo'] . '</span><ul>';
                $men = $w['men_id'];
            }

            if ($opc != $w['opc_id']) {
                if ($opc != '') {
                    $h .= '</ul></li>';
                }
                $h .= '<li><span class="folder">' . $w['opc_titulo'] . '</span><ul>';
                $opc = $w['opc_id'];
            }

            if (isset($w['nanais']) && $w['nanais'] != null) {
                $tchk = 'checked="checked"';
            } else {
                $tchk = '';
            }


            $h .= '<li><span class="file"><input ' . $tchk . ' type="checkbox" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] . '" />' . $w['tar_nombre'] . '</span></li>';


            $this->mixScript('
                var prm="";
                var usr_permisos = new Array();
                    $("#tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '").click(function(){
                        if ($("#tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . ':checked").length) {
                            usr_permisos.push({"sis_id":$(this).attr("sis_id"),"men_id":$(this).attr("men_id"),"opc_id":$(this).attr("opc_id"),"tar_id":$(this).attr("tar_id")});
                        } else {
                            if (confirm("Desea quitar este permiso al usuario ? ")) {
                                comunica("' . $this->screen . '",{
                                    metodo:"abcUsuarios",
                                    abc:"b",
                                    sis_id:$(this).attr("sis_id"),
                                    men_id:$(this).attr("men_id"),
                                    opc_id:$(this).attr("opc_id"),
                                    tar_id:$(this).attr("tar_id")
                                    ' . $usuario . '
                                });
                            }
                        }
                    });
                function objetoMeta(){
                    var stringObj = "[";
                    for(var index in usr_permisos) {
                        if(index>0)
                            stringObj += ",";
                        stringObj += "{";
                        var obj = usr_permisos[index];
                        c = 0;
                        for(var i in obj) {
                            if ( c > 0 )
                                stringObj += ",";
                            stringObj += \'"\' + i + \'"\' + \':"\' + obj[i] + \'"\';
                            c++;
                        }
                        console.log();
                        stringObj += "}";
                    }
                    stringObj += "]";
                    return stringObj;
                }
            ');
        }
        $h .= ' </ul></li><!--Opcion-->
                </ul></li> <!--Menu-->
                </ul><!--ul del sistema--></li>
                </ul><!--ul clase filetree-->';

        $this->agregaTools($this->ponBoton($btnCancelarU) . ' ' . $this->ponBoton($btnGuardarU) . ' ' . $usarbtnReset);

        $form->mixHtml('
                ' . $h . '
                </fieldset>

            </div>
                <!--<div id="btns"/>-->
            </div>
        ');
        $this->mixHtml($form->ponForm());

        $prmUserid = (isset($this->prm->usr_id) && $this->prm->usr_id != '') ? ',usr:"' . $this->prm->usr_id . '"' : '';        

        $this->mixScript('

            $(".li3").click(function(){
                $("#save-userId").hide();
                $("#cancel-userId").hide();
            });

            $("#tabs2").tabs();

            $("#cancel-user").click(function(){
                callbackComunica = function(){$(".li3").click();}
                comunica("' . $this->screen . '",{
                    metodo:"main",
                    act:2
                });
                return false;
            });

            $("#browser").treeview({
                animated:"normal",
                persist: "cookie"
            });
            $("#nvj_id").change(function(){
                    comunica("' . $this->screen . '",{
                        metodo:"Selecciona",
                        opcNvj:$("#nvj_id").val()
                        ' . $prmUserid . '
                    });
                    $("#Uee").html("");
                    $("#Subsecretaria").html("");
                    $("#Direccion").html("");
                    $("#Unidad").html("");
                    $("#Depa").html("");
                return false;
            });

            $("#usr_usuario").keypress(function( event ){
                tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                patron = /[A-Za-z0-9ñÑ]/;
                caracter = String.fromCharCode(tecla);
                console.log(tecla);
                if( tecla == 8 || tecla == 95 || tecla == 45 ){
                    return true;
                }
                return patron.test(caracter);
            });

            $("#usr_usuario").blur(function(){
                if($(this).val()!=""){
                    comunica("' . $this->screen . '",{
                        metodo:"validaDatosUsuario",
                        usr_usuario:$(this).val(),
                        abc:"' . $abc . '"
                    });
                }
            });
            $("#usr_rfc").bind({
                keypress:function(event){
                    tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                    patron = /[A-Za-z0-9&]/;
                    caracter = String.fromCharCode(tecla);
                        if( tecla == 8 || tecla == 13 || tecla == 9){
                            return true;
                        }
                        return patron.test(caracter);
                }
            });
            $("#usr_nombre").keypress(function(event){
                tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                    patron = /[A-Za-zñÑ \u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00f1\u00d1\u00FC\u00DC]+$/;
                    caracter = String.fromCharCode(tecla);
                    if( tecla == 8 || tecla == 13 || tecla == 9 ){
                        return true;
                    }
                    return patron.test(caracter);
            });
            $("#usr_paterno,#usr_materno").keypress(function(event){
                tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
                    patron = /[A-Za-zñÑ \u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00f1\u00d1\u00FC\u00DC]+$/;
                    caracter = String.fromCharCode(tecla);
                    if( tecla == 8 || tecla == 13 || tecla == 9){
                        return true;
                    }
                    return patron.test(caracter);
            });
        ');
    }

    private function abcUsuarios()
    {
        $perm = 0;
        if (isset($this->prm->usr_permisos)) {
            $permisos = json_decode($this->prm->usr_permisos);
            $perm = 1;
        }

        if (isset($this->prm->usr_usuario) && $this->prm->usr_usuario != '')
            $this->prm->usr_usuario = preg_replace("/\r\n+|\r+|\n+|\t+/i", "", $this->prm->usr_usuario);

        switch ($this->prm->abc) {
            case 'd':
                    if (isset($this->prm->correo) && $this->prm->correo != '' && isset($this->prm->telefono) && $this->prm->telefono != '' &&$this->prm->usr_id != '' )
                        {
                            $telefono = $this->prm->telefono;
                            $correo = $this->prm->correo;
                            $idU = $this->prm->usr_id;

                            $this->setSql('
                                SELECT usr_correo 
                                FROM '.BD_CTRL.'.cat_Usuario
                                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)  
                                WHERE usr_correo = ? 
                                AND usr_id != ? 
                                AND sts_id = 1 
                                AND gpo_id = ? 
                                AND ur_id = ?');
                            $parametros = [$this->prm->correo,$_SESSION[_S]['usr_id'],$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']];
                            $this->conectar();
                            $this->consultar($parametros);
                            $numeroRegCorreo = $this->getNumeroRegistros();

                            $this->setSql('
                                SELECT usr_telefono 
                                FROM '.BD_CTRL.'.cat_Usuario
                                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id) 
                                WHERE usr_telefono = ? 
                                AND usr_id != ? 
                                AND sts_id = 1 
                                AND gpo_id = ? 
                                AND ur_id = ?');
                            $parametros = [$this->prm->telefono,$_SESSION[_S]['usr_id'],$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']];
                            $this->conectar();
                            $this->consultar($parametros);
                            $numeroRegMail = $this->getNumeroRegistros();

                            if ($numeroRegCorreo > 0 || $numeroRegMail > 0) {
                                $mensaje = ($numeroRegCorreo > 0 && $numeroRegMail > 0) ? "Error al actualizar la información, el correo y teléfono ya existen." : (($numeroRegCorreo > 0) ? "Error al actualizar la información, el correo ya existe." : "Error al actualizar la información, el telefono ya existe.");
                                $this->mixScript('notify("' . $mensaje . '","error");');
                                return;
                            }

                            $this->prm->correo = $correo;
                            $this->prm->telefono = $telefono;
                            $this->prm->usr_id = $idU;
                            $this->prm->abc = "c";

                            $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
                            $sql ='
                                SET 
                                @usr_id = :usr_id,
                                @bit_observacion = "ACTUALIZACIÓN DE CORREO Y TELÉFONO POR EL USUARIO",
                                @usr_ip = INET_ATON( :ip );
                            ';
                            
                            $parametros = [
                                ':usr_id' => $_SESSION[_S]['usr_id'],
                                ':ip' => $ip
                            ];
                            $this->setSql($sql);
                            $this->conectar();
                            $this->consultar($parametros);

                            Usuario::abc($this->prm);
                            $this->mixScript('
                                comunica("' . $this->screen . '",{
                                    metodo:"main"
                                });
                                notify("Datos actualizados correctamente.","success");
                            ');
                    }else{
                        $this->mixScript('notify("Error al actualizar la información.","danger");');
                    }
                break;
            case 'c':
                $this->conectar();
                $this->setSql(
                    'SELECT vgu_id, vgu_clave
                    FROM ctrlUsuario.cat_Usuario
                    INNER JOIN ctrlUsuario.cat_VigenciaUsuario USING (usr_id)
                    WHERE usr_id= :usrid
                    AND vgu_ejercicio = :ejer
                    ;'
                );
                $parametros = [
                    ':usrid' => $this->prm->usr_id,
                    ':ejer' => APP_EJERCICIO
                ];
                $this->consultar($parametros);
                $r = $this->extraer();
                $vgu_Id = $r['vgu_id']; //id de la contraseña del usuario
                $clave = $r['vgu_clave']; //clave actual del usuario

                /*                 * ********************************** */
                if (isset($this->prm->usr_id) && $this->prm->usr_id != '') {

                    if (isset($this->prm->usr_clave) && $this->prm->usr_clave != '') {
                        if (strcasecmp(hash('sha256',$this->prm->usr_clave),$clave) == 0) {

                            // Marcos: 18/jul/18
                            // Esto de las Entidades es la onda :(
                            // ¿cómo es que funcionaba el año pasado?

                            // Héctor: 14/ags/19
                            // Actualizacion a la entidad VigenciaUsuario
                            
                            $this->conectar();
                            $this->setSql("SELECT AES_ENCRYPT(:clave, 'SEFIP2026') as cifr, sha2(:clave, 256) as has;");
                            $parametros = [':clave' => $this->prm->usr_nclave];
                            $this->consultar($parametros);
                            $fc = $this->extraer();
                            $vguCl = $fc['cifr'];
                            $clave = $fc['has'];

                            $this->prm->vgu_id = $vgu_Id;
                            $this->prm->vgu_clave = $clave;
                            $this->prm->vgu_claveAcceso = $vguCl; 
                            $this->prm->vgu_ejercicio = APP_EJERCICIO;

                            $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
                            $sql ='
                                SET 
                                @usr_id = :usr_id,
                                @bit_observacion = "SE MODIFICO CONTRASEÑA POR EL USUARIO",
                                @usr_ip = INET_ATON( :ip );
                            ';
                            
                            $parametros = [
                                ':usr_id' => $_SESSION[_S]['usr_id'],
                                ':ip' => $ip
                            ];
                            $this->setSql($sql);
                            $this->conectar();
                            $this->consultar($parametros);

                            VigenciaUsuario::abc($this->prm);
                            $this->mixScript('notify("Contraseña actualizada","success");
                                $(".pwd").val("");
                            ');
                        } else {
                            $this->mixScript('notify("La contraseña actual es incorrecta","error");');
                        }
                    } else {
                        if (isset($this->prm->desactivar)) {
                            $usu = new Usuario($this->prm);
                            if ($usu->getSts_id() == 1) {
                                $usu->setSts_id(2);
                                $texto = 'SE DESHABILITO A USUARIO';
                                $text = 'El Usuario ha sido deshabilitado';
                            } else {
                                $usu->setSts_id(1);
                                $texto = 'SE HABILITO A USUARIO';
                                $text = 'El Usuario ha sido habilitado';
                            }
                            $usu->update();

                            $bitUsu = new stdClass();
                            $bitUsu->usr_id = $this->prm->usr_id;
                            $bitUsu->observacion = $texto;
                            $bitUsu->abc = "a";
                            BitUsuario::abc($bitUsu);
                            $this->mixScript('notify("' . $text . '","success");
                                comunica("' . $this->screen . '",{
                                    metodo:"main",
                                    act: 2
                                });
                            ');
                        } else if (isset($permisos) && count($permisos) > 0 && $perm != 0) {
                            $permisos = json_decode($this->prm->usr_permisos);
                            $i = 0;
                            $psql = '';
                            foreach ($permisos as $valor) {
                                if ($i != 0)
                                    $psql .= ' UNION ';
                                $psql .= 'SELECT "' . $valor->sis_id . '" sis_id,"' . $valor->men_id . '" men_id,"' . $valor->opc_id . '" opc_id,' . $valor->tar_id . ' tar_id';
                                $i++;
                            }
                            $this->conectar();
                            if ($psql != '') {
                                $this->setSql('
                                    INSERT INTO cat_PermisosUsuarios
                                    (sis_id,men_id,opc_id,tar_id,usr_id,usu_usu)
                                    SELECT sis_id,men_id,opc_id,tar_id,"' . $this->prm->usr_id . '",' . $_SESSION[_S]['usr_id'] . ' FROM
                                    (
                                    SELECT usr_id,sis_id,men_id,opc_id,tar_id FROM
                                        cat_PermisosUsuarios
                                        WHERE usr_id="' . $this->prm->usr_id . '"
                                    ) x1 RIGHT JOIN
                                    (
                                    ' . $psql . '
                                    ) x2 USING(sis_id,men_id,opc_id,tar_id)
                                    WHERE usr_id is null;
                                ');
                                $this->consultar();

                                $sql = 'INSERT INTO '. BD_BITACORA.'.bit_Usuario
                                ( bit_usu, usr_id, bit_observacion, usu_id)
                                values ( 0, ' . $this->prm->usr_id . ', "SE MODIFICARON PERMISOS DE USUARIO", ' . $_SESSION[_S]['usr_id'] . ' ) ';
                                $this->setSql($sql);
                                $this->consultar();

                                $this->mixScript('notify("Usuario modificado","success");
                                    callbackComunica = function(){$(".li3").click();}
                                    comunica("' . $this->screen . '",{
                                        metodo:"main",
                                        act:2
                                    });
                                ');
                            }
                        } else if (isset($this->prm->password) && $this->prm->password != '') {
                            if (md5(strtoupper($this->prm->password)) == md5(strtoupper($this->prm->passwordConfirm))) {
                                $this->prm->usr_clave = $this->prm->password;
                                if (isset($this->prm->g) && isset($this->prm->u)) {
                                    $this->prm->gpo_id = $this->prm->g;
                                    $this->prm->ur_id = $this->prm->u;
                                }

                                Usuario::abc($this->prm);
                                $bitUsu = new stdClass();
                                $bitUsu->usr_id = $this->prm->usr_id;
                                $bitUsu->observacion = 'SE REESTABLECIÓ CONTRASEÑA DE USUARIO';
                                $bitUsu->abc = "a";
                                BitUsuario::abc($bitUsu);
                                $this->mixScript('notify("Se ha reestablecido la contraseña de usuario","success");
                                    comunica("' . $this->screen . '",{
                                        metodo:"main",
                                        act:2
                                    });
                                ');
                            } else
                                $this->mixScript('notify("Las contraseñas no coinciden favor de verificar","error");');
                        }else {
                            $u = new Usuario();
                            $u->setUsr_id($this->prm->usr_id);
                            $u->select();
                            $act = '';

                            if ($this->prm->usr_id != $_SESSION[_S]['usr_id']) {
                                $act .= '
                                    comunica("' . $this->screen . '",{
                                        metodo:"main",
                                        act:2
                                    });
                                ';
                            }

                            $bitUsu = new stdClass();
                            $bitUsu->usr_id = $this->prm->usr_id;
                            $bitUsu->observacion = 'SE MODIFICO DATOS DE USUARIO';
                            $bitUsu->abc = "a";

                            if ($this->prm->usr_usuario != $u->getUsuario()) {
                                $this->conectar();
                                $this->setSql('SELECT usr_usuario FROM cat_Usuario WHERE usr_usuario = "' . $this->prm->usr_usuario . '" and gpo_id=' . $this->prm->gpo_id . ' and ur_id=' . $this->prm->ur_id . '');
                                $this->consultar();
                                $this->desconectar();                            

                                if ($this->getNumeroRegistros() != 0) {
                                    $this->mixScript('notify("El nombre de usuario ya existe","warning");
                                        ' . $act . '
                                    ');
                                } else {
                                    Usuario::abc($this->prm);
                                    BitUsuario::abc($bitUsu);
                                    $this->mixScript('notify("Usuario modificado","success");
                                        ' . $act . '
                                    ');
                                }
                            } else {
                                Usuario::abc($this->prm);
                                BitUsuario::abc($bitUsu);
                                $this->mixScript('notify("Usuario modificado","success");
                                    ' . $act . '
                                ');
                            }
                        }
                    }
                }
                break;
            case 'b':

                $sis_id = $this->prm->sis_id;
                $men_id = $this->prm->men_id;
                $opc_id = $this->prm->opc_id;
                $tar_id = $this->prm->tar_id;

                $this->conectar();
                /* checar que el usuario tenga el permiso seleccionado */
                $this->setSql('
                    SELECT DISTINCT usr_id FROM cat_PermisosUsuarios
                    WHERE  sis_id="' . $sis_id . '"  AND men_id="' . $men_id . '" AND opc_id="' . $opc_id . '" and tar_id="' . $tar_id . '"
                    and usr_id ="' . $this->prm->usr_id . '";
                ');
                $this->consultar();
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $this->conectar();
                    $this->setSql('
                        DELETE FROM cat_PermisosUsuarios
                        WHERE sis_id="' . $sis_id . '"  AND men_id="' . $men_id . '" AND opc_id="' . $opc_id . '" AND tar_id="' . $tar_id . '"
                        AND usr_id in
                        (
                            SELECT * FROM (
                                SELECT DISTINCT usr_id FROM cat_PermisosUsuarios perm
                                JOIN cat_Usuario USING(usr_id)
                                WHERE sis_id="' . $sis_id . '"  and perm.usr_id ="' . $this->prm->usr_id . '"
                            )BORRABLES
                        );
                    ');
                    $this->consultar();
                    $this->desconectar();
                    $this->mixScript('notify("Permiso borrado","success");');
                }
                break;
            case 'a':
                switch ($this->prm->nvj_id) {
                    case 7:                        
                        $this->prm->ssc_id = (isset($this->prm->ssc_id) && $this->prm->ssc_id != '' ? $_SESSION[_S]['ssc_id'] : "");
                        
                        break;
                    case 6:                        
                        $this->prm->ssc_id = (isset($this->prm->ssc_id) && $this->prm->ssc_id != '' ? $_SESSION[_S]['ssc_id'] : "");
                        
                        break;
                    case 5:
                        
                        $this->prm->ssc_id = (isset($this->prm->ssc_id) && $this->prm->ssc_id != '' ? $this->prm->ssc_id : $_SESSION[_S]['ssc_id']);
                        $this->prm->dir_id = (isset($this->prm->dir_id) && $this->prm->dir_id != '' ? $this->prm->dir_id : $_SESSION[_S]['dir_id']);
                        
                        break;
                    case 4:
                        
                        $this->prm->ssc_id = (isset($this->prm->ssc_id) && $this->prm->ssc_id != '' ? $this->prm->ssc_id : $_SESSION[_S]['ssc_id']);
                        $this->prm->dir_id = (isset($this->prm->dir_id) && $this->prm->dir_id != '' ? $this->prm->dir_id : $_SESSION[_S]['dir_id']);
                        $this->prm->uni_id = (isset($this->prm->uni_id) && $this->prm->uni_id != '' ? $this->prm->uni_id : $_SESSION[_S]['uni_id']);
                        
                        break;
                    case 3:
                        
                        $this->prm->ssc_id = (isset($this->prm->ssc_id) && $this->prm->ssc_id != '' ? $this->prm->ssc_id : $_SESSION[_S]['ssc_id']);
                        $this->prm->dir_id = (isset($this->prm->dir_id) && $this->prm->dir_id != '' ? $this->prm->dir_id : $_SESSION[_S]['dir_id']);
                        $this->prm->uni_id = (isset($this->prm->uni_id) && $this->prm->uni_id != '' ? $this->prm->uni_id : $_SESSION[_S]['uni_id']);
                        $this->prm->dep_id = (isset($this->prm->dep_id) && $this->prm->dep_id != '' ? $this->prm->dep_id : $_SESSION[_S]['dep_id']);
                        
                        break;
                    default:

                        break;
                }
                
                $permisos = json_decode($this->prm->usr_permisos);
                if (isset($permisos) && count($permisos) <= 0) {

                    $btnAnterior = new stdClass();
                    $btnAnterior->value = 'Anterior';
                    $btnAnterior->id = 'anterior';

                    $this->mixScript('
                        notify("Debes seleccionar un permiso","warning");
                        $("#anterior").remove();
                        $("#addmod1").removeClass("active");
                        $("#addmod2").addClass("active");
                        $(".lii").removeClass("active");
                        $(".lii2").addClass("active");
                        $("#save-user").text("Guardar");
                        $(".btns").append(\'' . $this->ponBoton($btnAnterior) . '\');

                        $("#anterior").click(function(){
                            $("#addmod2").removeClass("active");
                            $("#addmod1").addClass("active");
                            $(".lii2").removeClass("active");
                            $(".lii").addClass("active");
                            $("#save-user").text("Siguiente");
                            $("#anterior").remove();
                        });
                    ');
                } else if (isset($permisos) && count($this->prm->usr_permisos) > 0) {
                    $pass1 = $this->prm->usr_clave;
                    $pass2 = $this->prm->confirmar;

                    if ($pass2 != $pass1) {
                        $this->mixScript('
                            notify("Las contraseñas no coinciden","error");
                            $("#addmod2").removeClass("active");
                            $("#addmod1").addClass("active");
                            $(".lii2").removeClass("active");
                            $(".lii").addClass("active");
                            //$("#tabs2").tabs({ active: 0 });
                        ');
                    } else {
                        if ($this->validaDatosUsuario()) {
                            $this->prm->usr_id = Usuario::abc($this->prm);
                            $bitUsu = new stdClass();
                            $bitUsu->usr_id = $this->prm->usr_id;
                            $bitUsu->observacion = "SE AGREGO USUARIO CORRECTAMENTE";
                            $bitUsu->abc = "a";
                            BitUsuario::abc($bitUsu);
                            $this->mixScript('notify("Usuario agregado correctamente","success");
                                callbackComunica = function(){$(".li3").click();}
                                    comunica("' . $this->screen . '",{
                                        metodo:"main",
                                        act:2
                                    });
                                ');

                            $permisos = json_decode($this->prm->usr_permisos);
                            $i = 0;
                            $psql = '';
                            foreach ($permisos as $valor) {
                                if ($i != 0)
                                    $psql .= ' UNION ';
                                $psql .= 'SELECT "' . $valor->sis_id . '" sis_id,"' . $valor->men_id . '" men_id,"' . $valor->opc_id . '" opc_id,' . $valor->tar_id . ' tar_id';
                                $i++;
                            }
                            $this->conectar();
                            if ($psql != '') {
                                $this->setSql('
                                    INSERT INTO cat_PermisosUsuarios
                                    (sis_id,men_id,opc_id,tar_id,usr_id,usu_usu)
                                    SELECT sis_id,men_id,opc_id,tar_id,"' . $this->prm->usr_id . '",' . $_SESSION[_S]['usr_id'] . ' FROM
                                    (
                                    SELECT usr_id,sis_id,men_id,opc_id,tar_id FROM
                                        cat_PermisosUsuarios
                                        WHERE usr_id="' . $this->prm->usr_id . '"
                                    ) x1 RIGHT JOIN
                                    (
                                    ' . $psql . '
                                    ) x2 USING(sis_id,men_id,opc_id,tar_id)
                                    WHERE usr_id is null;
                                ');
                                
                                $this->consultar();
                            }
                        } else {
                            $this->mixScript('
                                notify("El usuario ya existe, favor de verificar","warning");	'
                            );
                        }
                    }
                }
                
                break;
        }
    }

    private function actualizaPasswordCaducado()
    {
        $datos = isset($_SESSION[_S]['expired_data']) ? (object) $_SESSION[_S]['expired_data'] : null;
        $cp = (string) $this->getPrm('current_password');
        $np = (string) $this->getPrm('password');
        $conp = $this->getPrm('confirm_password');

        if (!$datos) {
            $js = <<<JS
            notify("No se han encontrado datos para actualizar la contraseña","error");
            setTimeout(() => window.location.reload(), 3000);
            JS;
            $this->mixScript($js);
            return;
        }
        if ($np !== $conp) {
            $js = <<<JS
            notify("Las contraseñas no coinciden","error");
            setTimeout(() => $("#update_overlay").hide(), 2000);
            JS;
            $this->mixScript($js);
            return;
        }
        $servicio = new ServicioUsuario();

        if (!$servicio->passwordEsComplejo($np)) {
            $js = <<<JS
            notify("La contraseña no cumple con los requisitos de seguridad","error");
            setTimeout(() => $("#update_overlay").hide(), 1000);
            JS;
            $this->mixScript($js);
            return;
        }

        $validaUsuario = $servicio->logueaUsuario($datos->gpo_id, $datos->ur_id, $datos->usr_usuario, $cp);

        if (!$validaUsuario) {
            $js = <<<JS
            notify("Las contraseñas actual no es valida","error");
            setTimeout(() => $("#update_overlay").hide(), 2000);
            JS;
            $this->mixScript($js);
            return;
        }

        if ($servicio->yaUsoElPassword($datos->usr_id, $np)) {
            $js = <<<JS
            notify("La contraseña ya fue utilizada","error");
            setTimeout(() => $("#update_overlay").hide(), 1000);
            JS;
            $this->mixScript($js);
            return;
        }

        if ($servicio->updatePassword($np, $datos->usr_id)){
            $js = <<<JS
            notify("Se actualizo la contraseña con éxito","success");
            setTimeout(() => window.location.reload(), 2000);
            JS;
            $this->mixScript($js); 
        }
        else {
            $js = <<<JS
            notify("ocurrio un error {$servicio->getError()}","error");
            setTimeout(() => window.location.reload(), 2000);
            JS;
            $this->mixScript($js); 
        }

    }

    private function actualizaPasswordOlvidado()
    {
        $datos = isset($_SESSION[_S]['recoveryData']) ? (object) $_SESSION[_S]['recoveryData'] : null;
        $np = (string) $this->getPrm('password');
        $conp = $this->getPrm('confirm_password');

        if (!$datos) {
            $js = <<<JS
            notify("No se han encontrado datos para actualizar la contraseña","error");
            setTimeout(() => window.location.reload(), 3000);
            JS;
            $this->mixScript($js);
            return;
        }
        $ahora = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $inicio = $datos->fechaUltimoCambio;
        $res  = (object)$ahora->diff(new DateTime($inicio), 'minutes');

        if ($res->i > 5) {
            $js = <<<JS
            notify("El tiempo para actualizar la contraseña ha expirado, solicite nuevamente el cambio de contraseña","error");
            setTimeout(() => window.location.reload(), 3000);
            JS;
            $this->mixScript($js);
            return;
        }

        if ($np !== $conp) {
            $js = <<<JS
            notify("Las contraseñas no coinciden","error");
            setTimeout(() => $("#update_overlay").hide(), 2000);
            JS;
            $this->mixScript($js);
            return;
        }
        $servicio = new ServicioUsuario();

        if (!$servicio->passwordEsComplejo($np)) {
            $js = <<<JS
            notify("La contraseña no cumple con los requisitos de seguridad","error");
            setTimeout(() => $("#update_overlay").hide(), 1000);
            JS;
            $this->mixScript($js);
            return;
        }

        if ($servicio->yaUsoElPassword($datos->usuario['usr_id'], $np)) {
            $js = <<<JS
            notify("La contraseña ya fue utilizada","error");
            setTimeout(() => $("#update_overlay").hide(), 1000);
            JS;
            $this->mixScript($js);
            return;
        }
        $_SESSION[_S]['expired_data'] = $datos->usuario;
        if ($servicio->updatePassword($np)){
            $js = <<<JS
            notify("Se actualizo la contraseña con éxito","success");
            setTimeout(() => window.location.reload(), 2000);
            JS;
            $this->mixScript($js);
            unset($_SESSION[_S]['recoveryData']);
            unset($_SESSION[_S]['expired_data']);
        }
        else {
            $js = <<<JS
            notify("ocurrio un error {$servicio->getError()}","error");
            setTimeout(() => window.location.reload(), 2000);
            JS;
            $this->mixScript($js); 
        }

    }

    private function Selecciona()
    {
        switch ($this->prm->opcNvj) {
            case 1://Unidad Ejecutora
                $this->Ue();
                break;
            case 3://Analista
                switch ($_SESSION[_S]['nvj_id']) {
                    case 8: //secretario
                        $this->prm->hasta = 4;
                        $this->Subsecretaria();
                        break;
                    case 7: //subsecretario
                        $this->prm->hasta = 4;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->Direccion();
                        break;
                    case 6: //director
                        $this->prm->hasta = 4;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->prm->dire_id = $_SESSION[_S]['dir_id'];
                        $this->Unidad();
                        break;
                    case 5: //jefe de unidad
                        $this->prm->hasta = 4;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->prm->dire_id = $_SESSION[_S]['dir_id'];
                        $this->prm->unid_id = $_SESSION[_S]['uni_id'];
                        $this->Departamento();
                        break;
                    case 4: //jefe de departamento
                        $this->prm->hasta = 4;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->prm->dire_id = $_SESSION[_S]['dir_id'];
                        $this->prm->unid_id = $_SESSION[_S]['uni_id'];
                        $this->Departamento();
                        break;
                }
                break;
            case 4://Jefe de Depto
                switch ($_SESSION[_S]['nvj_id']) {
                    case 8: //secretario
                        $this->prm->hasta = 4;
                        $this->Subsecretaria();
                        break;
                    case 7: //subsecretario
                        $this->prm->hasta = 4;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->Direccion();
                        break;
                    case 6: //director
                        $this->prm->hasta = 4;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->prm->dire_id = $_SESSION[_S]['dir_id'];
                        $this->Unidad();
                        break;
                    case 5: //jefe de unidad
                        $this->prm->hasta = 4;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->prm->dire_id = $_SESSION[_S]['dir_id'];
                        $this->prm->unid_id = $_SESSION[_S]['uni_id'];
                        $this->Departamento();
                        break;
                }
                break;
            case 5://Jefe de Unidad
                switch ($_SESSION[_S]['nvj_id']) {
                    case 8: //secretario
                        $this->prm->hasta = 5;
                        $this->Subsecretaria();
                        break;
                    case 7: //subsecretario
                        $this->prm->hasta = 5;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->Direccion();
                        break;
                    case 6: //director
                        $this->prm->hasta = 5;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->prm->dire_id = $_SESSION[_S]['dir_id'];
                        $this->Unidad();
                        break;
                }
                break;
            case 6://Director
                switch ($_SESSION[_S]['nvj_id']) {
                    case 8: //secretario
                        $this->prm->hasta = 6;
                        $this->Subsecretaria();
                        break;
                    case 7: //subsecretario
                        $this->prm->hasta = 6;
                        $this->prm->sscr_id = $_SESSION[_S]['ssc_id'];
                        $this->Direccion();
                        break;
                }
                break;
            case 7://Subsecretario
                switch ($_SESSION[_S]['nvj_id']) {
                    case 8: //secretario
                        $this->prm->hasta = 7;
                        $this->Subsecretaria();
                        break;
                }
                break;
        }
    }

    private function Ue()
    {
        $this->setContenedor('Uee');
        $MostrarUe = new stdClass();
        $MostrarUe->id = 'ue_id';
        $MostrarUe->name = 'ue_id';
        $MostrarUe->sql = 'SELECT ue_id, ue_nombre from cat_UnidadEjecutora where gpo_id="' . $this->prm->gpo_id . '" and ur_id="' . $this->prm->ur_id . '"';
        $MostrarUe->sql .= ( isset($_SESSION[_S]['prm']['X'][2][1]) ) ? ' AND ue_ejerceInversionPlaneacion = 1 ' : '';
        $MostrarUe->label = 'Unidad Ejecutora';
        $MostrarUe->width = '500px';
        $MostrarUe->required = true;
        $MostrarUe->mensaje = true;

        $this->mixHtml($this->ponCombo($MostrarUe));
    }

    private function Subsecretaria()
    {
        $ssc = (isset($this->prm->sscr_id) && $this->prm->sscr_id != '') ? '"' . $this->prm->sscr_id . '"' : '$("#ssc_id").val()';

        $this->setContenedor('Subsecretaria');
        $userSscId = new stdClass();
        $userSscId->id = 'ssc_id';
        $userSscId->name = 'ssc_id';
        $userSscId->sql = 'SELECT ssc_id,ssc_nombre from cat_Subsecretaria';
        $userSscId->label = 'Subsecretaria';
        
        $userSscId->mensaje = true;
        $userSscId->soloText = true;

        $this->mixHtml($this->ponCombo($userSscId));

        switch ($this->prm->hasta) {
            case 3: case 4: case 5: case 6:
                $this->mixScript('
                    $("#ssc_id").change(function(){
                        comunica("' . $this->screen . '",{
                            metodo:"Direccion",
                            sscr_id:' . $ssc . ',
                            hasta:' . $this->prm->hasta . '
                        });
                        return false;
                    });
                ');
                break;
            default:
                $this->mixScript('
                    $("#ssc_id").unbind("change");
                ');
                break;
        }
    }

    private function Direccion()
    {
        $this->setContenedor('Direccion');

        $ssc = (isset($this->prm->sscr_id) && $this->prm->sscr_id != '') ? '' . $this->prm->sscr_id . '' : '$("#ssc_id").val()';
        $dire = (isset($this->prm->dire_id) && $this->prm->dire_id != '') ? '' . $this->prm->dire_id . '' : '$("#dir_id").val()';

        $userDir = new stdClass();
        $userDir->id = 'dir_id';
        $userDir->name = 'dir_id';
        $userDir->sql = 'SELECT dir_id,dir_nombre from cat_Direccion where ssc_id = "' . $this->prm->sscr_id . '"';
        $userDir->label = 'Direccion';
        
        $userDir->mensaje = true;
        $userDir->soloText = true;

        $this->mixHtml($this->ponCombo($userDir));
        
        $dir = '';

        if (!is_null($this->comboValorUnico)) {
            switch ($this->prm->hasta) {
                case 3: case 4: case 5:
                    $this->mixScript('
                        comunica("' . $this->screen . '",{
                            metodo:"Unidad",
                            sscr_id:' . $ssc . ',
                            dire_id:' . $this->comboValorUnico . ',
                            hasta:' . $this->prm->hasta . '
                        });
                    ');
                    break;
                default:
                    $this->mixScript('
                        $("#dir_id").unbind("change");
                    ');
                    break;
            }
        } else {
            $dir = 'dire_id:' . $dire . ',';


            switch ($this->prm->hasta) {
                case 3: case 4: case 5:
                    $this->mixScript('
                        $("#dir_id").change(function(){
                            comunica("' . $this->screen . '",{
                                metodo:"Unidad",
                                sscr_id:' . $ssc . ',
                                ' . $dir . '
                                hasta:' . $this->prm->hasta . '
                            });
                            return false;
                        });
                    ');
                    break;
                default:
                    $this->mixScript('
                        $("#dir_id").unbind("change");
                    ');
                    break;
            }
        }
    }

    private function Unidad()
    {

        $ssc = (isset($this->prm->sscr_id) && $this->prm->sscr_id != '') ? '' . $this->prm->sscr_id . '' : '$("#ssc_id").val()';
        $dire = (isset($this->prm->dire_id) && $this->prm->dire_id != '') ? '' . $this->prm->dire_id . '' : '$("#dir_id").val()';
        $unid = (isset($this->prm->unid_id) && $this->prm->unid_id != '') ? '' . $this->prm->unid_id . '' : '$("#uni_id").val()';

        $this->setContenedor('Unidad');
        $userUni = new stdClass();
        $userUni->id = 'uni_id';
        $userUni->name = 'uni_id';
        $userUni->sql = 'SELECT uni_id,uni_nombre from cat_Unidad where ssc_id="' . $this->prm->sscr_id . '" and dir_id = "' . $this->prm->dire_id . '"';
        $userUni->label = 'Unidad';        
        $userUni->mensaje = true;
        $userUni->soloText = true;


        $this->mixHtml($this->ponCombo($userUni));

        $u = '';
        if (!is_null($this->comboValorUnico)) {
            switch ($this->prm->hasta) {
                case 3: case 4:
                    $this->mixScript('
                        comunica("' . $this->screen . '",{
                            metodo:"Departamento",
                            sscr_id:' . $ssc . ',
                            dire_id:' . $dire . ',
                            unid_id:' . $this->comboValorUnico . ',
                            hasta:' . $this->prm->hasta . '
                        });
                    ');
                    break;
                default:
                    $this->mixScript('
                        $("#uni_id").unbind("change");
                    ');
                    break;
            }
        } else {
            $u = 'unid_id:' . $unid . ',';

            switch ($this->prm->hasta) {
                case 3: case 4:
                    $this->mixScript('
                        $("#uni_id").change(function(){
                            comunica("' . $this->screen . '",{
                                metodo:"Departamento",
                                sscr_id:' . $ssc . ',
                                dire_id:' . $dire . ',
                                ' . $u . '
                                hasta:' . $this->prm->hasta . '
                            });
                            return false;
                        });
                    ');
                    break;
                default:
                    $this->mixScript('
                        $("#uni_id").unbind("change");
                    ');
                    break;
            }
        }
    }

    private function Departamento()
    {
        $this->setContenedor('Depa');

        $u = (isset($this->prm->unid_id) && $this->prm->unid_id != '') ? 'and uni_id="' . $this->prm->unid_id . '"' : '';

        $userDep = new stdClass();
        $userDep->id = 'dep_id';
        $userDep->name = 'dep_id';
        $userDep->sql = 'SELECT dep_id,dep_nombre from cat_Departamento where ssc_id="' . $this->prm->sscr_id . '" and dir_id = "' . $this->prm->dire_id . '" ' . $u . '';
        $userDep->label = 'Departamento';        
        $userDep->mensaje = true;
        $userDep->soloText = true;


        $this->mixHtml($this->ponCombo($userDep));
    }

    private function validaUser($val)
    {

        $this->conectar();
        $this->setSql('SELECT usr_usuario FROM cat_Usuario WHERE usr_usuario = "' . $val . '" and gpo_id=' . $this->prm->gpo_id . ' and ur_id=' . $this->prm->ur_id . '');
        
        $this->consultar();
        $this->desconectar();
        // Si la encontramos ...        
        if ($this->getNumeroRegistros() > 0) {            
            $this->mixScript('notify("El nombre de usuario ya existe","warning");
                $("#usr_usuario").focus();
            ');
            
            return 0;
        } else {            
            return 1;
        }
    }

    private function validaDatosUsuario()
    {
        foreach ($this->prm as $varName => $val) {
            if (is_string($val)) {
                $val = str_replace("+", " ", $val);
                switch ($varName) {
                    case "usr_usuario":
                        if (isset($this->prm->abc) && $this->prm->abc == "a") {
                            $this->validaUser($val);
                            if ($this->validaUser($val))
                                return 1;
                            else
                                return 0;
                        }

                        break;
                }
            }
        }
    }

    private function resetPasswd()
    {

        $this->setContenedor('rPasswd');

        $password = new stdClass();
        $password->id = 'password';
        $password->label = 'Nueva Contraseña';
        $password->placeholder = 'maximo 10 caracteres';
        $password->enDiv = true;
        $password->required = true;
        $password->maxlength = 10;
        $password->type = 'password';

        $passwordConfirm = new stdClass();
        $passwordConfirm->id = 'passwordConfirm';
        $passwordConfirm->class = 'passwordConfirm';
        $passwordConfirm->label = 'Confirmar contraseña';
        $passwordConfirm->placeholder = 'maximo 10 caracteres';
        $passwordConfirm->required = true;
        $passwordConfirm->maxlength = 10;
        $passwordConfirm->enDiv = true;
        $passwordConfirm->type = 'password';

        $modal = '<div id="myModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true">x</button>
                        <h4 class="modal-title" id="myModalLabel">Restablecer Contraseña de Usuario</h4>
                    </div>
                    <div class="modal-body">
                        <form id="ReestablecerUser">
                        <div class="row">
                            <legend>Contraseña</legend>
                            <fieldset class="col-md-6">
                                ' . $this->ponInput($password) . '
                            </fieldset>
                            <fieldset class="col-md-6">
                                ' . $this->ponInput($passwordConfirm) . '
                            </fieldset>
                        </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn" id="reset" form="">Aceptar</button>
                        <button type="button" class="btn cerrar" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);

        $datos = (isset($this->prm->g) && !empty($this->prm->g) && isset($this->prm->u) && !empty($this->prm->u)) ? ',g:' . $this->prm->g . ', u:' . $this->prm->u . '' : '';

        $this->mixScript('
            $(".cerrar").click(function(){ $("#myModal").remove(); });
            $("#reset").click(function(){
                valido = true;
                $("#ReestablecerUser").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                    if(!this.checkValidity()){
                        $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
                    }
                });
                $( ".formError" ).show("pulsate");
                setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
                setTimeout(function() { $( ".formError" ).remove(); }, 4500 );
                if(valido){
                    if($("#password").val()==$("#passwordConfirm").val()){
                        comunica("' . $this->screen . '",{
                            metodo: "abcUsuarios",
                            abc:"c",
                            usr_id:' . $this->prm->usr_id . ',
                            password:$("#password").val(),
                            passwordConfirm:$("#passwordConfirm").val()
                            ' . $datos . '
                        });
                        $("#myModal").remove();
                    } else
                        notify("las contraseñas no coinciden favor de verificar","error");
                }
            });
        ');
    }

    private function cambiarPasswd()
    {   

        $user = new Usuario();
        $user->setUsr_id($_SESSION[_S]['usr_id']);
        $user->select();

        $this->setContenedor('cPasswd');

        $userPass = new stdClass();
        $userPass->id = 'usr_clave';
        $userPass->class = 'pwd mb-2';
        $userPass->label = 'Contraseña actual';
        $userPass->maxlength = 30;
        $userPass->required = true;
        $userPass->placeholder = 'Contraseña actual';
        $userPass->enDiv = true;
        $userPass->type = 'password';

        $userNewPass = new stdClass();
        $userNewPass->id = 'usr_nclave';
        $userNewPass->class = 'pwd mb-2';
        $userNewPass->maxlength = 30;
        $userNewPass->required = true;
        $userNewPass->label = 'Nueva Contraseña';
        $userNewPass->placeholder = 'máximo 30 caracteres';
        $userNewPass->enDiv = true;
        $userNewPass->type = 'password';

        $userNewPassRepeat = new stdClass();
        $userNewPassRepeat->id = 'usr_nclaver';
        $userNewPassRepeat->class = 'pwd mb-2';
        $userNewPassRepeat->maxlength = 30;
        $userNewPassRepeat->required = true;
        $userNewPassRepeat->label = 'Confirmar Contraseña';
        $userNewPassRepeat->placeholder = 'máximo 30 caracteres';
        $userNewPassRepeat->type = 'password';
        $userNewPassRepeat->enDiv = true;

        $correoU = new stdClass();
        $correoU->id = 'correoU';
        $correoU->class = 'correoU mb-2';
        $correoU->maxlength = 50;
        $correoU->required = true;
        $correoU->label = 'Correo Electrónico Institucional';
        $correoU->placeholder = 'Correo Institucional';
        $correoU->type = 'mail';
        $correoU->enDiv = true;
        $correoU->value = $user->getCorreo();
        

        $telefono = new stdClass();
        $telefono->id = 'telefono';
        $telefono->class = 'telefono mb-2';
        $telefono->maxlength = 10;
        $telefono->required = true;
        $telefono->label = 'Número de Teléfono';
        $telefono->placeholder = 'Formato de 10 dígitos';
        $telefono->type = 'numero';
        $telefono->value = $user->getTelefono();

        $modal = '<div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display:block; padding-top: 100px;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title" style="color: white;" id="myModalTitle">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close cerrar" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="cuerpoModal">
                        <form id="ReestablecerUser">
                            <div class="row">
                                <div class="col-md-6">
                                    <b>' . $this->ponInput($userPass) . '</b>
                                    <b>' . $this->ponInput($userNewPass) . '</b>
                                    <b>' . $this->ponInput($userNewPassRepeat) . '</b>
                                </div>
                            </div>
                        </form>
                        <div class="mb-3">
                            <hr><h5>Consideraciones</h5></hr>
                            <span>M&iacute;nimo 8 caracteres y m&aacute;ximo 30</span><br>
                            <span>M&iacute;nimo 1 n&uacute;mero</span><br>
                            <span>Al menos una letra mayúscula </span><br>
                            <span>Al menos una letra minúscula </span><br>
                            <span>Al menos uno de los siguientes caracteres ! @ # $ % ^ & * ( ) _ + { } [ ] : ; < > , . ? ~ / </span>
                        </div>
                    </div>
                    <div class="modal-footer" id="pieModal">
                        <button type="submit" class="btn btn-outline-success me-1" id="reset" form=""><i class="fa-solid fa-floppy-disk me-2"></i>Aceptar</button>
                        <button type="button" class="btn btn-outline-danger me-1 cerrar" data-dismiss="modal"><i class="fa-solid fa-xmark me-2"></i>Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>'; 

        $this->mixHtml($modal);
        if ($this->prm->bandera==999)
        {
            $this->mixScript('
            // Marcos: 07/mzo/2017
            // Se filtra la Ññ para que no truene al entrar al sistema de Inversión
            $("#usr_nclave, #usr_nclaver").blur(function(){
                if ( $(this).val().search("Ñ") >= 0 || $(this).val().search("ñ") >= 0 ) {
                    notify("Por favor, no uses Ñ en la contraseña", "error");
                    $(this).focus();
                }
            });

            $(".cerrar").click(function(){ $("#myModal").remove(); });
            $("#reset").click(function(){
                valido = true;

            $("#ReestablecerUser").find("required").each(function(){
                valido = valido && this.checkValidity();
                if(!this.checkValidity()){
                    $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
                }
            });

            $( ".formError" ).show("pulsate");
                setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
                setTimeout(function() { $( ".formError" ).remove(); }, 4500 );
                if(valido) {
                    if($("#usr_clave").val()=="" && $("#usr_nclave").val()=="" && $("#usr_nclaver").val()== "") {
                        notify("Favor de ingresar los datos requeridos","error");
                    } else {
                        if($("#usr_clave").val()!="") {
                            if($("#usr_nclave").val()=="" && $("#usr_nclaver").val()== "") {
                                notify("Ingrese la nueva contraseña y confirme, son campos requeridos","error");
                            } else {
                                if($("#usr_nclave").val()!="") {
                                    if($("#usr_nclaver").val()!="") {
                                        if($("#usr_nclave").val()==$("#usr_nclaver").val()) {
                                            if (validarInput($("#usr_nclave").val().trim()) && validarInput($("#usr_nclaver").val().trim())){
                                                comunica("' . $this->screen . '",{
                                                    metodo: "abcUsuarios",
                                                    abc:"c",
                                                    usr_id:' . $this->prm->usr_id . ',
                                                    usr_clave:$("#usr_clave").val().trim(),
                                                    usr_nclave:$("#usr_nclave").val().trim()
                                                });
                                                $("#myModal").remove();
                                            }else {
                                                notify("No cumple con las consideraciones indicadas","error");
                                            }
                                        } else
                                            notify("La nuevas contraseñas deben coincidir","error");
                                        } else
                                            notify("Favor de confirmar la nueva contraseña","error");
                                        } else
                                            notify("Ingrese la nueva contraseña","error");
                                }
                            } else
                                notify("La contraseña actual es un campo requerido","error");
                        }
                }
            });

            setTimeout(function() { $( "#usr_clave" ).focus(); }, 300 );

            function validarInput(input) {
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}\[\]:;<>.,?~/]).{8,30}$/;
            return regex.test(input);
            }
        ');
        }else
            {
            $this->mixScript('

                const myModal = document.getElementById("myModal");
                const cuerpoModal = document.getElementById("cuerpoModal");
                const tituloModal = document.getElementById("myModalTitle");
                const pieModal = document.getElementById("pieModal");

                cuerpoModal.innerHTML = "";
                pieModal.innerHTML = "";

                tituloModal.textContent = "Actualizar datos";
                cuerpoModal.innerHTML = `
                    <form id=ActualizarDatos">
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="fa fa-info-circle me-1"></i>Importante</h5>
                            <p class="info-message">
                                Es indispensable que su correo electrónico y número de teléfono sean correctos y estén activos,
                                ya que se utilizarán en el proceso de autenticación y verificación de seguridad (2FA).
                            </p>
                        </div>
                        <div class="col-md-6">
                            <hr></hr>
                            <div class="form-group">
                                <b>' . $this->ponInput($correoU) . '</b>
                                <div id="errorCorreo" class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <b>' . $this->ponInput($telefono) . '</b>
                                <div id="errorTelefono" class="error-message"></div>
                            </div>
                        </div>
                    </form>
                `;

                    pieModal.innerHTML = `
                        <button type="submit" class="btn btn-outline-success me-1" id="UpCorreoT" form=""><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                    `;
                $(".cerrar").click(function(){ $("#myModal").remove(); });

                $(document).off("click", "#UpCorreoT").on("click", "#UpCorreoT", function() {
                        telefono = $("#telefono").val().trim();
                        correo = $("#correoU").val().trim();
                        comunica("' . $this->screen . '",{
                        metodo: "correoInstitucional",
                            usr_id : "' . $this->prm->usr_id . '"
                        });
                    });

                $("#correoU").blur(function(){
                    valor = $(this).val().trim();
                    if ( valor != "" ) {
                        const correo = valor;
                        comunica("' . $this->screen . '",{
                            metodo: "validaNuevoCorreo",
                            correo: correo
                        });
                    }
                });

                $("#telefono").blur(function(){
                    valor = $(this).val().trim();
                    if ( valor != "" ) {
                        const telefono = valor;
                        comunica("' . $this->screen . '",{
                            metodo: "validaNuevoTelefono",
                            telefono: telefono
                        });
                    }
                });

            ');
            }
    }

    private function correoInstitucional()
    {
    
        $dominiosPermitidos = [];
        $sql = 'SELECT dmi_dominio, dmi_activo from '.BD_SINPRES.'.cat_DominioInstitucional
        WHERE dmi_activo = ?;';
        $param = [1];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($param);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0)
            {
            while ($data = $this->extraer()) 
            {
                $dominiosPermitidos[] = $data['dmi_dominio'];
            }
            }
            // Extraer dominio del correo
        $this->mixScript('

            let dominiosPermitidos = ' . json_encode($dominiosPermitidos) . ';
            let dominiosExcluidos = ["gmail", "hotmail", "yahoo", "outlook","icloud"];
            const correo = document.getElementById("correoU").value.trim();
            const telefono = document.getElementById("telefono").value;
            let dominio = correo.split("@")[1]?.toLowerCase() ?? "";
            let bandera = false;

            if (/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+$/.test(correo) || dominiosExcluidos.some(d => dominio.includes(d.toLowerCase())))
            {   
                dominio = correo.split("@")[1].toLowerCase();
                if (dominiosPermitidos.some(d => dominio.endsWith(d.toLowerCase())) || dominiosPermitidos.some(d => dominio === d.toLowerCase()))
                {   
                    $("#correoU").css("border-color", "green");
                    bandera = true;
                }else{
                    $("#errorCorreo").text("Es necesario que ingrese un correo institucional.").css("color", "red");
                }
            }else {
                $("#errorCorreo").text("").css("color", "");
                $("#correoU").css("border-color", "");
                $("#errorCorreo").text("Ingrese un correo electrónico válido.").css("color", "red");
            }

        // Validación de teléfono (solo 10 dígitos)
            const numeroLimpio = telefono.replace(/\D/g, "");
            const telefonoValido =/^\d{10}$/.test(numeroLimpio)
            && !/^(\d)\1{9}$/.test(numeroLimpio)
            && numeroLimpio[0] !== "0"
            && !/(.)\1{3,}/.test(numeroLimpio)
            && numeroLimpio[0] !== "1";

            if (!telefonoValido) {
                $("#errorTelefono").text("Se aceptan únicamente números y debe tener exactamente 10 dígitos.").css("color", "red");
            }else
                {
                    $("#telefono").css("border-color", "green");
                }

            if (bandera && telefonoValido)
            {   
                comunica("' . $this->screen . '",{
                    metodo: "abcUsuarios",
                    abc: "d",
                    usr_id: "' . $this->prm->usr_id . '",
                    telefono: telefono,
                    correo: correo
                });
                $("#myModal").hide();
            }
            ');

    }

    // Determinamos si el correo electronico ya existe o no
    // 1: ya existe   0: no existe
    public function validaNuevoCorreo()
    {
        $numeroRegistros = 0;
        if ($this->prm->correo != '') {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                SELECT usr_correo 
                FROM '.BD_CTRL.'.cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)  
                WHERE usr_correo = ? 
                AND usr_id != ? 
                AND sts_id = 1 
                AND gpo_id = ? 
                AND ur_id = ?');
            $param = [$this->prm->correo,$_SESSION[_S]['usr_id'],$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']];
            $conexion->consultar($param);
            $numeroRegistros = $conexion->getNumeroRegistros();
            $conexion->desconectar();
        }
        if ($numeroRegistros > 0) {
            $this->mixScript('
                $("#errorCorreo").text("El correo ya existe.").css("color", "red");
            ');
        } else {
            $this->mixScript('
                $("#errorCorreo").text("").css("color", "green");
            ');
        }
    }

    // Determinamos si el correo telefono ya existe o no
    // 1: ya existe   0: no existe
    public function validaNuevoTelefono()
    {
        $numeroRegistros = 0;
        if ($this->prm->telefono != '') {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                SELECT usr_telefono 
                FROM '.BD_CTRL.'.cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id) 
                WHERE usr_telefono = ? 
                AND usr_id != ? 
                AND sts_id = 1 
                AND gpo_id = ? 
                AND ur_id = ?');
            $param = [$this->prm->telefono,$_SESSION[_S]['usr_id'],$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']];
            $conexion->consultar($param);
            $numeroRegistros = $conexion->getNumeroRegistros();
            $conexion->desconectar();
        }
        if ($numeroRegistros > 0) {
            $this->mixScript('
                $("#errorTelefono").text("El teléfono ya existe.").css("color", "red");
            ');
        } else {
            $this->mixScript('
                $("#errorTelefono").text("").css("color", "green");
            ');
        }
    }

}

new ControladorUsuarios();
