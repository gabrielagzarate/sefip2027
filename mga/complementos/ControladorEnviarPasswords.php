<?php

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ServicioUsuario.php';

class ControladorEnviarPasswords extends WebResponse
{
    private ServicioUsuario $servicioUsuario;
    private int $id;
    private int $ejercicio;

    private const CORREOS_PRUEBA = [
        ['email' => 'gabriela.garcia@tecnologias.oaxaca.gob.mx', 'name' => 'Gabriela Garcia'],
        ['email' => 'adriana.perez@tecnologias.oaxaca.gob.mx', 'name' => 'Adriana Perez'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->servicioUsuario = new ServicioUsuario();
        $this->ejercicio = APP_EJERCICIO;
        switch ($this->metodo) {
            default:
                $this->index();
                break;
            case 'llenaTablaUsuarios':
                $this->llenaTablaUsuarios();
                break;
            case 'enviaPasswords':
                $this->enviaPasswords();
                break;
        }
        $this->returnJson();
    }

    private function index()
    {
        $btnEnviar = new stdClass();
        $btnEnviar->id = "btnEnviaATodos";
        $btnEnviar->color = "verde";
        $btnEnviar->value = "Enviar a todos";
        $btnEnviar->icon = "solid fa-paper-plane me-2";

        $this->agregaTools($this->ponBoton($btnEnviar));
        $tabla = new stdClass();
        $tabla->id = 'tabla-usuarios';
        $tabla->titulo = 'Usuarios titulares de UR';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaUsuarios';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'Enviar';
        $th->width = '5px';
        array_push($tr, $th);
        
        $th = new stdClass();
        $th->texto = 'Id';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Ur';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Usuario';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Correo';
        array_push($tr, $th);

        $trbody = array();
        $td = new stdClass();
        $td->tipo = "boton";
        $td->icono = "paper-plane";
        $td->class = "btn-send-email";
        $td->attr = new stdClass();
        $td->attr->usr_id ='usr_id';
        $td->attr->usr_usuario ='usr_usuario';
        $td->attr->gpo_id ='gpo_id';
        $td->attr->ur_id ='ur_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_correo';
        array_push($trbody, $td);
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript(
            $this->wrLlamaLlenaTabla('tabla-usuarios').
            $this->getMainJs()
        );
    }

    private function llenaTablaUsuarios()
    {
        $ejercicio = APP_EJERCICIO;
        $this->setSql($this->sqlUsuariosTitulares());

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tabla-usuarios';
        $nav->nf = '999';
        $this->wrLlenaTabla($nav);
        $js = <<<JS
            $(".btn-send-email").on('click', function() {
                const btn = $(this);
                const id = btn.attr("usr_id");
                const usuario = btn.attr("usr_usuario");
                const gpoId = btn.attr("gpo_id");
                const urId = btn.attr("ur_id");
                const usu = '<span style="color:red; font-size:15px; font-weight:bold;">' + usuario + '</span>';
                const ur = '<span style="color:red; font-size:15px; font-weight:bold;">' + gpoId + urId + '</span>';
                alertify.confirm( "<h4>Actualizar y enviar contraseña</h4>",
                    "Actualizaras y enviaras la contraseña del usuario " + usu + " de la UR " + ur + ", esto no se puede revertir, deseas continuar?",
                    function() {
                        /*alertify.prompt("Confirmar",
                            "<h4>Puedes continuar con el proceso bajo tu completa responsabilidad,Escribe <b>ENTERADO</b> para realizar este proceso</h4>",
                            "",
                            function (evt, word) {
                                if (word === "ENTERADO") {*/
                                    procesaTodos = false;
                                    btn.addClass("pending").hide()
                                    .prop("id",id)
                                    .parent()
                                    .append($("<span/>").empty().html("pendiente"));
                                    window.preparaBotonTabla(id);
                                    $("#btnEnviaATodos").hide().attr("disable");
                                    comunica("{$this->pantalla}", {
                                        metodo:"enviaPasswords",
                                        id: id,
                                        ejercicio: {$ejercicio}
                                    })
                                /*} else {
                                    notify( "Escribiste: " + word + ".   Debes escribir ENTERADO", "warning" );
                                }
                            },
                            undefined
                        ).set("labels", {ok: "Proceder", cancel: "Cancelar"});*/
                    },
                    undefined
                ).set("defaultFocus","cancel").set("labels", {ok: "Yo se lo que hago", cancel: "Cancelar"});
            });
        JS;
        $this->mixScript($js);
    }

    public function enviaPasswords()
    {
        $this->id = (isset($this->prm->id)) ? intval($this->prm->id) : 0;
        $usuario = (object) $this->getUsuarioPorId($this->id);
        if (!$usuario) {
            $this->response("El usuario no existe");
            return;
        }

        if (!$usuario->usr_correo
            || empty($usuario->usr_correo)
            || !filter_var($usuario->usr_correo, FILTER_VALIDATE_EMAIL)
        ) {
            $this->response("El usuario no tiene correo valido");
            return;
        }

        $password = $this->generaPassword();
        $fecha = (new DateTime('now', new DateTimeZone('America/Mexico_City')))->sub(new DateInterval('P31D'));

        if ($this->servicioUsuario->updatePasswordOnDb(
            $this->id,
            $password,
            'ENVIO DE CONTRASEÑA',
            $fecha
        ) ) {
            $res = $this->sendEmail($password, $usuario);
            if ($res !== true) {
                $this->response("Error al enviar el correo: {$res}");
                return;
            } else {
                $this->response(true);
                return;
            }
        }
        $this->response("Error al actualizar la contraseña del usuario: {$this->id}");
    }

    private function esAmbienteProduccion(): bool
    {
        return SERVER_PRODUCCION;
    }

    public function response($error)
    {
        $js = is_string($error) ?
            <<<JS
                procesaTodos = false;
                notify("{$error}", "error");
                $("#{$this->id}").show().siblings(['i']).remove();
            JS
            :
            <<<JS
                $("#td_{$this->id}").html("<div class=\'div_ajustable\'>Correo enviado</div>");
                $("#{$this->id}").remove();
                enviarEmailATodos("{$this->pantalla}");
            JS;
        $this->mixScript($js);
    }

    public function generaPassword():string
    {   
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < 10; $i++) {
            $password .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $password;

    }

    private function sendEmail(string $password, mixed $usuario): mixed
    {
        $mailService = new ServicioEmail();
        $mailService->setAsunto('SEFIP ' . APP_EJERCICIO . ' | Entrega de credenciales de acceso');
        $mailService->setCuerpo($this->getEmailTemplate($usuario, $password, $this->esAmbienteProduccion()));

        if ($this->esAmbienteProduccion()) {
            $mailService->setDestinatario(strtolower($usuario->usr_correo), $usuario->usr_nombreCompleto);
            $mailService->setDestinatariosOcultos(self::CORREOS_PRUEBA);
        } else {
            $mailService->setDestinatarios(self::CORREOS_PRUEBA);
        }

        return $mailService->enviar();
    }

    private function getTextosEmailPorPuesto(?string $usrPuesto): array
    {
        $puesto = trim((string) $usrPuesto);
        $esAdministrativo = stripos($puesto, 'administrativo') !== false;

        $leyendaPuesto = $esAdministrativo
            ? 'Administrativo de la UR'
            : 'Titular de la UR';

        return [
            'leyendaPuesto' => $leyendaPuesto,
            'tipoUsuario' => $esAdministrativo ? 'administrativo' : 'titular',
            'destinatarioAmbientePrueba' => $esAdministrativo
                ? 'administrativo de la UR'
                : 'titular de la UR',
        ];
    }

    private function getEmailTemplate(mixed $usuario, string $password, bool $esProduccion): string
    {
        $ejercicio = $this->ejercicio;
        $ur = htmlspecialchars((string) ($usuario->gpo_id ?? '') . (string) ($usuario->ur_id ?? ''), ENT_QUOTES, 'UTF-8');
        $usrUsuario = htmlspecialchars((string) $usuario->usr_usuario, ENT_QUOTES, 'UTF-8');
        $passwordHtml = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
        $urlAcceso = "https://sefip.finanzasoaxaca.gob.mx/{$ejercicio}";
        $textosPuesto = $this->getTextosEmailPorPuesto($usuario->usr_puesto ?? '');
        $leyendaPuesto = htmlspecialchars($textosPuesto['leyendaPuesto'], ENT_QUOTES, 'UTF-8');
        $tipoUsuario = htmlspecialchars($textosPuesto['tipoUsuario'], ENT_QUOTES, 'UTF-8');
        $destinatarioAmbientePrueba = htmlspecialchars(
            $textosPuesto['destinatarioAmbientePrueba'],
            ENT_QUOTES,
            'UTF-8'
        );

        $avisoPrueba = '';
        if (!$esProduccion) {
            $correoUsuario = htmlspecialchars(strtolower((string) $usuario->usr_correo), ENT_QUOTES, 'UTF-8');
            $avisoPrueba = <<<HTML
                <tr>
                    <td style="padding: 0 32px 16px;">
                        <div style="padding: 12px 16px; background-color: #fff8e6; border-left: 4px solid #e6ad2f; color: #5c4a1f; font-size: 13px; line-height: 1.5;">
                            <strong>Correspondencia de ambiente de prueba.</strong>
                            Este mensaje no fue remitido al {$destinatarioAmbientePrueba}.
                            Destinatario institucional registrado: {$correoUsuario}
                        </div>
                    </td>
                </tr>
            HTML;
        }

        return <<<HTML
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Credenciales de acceso SEFIP {$ejercicio}</title>
            </head>
            <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333333; background-color: #f4f4f4;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4; padding: 24px 0;">
                    <tr>
                        <td align="center">
                            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 24px 32px 16px; border-bottom: 3px solid #8E418D;">
                                        <img src="https://sefip.finanzasoaxaca.gob.mx/{$ejercicio}/images/logo-sistema-{$ejercicio}.png"
                                            alt="SEFIP {$ejercicio}" width="200" style="display: block; margin-bottom: 12px;">
                                        <p style="margin: 0; font-size: 18px; color: #666666; letter-spacing: 0.5px; text-transform: uppercase;">
                                            Sistema Estatal de Finanzas Públicas {$ejercicio}
                                        </p>
                                        <h1 style="margin: 8px 0 0; font-size: 20px; color: #8E418D; font-weight: bold;">
                                            Credenciales de acceso
                                        </h1>
                                    </td>
                                </tr>
                                {$avisoPrueba}
                                <tr>
                                    <td style="padding: 24px 32px;">
                                        <p style="margin: 0 0 16px; line-height: 1.6;">
                                            Estimado(a) <strong>{$leyendaPuesto} {$ur}</strong>:
                                        </p>
                                        <p style="margin: 0 0 16px; line-height: 1.6; text-align: justify;">
                                            Por medio del presente, se le hace entrega de las credenciales de acceso a la plataforma
                                            <strong>SEFIP {$ejercicio}</strong>, con el carácter de usuario {$tipoUsuario} de la Unidad Responsable,
                                            para el ejercicio fiscal {$ejercicio}.
                                        </p>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 20px 0; background-color: #fafafa; border: 1px solid #eeeeee; border-radius: 6px;">
                                            <tr>
                                                <td style="padding: 14px 18px; border-bottom: 1px solid #eeeeee;">
                                                    <span style="display: block; font-size: 11px; color: #888888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">URL de acceso</span>
                                                    <a href="{$urlAcceso}" style="color: #8E418D; text-decoration: none; font-weight: bold;">{$urlAcceso}</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 14px 18px; border-bottom: 1px solid #eeeeee;">
                                                    <span style="display: block; font-size: 11px; color: #888888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Usuario</span>
                                                    <span style="font-size: 15px; font-weight: bold; color: #333333;">{$usrUsuario}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 14px 18px;">
                                                    <span style="display: block; font-size: 11px; color: #888888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Contraseña temporal</span>
                                                    <span style="font-size: 18px; font-weight: bold; color: #8E418D; letter-spacing: 2px;">{$passwordHtml}</span>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 0 0 12px; line-height: 1.6; text-align: justify;">
                                            <strong>Disposiciones de seguridad:</strong> en el primer ingreso, el sistema solicitará el cambio
                                            de contraseña. Asimismo, deberá actualizarla de manera periódica cada 30 días.
                                        </p>
                                        <p style="margin: 0 0 16px; line-height: 1.6; text-align: justify;">
                                            Para cualquier aclaración o soporte técnico relacionado con el acceso, le invitamos a consultar el
                                            portal de ayuda en
                                            <a href="https://ayuda.finanzasoaxaca.gob.mx/" style="color: #8E418D;">ayuda.finanzasoaxaca.gob.mx</a>.
                                        </p>
                                        <p style="margin: 0; line-height: 1.6;">
                                            Atentamente,<br>
                                            <strong>Agencia de Tecnologías e Innovación Digital</strong><br>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 32px; background-color: #fafafa; border-top: 1px solid #eeeeee;">
                                        <p style="margin: 0; font-size: 12px; color: #888888; line-height: 1.5;">
                                            Este es un mensaje automático. Le solicitamos no responder a este correo.
                                            La información contenida es confidencial y de uso exclusivo del destinatario.
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

    private function getMainJs(): string
    {
        return <<<JS
            $.getScript( "js/users.service.min.js", function( data, textStatus, jqxhr ) {

                $("#btnEnviaATodos").show().removeAttr("disable");
                $("#btnEnviaATodos").off('click').on('click', function() {
                    alertify.confirm( "ACTUALIZARAS Y ENVIARAS CONTRASEÑAS",
                        "Actualizaras y enviaras las contraseñas de todos los usuarios Titulares de las URs. ¿Deseas continuar? esto no se puede deshacer.",
                        function() {
                            alertify.prompt("Confirmar",
                                "<h4>Puedes continuar con el proceso bajo tu completa responsabilidad, ya que actualizar las contraseñas tendrá un impacto en todos los usuarios.Escribe <b>ENTERADO</b> para realizar este proceso</h4>",
                                "",
                                function (evt, word) {
                                    if (word === "ENTERADO") {
                                        $(this).hide();
                                        window.minall();
                                        enviarEmailATodos("{$this->pantalla}");
                                    } else {
                                            notify( "Escribiste: " + word + ".   Debes escribir ENTERADO", "warning" );
                                    }
                                },
                                undefined
                            ).set("labels", {ok: "Proceder", cancel: "Cancelar"});
                        },
                        undefined
                    ).set("labels", {ok: "Yo SOY JEFA, se lo que hago", cancel: "Cancelar"});
                    
                });
                window.usrServiceLoaded = true
            });
        JS;
    }

    private function getUsuarioPorId(int $id):mixed
    {
        $this->conectar();
        $this->setSql($this->sqlUsuariosTitulares('AND u.usr_id = :usr_id'));
        $this->consultar([':usr_id' => $id]);
        $this->desconectar();
        return $this->getNumeroRegistros() > 0 ? $this->extraer() : null;
    }

    private function sqlUsuariosTitulares(string $extraWhere = ''): string
    {
        $dbUsuarios = BD_USUARIO;
        $dbCtrl = BD_CTRL;
        $ejercicio = APP_EJERCICIO;

        return <<<SQL
            SELECT
                u.usr_id, ur.gpo_id, ur.ur_id,
                CONCAT(ur.gpo_id, ur.ur_id, ' - ', ur.ur_nombre) AS ur_nombre,
                u.usr_usuario, u.usr_fecha, u.usr_correo, u.usr_puesto,
                TRIM(CONCAT_WS(' ', u.usr_nombre, u.usr_paterno, u.usr_materno)) AS usr_nombreCompleto
            FROM
                {$dbUsuarios}.cat_Usuario AS u
                INNER JOIN {$dbUsuarios}.cat_VigenciaUsuario vu USING (usr_id)
                INNER JOIN {$dbCtrl}.cat_Usuario cu USING (usr_id)
                INNER JOIN {$dbCtrl}.cat_UnidadResponsable ur USING (uur_id)
            WHERE
                vu.sts_id = 1 AND cu.usr_titular = 1
                AND vu.vgu_ejercicio = {$ejercicio}
                AND ur_vigente = "SI"
                {$extraWhere}
            ORDER BY ur.gpo_id, ur.ur_id, u.usr_usuario
        SQL;
    }
}

if (in_array(strtolower((String) getSession('usu', true)), ['ggarciaz','alopezp'])) {
    new ControladorEnviarPasswords();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
