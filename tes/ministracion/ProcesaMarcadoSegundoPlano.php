<?php
require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../lib/scp/phpmailer/src/SMTP.php';
require_once dirname(__FILE__) . '/../../lib/scp/phpmailer/src/PHPMailer.php';

class ProcesaMarcadoSegundoPlano
{
    public $tem_carga;
    public $tipo_carga;
    public $observacion;
    public $ip;
    public $usuario;
    public function __construct($tem_carga, $tipo_carga, $observacion, $ip, $usuario)
    {
        $this->tem_carga = $tem_carga;
        $this->tipo_carga = $tipo_carga;
        $this->observacion = $observacion;
        $this->ip = $ip;
        $this->usuario = $usuario;
    }
    function Execute()
    {   $data = $this->getCorreo($this->usuario);
        if ($data) {
            $correo = $data['usr_correo'];
        } else {
            $correo = 'antonio.santiago@dgtid.oaxaca.gob.mx';
        }
        $this->actualizaCarga($this->tem_carga, $this->usuario, $this->ip, 50, 'Inicio el Proceso de marcado de ministrado');
        $this->sendEmail($data['usr_correo'], 'Se incio el marcado de ministrado id: ' . $this->tem_carga . $this->observacion);
        $conexion = new AccesoDatos;
        switch ($this->tipo_carga) {
            case 7:
                $conexion->conectar();
                $conexion->setSql('SET @usr_id = :usr_id , @usr_ip = INET_ATON(:usr_ip),@bit_observacion= :bit_observacion, @tem_carga = :tem_carga ;');
                $paramSet = [
                    'usr_id' => $this->usuario,
                    'usr_ip' => $this->ip,
                    'bit_observacion' => $this->observacion,
                    'tem_carga' => $this->tem_carga,
                ];
                $conexion->consultar($paramSet);
                $conexion->setSql('CALL '.BD_TESORERIA.'.sp_CargaMarcado'.APP_EJERCICIO.'(@tem_carga,@usr_ip,@usr_id)');
                $conexion->consultar();
                $conexion->setSql("SELECT @banderaTesoreria, @mensajeTesoreria, @banderaPresupuesto, @mensajePresupuesto, @banderaContabilidad, @mensajeContabilidad;");
                $conexion->consultar();
                $res = $conexion->extraer();
                $conexion->desconectar();
                break;
            case 8:
                $conexion->conectar();
                $conexion->setSql('SET @usr_id = :usr_id , @usr_ip = INET_ATON(:usr_ip),@bit_observacion= :bit_observacion, @tem_carga = :tem_carga;');
                $paramSet = [
                    'usr_id' => $this->usuario,
                    'usr_ip' => $this->ip,
                    'bit_observacion' => $this->observacion,
                    'tem_carga' => $this->tem_carga,
                ];
                $conexion->consultar($paramSet);
                $conexion->setSql('CALL '.BD_TESORERIA.'.sp_CargaMarcado'.APP_EJERCICIO.'(@tem_carga,@usr_ip,@usr_id)');
                $conexion->consultar();
                $conexion->setSql("SELECT @banderaTesoreria, @mensajeTesoreria, @banderaPresupuesto, @mensajePresupuesto, @banderaContabilidad, @mensajeContabilidad;");
                $conexion->consultar();
                $res = $conexion->extraer();
                $conexion->desconectar();
                break;
            default:
                die('Error en el tipo de carga');
                break;
        }
        $msg = $res['@mensajeTesoreria'];
        $this->actualizaCarga($this->tem_carga, $this->usuario, $this->ip, 100, 'Termino el proceso de marcado de ministrado ' . $msg);
        $this->sendEmail($correo, 'Termino el proceso de marcado de ministrado: ' . $this->tem_carga);
    }

    public function sendEmail($usr_email, $mensaje)
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Port = SMTP_PORT;
        $mail->Username = SMTP_NOTIFICACIONES_USER;
        $mail->Password = SMTP_NOTIFICACIONES_PASS;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom(SMTP_NOTIFICACIONES_USER, 'Tesoreria');
        $mail->isHTML(true);
        $mail->Subject = 'Marcado de Ministrado';
        $mail->Body  = $mensaje;
        $destinatarios = array(
            'user' => $usr_email,
        );
        $mail->addBCC("gabriela.garcia@dgtid.oaxaca.gob.mx");
        $mail->addBCC("antonio.santiago@dgtid.oaxaca.gob.mx");
    
        foreach ($destinatarios as $email => $value) {
            $mail->addAddress($value, $email);
            $mail->send();
            $mail->ClearAddresses();
        }
    }
    private function actualizaCarga($tem_carga, $usuario, $ip, $estado, $observacion)
    {
        $c = new AccesoDatos();
        $c->conectar();
        $sql = 'SET @usr_id = :usr_id , @usr_ip = INET_ATON(:usr_ip), @bit_observacion= :bit_observacion;';
        $paramSet = [
            'usr_id' => $usuario,
            'usr_ip' => $ip,
            'bit_observacion' => $observacion,
        ];
        $c->setSql($sql);
        $c->consultar($paramSet);
        $sql = '
            UPDATE cat_CargaMasiva
            SET ecr_id = :ecr_id
            WHERE  tem_Carga = :tem_Carga
            ';
        $paramUpdate = [
            'ecr_id' => $estado,
            'tem_Carga' => $tem_carga,
        ];
        $c->setSql($sql);
        $c->consultar($paramUpdate);
        $c->desconectar();
    }

    function getCorreo($usuario)
    {
        $c = new AccesoDatos();
        $c->conectar();
        $sql = 'SELECT usr_correo FROM '.BD_USUARIO.'.cat_Usuario WHERE  usr_id = ? and  usr_correo <> " " limit 1
            ';
        $params = [$usuario];
        $c->setSql($sql);
        $c->consultar($params);
        $c->desconectar();
        $data = $c->extraer();;
        if ($c->getNumeroRegistros() == 0) {
            return false;
        } else {
            return $data;
        }
    }

}
$tem_carga = $argv[1];
$tipo_carga = $argv[2];
$observacion = $argv[3];
$ip = $argv[4];
$usuario  =  $argv[5];
$procesa = new ProcesaMarcadoSegundoPlano($tem_carga, $tipo_carga, $observacion, $ip, $usuario);
$procesa->Execute();
