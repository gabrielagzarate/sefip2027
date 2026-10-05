<?php

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/scp/phpmailer/src/Exception.php';
require_once dirname(__FILE__) . '/../../lib/scp/phpmailer/src/PHPMailer.php';
require_once dirname(__FILE__) . '/../../lib/scp/phpmailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class emailNotificacionesP extends AccesoDatos
{
    private $mailP;

    public function __construct()
    {
        parent::__construct();
        $this->mailP = new PHPMailer(true);
    } // function_construct

    public function configEmailP($mod = '', $cvefin = '')
    {
        try {
            //Ajustes del servidor
            $this->mailP->isSMTP();                                              // Send using SMTP
            $this->mailP->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailP->CharSet = PHPMailer::CHARSET_UTF8;
            $this->mailP->Host = SMTP_HOST;
            $this->mailP->SMTPAuth = true;
            $this->mailP->Username = SMTP_NOTIFICACIONES_USER;
            $this->mailP->Password = SMTP_NOTIFICACIONES_PASS;
            $this->mailP->Port = SMTP_PORT;
            $this->mailP->SMTPOptions = [ // TCP port to connect to
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            //indicamos  correo y nombre para que lo visualice el usuario
            $this->mailP->setFrom(SMTP_NOTIFICACIONES_USER, 'Sefip Presupuesto');

            // Contenido del mensaje
            $this->mailP->isHTML(true);                                // Set email format to HTML
            $this->mailP->Subject = 'Previsión Aplicada';  // Asunto
            // $this->mail->Body    = 'Cuerpo del correo<b>aqui!</b>'; //cuerpo
            $this->mailP->Body = $this->body($this->mailP, $mod, $cvefin);
            // echo $_SERVER['SERVER_ADDR'];
            if ($_SERVER['SERVER_ADDR'] != '::1' && $_SERVER['SERVER_ADDR'] != '127.0.0.1') {
                $destinatarios = [ //destinatarios
                'PREVISIONES' => ' seguimiento.previsiones@gmail.com',
                ];

                foreach ($destinatarios as $email => $value) {
                    $this->mailP->addAddress($value, $email); //carga el destinatario
                }

                $this->mailP->addBCC('ariadna.prieto@tecnologias.oaxaca.gob.mx');
                $this->mailP->addBCC('adriana.perez@tecnologias.oaxaca.gob.mx');
            } else {
                $this->mailP->addAddress('ariadna.prieto@tecnologias.oaxaca.gob.mx', 'Ariadna');
            }

            $this->mailP->send(); //Reliza el envio
            $this->mailP->ClearAddresses(); // Limpia los "Address" cargados previamente para volver a cargar

            return 'Exito';//'El mensaje se ha enviado con exito';

        } catch (PHPMailerException $e) {
            $error = 'Error';
            $this->logEmail($this->mailP->ErrorInfo);
            return $error;
        } catch (Throwable $e) {
            $error = 'Error';
            $this->logEmail($e->getMessage());
            return $error;
        }
        $this->logEmail($this->mailP);
    } //fin confEmail

    public function body($mailP, $mod = '', $cvefin = '')
    {
        $datos = $this->datosFolio($mod);
        $prev = '';
        $row = '';
        foreach ($datos as $key) {
            $row .= ' 
                <tr>
                    <td>' . $key['ur'] . '</td>
                    <td></td>
                    <td>' . $key['clave'] . '</td>
                    <td></td>
                    <td>' . $key['oficio'] . '</td>
                    <td></td>
                    <td>' . $key['clavefin'] . '</td>
                    <td></td>
                    <td>&nbsp;&nbsp;$' . (number_format($key['importes'], 2)) . '</td>
                    <td></td>
                    <td>' . $key['fecha'] . '</td>
                    <td></td>
                    <td>' . $key['nfolio'] . '</td>
                </tr>';
        }

        $message = '
            <!DOCTYPE html>
            <html style="box-sizing: border-box; font-size: 14px; margin: 0;">
                <head>
                    <meta name="viewport" content="width=device-width" />
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        ';
        $message .= '
                </head>
                <body itemscope style="box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em; background-color: #f6f6f6; margin: 0; color: #747477;" bgcolor="#f6f6f6">
                    <table class="body-wrap" style="box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;" bgcolor="#f6f6f6">
                        <tr style="box-sizing: border-box; font-size: 14px; margin: 0;">
                            <td style="box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
                            <td class="container" width="600" style="box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;" valign="top">
                            <div class="content" style="box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                                <table class="main" width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-size: 14px; border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                                    <tr style="box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <td class="alert alert-warning" style="box-sizing: border-box; font-size: 16px; vertical-align: top; color: #696A69; font-weight: 500; text-align: center; border-radius: 3px 3px 0 0; background-color: #f9f7f7; margin: 0; padding: 20px;" align="center" bgcolor="#f9f7f7" valign="top"> <h2 class="text-center"></h2>
                                            <div class="thumbnail"><img src="https://sefip.finanzasoaxaca.gob.mx/2024/images/sefip.png"/></div>
                                        </td>
                                    </tr>
                                    <tr style="box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <td class="content-wrap" style="box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;" valign="top">
                                            <table width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-size: 14px; margin: 0;">
                                                <tr style="box-sizing: border-box; font-size: 14px; margin: 0;">
                                                    <td class="content-block" style="box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top"> Se notifica que se ha dado cobertura a la previsión solicitada para la clave de financiamiento ' . $cvefin . ' con los siguientes datos: <br>
                                                        <table class="egt">
                                                            <tr>
                                                                <th>UR</th>
                                                                <th></th>
                                                                <th>Cve Presupuestal de la Previsión</th>
                                                                <th></th>
                                                                <th>Oficio Autorización</th>
                                                                <th></th>
                                                                <th>Clave Financiamiento</th>
                                                                <th></th>
                                                                <th>Importe Total</th>
                                                                <th></th>
                                                                <th>Fecha y hora</th>
                                                                <th></th>
                                                                <th>No. Folio</th>
                                                            </tr>
                                                            ' . $row . '
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <div class="footer" style="box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                                    <table width="100%" style="box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <tr style="box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="aligncenter content-block" style="box-sizing: border-box; font-size: 12px; vertical-align: top; color: #999; text-align: center; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                        <td style="box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top">
                        </td>
                    </tr>
                </table>
            </body>
        </html>';
        return ($message);
    } //fin body

    public function datosFolio($mod = '')
    {
        $sql = '
            SELECT 
                d.omo_id, d.mod_gpo_id, d.mod_ur_id, d.mod_id, p.gpo_id, p.ur_id, p.ue_id, 
                p.epg_id,p.pg_id,p.pe_id,p.ecf_id, p.tcp_id,pe.pe_nombre, t.mov_nombre, 
                d.dmo_importe,emo_id, p.pre_claveCompleta,pv.pre_planeacion, mod_oficioArchivo, cf.ecf_clave, mod_fechaAutorizacion, CONCAT(d.gpo_id,d.ur_id) ur
            FROM scp_DetalleModificacion d
            INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER join presupuestoVigente pv using(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
            INNER JOIN (
                SELECT omo_id,mod_gpo_id,mod_ur_id,mod_id,emo_id, mod_oficioArchivo, mod_fechaAutorizacion
                FROM scp_MaestroModificacion
                ' . ($mod->getOmo_id() != '' ?
                " WHERE omo_id='" . $mod->getOmo_id() . "' " : '') . '
                        ' . ($mod->getMod_gpo_id() != '' ?
                ' AND mod_gpo_id=' . $mod->getMod_gpo_id() . ' ' : '') . '
                        ' . ($mod->getMod_ur_id() != '' ?
                ' AND mod_ur_id=' . $mod->getMod_ur_id() . ' ' : '') . '
                        ' . ($mod->getMod_id() != '' ?
                ' AND mod_id=' . $mod->getMod_id() . '' : '') . '
            ) FOLIO USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
            INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
            INNER JOIN cat_TipoMovimiento t using(mov_id)
            INNER JOIN enc_ClaveFinanciamiento cf USING(ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cv ON (cv.cff_id=p.cff_id and cv.fte_id=p.fte_id and cv.sft_id=p.sft_id and cv.ori_id=p.ori_id 
                and cv.sor_id=p.sor_id and cv.cfi_id=p.cfi_id and cv.cfi_anio=p.cfi_anio and cv.cfi_ejercicio2 = ' . APP_EJERCICIO . ')
            ' . ($mod->getOmo_id() != '' ? " WHERE d.omo_id='" . $mod->getOmo_id() . "' " : '') . '
            ' . ($mod->getMod_gpo_id() != '' ? ' AND d.mod_gpo_id=' . $mod->getMod_gpo_id() . ' ' : '') . '
            ' . ($mod->getMod_ur_id() != '' ? ' AND d.mod_ur_id=' . $mod->getMod_ur_id() . ' ' : '') . '
            ' . ($mod->getMod_id() != '' ? ' AND d.mod_id=' . $mod->getMod_id() . '' : '') . ';
            ';

        $sqlconexion = new AccesoDatos();
        $sqlconexion->conectar();
        $sqlconexion->setSql($sql);
        $sqlconexion->consultar();
        $sqlconexion->desconectar();
        $result = [];

        while ($row = $sqlconexion->extraer()) {
            if ($row['mod_oficioArchivo'] != '') {
                $oficio = $row['mod_archivoOficio'];
            } else {
                $oficio = '';
            }
            $dato = [
                'ur'       => ($row['ur']),
                'oficio'   => $oficio,
                'clavefin' => $row['ecf_clave'],
                'clave'    => $row['pre_claveCompleta'],
                'importes' => $row['dmo_importe'],
                'fecha'    => $row['mod_fechaAutorizacion'],
                'nfolio'   => $row['mod_id'],
            ];
            array_push($result, $dato);
        }//fin while
        return $result;
    } // Fin datosFolio

    public function logEmail($data)
    {
        $linea = 'CLASE[emailNotificacionesP]||ERROR[' . $data . ']';
        if (function_exists('escribirLogProyecto')) {
            escribirLogProyecto('errorEmail', $linea);
            return;
        }
        $dirLog = dirname(__DIR__, 2) . '/log';
        if (!is_dir($dirLog)) {
            @mkdir($dirLog, 0777, true);
        }
        @file_put_contents(
            $dirLog . '/errorEmail-' . date('Y-m-d') . '.log',
            date('d-m-Y H:i:s') . ' ' . $linea . PHP_EOL,
            FILE_APPEND
        );
    } // Fin logEmail
} // Fin class emailNotificaciones
