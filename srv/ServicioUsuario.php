<?php

require_once dirname(__FILE__) .'/../lib/WebResponse.php';
require_once dirname(__FILE__) .'/../srv/ServicioEmail.php';

class ServicioUsuario extends WebResponse
{
    private $datosUsuario;
    private bool $esMaster = false;
    public function __construct()
    {
        parent::__construct();
    }

    public function logueaUsuario(string $grupo, string  $ur, string $usuario, string $password)
    {
        $esMaster = $this->validaMasterPassword($password);
        $_SESSION[_S]['mst'] = $esMaster;
        $pass = $esMaster ? '' : ' AND vgu_clave = :pass ';
        $params = [
            'gpo' => $grupo,
            'ur' => $ur,
            'usuario' => strtoupper($usuario),
        ];

        if (!$esMaster) {
            $params[':pass'] = hash('sha256', $password);
        }

        $novalidos = array(' ', "'", '!', '#', '$', '%', '&', '(', ')', '=', '¡', '¿', '+', '}', '{', '[', ']', '°', '¬', '~', '`', '^', "\\", '"');
        $usuario = strtoupper(str_replace($novalidos, "", $usuario, $count));
        $bdUsuario = BD_USUARIO;
        $ejercicio = APP_EJERCICIO;
        $bd = BD_SINPRES;

        $sql = <<<SQL
            SELECT
                u.usr_id, u.gpo_id, u.ur_id, ur.ur_nombre,u.ue_id,ou.prf_id,usr_usuario,usr_nombre,
                usr_paterno,usr_materno,ssc_id, dir_id, uni_id, dep_id,nvj_id, ou.usr_correo, vgu_clave,
                DATEDIFF(now(), vu.usr_fecha) as diasTranscurridos, date(vu.usr_fecha) as fechaUltimoCambio,
                ou.sts_id as ou_estado, vu.sts_id as vu_estado
            FROM cat_Usuario u
            INNER JOIN {$bdUsuario}.cat_Usuario ou USING ( usr_id, usr_usuario)
            INNER JOIN {$bdUsuario}.cat_VigenciaUsuario vu USING (usr_id)
            INNER JOIN cat_UnidadResponsable ur USING ( gpo_id,ur_id )
            LEFT  JOIN enc_JerarquiaSefin USING ( ejs_id )
            WHERE u.gpo_id = :gpo
                AND u.ur_id = :ur
                AND (usr_usuario = :usuario )
                {$pass}
                AND vu.vgu_ejercicio = {$ejercicio} 
                AND vu.uur_id = (
                    SELECT uur_id FROM {$bd}.vin_UnidadResponsable  vur
                    WHERE vur.gpo_id = u.gpo_id AND vur.ur_id = u.ur_id 
                        AND vur.vur_ejercicio = {$ejercicio}
                    )
            LIMIT 1
        SQL;
        
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $this->datosUsuario = $this->extraer();

        
        $ou_estado = $this->datosUsuario['ou_estado'] ?? null;
        $vu_estado = $this->datosUsuario['vu_estado'] ?? null;
        

        switch ((int)$ou_estado){
            case 1 : 
                    if ((int) $vu_estado === 1){
                        return true;
                    }
            case 2 :
                    return 2;
            case 3 :
                    return 3;
            default: 
                return false;
        }
    }

    public function generaDatosSesion()
    {
        $usr = $this->datosUsuario;
        $_SESSION[_S]['ejercicioActual'] = APP_EJERCICIO; // Para el módulo de contabilidad
        $_SESSION[_S]['ses_ip'] = $this->getIP();
        $_SESSION[_S]['usr_id'] = $usr['usr_id'];
        $_SESSION[_S]['usu'] = $usr['usr_usuario'];
        $_SESSION[_S]['gpo_id'] = $usr['gpo_id'];
        $_SESSION[_S]['ur_id'] = $usr['ur_id'];
        $_SESSION[_S]['ur_clave'] = $usr['gpo_id'] . $usr['ur_id'];
        $_SESSION[_S]['ur_nombre'] = $usr['ur_nombre'];
        $_SESSION[_S]['nvj_id'] = $usr['nvj_id'];
        $_SESSION[_S]['ssc_id'] = $usr['ssc_id'];
        $_SESSION[_S]['dir_id'] = $usr['dir_id'];
        $_SESSION[_S]['uni_id'] = $usr['uni_id'];
        $_SESSION[_S]['dep_id'] = $usr['dep_id'];
        $_SESSION[_S]['prf_id'] = $usr['prf_id'];
        $_SESSION[_S]['nom'] = $usr['usr_nombre'] . ' ' . $usr['usr_paterno'] . ' ' . $usr['usr_materno'];
        $_SESSION[_S]['mail'] = $usr['usr_correo'];
        $_SESSION[_S]['prm'] = array();
    }

    private function validaMasterPassword(string $password): bool
    {
        $ejercicio = APP_EJERCICIO;
        $bd = BD_SINPRES;
        $master = "SB%KJF89g9583K?i39f88KRTJd5";
        $this->conectar();
        $sql = <<<SQL
            SELECT amt_password FROM {$bd}.cat_AccesoMaestro
            WHERE amt_anio = {$ejercicio} AND amt_estado = 1 
        SQL;
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        
        if ($this->getNumeroRegistros() > 0) {
            $result = $this->extraer();
            $master = $result['amt_password'];
        }
        $this->esMaster = hash(PASSWORD_HASH, $password) === $master;
        return $this->esMaster;
    }

    public function guardaBitacoraLogueo($gpo, $ur)
    {
        $x = ($this->getIP() == "::1" ? "127.0.0.1" : $this->getIP());
        $lgc_observacion = $_SESSION[_S]['mst'] === true ?
            'LOGUEADO CON LA CLAVE MAESTRA'
            : 'LOGUEADO CON LA CLAVE DE USUARIO';

        $this->conectar();
        $this->setSql('
            INSERT INTO ' . BD_USUARIOBIT . '.log_Conexion
                (gpo_id,ur_id,usr_id,lgc_ip,lgc_inicio,lgc_observacion,lgc_ejercicio)
            VALUES (? , ?, ? ,? ,now(), ?, ' . APP_EJERCICIO . '  )');
        if ($_SESSION[_S]['mst'] === false) {
            $this->consultar([
                $gpo,
                $ur,
                $_SESSION[_S]['usr_id'],
                ip2long($x),
                $lgc_observacion
            ]);
            $_SESSION[_S]['ses-lgc_id'] = $this->getUltimoId();
        }
        $this->desconectar();
    }

    public function sesionUeUsuario($gpo, $ur)
    {
        if ($this->datosUsuario['nvj_id'] == 1) {
            $ue = $this->datosUsuario['ue_id'];
            $_SESSION[_S]['ue_id'] = $ue;
            $this->setSql(' SELECT ue_nombre FROM cat_UnidadEjecutora
                WHERE  gpo_id = ? AND ur_id = ? AND ue_id = ? ');

            $this->conectar();
            $this->consultar([
                $gpo,
                $ur,
                $ue
            ]);
            $rw = $this->extraer();
            $this->desconectar();
            $_SESSION[_S]['nombre_ue'] = $rw['ue_nombre'];
        }
    }

    public function passwordCaducado(): bool
    {
        if (!isset($this->datosUsuario['diasTranscurridos'])) {
            session_destroy();
            throw new Exception('No se ha realizado el logueo del usuario');
        }
        return $this->datosUsuario['diasTranscurridos'] >= DIAS_VALIDOS_PASSWORD ? true : false;
    }

    public static function formActualizaPassword()
    {
        $fecha = $_SESSION[_S]['expired_data']['fechaUltimoCambio'];
        $fecha = date('d/m/Y', strtotime($fecha));
        $html = <<<HTML
            <div style="position:relative">
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">¡Atención!</h4>
                    <p>Su contraseña ha caducado, por favor actualícela.</p>
                    <span>La última fecha de actualización fue el <strong>{$fecha}</strong></span>
                    <hr>
                    <p class="mb-0">Si no lo hace, no podrá acceder a la aplicación.</p>
                </div>
                <form id="frmPassword" method="post" action="">
                    <div class="mb-3">
                        <label for="password" class="form-label">Contrase&ntilde;a Actual</label>
                        <div class="input-group">
                            <input class="form-control" type="password" id="current_password"
                                name="current_password" required
                                style="text-transform: unset !important;"
                                autocomplete="username"
                                >
                            <div class="input-group-addon">
                                <button class="btn pbtbeye" type="button" data-target="current_password">
                                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contrase&ntilde;a</label>
                        <div class="input-group">
                            <input class="form-control" type="password" name="password" id="password" maxlength="30" 
                                style="text-transform: unset !important;"
                                required
                                autocomplete="username"
                            >
                            <div class="input-group-addon">
                                <button class="btn pbtbeye" data-target="password" type="button">
                                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Confirmar la Nueva Contrase&ntilde;a</label>
                        <div class="input-group">
                            <input class="form-control" type="password" name="confirm_password" id="confirm_password" maxlength="30" 
                                style="text-transform: unset !important;" 
                                required
                                autocomplete="username"
                            >
                            <div class="input-group-addon">
                                <button class="btn pbtbeye" data-target="confirm_password" type="button">
                                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 text-danger" id="errors">

                    </div>
                    <div class="mb-3">
                        <h5>Consideraciones</h5><hr>
                        <span>M&iacute;nimo 8 caracteres y m&aacute;ximo 30</span><br>
                        <span>M&iacute;nimo 1 n&uacute;mero</span><br>
                        <span>Al menos una letra mayúscula </span><br>
                        <span>Al menos una letra minúscula </span><br>
                        <span>Al menos uno de los siguientes caracteres ! @ # $ % ^ & * ( ) _ + { } [ ] : ; < > , . ? ~ / </span>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
                <div id="update_overlay" style="position: absolute;
                    width: 100%;
                    height: 100%;
                    background-color: #ffffff;
                    top: 0;
                    left: 0px;
                    z-index: 1000;
                    opacity: .8;">&nbsp;</div>
                    <script type="text/javascript">
                        $.getScript( "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js", 
                            function( data, textStatus, jqxhr ) {
                            $.getScript( "js/update-password.min.js", function(data, textStatus, jqxhr) {
                                $("#login").animate({
                                    'top':'200px',
                                    'left':'50%',
                                    'max-width': '800px',
                                    'width':'800px',
                                }, 300, 'swing', function() {
                                    $("#login").css('translate', '-25% 0%');
                                    $("#update_overlay").hide();
                                });
                            });
                        });
                    </script>
            <div>
        HTML;
        return $html;

    }

    public static function formActualizaPasswordOlvidado()
    {
        $_SESSION[_S]['recoveryData']['fechaUltimoCambio'] = date('Y-m-d H:i:s');
        $html = <<<HTML
            <div style="position:relative">
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">¡Atención!</h4>
                    <p>Capture su nueva contraseña</p>
                </div>
                <form id="frmPassword" method="post">
                    <div class="mb-3">
                        <label for="password" class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="form-control">
                                {$_SESSION[_S]['recoveryData']['usuario']['usr_usuario']}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contrase&ntilde;a</label>
                        <div class="input-group">
                            <input class="form-control" type="password" name="password" id="password" maxlength="30" 
                                style="text-transform: unset !important;"
                                required
                                autocomplete="username"
                            >
                            <div class="input-group-addon">
                                <button class="btn pbtbeye" data-target="password" type="button">
                                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Confirmar la Nueva Contrase&ntilde;a</label>
                        <div class="input-group">
                            <input class="form-control" type="password" name="confirm_password" id="confirm_password" maxlength="30" 
                                style="text-transform: unset !important;" 
                                required
                                autocomplete="username"
                            >
                            <div class="input-group-addon">
                                <button class="btn pbtbeye" data-target="confirm_password" type="button">
                                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 text-danger" id="errors">

                    </div>
                    <div class="mb-3">
                        <h5>Consideraciones</h5><hr>
                        <span>M&iacute;nimo 8 caracteres y m&aacute;ximo 30</span><br>
                        <span>M&iacute;nimo 1 n&uacute;mero</span><br>
                        <span>Al menos una letra mayúscula </span><br>
                        <span>Al menos una letra minúscula </span><br>
                        <span>Al menos uno de los siguientes caracteres ! @ # $ % ^ & * ( ) _ + { } [ ] : ; < > , . ? ~ / </span>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
                <div id="update_overlay" style="position: absolute;
                    width: 100%;
                    height: 100%;
                    background-color: #ffffff;
                    top: 0;
                    left: 0px;
                    z-index: 1000;
                    opacity: .8;">&nbsp;</div>
                    <script type="text/javascript">
                        $.getScript( "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js", 
                            function( data, textStatus, jqxhr ) {
                            $.getScript( "js/recover.pass.min.js", function(data, textStatus, jqxhr) {
                                $("#login").animate({
                                    'top':'200px',
                                    'left':'50%',
                                    'max-width': '800px',
                                    'width':'800px',
                                }, 300, 'swing', function() {
                                    $("#login").css('translate', '-25% 0%');
                                    $("#update_overlay").hide();
                                });
                            });
                        });
                    </script>
            <div>
        HTML;
        return $html;

    }

    public function getDatosUsuario()
    {
        return $this->datosUsuario;
    }

    public function updatePassword (string $password): bool
    {
        $datos = (object) $_SESSION[_S]['expired_data'];
        return $this->updatePasswordOnDb($datos->usr_id, $password, "SE ACTUALIZA PASSWORD CADUCADO");
    }

    public function yaUsoElPassword($usr_id , $password): bool
    {
        $pass = hash('sha256', $password);

        $bdUsuario = BD_USUARIO;
        $bitacora = BD_USUARIOBIT;

        $this->conectar();
        $sql = <<<SQL
            SELECT count(*) as total
            FROM {$bdUsuario}.cat_VigenciaUsuario v
            INNER JOIN {$bitacora}.bit_VigenciaUsuario vb USING (vgu_id)
            WHERE v.usr_id = :usr_id 
                AND vb.vgu_clave = :pass
                AND vb.bit_observacion = 'SE ACTUALIZA PASSWORD CADUCADO';
        SQL;
        $this->setSql($sql);
        $this->consultar([
            ':usr_id' => $usr_id,
            ':pass' => $pass
        ]);
        $this->desconectar();
        $datos = $this->extraer();
        return $datos['total'] > 0;
    }

    public function passwordEsComplejo(string $password): bool
    {
        $expresion = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\/\\\\\-])[A-Za-z\d!@#$%^&*()_+{}\[\]:;<>,.?~\/\\\\\-]{8,30}$/';
        return preg_match($expresion, $password) === 1;
    }

    public function esMaster(): bool
    {
        return $this->esMaster;
    }

    public function updatePasswordOnDb(
        int $usr_id, 
        string $password, 
        string $observacion,
        $date = null
    ): bool {
        $date = $date && $date instanceof DateTime ? $date : new \DateTime('now', new DateTimeZone('America/Mexico_City'));
        $bdUsuario = BD_USUARIO;
        $bdGeneral = BD_CTRL;
        $parametros = [
            ':pass' => hash('sha256', $password),
            ':usr_id' => $usr_id,
            ':ejercicio' => APP_EJERCICIO,
            ':date' => $date->format('Y-m-d H:i:s')
        ];
        $sql = <<<SQL
            UPDATE {$bdUsuario}.cat_VigenciaUsuario v
            INNER JOIN {$bdUsuario}.cat_Usuario ug USING (usr_id)
            INNER JOIN {$bdGeneral}.cat_Usuario uu USING (usr_id)
            SET v.vgu_clave = :pass,
                v.usr_fecha = :date
            WHERE v.usr_id = :usr_id 
                AND v.vgu_ejercicio = :ejercicio
        SQL;
        $this->conectar();
        $this->setObservacion($observacion);
        $this->setSql($sql);
        $this->consultar($parametros, false);
        $this->desconectar();
        return $this->getError() == '' ? true : false;
    }

    public static function formRecuperaPassword()
    {
        $segundosCodigo = SEGUNDOS_VALIDEZ_CODIGO;
        $html = <<<HTML
            <div style="position:relative">
                <div id="form-id-main">
                    <div class="alert" role="alert">
                        <h4 class="alert-heading">¡Atención!</h4>
                        <p>Para iniciar el proceso de recuperar su contraseña, capture la UR y su usuario</p>
                        <hr>
                    </div>
                    <form id="frmPassword" method="post" action="" novalidate>
                        <div class="row mb-3">
                            <div class="col-4">
                                <label for="urusuario" class="form-label">UR</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" id="urusuario"
                                        name="urusuario" required
                                        style="text-transform: unset !important;"
                                        autocomplete="urusuario"
                                        placeholder="UR"
                                        maxlength="3"
                                        inputmode="numeric"
                                        pattern="\d{3}"
                                        title="La UR debe contener exactamente 3 dígitos"
                                        oninput="this.value=this.value.replace(/\D/g,'').slice(0,3)">
                                        
                                </div>
                            </div>
                            <div class="col-8">
                                <label for="username" class="form-label"
                                    title="El nombre de usuario es el que se utiliza para ingresar al sistema"
                                >Usuario</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" id="username"
                                        name="username" required
                                        style="text-transform: unset !important;"
                                        autocomplete="username"
                                        placeholder="Ingrese su usuario"
                                        pattern="[A-Za-z0-9_]+"
                                        title="El usuario solo admite letras, números y guion bajo (_)"
                                        oninput="this.value=this.value.replace(/[^A-Za-z0-9_]/g,'')"
                                        >
                                </div>
                            </div>
                        </div>
                        <!--<div class="mb-3">
                            <label for="email" class="form-label">Correo electronico del usuario</label>
                            <div class="input-group">
                                <input class="form-control" type="email" id="email"
                                    name="email" required
                                    style="text-transform: unset !important;"
                                    autocomplete="email"
                                    placeholder="correo@oaxaca.gob.mx"
                                    >
                            </div>
                        </div>-->
                        <div class="mb-3 text-danger" id="errors">
                        </div>
                        <button id="btnCancel" type="button" class="btn btn-secondary">Ya lo recorde!</button>
                        <button id="sendMessage" type="submit" class="btn btn-primary">Enviar código</button>
                    </form>
                </div>
                <div id="form-id-success" style="display:none">   
                </div>
                <div id="update_overlay" style="position: absolute;
                    width: 100%;
                    height: 100%;
                    background-color: #ffffff;
                    top: 0;
                    left: 0px;
                    z-index: 1000;
                    opacity: .8;">&nbsp;</div>
                    <script type="text/javascript">
                        window.SEGUNDOS_VALIDEZ_CODIGO = {$segundosCodigo};
                        $.getScript( "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js",
                            function( data, textStatus, jqxhr ) {
                                console.log(data);
                            $.getScript( "js/recovery.password.min.js", function(data, textStatus, jqxhr) {
                                $("#login").animate({
                                    'top':'200px',
                                    'left':'50%',
                                    'max-width': '800px',
                                    'width':'800px',
                                }, 300, 'swing', function() {
                                    $("#login").css('translate', '-25% 0%');
                                    $("#update_overlay").hide();
                                });
                                $("#btnCancel").on("click", function() {
                                    comunica("srv/Controlador.php", {metodo:"login"});  
                                });
                            });
                        });

                    </script>
                    
            <div>
        HTML;
     
        return $html;
    }

    public static function sesionBloqueada(): bool
    {
        $data = isset($_SESSION[_S]['recoveryData']) ? $_SESSION[_S]['recoveryData'] : null;
        if (!$data) {
            return false;
        }
        if (isset($data['count']) && $data['count'] >= 5) {
            $ahora = new DateTime('now', new DateTimeZone('America/Mexico_City'));
            $inicio = $data['inicio'];
            $res  = $ahora->diff(new DateTime($inicio), 'minute');
            $minutos = $res->i;
            if ($minutos < 10) {
                return true;
            } else {
                unset($_SESSION[_S]['recoveryData']);
            }
        }
        return false;
    }

    public static function enmascararCorreo(string $correo): string
    {
        $correo = strtolower(trim($correo));
        $partes = explode('@', $correo, 2);
        if (count($partes) !== 2 || $partes[0] === '' || $partes[1] === '') {
            return '***';
        }

        $local = $partes[0];
        $dominio = $partes[1];
        $visible = min(2, strlen($local));

        return substr($local, 0, $visible) . '***@' . $dominio;
    }

    public static function enviaCodigo(string $urusuario, string $username): string
    {
        if (!isset($_SESSION[_S]['recoveryData'])) {
            $_SESSION[_S]['recoveryData'] = ['count' => 0, 'inicio' => null];
        } elseif (!isset($_SESSION[_S]['recoveryData']['count'])) {
            $_SESSION[_S]['recoveryData']['count'] = 0;
            $_SESSION[_S]['recoveryData']['inicio'] = null;
        }

        $bdUsuario = BD_USUARIO;
        $ctrl = BD_CTRL;
        $usernameUp = strtoupper($username);
        // Pueden existir varios registros en {$bdUsuario}.cat_Usuario con el
        // mismo usr_usuario (homónimos en distintas UR). El INNER JOIN contra
        // {$ctrl}.cat_Usuario filtrando por la UR garantiza que se tome
        // exactamente el usuario que corresponde a esa UR en el ejercicio.
        $sql = <<<SQL
            SELECT u.usr_id, u.usr_usuario, u.usr_nombre, u.usr_paterno, u.usr_materno, u.usr_correo,
                   u.sts_id AS usr_sts,
                   v.vgu_ejercicio AS vgu_ejer,
                   us.usr_id AS ctrl_usr_id
            FROM {$bdUsuario}.cat_Usuario u
            INNER JOIN {$ctrl}.cat_Usuario AS us
                ON us.usr_id = u.usr_id
                AND CONCAT(us.gpo_id, us.ur_id) = :urusuario
            LEFT JOIN {$bdUsuario}.cat_VigenciaUsuario v
                ON v.usr_id = u.usr_id
                AND v.vgu_ejercicio = :ejercicio
            WHERE u.usr_usuario = :username
            LIMIT 1
        SQL;

        $servicio = new AccesoDatos();
        $servicio->setSql($sql);
        $servicio->conectar();
        $servicio->consultar([':urusuario' => $urusuario, ':ejercicio' => APP_EJERCICIO, ':username' => $usernameUp]);

        if ($servicio->getNumeroRegistros() == 0) {
            // No hay coincidencia exacta (usuario + UR) en el ejercicio actual.
            // Verificamos si el usuario existe en la base multianual para
            // diferenciar "no existe" de "no dado de alta en este ejercicio".
            $sqlExiste = <<<SQL
                SELECT 1
                FROM {$bdUsuario}.cat_Usuario
                WHERE usr_usuario = :username
                LIMIT 1
            SQL;
            $servicio->setSql($sqlExiste);
            $servicio->consultar([':username' => $usernameUp]);
            $existeMultianual = $servicio->getNumeroRegistros() > 0;
            $servicio->desconectar();
            return $existeMultianual ? 'sin_ejercicio' : 'no_existe';
        }

        $usuario = $servicio->extraer();
        $servicio->desconectar();

        if (empty($usuario['vgu_ejer'])) {
            return 'sin_ejercicio';
        }

        if ((int) $usuario['usr_sts'] !== 1) {
            if ((int) $usuario['usr_sts'] === 2) {
                return 'inactivo';
            } elseif ((int) $usuario['usr_sts'] === 3) {
                return 'sin_correo';
            }
        }

        $_SESSION[_S]['recoveryData']['usuario'] = $usuario;
        $usr_nombre = $usuario['usr_nombre'] . ' ' . $usuario['usr_paterno'] . ' ' . $usuario['usr_materno'];

        // Generar código de recuperación
        $codigo = rand(100000, 999999);
        $_SESSION[_S]['recoveryData']['codigo'] = (string)$codigo;
        $_SESSION[_S]['recoveryData']['fechaCodigo'] = date('Y-m-d H:i:s');

        $mail = new ServicioEmail();
        $mail->setAsunto('Recuperación de contraseña - SEFIP ' . APP_EJERCICIO);
        $mail->setCuerpo(self::plantillaCorreoRecuperacion($usr_nombre, (string) $codigo));
        $mail->setDestinatario($usuario['usr_correo'], $usr_nombre);
        $mail->enviar();

        return 'ok';
    }

    private static function plantillaCorreoRecuperacion(string $nombre, string $codigo): string
    {
        $nombreHtml = htmlspecialchars(trim($nombre), ENT_QUOTES, 'UTF-8');
        $codigoHtml = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');
        $ejercicio = APP_EJERCICIO;
        $segundos = SEGUNDOS_VALIDEZ_CODIGO;
        $validezTexto = $segundos >= 60
            ? (int) floor($segundos / 60) . ' minuto' . (floor($segundos / 60) > 1 ? 's' : '')
            : $segundos . ' segundos';

        return <<<HTML
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Recuperación de contraseña - SEFIP {$ejercicio}</title>
            </head>
            <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333333; background-color: #f4f4f4;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4; padding: 24px 0;">
                    <tr>
                        <td align="center">
                            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 24px 32px 16px; border-bottom: 3px solid #8B1E3F;">
                                        <img src="https://sefip.finanzasoaxaca.gob.mx/{$ejercicio}/images/logo-sistema-{$ejercicio}.png"
                                            alt="SEFIP {$ejercicio}" width="200" style="display: block; margin-bottom: 12px;">
                                        <h1 style="margin: 0; font-size: 20px; color: #8B1E3F; font-weight: bold;">Recuperación de contraseña</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 24px 32px;">
                                        <p style="margin: 0 0 16px; line-height: 1.6;">Estimado(a) <strong>{$nombreHtml}</strong>,</p>
                                        <p style="margin: 0 0 16px; line-height: 1.6;">
                                            Has solicitado recuperar tu contraseña de acceso a la plataforma
                                            <strong>SEFIP {$ejercicio}</strong>. Utiliza el siguiente código para continuar con el proceso:
                                        </p>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 24px 0;">
                                            <tr>
                                                <td align="center" style="background-color: #f8f0f3; border: 2px dashed #8B1E3F; border-radius: 8px; padding: 20px;">
                                                    <p style="margin: 0 0 8px; font-size: 13px; color: #666666; text-transform: uppercase; letter-spacing: 1px;">Tu código de verificación</p>
                                                    <p style="margin: 0; font-size: 32px; font-weight: bold; color: #8B1E3F; letter-spacing: 6px;">{$codigoHtml}</p>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 0 0 16px; line-height: 1.6; color: #555555;">
                                            Este código es válido por <strong>{$validezTexto}</strong>. Si expira, deberás solicitar uno nuevo desde la pantalla de inicio de sesión.
                                        </p>
                                        <p style="margin: 0 0 16px; line-height: 1.6; color: #555555;">
                                            Si no solicitaste este cambio, ignora este correo. Tu contraseña actual permanecerá sin cambios.
                                        </p>
                                        <p style="margin: 0; line-height: 1.6;">
                                            Saludos cordiales,<br>
                                            <strong>Equipo de soporte SEFIP</strong>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 32px; background-color: #fafafa; border-top: 1px solid #eeeeee;">
                                        <p style="margin: 0; font-size: 12px; color: #888888; line-height: 1.5;">
                                            Este es un mensaje automático, por favor no responda a este correo.<br>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        HTML;
    }

    public static function verificaCodigo(string $codigo): bool
    {
        if (!isset($_SESSION[_S]['recoveryData'])) {
            return false;
        }

        if (!isset($_SESSION[_S]['recoveryData']['codigo']) || !isset($_SESSION[_S]['recoveryData']['fechaCodigo'])) {
            return false;
        }
        $response = false;
        $data = $_SESSION[_S]['recoveryData'];
        $ahora = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $inicio = $data['fechaCodigo'];
        $res  = $ahora->diff(new DateTime($inicio), 'second');
        $segundos = $res->s;
        if ($segundos < SEGUNDOS_VALIDEZ_CODIGO && $data['codigo'] === $codigo) {
            $response = true;
        }
        return $response;
    }
}
