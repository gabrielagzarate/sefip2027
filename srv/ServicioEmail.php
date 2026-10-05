<?php

require_once dirname(__FILE__) . '/../lib/constantes.php';
$vendorAutoload = dirname(__FILE__) . '/../vendor/autoload.php';
if (is_readable($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/Exception.php';
    require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/PHPMailer.php';
    require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class ServicioEmail
{
    private $to =  [];
    private $subject = '';
    private $body = '';
    private $bcc = [];
    private PHPMailer $mail;

    public function __construct()
    {
        
    }

    public function setAsunto(string $subject)
    {
        $this->subject = $subject;
    }

    public function setDestinatario(string $mail, string $nombre = '')
    {
        $this->to[] = ['email' => $mail, 'name' => $nombre];
    }

    public function setDestinatarios(array $data)
    {
        foreach ($data as $d) {
            if ($res = $this->validateEmailAndName($d)) {
                $this->to[] = $res;
            }
        }
    }

    public function setDestinatarioOculto(string $email, string $nombre = '')
    {
        $this->bcc[] = ['email' => $email, 'name' => $nombre];
    }

    public function setDestinatariosOcultos(array| string $data)
    {
        foreach ($data as $d) {
            if ($res = $this->validateEmailAndName($d)) {
                $this->bcc[] = $res;
            }
        }
    }

    public function setCuerpo(string $body)
    {
        $this->body = $body;
    }

    public function enviar(): bool|string
    {
        try {
            $this->_buildConf();
            
            if (count($this->to) == 0) {
                throw new Exception("No hay destinatarios para enviar el correo");
            }

            if ($this->body == '') {
                throw new Exception("El cuerpo del correo no puede estar vacio");
            }
            if ($this->subject == '') {
                $this->mail->Subject = 'Notificación Sefip';    
            }

            foreach ($this->to as $dest) {
                $this->mail->addAddress($dest['email'], $dest['name']);
            }
            foreach ($this->bcc as $bcc) {
                $this->mail->addBCC($bcc['email'], $bcc['name']);
            }
            $this->mail->Body = $this->body;
            $this->mail->Subject = $this->subject;
            $this->mail->send();
            return true;
        } catch (PHPMailerException $e) {
            $this->logFalloEmail($e->getMessage());
            return false;
        } catch (Throwable $e) {
            $this->logFalloEmail($e->getMessage());
            return false;
        }
    }

    private function logFalloEmail(string $mensaje): void
    {
        $linea = 'CLASE[ServicioEmail]||'
            . 'CLIENTE[' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . ']||'
            . 'ERROR[' . $mensaje . ']||'
            . 'ASUNTO[' . $this->subject . ']';
        if (function_exists('escribirLogProyecto')) {
            escribirLogProyecto('errorEmail', $linea);
            return;
        }
        $dirLog = dirname(__DIR__) . '/log';
        if (!is_dir($dirLog)) {
            @mkdir($dirLog, 0777, true);
        }
        @file_put_contents(
            $dirLog . '/errorEmail-' . date('Y-m-d') . '.log',
            date('d-m-Y H:i:s') . ' ' . $linea . PHP_EOL,
            FILE_APPEND
        );
    }

    private function _buildConf()
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_NOTIFICACIONES_USER;
        $mail->Password   = SMTP_NOTIFICACIONES_PASS;
        $mail->Port       = SMTP_PORT; 
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom(SMTP_NOTIFICACIONES_USER, 'Sefip Notificaciones'); 
        $mail->isHTML(true);
        $this->mail = $mail;
    }

    private function validateEmailAndName(array $data): array | bool
    {
        if (array_is_list($data)) {
            $email = (filter_var($data[0], FILTER_VALIDATE_EMAIL) ? 0: (filter_var($data[1], FILTER_VALIDATE_EMAIL) ? 1 : null));
            if (is_null($email)) {
                return false;
            }
            return ['email' => $data[$email], 'name' => $data[1 - $email] ?? ''];
            
        } else {
            if (
                (!array_key_exists('email', $data) || $data['email'] == '') || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
                || (array_key_exists('name', $data) && !is_string($data['name']) || $data['name'] == '')
            ) {
                return false;
            };
            return ['email' => $data['email'], 'name' => $data['name'] ?? ''];
        }
        return false;
    }
}
