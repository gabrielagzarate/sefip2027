<?php

declare (strict_types = 1);

if ($_SERVER['SERVER_ADDR'] == "172.17.90.19") {
    error_reporting(E_ERROR);
} else {
    error_reporting(E_ALL);
}
if (session_id() == PHP_SESSION_NONE && !isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . "/../lib/constantes.php";
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/ServicioEmail.php';
require_once dirname(__FILE__) . '/Telegram.php';
require_once dirname(__FILE__) . '/ServicioUsuario.php';

class Controlador extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $_SESSION[_S]['mod_id'] = (isset($this->prm->sis_id)) ? $this->prm->sis_id : ((empty($_SESSION[_S]['mod_id'])) ? null : $_SESSION[_S]['mod_id']);
        //se agrega validación para verificar que este el  ControladorMensaje y activar notificación
        $this->file = (is_file(dirname(__FILE__) . "/ControladorMensajes.php") ? require_once dirname(__FILE__) . "/ControladorMensajes.php" : '');

        if ($this->sistemaAbierto()) {
            if ($this->metodoRequiereSesion($this->metodo) && !sesionActiva()) {
                $this->sesionExpirada();
            } else {
            switch ($this->metodo) {
                case 'ticket':
                    $this->ticket();
                    break;
                case 'login':
                    $this->login();
                    break;
                case 'loguear':
                    $this->loguear();
                    break;
                case 'seleccionaSistema':
                    $_SESSION[_S]['mod_id'] = $this->prm->sis_id;
                case 'sinpres':
                    $this->sinpres();
                    break;
                case 'refrescaMenu':
                    $this->menu();
                    break;
                case 'syncWebSocket':
                    $this->syncWebSocket();
                    break;
                case 'cerrarSesion':
                    if (isset($_SESSION[_S]['ses-lgc_id'])) {
                        $this->conectar();
                        $this->setSql('UPDATE ' . BD_USUARIOBIT . '.log_Conexion SET lgc_fin=now() WHERE lgc_id = ?;');
                        if (!isset($_SESSION[_S]['mst'])) {
                            $this->consultar([$_SESSION[_S]['ses-lgc_id']]);
                        }
                        $this->desconectar();
                    }
                    unset($_SESSION[_S]);
                    $this->mixScript(" location.reload(); ");
                    break;
                case 'tienePantallaAyuda':
                    $this->tienePantallaAyuda();
                    break;
                case 'logLogueo':
                    $this->logLogueo();
                    break;
                case 'recuperaPassword':
                    $this->recuperaPassword();
                    break;
                case 'procesarVerificacion2FA':
                    $this->procesarVerificacion2FA();
                    break;
                case 'reenviarCodigo2FA':
                    $this->reenviarCodigo2FA();
                    break;
                default:
                    break;
            }
            }
        }
        $this->returnJson();
    }

    private function metodoRequiereSesion(string $metodo): bool
    {
        return in_array($metodo, [
            'sinpres',
            'refrescaMenu',
            'syncWebSocket',
            'tienePantallaAyuda',
            'seleccionaSistema',
        ], true);
    }

    private function sesionExpirada(): void
    {
        unset($_SESSION[_S]);
        $this->setContenedor('cuerpoSinpres');
        $this->login();
    }

    private function modalAviso(){
        $modal= <<<HTML
                    <div id="modalMensajesLogin" class="modal fade sefip-modal-mensaje" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content sefip-modal-mensaje-content">
                                <div class="modal-header sefip-modal-mensaje-header sefip-modal-mensaje--danger">
                                    <h4 class="modal-title w-100 text-center">Aviso importante – Actualización obligatoria de datos</h4>
                                    <button type="button" class="btn-close btn-close-white cerrar-mensaje-login" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body sefip-modal-mensaje-body">
                                    <p>Con el fin de fortalecer la seguridad, trazabilidad y correcta identificación de las personas usuarias, es obligatorio que cada usuario mantenga actualizados su <strong>correo electrónico y número telefónico</strong> en el sistema, considerando lo siguiente:</p>
                                    <ul>
                                        <li>El correo electrónico debe ser institucional.</li>
                                        <li>El correo y el número telefónico son datos personales y deben corresponder exclusivamente al titular de la cuenta.</li>
                                        <li>No puede existir más de un usuario con el mismo correo ni con el mismo número telefónico.</li>
                                        <li>Se realizarán validaciones continuas para verificar que el correo registrado pertenezca a la persona a la que fue asignado el usuario.</li>
                                        <li>La fecha límite para realizar la actualización es el 30 de enero. Después de esta fecha, los usuarios que cuenten con correos no institucionales o con datos inválidos serán inactivados.</li>
                                    </ul>
                                    <p>Agradecemos su colaboración para mantener la integridad y seguridad de la plataforma.</p>
                                </div>
                                <div class="modal-footer sefip-modal-mensaje-footer">
                                    <button type="button" class="btn btn-outline-secondary cerrar-mensaje-login" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>
        HTML;
        $script = <<<JS
            (function(){
                var \$modal = $("#modalMensajesLogin");
                \$modal.on("hidden.bs.modal", function(){
                    \$modal.remove();
                });
                if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(\$modal[0], {backdrop:"static", keyboard:false}).show();
                } else {
                    \$modal.modal({backdrop:"static", keyboard:false});
                    \$modal.modal("show");
                }
            })();
        JS;
        $this->mixScript($script);
        return $this->mixHtml($modal);
    }

    private function login()
    {
        $this->setContenedor('cuerpoSinpres');
        $form = new stdClass();
        $form->id = 'login-form';
        $form->controlador = 'srv/Controlador.php';
        $form->metodo = 'loguear';
        $form->class = '';
        $form = new form($form);
        if (ServicioUsuario::sesionBloqueada()) {
            $form->mixHtml('<div class="alert alert-danger" role="alert">Has superado el número máximo de intentos, por favor intenta en 10 minutos.</div>');
        } else {
            IF(AVISO_LOGIN){
                $this->modalAviso();
            }
            $form->mixHtml('
                <div class="input-group mb-3">
                    <input type="text" required maxlength="3" name="claveUr" placeholder="UR" id="claveUr" class="form-control input-ur" tabindex="1">
                    <input type="text" required name="usuario" placeholder="Usuario" id="usuario" class="form-control input-user" tabindex="2">
                </div>
                <div class="input-group mb-3">
                    <input type="password" style="text-transform: none !important;" required placeholder="CONTRASEÑA" name="pass" id="pass" class="form-control input-pass" tabindex="3" autocomplete="current_password">
                    <div class="input-group-append">
                        <span class="input input-group-text" id="show-pass"><i class="fa fa-eye"></i></span>
                    </div>
                </div>
                <div class="mb-0">
                    <img alt="captcha" width="100%" id="siimage" src="./lib/captcha/securimage_show.php?sid=' . md5((string) time()) . '" />
                </div>
                <div class="input-group mb-3">
                    <button id="refrescaCaptcha" aria-label="Recargar captcha" type="button" class="btn btn-captcha">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                        </svg>
                    </button>
                    <input type="text" tabindex="4" class="form-control" id="captcha" name="captcha" placeholder="texto de seguridad" maxlength="5" required>
                </div>
                <button tabindex="5" aria-label="Iniciar sesión" type="submit" class="btn btn-login col-md-12">Entrar</button>
                <div class="text-center mt-3">
                    <a href="#" id="recuperaPassword" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
                </div>
            ');
        }
        if(SERVER_PRODUCCION){
            $this->mixHtml(' 
                <div style= "position: absolute;left: 53%; top: 47%;margin-left: -250px;margin-top: -180px;">
                    <a href="descargas/SEFIP_2FA_2026.pdf" target="_blank" rel="noopener noreferrer" class="link-primary fw-semibold">
                        Guía para configurar la autenticación de dos pasos <strong>(2FA)</strong>
                    </a>
                </div>
            ');
        }
        $this->mixHtml('
            <div class="login" id="login">
                <div class="row align-items-center">
                    <div class="col-5">
                        <img src="img/logo_sefip_2027-login-w.png" width="100%" alt="Logo SEFIP" class="img-responsive mx-auto d-block">
                    </div>
                    <input type="hidden" id="camposAyuda" value="" />
                    <input type="hidden" id="ultimoCampo" value="" />
                    <input type="hidden" id="pantallaAyuda" value="" />

                    <div class="col-7">
                        ' . $form->ponForm() . '
                    </div>
                    
                </div>
            </div>
            <contenedorScript></contenedorScript>
        ');
        $this->mixScript('

            $("#show-pass").mousedown(function(){
                $("#pass").attr("type","text");
            }).mouseup(function(){
                $("#pass").attr("type","password");
            }).mouseout(function(){
                $("#pass").attr("type","password");
            });
            $("#recuperaPassword").click(function(e){
                e.preventDefault();
                comunica("srv/Controlador.php", {metodo:"recuperaPassword"});
            });
            $("#claveUr").keypress(function(event){
                        var anularTecla = false;
                        var_per = [0,8];
                        var esPermitido = var_per.indexOf( event.which ) >= 0;
                        anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                        if( anularTecla ) event.preventDefault();
                    });

            $("#siimage,#refrescaCaptcha").click(function(){
                $("#siimage").attr("src","./lib/captcha/securimage_show.php?sid=" + Math.random() );
                $("#captcha").select();
            });
            Mousetrap.bind("up up down down left right left right b a enter", function() { comunica("mga/loginSuper.php"); });
            setTimeout( function() { $( "#claveUr" ).focus() }, 300 );

            $("#select").selectpicker();

        ');
    }

    private function loguear()
    {
        if (ServicioUsuario::sesionBloqueada()) {
            return false;
        }
        
        $this->revisaCapcha();
        $gpo = substr((string) $this->getPrm('claveUr','000'), 0, 1);
        $ur = substr((string) $this->getPrm('claveUr','000'), 1, 2);
        $servicioUsuario  = new ServicioUsuario();
        $usuarioEncontrado = $servicioUsuario->logueaUsuario(
            $gpo,
            $ur,
            $this->prm->usuario, 
            $this->prm->pass
        );

        if ($usuarioEncontrado === 2) {
            $this->mixScript('
                notify("USUARIO INACTIVO", "warning");
                $("#siimage").attr("src","./lib/captcha/securimage_show.php?sid=" + Math.random() );
                $("#captcha").empty();
                setTimeout( function() {
                    $( "#captcha" ).val("");
                    $( "#claveUr" ).focus()
                }, 300 );
            ');
            return;
        }

        if ($usuarioEncontrado === 3) {
            $this->mixScript('
                notify("INACTIVO POR CORREO NO INSTITUCIONAL", "warning");
                $("#siimage").attr("src","./lib/captcha/securimage_show.php?sid=" + Math.random() );
                $("#captcha").empty();
                setTimeout( function() {
                    $( "#captcha" ).val("");
                    $( "#claveUr" ).focus()
                }, 300 );
            ');
            return;
        }

        if (!$usuarioEncontrado){
            $this->mixScript(' notify("LOS DATOS NO COINCIDEN", "warning");
                $("#siimage").attr("src","./lib/captcha/securimage_show.php?sid=" + Math.random() );
                $("#captcha").empty();
                setTimeout( function() {
                    $( "#captcha" ).val("");
                    $( "#claveUr" ).focus()
                }, 300 );
            ');
            return;
        }


        if (CAMBIO_PASSWORD_OBLIGATORIO
            && $servicioUsuario->passwordCaducado()
            && !$servicioUsuario->esMaster()
        ) {
            $_SESSION[_S]['expired_data'] = $servicioUsuario->getDatosUsuario();
            $this->formActualizaPassword();
            return;
        }
        // OBTENEMOS EL SERVIDOR DEFAULT PARA ARMAR CORRECTAMENTE LA VARIABLE EJERCICIO
        // ------------------------------------------------------------------------------------------------
        $varPaso = $this->getServerDefault();
        // LA VARIABLE EJERCICIO NECESITA QUE LA VARIABLE SERVERDEFAULT EN ACCESODATOS.PHP EMPIECE EN
        // SINPRES, SEGUIDA POR EL EJERCICIO EN CUATRO DÍGITOS, Y LUEGO LO QUE QUIERAN
        if (strtoupper(substr($varPaso, 0, 5)) != "SEFIP") {
            $_SESSION[_S]['ejercicio'] = date('Y');
        } else {
            $_SESSION[_S]['ejercicio'] = substr($varPaso, 5);
        }

        require_once dirname(__FILE__) . '/ClasificacionAdministrativaB.php';
        $servicioUsuario->generaDatosSesion();
        $this->creaPermisos();
        $servicioUsuario->sesionueUsuario($gpo, $ur);
        $servicioUsuario->guardaBitacoraLogueo($gpo, $ur);

        if ($_SESSION[_S]['prf_id'] == 'P') {
            $_SESSION[_S]['programador-id'] = $usr['usr_id'];
            $this->modulosProgramador();
        } else {
            $usr = '';
            $data = $_SESSION[_S]['usr_id'];
            $usr = new AccesoDatos();
            $usr->conectar();
            $sql = "SELECT usr_telefono, usr_Bt
                FROM ctrlUsuario.cat_Usuario
                where usr_id = :usr_id";
            $usr->setSql($sql);
            $params =[
                    ':usr_id' =>$data
                ];
            $usr->consultar($params);
            $row = $usr->extraer();
            $phone = $row['usr_telefono'];
            $banderiux = $row['usr_Bt'];
            if (!$this->usuarioRequiereDobleFactor($banderiux)) {
                $this->elegirSistema();
            } elseif (empty($phone) && empty($_SESSION[_S]['mail'])) {
                $this->mixScript('comunica("srv/Controlador.php", {metodo:"login"});');
                $this->alerta('EL USUARIO CON EL QUE INTENTA INGRESAR NO TIENE NÚMERO TELEFONICO NI CORREO ELECTRÓNICO ASIGNADO', 'error');
            } elseif ($servicioUsuario->esMaster()) {
                $this->elegirSistema();
            } else {
                $this->ingresaToken();
            }
        }
    }

    private function creaPermisos()
    {
        $con = new AccesoDatos();
        $con->conectar();
        // consulta donde se recuperan los permisos
        $con->setSql('
            SELECT sis_id, men_id, opc_id, tar_id
            FROM cat_PermisosUsuarios p
            INNER JOIN cat_Menu m USING (sis_id, men_id)
            WHERE m.sts_id = 1 AND p.usr_id = ' . $_SESSION[_S]['usr_id'] . '
        ');
        $con->consultar();
        // se inicializa la variable donde se estara almacenando cada permiso
        while ($rw = $con->extraer()) {
            $_SESSION[_S]['prm'][$rw['sis_id']][$rw['men_id']][$rw['opc_id']][$rw['tar_id']] = 1;
        }

        if (date("Y") > APP_EJERCICIO) {
            unset($_SESSION[_S]['prm']['S']);
        }
        $con->desconectar();
    }

    private function formActualizaPassword()
    {
        $formulario = ServicioUsuario::formActualizaPassword();
        $this->setContenedor('login-form');
        $this->mixHtml('<div><ul class = "sefip-modulos">' . $formulario . '</ul><div>');
    }

    private function elegirSistema()
    {
        
        if (date("Y") > APP_EJERCICIO) {
            unset($_SESSION[_S]['prm']['S']);
        }
        $where = "";

        switch (count($_SESSION[_S]['prm'])) {
            case 0 :
                
                $this->alerta('No tiene permisos asignados o la opción a la que tiene acceso no está activa', 'error');
                break;
            case 1:
                
                $mod = array_keys($_SESSION[_S]['prm']);
                $_SESSION[_S]['mod_id'] = $mod[0];
                if (isset($mod[0]) && $mod[0] == "I") {
                    session_destroy();
                    $this->mixScript('
                        $.ajax({
                            data : { accion: "autenticar", ur : "' . $this->prm->claveUr . '".toUpperCase(), user : "' . $this->prm->usuario . '".toUpperCase(), passw : "' . $this->prm->pass . '".toUpperCase() }
                            , type : "POST"
                            , async : false
                            , timeout : 60000
                            , dataType : "json"
                            , url : "https://qinversion.finanzasoaxaca.gob.mx:9103/SI2018/sesion.data"
                            , success : function(response){
                                if(response.status == "ok"){
                                    $("body").append("<form method=\"post\" id=\"formSI\" action=\"https://qinversion.finanzasoaxaca.gob.mx:9103/SI2018/sesion.data\"> <input type=\"hidden\" name=\"accion\" value=\"iniciar\" /> <input type=\"hidden\" name=\"sesionId\" value=\'"+ response.datos.sesionId +"\' /> </form>");
                                    $("#formSI").submit();
                                }
                            }
                        });
                    ');
                } else {
                    $this->sinpres();
                }
                break;
            default:
                
                $this->setSql('SELECT  sis_id, sis_siglas, sis_nombre
                            FROM cat_PermisosUsuarios usu
                            INNER JOIN cat_Sistema USING (sis_id)
                            INNER JOIN cat_Menu using (sis_id)
                            WHERE sts_id = 1 AND usu.usr_id = ' . $_SESSION[_S]['usr_id'] . $where . '
                            GROUP BY sis_id;
                ');
                $this->conectar();
                $this->consultar();
                $this->desconectar();
                $botones = '';
                $inversion = 'https://qinversion.finanzasoaxaca.gob.mx:9103/SI2018/sesion.data';

                if ($_SERVER['SERVER_ADDR'] == '172.17.90.17') {
                    $inversion = 'https://inversion.finanzasoaxaca.gob.mx:9103/SI2018/sesion.data';
                }

                while ($sis = $this->extraer()) {
                    $botones .= '<li id ="sis21_' . $sis['sis_siglas'] . '" accesskey="' . $sis['sis_id'] .
                    '" class="modulo-' . strtolower($sis['sis_siglas']) . '"><a href="#"><span>' .
                        $sis['sis_siglas'] . '</span>' . $sis['sis_nombre'] . '</a></li>';
                    if ($sis['sis_id'] == 'I') {
                        $this->mixScript('
                            $("#sis21_' . $sis['sis_siglas'] . '").click(function() {
                                $.ajax({
                                    data : { accion: "autenticar", ur : "' . $this->prm->claveUr . '".toUpperCase(), user : "' . $this->prm->usuario . '".toUpperCase(), passw : "' . $this->prm->pass . '".toUpperCase() }
                                    , type : "POST"
                                    , async : false
                                    , timeout : 60000
                                    , dataType : "json"
                                    , url : "' . $inversion . '"
                                    , success : function(response){
                                        if(response.status == "ok"){
                                            $("body").append("<form method=\"post\" id=\"formSI\" action=\"' . $inversion . '\"> <input type=\"hidden\" name=\"accion\" value=\"iniciar\" /> <input type=\"hidden\" name=\"sesionId\" value=\'"+ response.datos.sesionId +"\' /> </form>");
                                            $("#formSI").submit();
                                        }
                                    }
                                });
                            });
                        ');
                    } else {
                        
                        $this->mixScript('
                            $("#sis21_' . $sis['sis_siglas'] . '").click(function() {
                                comunica("srv/Controlador.php", {metodo:"seleccionaSistema", sis_id:"' . $sis['sis_id'] . '"});
                            });
                        ');

                    }
                }
                $this->setContenedor('login-form');
                $this->mixHtml('<div><ul class = "sefip-modulos">' . $botones . '</ul><div>');

                break;
        }
    }

    private function ingresaToken()
    {
        $telegram = new Telegram();
        $r = $telegram->send_2fa_code();
        if (!$r['success'] && !$this->isTelegramMandatoryError($r)) {
            /** No cumple las reglas para enviar por telegram, se procede a enviar por correo-electrónico */
            $r = $this->sendVerificationCode($r['code'], $telegram);
        } elseif (!$r['success'] && $this->isTelegramMandatoryError($r)) {
            $r['message'] = 'No fue posible enviar el código por Telegram. Verifique que su cuenta esté activa y vuelva a intentarlo.';
        }

        $captcha_validado = $this->prm->captcha;
        $token = '
            <div style="position:relative">
                <div class="" role="alert">
                    <h4 class="alert-heading">¡Bienvenido!</h4>
                </div>
                <form id="frmTelegram" method="post" action="">
                    <div class="mb-3">
                        <label for="telegram" class="form-label">'.$r['message'].'</label>
                        <div class="input-group mb-3" >
                            <input type="text" tabindex="4" class="form-control" id="Intelegram" name="telegram" placeholder="Código de seguridad" maxlength="6" required>
                            <input type="hidden" id="captcha_pasado" value="' . htmlspecialchars($captcha_validado) . '">
                        </div>
                        <button  id="btnIngresar"  type="button" class="btn btn-success col-md-12">Ingresar</button>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="reenvio-texto">Reenviar código en:&nbsp;</span>
                            <span id="countdown-timer" data-segundos-iniciales="180">03:00</span>
                            <button tabindex="5" aria-label="Reenviar" id ="reenviar" type="submit" class="btn btn-login col-md-12" disabled>Reenviar código</button>
                        </div>
                    </div>
                    <button tabindex="5" id = "btnCancel" aria-label="Cerrar sesión" type="submit" class="btn btn-login col-md-12">Cancelar</button>
                </form>
            </div>
        ';
        $this->mixScript('
            var elementoContador = document.getElementById("countdown-timer");
            var segundosIniciales = parseInt(elementoContador.getAttribute("data-segundos-iniciales"));
            var segundosRestantes = segundosIniciales;
            var btnReenviar = document.getElementById("reenviar");
            var intervalo;
            const ENDPOINT = "srv/Telegram.php";

            function actualizarContador() {
                if (segundosRestantes <= 0) {
                    clearInterval(intervalo);
                    btnReenviar.disabled = false;
                    btnReenviar.addEventListener("click", solicitarNuevoCodigo);
                    return;
                }
                var minutos = Math.floor(segundosRestantes / 60);
                var segundos = Math.floor(segundosRestantes % 60);
                elementoContador.innerHTML = String(minutos).padStart(2, "0") + ":" + String(segundos).padStart(2, "0");
                segundosRestantes--;
            }
            
            function solicitarNuevoCodigo(event) {
                event.preventDefault();
                btnReenviar.disabled = true;
                btnReenviar.removeEventListener("click", solicitarNuevoCodigo);
    
                comunica("srv/Controlador.php", { metodo: "reenviarCodigo2FA" });
    
                segundosRestantes = segundosIniciales;
                actualizarContador();
                intervalo = setInterval(actualizarContador, 1000);
            }
            
            function runVerifyCode() {
                event.preventDefault();
                const inputCode = document.getElementById("Intelegram").value;
            
                if (!inputCode) {
                    updateStatus("Por favor, introduce el código de verificación.", "error");
                    return;
                }

                const datosParaControlador = {
                    metodo: "procesarVerificacion2FA",
                    input_code: inputCode,
                    claveUr: "' . addslashes($this->prm->claveUr) . '",
                    usuario: "' . addslashes($this->prm->usuario) . '",
                    pass: "' . addslashes($this->prm->pass) . '",
                    captcha: document.getElementById("captcha_pasado").value
                };
    
                comunica("srv/Controlador.php", datosParaControlador);
            }
        
            function bloquearDespuesDelClic() {
                btnReenviar.disabled = true;
                btnReenviar.innerHTML = "Código enviado"; 
            }

            actualizarContador();

            var intervalo = setInterval(actualizarContador, 1000);

            $("#btnCancel").on("click", function(e) {
                e.preventDefault();
                comunica("srv/Controlador.php", {metodo:"login"});
            });

            document.getElementById("btnIngresar").addEventListener("click", runVerifyCode);
        ');
        $this->setContenedor('login-form');
        $this->mixHtml('<div><ul class = "sefip-modulos">' . $token . '</ul><div>');
    }
    private function procesarVerificacion2FA()
    {
        $tel = new Telegram();
        $inputCode = $this->prm->input_code ?? '';
        $resultado_verificacion = $tel->verify_2fa_code($inputCode);
        if ($resultado_verificacion['success']) {
            $this->elegirSistema();
        } else {

            $this->alerta('ERROR EN EL CÓDIGO INGRESADO,SOLO ES VALIDO 3 MINUTOS O ASEGURESE QUE ES EL MISMO QUE LLEGO A SU CUENTA DE TELEGRAM,', 'error');

        }
    }

    private function reenviarCodigo2FA()
    {
        $telegram = new Telegram();
        $r = $telegram->send_2fa_code();
        if (!$r['success'] && !$this->isTelegramMandatoryError($r)) {
            /** No cumple las reglas para enviar por telegram, se procede a enviar por correo-electrónico */
            $r = $this->sendVerificationCode($r['code'], $telegram);
            if (!$r['success']) {
                $this->alerta('No fue posible reenviar el código de verificación.', 'error');
            }
        } elseif (!$r['success'] && $this->isTelegramMandatoryError($r)) {
            $this->alerta('No fue posible reenviar el código por Telegram. Verifique su cuenta e intente nuevamente.', 'warning');
        }
    }

    private function isTelegramMandatoryError(array $response): bool
    {
        $httpCode = $response['http_code'] ?? null;
        return (int) $httpCode === 409;
    }

    private function bandera2FAActiva($bandera): bool
    {
        return $bandera !== null && (int) $bandera === 1;
    }

    private function usuarioRequiereDobleFactor($banderaUsuario): bool
    {
        if (!DOBLE_FACTOR_DISPONIBLE) {
            return false;
        }

        return $this->bandera2FAActiva($banderaUsuario);
    }

    private function modulosProgramador()
    {
        $this->setContenedor('login-form');
        $this->mixHtml('<div class = "row">');
        $this->mixHtml('
            <button aria-label="modulo de control del sistema" id ="sis21_mcs" type="button" accesskey="x" class="btn black col-md-6 sis">
                <h1>MCS</h1>
                <small>MODULO DE CONTROL DEL SISTEMA</small>
            </button>
        ');
        $this->mixScript('$("#sis213_mcs").click(function(){ comunica("srv/Controlador.php",{metodo:"seleccionaSistema",sis_id:"mcs" }); });');
        $this->mixHtml('</div>');
    }

    private function getModulos($sis)
    {
        $active = ($_SESSION[_S]['mod_id'] == $sis['sis_id'] ? " active " : '');
        $mod = $_SESSION[_S]['mod_id'];
        $siglas = strtolower($sis['sis_siglas']);
        $title = strtoupper($sis['sis_siglas']);
        $sis = $sis['sis_id'];

        $modulo = <<<HTML
            <div id="sis23_{$siglas}" rel="{$sis}"
                class="{$active} navbar-item item-{$siglas}">
                <a href="#" class="navbar-link d-flex align-items-center">
                    <span class="d-lg-inline d-md-none">{$title}</span>
                </a>
            </div>
        HTML;

        $script = <<<JS
            $("#sis23_{$siglas}").click(function() {
                if (!$(this).is(".active")) {
                    if ($("#mainContainer").length) {
                        if ($("body").hasClass("panel-expand")) {
                            $("body").removeClass("panel-expand");
                        }
                        $("#mainContainer").tooltip("dispose");
                        $("#mainContainer").closest(".panel").remove();
                        $(window).trigger("panelCerrado");
                    }
                    comunica("srv/Controlador.php", {
                        metodo:"refrescaMenu",
                        sis_id: "{$sis}"
                    });
                }
                else {
                    console.log("ya estas aqui");
                }
            });
        JS;
        return ['html' => $modulo, 'script' => $script];
    }

    private function sinpres()
    {
        if (!sesionActiva()) {
            $this->sesionExpirada();
            return;
        }

        unset($_SESSION['intentos']);
        require_once dirname(__FILE__) . '/ClasificacionAdministrativaB.php';
        $this->mixScript(' $("#login_css").remove(); ');
        $this->setContenedor('cuerpoSinpres');

        // opciones de sistemas/modulos a las que puede acceder
        $this->setSql("
            SELECT  sis_id, sis_siglas, sis_nombre, sis_color
            FROM cat_PermisosUsuarios usu
            INNER JOIN cat_Sistema USING(sis_id)
            INNER JOIN cat_Menu using (sis_id)
            WHERE sts_id =1 AND usu.usr_id = :usr_id
            GROUP BY sis_id;
        ");
        $this->conectar();
        $this->consultar(['usr_id' => $_SESSION[_S]['usr_id']]);
        $this->desconectar();

        $modulos = '';
        $scriptModulos = '';
        while ($sis = $this->extraer()) {
            $modulo = $this->getModulos($sis);
            $modulos .= $modulo['html'];
            $scriptModulos .= $modulo['script'];
        }
        $this->mixScript($scriptModulos);
        $strRaiz = dirname($_SERVER['PHP_SELF']) . "/../";

        $men = (isset($_SESSION[_S]['mst']) && $_SESSION[_S]['mst'] == true);

        //Opción de mensajes.
        if ($this->file) {
            if ($this->notificaSinpres()) {
                $this->mixScript(ControladorMensajes::consultaMensajes());
            }
        }

        //Finalización de mensajes
        $params = [
            'men' => $men,
            'strRaiz' => $strRaiz,
            'modulos' => $modulos,
            'nuevoUsuario' => (in_array((string) ($_SESSION[_S]['ur_clave'] ?? ''), ['115', '116'], true)
                || (int) ($_SESSION[_S]['nvj_id'] ?? 0) > 2),
        ];
        $html = $this->getSinpresHtml($params);
        $this->mixHtml($html);
        $this->mixStyle('
            #stickyBalanzaDetalle{min-width: 98%;background:none repeat scroll 0 0 rgba(246,246,246,0.85);border-radius:3px 3px 3px 3px;border:1px solid #a7a7a7;box-shadow:0 2px 16px #000, 0 0 1px #000, 0 0 1px #000;-o-box-shadow:0 2px 16px #000, 0 0 1px #000, 0 0 1px #000;-webkit-box-shadow:0 2px 16px #000, 0 0 1px #000, 0 0 1px #000;-moz-box-shadow:0 2px 16px #000, 0 0 1px #000, 0 0 1px #000;text-shadow:0 0 15px #fff, 0 0 15px #fff;-moz-box-sizing:border-box;margin-left:1%;margin-right:1%;margin-bottom:1%;padding:2px}
            #stickyBalanzaDetalle.stick{position:fixed;top:3px;z-index:10000;border-radius:0 0 .5em .5em}
        ');
        $scriptSinpres = $this->getSinpresScript();
        $this->mixScript($scriptSinpres);
        $this->menu();
    }

    public function menu()
    {
        if (!sesionActiva()) {
            $this->sesionExpirada();
            return;
        }

        $where = "";
        if (isset($this->prm->sis_id)) {
            $_SESSION[_S]['mod_id'] = $this->prm->sis_id;
        }
        if (date('Y-m-d H:i:s') < APP_EJERCICIO . '-01-01 00:00:00' && $_SERVER['SERVER_ADDR'] == '172.17.90.88' && !isset($_SESSION[_S]['mst'])) {
            $where = " AND men_id<>7";
        }
        $this->conectar();
        if (empty($_SESSION[_S]['programador-id']) || $_SESSION[_S]['mod_id'] != 'mcs') {
            $this->setSql('
                SELECT DISTINCT sis_id,sis_siglas,men_id, men_orden, opc_orden, men_imagen, men_url, men_icono,
                    opc_id, opc_imagen, opc_url, men_titulo, opc_titulo, opc_icono, tar_id
                FROM cat_PermisosUsuarios perm
                JOIN cat_Sistema USING(sis_id)
                JOIN cat_Menu m USING(sis_id,men_id)
                JOIN cat_Opcion o USING(sis_id,men_id,opc_id)
                WHERE sis_id="' . $_SESSION[_S]['mod_id'] . '" AND perm.usr_id=' . $_SESSION[_S]['usr_id']
                . ' AND m.sts_id=1 AND o.sts_id=1 #and sis_id != "X"
                ' . $where . '
                ORDER BY men_orden,opc_orden;
            ');
        } else {
            $this->creaPermisos();
            $this->setSql('
                SELECT DISTINCT sis_id,sis_siglas,men_id, men_imagen, men_url, men_icono, opc_id,
                    opc_imagen, opc_url, men_titulo, opc_titulo, opc_icono
                FROM cat_Sistema
                INNER JOIN cat_Menu m  USING(sis_id)
                INNER JOIN cat_Opcion o USING(sis_id,men_id)
                WHERE sis_id = "Z"
                ORDER BY men_id,opc_id;
            ');
        }
        $this->consultar();
        $this->desconectar();
        $_SESSION[_S]['prm'][$_SESSION[_S]['mod_id']] = array();
        $menus = array();
        $menu = new stdClass();
        $m = '';
        $o = '';
        $sistema = '';
        $sis_id = '';

        while ($rw = $this->extraer()) {
            $sistema = $rw['sis_siglas'];
            $sis_id = $rw['sis_id'];

            if ($m != "_" . $rw['men_id']) {
                if ($m != '') {
                    $menus[$m] = $menu;
                    $menu = new stdClass();
                }
                $m = "_" . $rw['men_id'];
                $menu->texto = ucwords(mb_strtolower($rw['men_titulo'], 'UTF-8'));
                $menu->id = "_" . $rw['men_id'];
                $menu->icono = $rw['men_icono'];
                $menu->opciones = array();
                $o = '';
            }
            if ($o != $rw['opc_id']) {
                $opcion = new stdClass();
                $opcion->id = "_" . $rw['opc_id'];
                $opcion->controlador = $rw['opc_url'];
                $opcion->folder = $rw['men_url'];
                $opcion->texto = ucwords(mb_strtolower($rw['opc_titulo'], 'UTF-8'));
                $opcion->img = $rw['opc_imagen'];
                $opcion->icono = $rw['opc_icono'];
                $menu->opciones["_" . $rw['opc_id']] = $opcion;
                $o = $rw['opc_id'];
            }
            if (empty($_SESSION[_S]['programador-id']) || $_SESSION[_S]['mod_id'] != 'mcs') {
                $_SESSION[_S]['prm'][$_SESSION[_S]['mod_id']][$rw['men_id']][$rw['opc_id']][$rw['tar_id']] = 1;
            }
        }

        if (isset($menu->texto)) {
            $menus[$m] = $menu;
        }

        $this->conectar();
        $andPrm = '';
        if ($_SESSION[_S]['nvj_id'] <= 2) {
            switch ($_SESSION[_S]['prf_id']) {
                case 'S':
                    $andPrm = ' AND omo_id="C" ';
                    break;
                case 'U':
                    $andPrm = ' AND omo_id="D" ';
                    break;
                default:
                    $andPrm = ' AND omo_id="A" ';
                    break;
            }

            $this->setSql('
                SELECT omo_id, mmd_id
                FROM cat_PermisosUrModificaciones
                WHERE gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'] . ' ' . $andPrm . ' AND sts_id = 1
            ');
        }

        $_SESSION[_S]['modulo'] = strtolower($sistema);
        $sistemaNombre = ucwords(mb_strtolower($this->obtenerNombreSistema($sis_id), 'UTF-8'));
        $sistema = date("Y-m-d H:i:s") <= APP_EJERCICIO.'-01-01 00:00:00' && ($sistema == 'SCG' || $sistema == 'SCP' || $sistema == 'TES') && $_SERVER['SERVER_ADDR'] == '172.17.90.88'  && !$_SESSION[_S]['mst'] ? '' : $sistema;
 
        $sistemaNombre = $sistema == '' ? '' : $sistemaNombre;
        $js = <<<JS
            $('#sefipSidebar').empty();
            $('.navbar-sefip .active').removeClass('active');
            $('#sis23_{$_SESSION[_S]['modulo']}').addClass('active');
            $('#sidebar').removeClass();
            $('#sidebar').addClass('app-sidebar {$_SESSION[_S]['modulo']}');
            sistema = $('<div/>').addClass('menu');
            sistema.append(
                $('<div/>').addClass('menu-profile').append(
                    $('<a/>').addClass('menu-profile-link').html(`
                        <div class="menu-profile-cover with-shadow"></div>
                        <div class="menu-profile-info">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    {$sistema}
                                </div>
                            </div>
                            <small>{$sistemaNombre}</small>
                        </div>
                    `)
                )
            );
        JS;

        $js2 = 'window._sefipMenuActions = window._sefipMenuActions || {};';
        if ($sistema !== '') {
            foreach ($menus as $menu) {
                $js2 .= "
                    menu = $('<div/>').addClass('menu-item has-sub').append(
                        $('<a/>').addClass('menu-link')
                            .attr('href', 'javascript:;')
                            .html(`
                            <div class='menu-icon'>
                                <i class='demo-icon $menu->icono'></i>
                            </div>
                            <div class='menu-text'>$menu->texto</div>
                            <div class='menu-caret'></div>`)
                    );
                    opciones = $('<div/>').addClass('menu-submenu');
                ";

                foreach ($menu->opciones as $opcion) {
                    $opcionId = $sis_id . $menu->id . $opcion->id;
                    $menId = str_replace('_', '', $menu->id);
                    $opcId = str_replace('_', '', $opcion->id);

                    $click = "
                        prepararOpcion($(this));
                        globalParametrosComunica = {
                            sis_id: '$sis_id',
                            men_id: '$menId',
                            opc_id: '$opcId',
                            claveOpcion: '$opcionId'
                        };
                    ";

                    $click .= "
                        creaMainContainer();
                        $('#toggleCA').show();
                         actualizaBreadcrumbs(" . json_encode($menu->texto) . ", " . json_encode($opcion->texto) . ");
                    ";

                    $folderForUrl = trim((string) $opcion->folder);
                    $scriptFile = (string) $opcion->controlador;
                    if ($scriptFile !== '' && !str_ends_with($scriptFile, '.php')) {
                        $scriptFile .= '.php';
                    }
                    $moduloFs = dirname(__FILE__) . '/../' . $_SESSION[_S]['modulo'];
                    $fsPath = ($folderForUrl !== '')
                        ? $moduloFs . '/' . $folderForUrl . '/' . $scriptFile
                        : $moduloFs . '/' . $scriptFile;
                    if ($scriptFile !== '' && !is_file($fsPath)
                        && is_file($moduloFs . '/complementos/' . $scriptFile)
                    ) {
                        $folderForUrl = 'complementos';
                    }

                    if ($sis_id == 'S' || $folderForUrl !== '') {
                        $click .= "comunica('" . $_SESSION[_S]['modulo']
                            . "/$folderForUrl/$scriptFile');";
                    } else {
                        $click .= "comunica('" . $_SESSION[_S]['modulo']
                            . "/$scriptFile');";
                    }

                    $js2 .= "
                        window._sefipMenuActions['$opcionId'] = function() {
                            $click
                        };
                        var opcion = $('<div/>').addClass('menu-item').append(
                            $('<a/>').addClass('menu-link').attr('href','javascript:;')
                            .prop('id', '$opcionId')
                            .attr('data-sefip-opc-id', '$opcionId')
                            .html(`
                                <div class='menu-icon'>
                                    <i class='demo-icon $opcion->icono'></i></div>
                                    <div class='menu-text'>$opcion->texto</div>
                                </div>
                            `)
                        );
                        opciones.append(opcion);
                    ";
                }
                $js2 .= "
                menu.append(opciones);
                sistema.append(menu);";
            }
        }

        $clasAdmin = ClasificacionAdministrativa::ponClasificacionAdministrativa();

        //Separamos el codigo html y JS:
        $p = strpos($clasAdmin, '<script');
        $htmlCA = substr($clasAdmin, 0, $p);
        $htmlCA = str_replace(array("\n", "\r", "\t"), '', $htmlCA);

        $jsCA = substr($clasAdmin, $p + 1);
        $jsCA = substr($jsCA, strpos($jsCA, '>') + 1);
        $p = strpos($jsCA, '</script>');
        $jsCA = substr($jsCA, 0, $p);

        $poaScrip = '';
        if ($sis_id == 'A') {
            $poaScrip = "
                techos = $('<button/>').addClass('btn btn-outline-secondary btn-xs m-1')
                    .attr('aria-label','Techos presupuestales');
                    techos.attr('id', 'techos');
                    techos.html('<i class=\"fa fa-usd\" aria-hidden=\"true\"></i>');
                phb.append(techos);";
        }
        $js .= <<<JS
            function creaMainContainer() {
                if (!$('#mainContainer').length) {
                    $('#divMensajes').hide();
                    mc = $('<div/>').addClass('panel');
                    mc.attr('id', 'mainContainer');
                    ph = $('<div/>').addClass('panel-heading display-flex flex-column flex-sm-row').append($('<div/>'));
                    phb = $('<div/>').addClass('panel-heading-btn');
                    btca = $('<button/>').addClass('btn btn-outline-secondary btn-xs m-1').attr('aria-label', 'toggleCa');
                    btca.attr('id', 'toggleCA');
                    btca.html('<i class="fa fa-th-list" aria-hidden="true"></i>');
                    {$poaScrip}
                    pex = $('<a/>').addClass('btn btn-xs btn-icon btn-circle btn-default').prop('id' ,'main_expand_tool');
                    pex.attr('href', 'javascript:;');
                    pex.attr('data-toggle', 'panel-expand');
                    pex.html('<i class="fa fa-expand"></i>');
                    prm = $('<a/>').addClass('btn btn-xs btn-icon btn-circle btn-danger');
                    prm.attr('href', 'javascript:;');
                    prm.attr('data-toggle', 'panel-remove');
                    prm.html('<i class="fa fa-times"></i>');

                    tools = $('<div/>').addClass('tools panel-title d-flex flex-column flex-sm-row');
                    tools.attr('id', 'toolsPantalla');
                    tools.attr('style', 'min-height: 32px;');

                    phb.append(btca);
                    phb.append(pex);
                    phb.append(prm);

                    ph.append(phb);
                    ph.prepend(tools);

                    cac = $('<div/>').attr('id', 'contenedorClasAdminCombos');
                    cac.addClass('cabecera bg-white p-3');
                    cac.attr('style', 'position: relative;');
                    casc = $('<div/>').addClass('clasAdminCombos col-md-12');
                    casc.attr('id', 'clasAdminSeccionCombos');
                    casc.html('{$htmlCA}');
                    cac.append(casc);
                    cac.append($('<div/>').addClass('clasAdminCombos col-md-12').attr('id', 'muestraTechos'));
                    cont = $('<div/>').addClass('panel-body');
                    cont.attr('id', 'contenedor');
                    balanzaDetalle = $('<div/>').addClass('col-md-12 sombra-5');
                    balanzaDetalle.attr('id', 'contenedorBalanzaDetalle');
                    balanzaDetalle.attr('style', 'display:none;opacity: 0.93;background-color: #fff');
                    mc.append(ph);
                    mc.append(cac);
                    mc.append(cont);
                    mc.append(balanzaDetalle);
                    $('#content').append(mc);
                    {$jsCA}
                    $('#toggleCA, #toggleCAx').click(function() {
                        $('#clasAdminSeccionCombos').toggle('fast');
                        if ($('#clasAdminSeccionCombos').css('display') == 'none'
                            || $(this).prop('id')=='toggleCAx'
                            || $('#contenedorClasAdminCombos').hasClass('shadow')
                        ) {
                            $('#contenedorClasAdminCombos').removeClass('shadow');
                        } else {
                            $('#contenedorClasAdminCombos').addClass('shadow');
                        }
                    });
                    $('#muestraTechos').hide();
                    $('#techos').click(function() {comunica('srv/ControladorMuestraTechos.php');});
                    $('#clasAdminSeccionCombos').toggle();
                }
            }

            function actualizaBreadcrumbs(menuText, opcionText) {
                var menuCrumb = $('#breadcrumbMenu');
                var opcionCrumb = $('#breadcrumbOpcion');
                if (menuText) {
                    menuCrumb.text(menuText).show();
                } else {
                    menuCrumb.text('').hide();
                }
                if (opcionText) {
                    opcionCrumb.text(opcionText).show();
                } else {
                    opcionCrumb.text('').hide();
                }
            }

            function prepararOpcion(e) {
                let id = e.id || 'X_0_0';
                $('.menu-link').removeClass('active');
                $('#'+id).addClass('active');
                $('div[aria-describedby=cargaDatosProgFin]').html('');
                $('div[aria-describedby=cargaDatosMayor]').html('');
                $('div[aria-describedby=cargaDatosCuentas]').html('');
                $('div[aria-describedby=cargaDatosProveedor]').html('');
                $('.cabecera').show();
            }
            function runSefipMenuOption(id, element) {
                if (typeof hideComboSection === 'function') {
                    hideComboSection();
                }
                if (window._sefipMenuActions && typeof window._sefipMenuActions[id] === 'function') {
                    window._sefipMenuActions[id].call(element || null);
                }
                return false;
            }
            // Handler delegado en document para que las opciones del menú
            // funcionen tanto en el sidebar expandido como en el float
            // submenu que aparece cuando el sidebar está compactado. El
            // template (Color Admin) clona el submenú con .html(), lo que
            // pierde los binds directos pero conserva los atributos como
            // data-sefip-opc-id, por lo que el handler delegado sí lo
            // detecta sobre los clones.
            $(document).off('click.sefipMenu', 'a.menu-link[data-sefip-opc-id]');
            $(document).on('click.sefipMenu', 'a.menu-link[data-sefip-opc-id]', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-sefip-opc-id');
                runSefipMenuOption(id, this);
                if ($('#app').hasClass('app-sidebar-minified')) {
                    $('#app-sidebar-float-submenu').remove();
                }
                return false;
            });
            $js2
            $(window).on('panelCerrado', function() {
                $('#otraInfoFooter').text($('#otraInfoFooter').attr('original'));
                $('#divMensajes').show();
            });
            sistema.append($('<div/>').addClass('menu-item d-flex').html(`
                <a href="javascript:;" class="app-sidebar-minify-btn ms-auto" data-toggle="app-sidebar-minify">
                    <i class="fa fa-angle-double-left"></i>
                </a>
            `));
            $('#sefipSidebar').append(sistema);
            if (typeof started === 'undefined') {
                App.init()
                var started = true;
            }
        JS;
        $this->setCss('fonts/' . $_SESSION[_S]['modulo'] . '/css/' . $_SESSION[_S]['modulo'] . '.css');
        $this->mixScript($js);
    }

    private function tonoMensajeSistema(int $color): string
    {
        switch ($color) {
            case 1:
                return 'sefip-modal-mensaje--success';
            case 2:
                return 'sefip-modal-mensaje--warning';
            case 3:
                return 'sefip-modal-mensaje--danger';
            default:
                return 'sefip-modal-mensaje--info';
        }
    }

    private function mensajeAplicaAlModulo(int $sistema): bool
    {
        switch ($sistema) {
            case 11:
            case 12:
                return in_array($_SESSION[_S]['mod_id'], ['C', 'S', 'A'], true);
            case 13:
                return $_SESSION[_S]['mod_id'] === 'A';
            case 14:
                return $_SESSION[_S]['mod_id'] === 'S';
            case 15:
                return $_SESSION[_S]['mod_id'] === 'C';
            default:
                return false;
        }
    }

    private function linksArchivosMensaje(int $smeId): string
    {
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql(' SELECT sam_nombre,sam_tipo,sam_descripcion,sam_rutaArchivo FROM ' . BD_SINPRES . '.sis_ArchivoMensaje WHERE sme_id = ' . $smeId . '; ');
        $con->consultar();
        $con->desconectar();

        $links = '';
        while ($a = $con->extraer()) {
            switch ($a['sam_tipo']) {
                case 'txt':
                    $tipo = 'txt';
                    break;
                case 'pdf':
                    $tipo = 'pdf';
                    break;
                case 'doc':
                case 'docx':
                    $tipo = 'doc';
                    break;
                case 'xls':
                case 'xlsx':
                    $tipo = 'xls';
                    break;
                default:
                    $tipo = 'img';
                    break;
            }
            $href = ($a['sam_rutaArchivo'] === '' || $a['sam_rutaArchivo'] === null)
                ? 'archivos/adjuntosMensajes/' . $a['sam_nombre'] . '.' . $a['sam_tipo']
                : $a['sam_rutaArchivo'];
            $links .= '<li class="sefip-modal-mensaje-adjunto-item">'
                . '<a class="sefip-modal-mensaje-adjunto-link" target="_blank" href="' . $href . '">'
                . '<img alt="' . htmlspecialchars($a['sam_nombre'], ENT_QUOTES, 'UTF-8') . '" src="img/' . $tipo . '.png" width="24" height="24" class="me-2" />'
                . htmlspecialchars($a['sam_descripcion'], ENT_QUOTES, 'UTF-8')
                . ' <i class="fa fa-download ms-1" aria-hidden="true"></i>'
                . '</a></li>';
        }

        return $links;
    }

    private function mensajesModalDeSistema()
    {
        $this->conectar();
        $this->setSql('
            SELECT sme_id,sme_asunto,sme_Mensaje,sme_color,sme_sistema
            FROM ' . BD_SINPRES . '.sis_Mensaje
            WHERE now() between sme_fechaInicial AND sme_fechaFinal
            AND sme_tipo = "M"
        ');
        $this->consultar();
        $this->desconectar();
        $html = '';
        $contMensajes = 0;

        if ($this->getNumeroRegistros() != 0) {
            while ($r = $this->extraer()) {
                if (!$this->mensajeAplicaAlModulo((int) $r['sme_sistema'])) {
                    continue;
                }

                $contMensajes++;
                $modalId = 'modalMensajes' . $contMensajes;
                $tono = $this->tonoMensajeSistema((int) $r['sme_color']);
                $links = $this->linksArchivosMensaje((int) $r['sme_id']);
                $adjuntos = $links !== ''
                    ? '<div class="sefip-modal-mensaje-adjuntos"><p class="sefip-modal-mensaje-adjuntos-titulo">Archivos adjuntos</p><ul class="sefip-modal-mensaje-adjuntos-lista">' . $links . '</ul></div>'
                    : '';

                $html .= '
                    <div id="' . $modalId . '" class="modal sefip-modal-mensaje" style="display: block;" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content sefip-modal-mensaje-content">
                                <div class="modal-header sefip-modal-mensaje-header ' . $tono . '">
                                    <h4 class="modal-title w-100 text-center">' . htmlspecialchars($r['sme_asunto'], ENT_QUOTES, 'UTF-8') . '</h4>
                                    <button type="button" class="btn-close btn-close-white cerrar cerrar-mensaje-sistema" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body sefip-modal-mensaje-body">' . $r['sme_Mensaje'] . $adjuntos . '</div>
                                <div class="modal-footer sefip-modal-mensaje-footer">
                                    <button type="button" class="btn btn-outline-secondary cerrar cerrar-mensaje-sistema" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>';

                $this->mixScript('
                    $("#' . $modalId . '").modal("show");
                    $("#' . $modalId . ' .cerrar").click(function() {
                        $("#' . $modalId . '").modal("hide");
                    });
                ');
            }
        }

        return $html;
    }

    private function mensajesDeSistema()
    {
        $this->conectar();
        $this->setSql('
            SELECT sme_id,sme_asunto,sme_Mensaje,sme_color,sme_sistema
            FROM ' . BD_SINPRES . '.sis_Mensaje
            WHERE now() between sme_fechaInicial AND sme_fechaFinal
            AND sme_tipo = "N"
            ORDER BY sme_id desc
        ');
        $this->consultar();
        $this->desconectar();
        $html = '';

        if ($this->getNumeroRegistros() != 0) {
            while ($r = $this->extraer()) {
                //Se reinicia band a false en cada iteracion para considerar solo los casos postivos.
                $band = false;
                switch ($r['sme_sistema']) {
                    case 11:
                        if ($_SESSION[_S]['mod_id'] == "C" || $_SESSION[_S]['mod_id'] == "S" || $_SESSION[_S]['mod_id'] == "A") {
                            $band = true;
                        }
                        break;
                    case 12:
                        if ($_SESSION[_S]['mod_id'] == "C" || $_SESSION[_S]['mod_id'] == "S" || $_SESSION[_S]['mod_id'] == "A") {
                            $band = true;
                        }
                        break;
                    case 13:
                        if ($_SESSION[_S]['mod_id'] == "A") {
                            $band = true;
                        }
                        break;
                    case 14:
                        if ($_SESSION[_S]['mod_id'] == "S") {
                            $band = true;
                        }
                        break;
                    case 15:
                        if ($_SESSION[_S]['mod_id'] == "C") {
                            $band = true;
                        }
                        break;
                }

                if ($band) {
                    switch ($r['sme_color']) {
                        case 1:
                            $c = "success";
                            break;
                        case 2:
                            $c = "warning";
                            break;
                        case 3:
                            $c = "error";
                            break;
                    }
                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql(' SELECT sam_nombre,sam_tipo,sam_descripcion,sam_rutaArchivo FROM ' . BD_SINPRES . '.sis_ArchivoMensaje WHERE sme_id = ' . $r['sme_id'] . '; ');
                    $con->consultar();
                    $con->desconectar();
                    $links = '';
                    while ($a = $con->extraer()) {
                        switch ($a['sam_tipo']) {
                            case 'txt':
                                $tipo = 'txt';
                                break;
                            case 'pdf':
                                $tipo = 'pdf';
                                break;
                            case 'doc':
                            case 'docx':
                                $tipo = 'doc';
                                break;
                            case 'xls':
                            case 'xlsx':
                                $tipo = 'xls';
                                break;
                            default:
                                $tipo = 'img';
                                break;
                        }
                        $links .= '<li class="list-group-item primary"><a target="_blank" href="' .
                            (($a['sam_rutaArchivo']) == "" ? 'archivos/adjuntosMensajes/' . $a['sam_nombre'] . '.' . $a['sam_tipo']
                            : $a['sam_rutaArchivo']) . '"><img alt="' . $a['sam_nombre'] . '" src="img/' . $tipo . '.png" width="30px" class="me-2" />' . $a['sam_descripcion'] . ' <span class="glyphicon glyphicon-download"></span></a></li> ';
                    }

                    $html .= '
                            <div class="panel panel-' . $c . '">
                                <div class="panel-heading">
                                    <h3 class="panel-title">' .
                        $r['sme_asunto'] . '</h3>
                                </div>
                                <div class="panel-body">
                                    ' . $r['sme_Mensaje'] . '
                                </div>
                                <ul class="list-group">' . $links . '</ul>
                            </div>
                        ';
                }
            }
        }
        return $html;
    }

    public function tienePantallaAyuda()
    {
        $idPantalla1 = $this->prm->idPantalla;

        $idPantalla = str_replace("_", "", $idPantalla1);

        $sis_id = substr($idPantalla, 0, 1);
        $men_id = substr($idPantalla, 1, 1);
        $opc_id = substr($idPantalla, 2, 1);
        $nvj = $_SESSION[_S]['nvj_id'];

        $this->mixScript('$("#pantallaAyuda").val("' . $idPantalla . '");');

        $this->conectar();
        $this->setSql('SELECT COALESCE( COUNT(*), 0 ) AS registros
            FROM cat_Ayuda
            WHERE sis_id = "' . $sis_id . '" AND men_id = "' . $men_id . '"
                AND opc_id = "' . $opc_id . '" AND ayd_campo = "" and sts_id = "1" AND
            ( CONCAT( ",", ayd_nivelesJerarquicos, "," ) LIKE "%,' . $_SESSION[_S]['nvj_id'] . ',%"
                OR ayd_nivelesJerarquicos IS NULL OR TRIM( ayd_nivelesJerarquicos ) = ""
            ) AND TRIM( ayd_link ) <> "";');
        $this->consultar(false);
        $this->desconectar();
        if ($this->getErrno() > 0) {
            return;
        }
        $row = $this->extraer();
        $tieneAyuda = (($row['registros'] > 0) ? 1 : 0);
        if ($tieneAyuda == 1) {
            $this->mixScript('$("#ayudadropdown").show();');
        } else {
            $this->mixScript('$("#ayudadropdown").hide();');
        }

        /* PONE EN UN INPUT OCULTO, LOS CAMPOS DE LA PANTALLA ACTUAL QUE TIENEN AYUDA---- */

        $this->conectar();
        $this->setSql('SELECT ayd_campo
            FROM cat_Ayuda
            WHERE sis_id = "' . $sis_id . '" AND men_id = "' . $men_id . '" AND opc_id = "' . $opc_id . '"
                AND ayd_campo <> ""
                AND ( CONCAT( ",", ayd_nivelesJerarquicos, "," ) LIKE "%,' . $_SESSION[_S]['nvj_id'] . ',%"
                    OR ayd_nivelesJerarquicos IS NULL OR TRIM( ayd_nivelesJerarquicos ) = ""
                )
            AND TRIM( ayd_link ) <> ""
            GROUP BY ayd_campo;');
        $this->consultar();
        $this->desconectar();
        $camposAyuda = ".";

        while ($row = $this->extraer()) {
            if (trim($row['ayd_campo']) != "") {
                $camposAyuda .= "(" . $row['ayd_campo'] . ")";
            }
        }

        $this->mixScript('$("#camposAyuda").val("' . $camposAyuda . '" );');
        $this->mixScript('$("#tip").addClass("gris");');
    }

    private function sistemaAbierto()
    {
        $return = true;
        $ips = array("172.17.94.96", "172.17.94.131");
        $ip = $_SERVER['REMOTE_ADDR'];

        if ((file_exists(dirname(__FILE__) . '/../mensaje.txt'))
            && !in_array($ip, $ips)
        ) {
            $this->setContenedor('cuerpoSinpres');
            $this->mixHtml('
                <div class="sefip-mantenimiento" role="alert" aria-live="polite">
                    <div class="sefip-mantenimiento-card">
                        <div class="sefip-mantenimiento-brand">
                            <img alt="SEFIP 2027" src="img/logo_sefip_2027-login-w.png" class="sefip-mantenimiento-logo">
                        </div>
                        <div class="sefip-mantenimiento-body">
                            <div class="sefip-mantenimiento-icon" aria-hidden="true">
                                <i class="fa fa-wrench"></i>
                            </div>
                            <h1 class="sefip-mantenimiento-titulo">Sistema temporalmente fuera de servicio</h1>
                            <p class="sefip-mantenimiento-texto">Disculpe las molestias que esto pueda ocasionar, gracias por su comprensión.</p>
                        </div>
                    </div>
                </div>
            ');
            $return = false;
        }

        return $return;
    }

    private function obtenerNombreSistema($sisId)
    {
        $this->conectar();
        $this->setSql("SELECT sis_nombre FROM cat_Sistema WHERE sis_id = '$sisId';");
        $this->consultar();
        $res = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {
            return 'Sin permisos';
        } else {
            return $res['sis_nombre'];
        }
    }

    public function ticket()
    {
        $mod = 0;
        $detalle = '';
        $grupo = '';
        switch ($_SESSION[_S]['mod_id']) {
            case "C":
                $mod = 18;
                $detalle = ' #indique la pantalla/menu del modulo contable donde requiere apoyo ejemplo: CATALOGO > MES CONTABLE';
                $grupo = 'SEFIP-CON';
                break;
            case "A":
                $mod = 17;
                $detalle = ' #indique la pantalla/menu del modulo poa donde requiere apoyo ejemplo: PROGRAMACION > ACTIVIDADES/OBRAS';
                $grupo = 'SEFIP-POA';
                break;
            case "S":
                $detalle = ' #indique la pantalla/menu del modulo de control presupuestal donde requiere apoyo ejemplo: AFECTACIONES  > GENERAR COMPROMISO';
                $mod = 19;
                $grupo = 'SEFIP-SCP';
                break;
            case "T":
                $detalle = '#indique la pantalla/menu del modulo de la tesoreria donde requiere apoyo ejemplo: TESORERIA  > PROGRAMACION';
                $grupo = 'SEFIP-TES';
                $mod = 20;
                break;
            default:
                return false;
                break;
        }
        $mail = isset($_SESSION[_S]['mail']) ? $_SESSION[_S]['mail'] : '';
        $extra = '';
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $extra = '$form.append($("<input/>").attr("type","hidden").addClass("ahide").attr("name","topic_group").val("' . $grupo . '"));';
        }

        //Se borro el atributo que mandaba el nombre del usuario, ya que en las variables de sesion no existe.
        $this->mixScript('
            $form = $("<form />").attr("method","post").attr("action","https://ayuda.finanzasoaxaca.gob.mx/open.php").attr("target","_blank");
            $form.html($("<input/>").attr("type","hidden").addClass("haide").attr("name","topic_id").val(' . $mod . '));
            $form.append($("<input/>").attr("type","hidden").addClass("ahide").attr("name","detalles").val("' . $detalle . '"));
            $form.append($("<input/>").attr("type","hidden").addClass("ahide").attr("name","usuario").val("' . $_SESSION[_S]['usu'] . '"));
            $form.append($("<input/>").attr("type","hidden").addClass("ahide").attr("name","ur").val("' . $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] . '"));
            $form.append($("<input/>").attr("type","hidden").addClass("ahide").attr("name","email").val("' . $mail . '"));
            ' . $extra . '
            $("body").append($form);
            $form.submit();
            $form.remove();
        ');
    }

    public function syncWebSocket()
    {
        $datos = '{"usr_id":"' . md5((string) $_SESSION[_S]['usr_id']) . '","nvj":"' . md5((string) $_SESSION[_S]['nvj_id']) . '"}';
        $contenido = '{"type":"sync","uid":"' . md5((string) $_SESSION[_S]['usr_id']) . '","datos":' . $datos . '}';
        $this->wr_WebSocket($contenido);
    }

    private function notificaSinpres()
    {
        $this->conectar();
        $this->setSql('
                SELECT nts_id, nts_ejercicio
                FROM  ' . BD_SINPRES . '.cat_NotificacionSistema
                WHERE nts_ejercicio = ' . APP_EJERCICIO . ' AND sts_id = 1 ;');
        $this->consultar();
        $row = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function getSinpresScript()
    {
        $stopFunction = (isset($_SESSION[_S]['prm']['A']['11']['11'])
            || isset($_SESSION[_S]['prm']['A']['11']['10'])
            || isset($_SESSION[_S]['prm']['A']['11']['5'])
            || isset($_SESSION[_S]['prm']['A']['11']['1']) ?
            'StopFunction();' : '');
        return <<<JS
        var globalParametrosComunica = "";
        function ActivaAtajosMenu(){  };
        function DesactivaAtajosMenu(){  };
        // Al volver al inicio por AJAX, el app-loader puede quedarse visible si App.init ya corrio.
        $("#loader").hide();
        $("#clasAdminSeccionCombos").toggle();
        $("#tutorialTicket").click(function(e){
            
            muestraAyuda("x","https://docs.google.com/presentation/d/1wE8CBkHPADLgyGINDLGU7OLYzJPOlUdH/embed?start=false&loop=false&delayms=3000");

        });
        function cerrarSesion() {
            comunica("srv/Controlador.php", {metodo: "cerrarSesion"});
            {$stopFunction}
        }

        $("#utilerias").click(function() {
            creaMainContainer();
            comunica("srv/ControladorUtilerias.php");
        });
        $("#sefipHome").click(function(e){
            e.preventDefault();
            comunica("srv/Controlador.php",{metodo:"sinpres"});
        });
        $("#prf").click(function(){
            creaMainContainer();
            comunica("srv/ControladorUsuarios.php",{
                metodo:"main"
            });
        });
        $("#tik").click(function(){
            creaMainContainer();
            comunica("srv/ControladorTicket.php",{
            metodo:"main"
            });
        });
        $("#generaTicket").click(function(){
            comunica("srv/Controlador.php",{metodo:"ticket"});
        });
        $("#ayuda").click(function(e){
            e.preventDefault();
            muestraAyuda( "ayuda" );
            $("body").append($(".cssDialogAyuda"))
        });
        // Para disparar la ayuda de items multiples
        $("#listaAyuda").on("click", "li a", function(e) {
                e.preventDefault();
                muestraAyuda( $(this).attr("href"), $(this).attr("lnk") );
        });

        function muestraAyuda( tipoAyuda, strLink ){
            var iframe = $("<iframe frameborder=0 marginwidth=0 marginheight=0 allowfullscreen></iframe>");
            var dialog = $("<div></div>").append(iframe).appendTo("body").dialog({
                autoOpen: false,
                modal: true,
                title: "Pantalla de Ayuda",
                resizable: false,
                dialogClass: "cssDialogAyuda",
                width: "auto",
                height: "auto",
                create: function(event, ui) {
                    $(event.target).parent().css("position", "fixed");
                    $(".ui-dialog-titlebar-close").addClass("btn btn-danger btn-small").append("X");
                }, resizeStop: function(event, ui) {
                    var position = [(Math.floor(ui.position.left) - $(window).scrollLeft()),
                        (Math.floor(ui.position.top) - $(window).scrollTop())];
                    $(event.target).parent().css("position", "fixed");
                    $(dlg).dialog("option","position",position);
                }, close: function () {
                    iframe.attr("src", "");
                    $("#"+tipoAyuda).show();
                    $("#listaAyuda").empty();
                    dialog.dialog("dispose").remove();
                    $("div[aria-describedby=ui-id-1]").html("");
                }
            });

            // Si no nos dan el link de ayuda ...
            if (typeof strLink === "undefined" ) {
                if ( tipoAyuda == "ayuda" ) {
                    ldata = { "o" : $("#pantallaAyuda").val() };
                } else {
                    ldata = { "o" : $("#pantallaAyuda").val(), "c" : $("#ultimoCampo").val() };
                }

                var ventana  = $(this);
                aLinks = [];

                $.ajax({
                    data: ldata,
                    url:  "srv/dameLinkAyuda.php",
                    type: "post",
                    success: function ( response ) {
                        var json = $.parseJSON(response);
                        // Si regresar 1 solo tema de ayuda, lo mostramos directamente
                        if ( json.length == 1 ) {
                            for (var i = 0; i < json.length; i++) {
                                aLinks[i] = {
                                    url: json[i].url
                                }
                            }
                            $("#"+tipoAyuda).hide()
                            var src = aLinks[0].url;
                            var title = ventana.attr("data-title");
                            var width = $(window).width() - 120;
                            var height = $(window).height() - 120;
                            iframe.attr({
                                width: +width,
                                height: +height-30,
                                src: src
                            });
                            dialog.dialog("open", "option", "title", title );
                        // Si es mas de un tema ... mostramos una lista desplegable
                        } else {
                            $("#listaAyuda").empty();
                            $("#listaAyuda").append("<li class=\"dropdown-header\">Temas de ayuda</li>");
                            for (var i = 0; i < json.length; i++) {
                                var item = "<li class=\"media media-lg\"><a href=" + tipoAyuda + " lnk=\"" + json[i].url + "\"><div class=\"media-left\"><i class=\"fa fa-question-circle-o media-object bg-grey\"></i></div><div class=\"media-body\"><h6>" + json[i].d + "</h6></div></a></li>";
                                $("#listaAyuda").append(item);
                            }
                            // >>> esto lo pongo aqui porque ya creamos la variable dialog ... modificar el codigo para no crear
                            // la variable sino hasta que se use
                            dialog.dialog("dispose").remove();
                        }
                    }
                });
            // Si nos pasan el link, solo mostramos la ayuda correspondiente
            } else {
                $("#"+tipoAyuda).hide();
                var ventana  = $(this);
                var src = strLink;
                var title = ventana.attr("data-title");
                var width = $(window).width() - 120;
                var height = $(window).height() - 120;
                iframe.attr({
                    width: +width,
                    height: +height-30,
                    src: src
                });
                dialog.dialog("open", "option", "title", title );
            }
        };
        JS;
    }

    private function getSinpresHtml($params)
    {
        $men = $params['men'] ? '<div id="cfmsg" style="display:none"></div>' : '';
        $nomUsuario = $_SESSION[_S]['nom'] ?? '';
        $gpoUrTitulo = ($_SESSION[_S]['gpo_id'] ?? '') . ($_SESSION[_S]['ur_id'] ?? '');
        $urNombreTitulo = $_SESSION[_S]['ur_nombre'] ?? '';
        $html = <<<HTML
        <!-- begin #page-loader -->
        <div id="loader" class="app-loader">
            <span class="spinner"></span>
        </div>
        <!-- end #page-loader -->
        {$men}
        <div id="cgPantallaCompleta" style="z-index: 1041;"></div>
        <div id="scpPantallaCompleta" style="z-index: 1029;"></div>
        <div id="bdPantallaCompleta" style="margin:4px; position: fixed; top: 4px; width: 99%; z-index: 1041;background-color: #fff; --bs-modal-bg: #fff;"></div>
        <div id="vclcPantallaCompleta" style="z-index: 1029;"></div>
        <div id="stickyBalanzaDetalle" style="display:none">
            <div id="navBotonesBD" class="well" style="padding:4px !important; margin-bottom:2px !important;" ></div>
        </div>
        <div id="paraModales">
            <div class="imgheader"></div>
            {$this->mensajesModalDeSistema()}
        </div>

        <!-- aqui ***AYUDA*** -->
        <input type="hidden" id="camposAyuda" value="" />
        <input type="hidden" id="ultimoCampo" value="" />
        <input type="hidden" id="pantallaAyuda" value="" />

        <!-- BEGIN #app -->
        <div id="app" class="app app-header-fixed app-sidebar-fixed app-with-light-sidebar">
            <div id="header" class="app-header">
                <!-- BEGIN navbar-header -->
                <div class="navbar-header">
                    <a href="#" id="sefipHome" class="navbar-brand">
                        <img src="images/logo_sefip_2027-navbar-w_1.png" alt="logo sistema SEFIP">
                    </a>
                    <button type="button" class="navbar-mobile-toggler" data-bs-toggle="collapse"
                        data-bs-target="#top-navbar">
                        <span class="fa-stack fa-lg">
                            <i class="far fa-square fa-stack-2x"></i>
                            <i class="fa fa-cog fa-stack-1x mt-1px"></i>
                        </span>
                    </button>
                    <button type="button" class="navbar-mobile-toggler" data-toggle="app-sidebar-mobile">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <!-- END navbar-header -->
                <!-- BEGIN navbar-collapse -->
                <div class="collapse d-md-block me-auto" id="top-navbar">
                    <div class="navbar-nav navbar-sefip">
                        {$params['modulos']}
                    </div>
                </div>
                <!-- END navbar-collapse -->
                <!-- BEGIN header-nav -->
                <div class="navbar-nav navbar-sefip-right">
                    {$this->getMensajesHtml()}
                    <div class="navbar-item">
                        <a href="#" class="navbar-link" id="utilerias">
                            <i class="fa fa-cog" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="navbar-item">
                        <a href="#" class="navbar-link" onClick="document.location.href='{$params['strRaiz']}';">
                            <i class="fa fa-home" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="navbar-item">
                        <a href="#" class="navbar-link" onClick="cerrarSesion()">
                            <i class="fa fa-power-off" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="navbar-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" class="navbar-link dropdown-toggle icon">
                            <i class="fa fa-phone"></i>
                        </a>
                        <div class="dropdown-menu media-list dropdown-menu-end">
                            <h6 class="dropdown-header">Solicitudes</h6>
                            <a id="generaTicket" href="javascript:;" class="dropdown-item media">
                                <div class="media-left">
                                    <i class="fa fa-ticket bg-default"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="media-heading">Generar Ticket</h6>
                                    <div class="text-muted fs-10px">Sistema de Tikets de Finanzas</div>
                                </div>
                            </a>
                            <a id="tutorialTicket" href="javascript:;" class="dropdown-item media">
                                <div class="media-left">
                                    <i class="fa fa-file-pdf bg-default"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="media-heading">Tutorial Ticket</h6>
                                    <div class="text-muted fs-10px">Descarga pdf</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="navbar-item dropdown sefip-dropdown-usuario">
                        <a href="#" data-bs-toggle="dropdown" data-bs-popper="static" class="navbar-link dropdown-toggle fs-14px">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </a>
                        <div class="dropdown-menu media-list dropdown-menu-end sefip-dropdown-usuario-menu" style="min-width:11rem;">
                            <div class="dropdown-header sefip-dropdown-usuario-nombre" style="white-space:normal!important;word-break:break-word;overflow-wrap:anywhere;line-height:1.25;">{$nomUsuario}</div>
                            <a id="prf" href="javascript:;" class="dropdown-item media">
                                <div class="media-left">
                                    <i class="fa fa-user bg-default"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="media-heading">Ver Perfil</h6>
                                </div>
                            </a>
                            {$this->getNuevoUsuarioOption()}
                            <a href="javascript:;" class="dropdown-item media"
                                onClick="cerrarSesion()">
                                <div class="media-left">
                                    <i class="fa fa-power-off bg-default text-danger"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="media-heading">Cerrar Sesión</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- END header-nav -->
            </div>
            <!-- END #header -->
            <!-- BEGIN #sidebar -->
            <div id="sidebar" class="app-sidebar poa">
                <div id="sefipSidebar" class="app-sidebar-content" data-scrollbar="true" data-height="100%">
                </div>
            </div>
            <div class="app-sidebar-bg"></div>
            <div class="app-sidebar-mobile-backdrop">
                <a href="#" data-dismiss="app-sidebar-mobile" class="stretched-link"></a>
            </div>
            <!-- END #sidebar -->
            <!-- BEGIN #content -->
            <div id="content" class="app-content">
                <!-- begin page-header -->
                <div id="otraInfoFooter" class="pull-right"></div>
                   <div class="row">
                    <div class="col-md-6 mt-2">
                        <div id="otraInfoFooter" class="pull-left"></div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div aria-label="breadcrumb" class="mb-0">
                            <ol class="breadcrumb mb-0 justify-content-end" id="breadcrumbPath">
                                <li class="breadcrumb-item"><a href="javascript:;" onclick="window.location.reload();">Inicio</a></li>
                                <li id="breadcrumbMenu" class="breadcrumb-item" style="display:none"></li>
                                <li id="breadcrumbOpcion" class="breadcrumb-item active" aria-current="page" style="display:none"></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <h1 class="page-header">
                    {$gpoUrTitulo}
                    <small>{$urNombreTitulo}</small>
                </h1>
                <!-- end page-header -->
                <div id="divContenedorGeneralModal"></div>
                <div id="divMensajes" class="col-md-12">{$this->mensajesDeSistema()}</div>
            </div>
            <!-- END #content -->
            <!-- BEGIN scroll to top btn -->
            <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top" data-toggle="scroll-to-top"><i class="fa fa-angle-up"></i></a>
            <!-- END scroll to top btn -->
        </div>
        <contenedorScript></contenedorScript>
        HTML;
        return $html;
    }

    private function getNuevoUsuarioOption()
    {
        if (!sesionActiva()) {
            return '';
        }

        $this->conectar();
        $this->setSql('SELECT usr_sa FROM ' . BD_CTRL . '.cat_Usuario WHERE usr_id = :usr_id;');
        $this->consultar(['usr_id' => $_SESSION[_S]['usr_id']]);
        $row = $this->extraer();
        if ($row === false) {
            $this->desconectar();
            return '';
        }
        $solUsuario = $row['usr_sa'] ?? 0;
        $this->desconectar();

        $u = new Usuario();
        $u->setUsr_id($_SESSION[_S]['usr_id']);
        $u->select();
        $usuario = $u->getUsuario();
        if ($usuario === null || strlen((string) $usuario) < 4) {
            return '';
        }
        $valor = substr((string) $usuario, 3);
        if (($_SESSION[_S]['nvj_id'] == 2 && (in_array($valor, ["ADMIN_POA", "TITULAR_SCP", "ADMIN_SCG"])))|| $solUsuario ==1) {
            return <<<HTML
                <a href="javascript:;" class="dropdown-item media" id="tik">
                    <div class="media-left">
                        <i class="fa fa-user-plus bg-default"></i>
                    </div>
                    <div class="media-body">
                        <h6 class="media-heading">Solicitar Nuevo Usuario</h6>
                    </div>
                </a>
            HTML;
        }
        return '';
    }

    private function getMensajesHtml()
    {
        return <<<HTML
        <div class="navbar-item dropdown">
            <a href="#" data-bs-toggle="dropdown" class="navbar-link dropdown-toggle icon">
                <i class="fa fa-envelope"></i>
                <span class="badge" id="avisos">0</span>
            </a>
            <div class="dropdown-menu media-list">
                <div class="dropdown-header">Mensajes</div>
                <a href="javascript:;" class="dropdown-item" id="detalleavisos">
                    <div class="media-left">
                        <i class="fa fa-envelope-open bg-default"></i>
                    </div>
                    <div class="media-body">
                        <h6 class="media-heading">Mensajes</h6>
                        <div class="text-muted fs-10px">No tiene mensajes</div>
                    </div>
                </a>
                <div class="dropdown-header">Evidencias</div>
                <a href="javascript:;" class="dropdown-item" id="notificaevidencias">
                    <div class="media-left">
                        <i class="fa fa-envelope-open bg-default"></i>
                    </div>
                    <div class="media-body">
                        <h6 class="media-heading">No solicitan evidencias</h6>
                    </div>
                </a>
            </div>
        </div>
        HTML;
    }

    private function revisaCapcha()
    {
        require_once dirname(__FILE__) . '/../lib/captcha/securimage.php';
        $img = new Securimage();
        $captchaIngresado = isset($this->prm->captcha) ? trim((string) $this->prm->captcha) : '';
        $captchaEnSesion = isset($_SESSION[_S]['securimage_code_value']['default']);

        if (!$captchaEnSesion || $captchaIngresado === '' || $img->check($captchaIngresado) !== true) {
            $this->mixScript('
                notify("Captcha Incorrecto", "warning");
                $("#siimage").attr("src","./lib/captcha/securimage_show.php?sid=" + Math.random() );
                setTimeout( function() { $( "#captcha" ).val("").focus() }, 300 );
                ');
            $this->returnJson();
            die();
        } else {
            $this->mixScript('
                comunica("srv/Controlador.php",{
                    metodo : "logLogueo",
                    claveur : '.$this->prm->claveUr.',
                    usu : "'.strtoupper($this->prm->usuario).'"
                });
            ');
        }
    }

    private function logLogueo()
    {
        if (!file_exists("./log")) {
            mkdir("./log", 0777, true);
        }
        
        $rutalog = "log/LogLogueo";
        $file = fopen($rutalog . ".log", "a");
        $hoy = date("Y-m-d H:i:s");

        fwrite($file, $hoy . ": CLAVE UR:" . $this->prm->claveur. "   USUARIO: ".$this->prm->usu. PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog . ".log");
        $peso = ($peso / 1024) / 1024;
        if ($peso > 8) {
            $rutamodificada = $rutalog . $hoy . ".log";
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog . ".log", "a");
            fwrite($file, "Creado:" . $hoy . PHP_EOL);
            fclose($file);
        }
    }

    public function recuperaPassword()
    {   
        if (ServicioUsuario::sesionBloqueada()){
            $this->mixScript('
                notify("Su sesión ha sido bloqueada por múltiples intentos de acceso fallidos. Por favor, contacte al administrador del sistema.", "error");
                setTimeout( function() { window.location.reload() }, 300 );
            ');
            return;
        }
        if (!isset($this->prm->urusuario) && !isset($this->prm->codigo)) {
            $formulario = ServicioUsuario::formRecuperaPassword();
            $this->setContenedor('login-form');
            $this->mixHtml('<div><ul class = "sefip-modulos">' . $formulario . '</ul><div>');
            return;
        }
        if (isset($this->prm->urusuario) && isset($this->prm->username)) {
            $urusuario = (string) $this->prm->urusuario;
            $username  = (string) $this->prm->username;

            if (
                $urusuario === '' || $username === ''
                || trim($urusuario) === '' || trim($username) === ''
                || preg_match('/\s/', $urusuario) || preg_match('/\s/', $username)
            ) {
                $this->mixScript('
                    notify("Los campos UR y Usuario son obligatorios y no deben contener espacios.", "warning");
                    $("#update_overlay").hide();
                ');
                $this->returnJson();
                die();
            }

            if (!preg_match('/^[0-9]{3}$/', $urusuario)) {
                $this->mixScript('
                    notify("La UR debe contener exactamente 3 dígitos (0-9).", "warning");
                    $("#update_overlay").hide();
                ');
                $this->returnJson();
                die();
            }

            if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
                $this->mixScript('
                    notify("El usuario solo admite letras, números y guion bajo (_).", "warning");
                    $("#update_overlay").hide();
                ');
                $this->returnJson();
                die();
            }

            $resultado = ServicioUsuario::enviaCodigo($urusuario, $username);

            if ($resultado === 'no_existe') {
                $this->mixScript('
                    notify("Los datos no coinciden. Verifica la UR y el usuario.", "warning");
                    if (typeof window.resetRecoveryForm === "function") { window.resetRecoveryForm(); }
                ');
                $this->returnJson();
                die();
            }

            if ($resultado === 'sin_ejercicio') {
                $ejer = APP_EJERCICIO;
                $this->mixScript('
                    notify("El usuario no se encuentra registrado en el ejercicio ' . $ejer . '. Contacte al administrador del sistema.", "warning");
                    if (typeof window.resetRecoveryForm === "function") { window.resetRecoveryForm(); }
                ');
                $this->returnJson();
                die();
            }

            if ($resultado === 'inactivo') {
                $this->mixScript('
                    notify("El usuario no se encuentra activo. Contacte al administrador del sistema.", "warning");
                    if (typeof window.resetRecoveryForm === "function") { window.resetRecoveryForm(); }
                ');
                $this->returnJson();
                die();
            }

            if ($resultado === 'sin_correo') {
                $this->mixScript('
                    notify("El usuario esta inactivo porque no tiene un correo electrónico registrado. Contacte al administrador del sistema.", "warning");
                    if (typeof window.resetRecoveryForm === "function") { window.resetRecoveryForm(); }
                ');
                $this->returnJson();
                die();
            }

            if ($resultado === 'ok') {
                $correo = (string) ($_SESSION[_S]['recoveryData']['usuario']['usr_correo'] ?? '');
                $correoEnm = ServicioUsuario::enmascararCorreo($correo);
                $correoJs = json_encode($correoEnm);
                $this->mixScript('
                    if (typeof window.showRecoveryCodeStep === "function") {
                        window.showRecoveryCodeStep();
                    }
                    var $msg = $("#recovery-email-msg");
                    if ($msg.length) {
                        $msg.text("Se ha enviado un código a su correo " + ' . $correoJs . ' + " para recuperar su acceso.");
                    }
                ');
            }

            return;
        }

        if (isset($this->prm->codigo) && ServicioUsuario::verificaCodigo($this->prm->codigo)) {
            $formulario = ServicioUsuario::formActualizaPasswordOlvidado();
            $this->setContenedor('login-form');
            $this->mixHtml('<div><ul class = "sefip-modulos">' . $formulario . '</ul><div>');
        } else {
            $d = json_encode($_SESSION[_S]);
            $this->mixScript('
                notify("Código inválido", "warning");
                console.log('.$d.');
                setTimeout( function() { $( "#codigo" ).val("").focus() }, 300 );
                ');
            $this->returnJson();
            die();
        }
    }

    private function sendVerificationCode($code, Telegram $telegram = null)
    {
        $telegram = $telegram ?? new Telegram();
        $correo = strtolower((string) ($_SESSION[_S]['mail'] ?? ''));
        $mailService = new ServicioEmail();
        $mailService->setAsunto('Código de verificación SEFIP 2027');
        $mailService->setCuerpo($this->getEmailTemplate($_SESSION[_S]['usu'], $code));
        $mailService->setDestinatario($correo, $_SESSION[_S]['nom']);

        $sent = $mailService->enviar();
        $telegram->logCorreoRespaldo2FA((bool) $sent, $correo);
        if ($sent) {
            return ['success' => true, 'message' => 'Se ha enviado un código a su correo ' . substr($correo, 0, 3) . '***'];
        } else {
            return ['success' => false, 'message' => 'Error al enviar el correo electrónico.'];
        }
    }

    private function getEmailTemplate(mixed $usuario, string $code): string
    {
        return <<<HTML
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Acceso SEFIP 2027</title>
            </head>
            <body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; background-color: #FFF;">
                <div style="max-width: 600px; margin: auto;">
                    <img src="https://sefip.finanzasoaxaca.gob.mx/2027/images/logo-sistema-2027.png" alt="Logo Oaxaca" width="200" style="display: block; margin-bottom: 20px;">
                    <h2 style="color: #444;">Estimado {$usuario},</h2>
                    <p>Su código de verificación para acceder a SEFIP es: {$code}</p>
                </div>
            </body>
            </html>
        HTML;
    }
}
new Controlador();