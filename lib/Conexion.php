<?php
/* ------------------------------------------------------------------------------------------------------------------ */
// File name   : Conexion.php
// Version     : 2.0
// Begin       : --/--/--
// Last Update : 20/07/2017
// -------------------------------------------------------------------
//@autor ???
//UPDATE @autor LI Japhet Mata Arellanes
/* ****************************************************************************************************************** */
/*  GARANTIA: SI LO ROMPES PUEDES CONSERVAR LOS PEDAZOS

NOTAS DE LA VERSION:
-SE SEPARA ESTA CLASE DEL FICHERO ACCESO DATOS
-SE AGREGA LA OPCION PUERTO Y SUS FUNCIONES SET Y GET
 */
class Conexion
{
    private $servidor;
    private $usuario;
    private $password;
    private $basedatos;
    private $puerto = 3306;
    private $type;
    private PDO|null $resource;
    private PDOStatement $result;

    public function getResult(): PDOStatement | null
    {
        if ($this->result instanceof PDOStatement) {
            return $this->result;
        }

        return null;
    }

    public function setResult(PDOStatement $res): void
    {
        $this->result = $res;
    }

    public function getPDOConnectionString(): string
    {
        $type = strtolower($this->getType());
        $string = "";
        switch ($type) {
            case 'mysql':
                $string = "{$type}:host={$this->getServidor()};dbname={$this->getBaseDatos()};port={$this->getPuerto()}";
                break;
            case 'sqlsrv':
                $string = "{$type}:Server={$this->getServidor()},{$this->getPuerto()};Database={$this->getBaseDatos()}";
                break;
            default:
                $string = "{$type}:host={$this->getServidor()};dbname={$this->getBaseDatos()};port={$this->getPuerto()}";
                break;
        }
        return $string;
    }

    public function getResource(): PDO
    {
        return $this->resource;
    }

    public function setResource(PDO | null $r): void
    {
        $this->resource = $r;
    }
    public function getServidor()
    {
        return $this->decypher($this->servidor);
    }
    public function getUsuario()
    {
        return $this->decypher($this->usuario);
    }
    public function getPassword()
    {
        return $this->decypher($this->password);
    }
    public function getBaseDatos()
    {
        return $this->decypher($this->basedatos);
    }
    public function getPuerto()
    {
        return $this->decypher($this->puerto);
    }
    public function getType()
    {
        return $this->decypher($this->type);
    }
    public function setServidor($val)
    {
        $this->servidor = $this->cypher($val);
    }
    public function setUsuario($val)
    {
        $this->usuario = $this->cypher($val);
    }
    public function setPassword($val)
    {
        $this->password = $this->cypher($val);
    }
    public function setBaseDatos($val)
    {
        $this->basedatos = $this->cypher($val);
    }
    public function setPuerto($val)
    {
        $this->puerto = $this->cypher($val);
    }
    public function setType($val)
    {
        $this->type = $this->cypher($val);
    }
    private function cypher($data)
    {
        $iv = '1234567812345678';
        $method = 'AES-256-CBC';
        $pass = '1234';
        return openssl_encrypt($data, $method, $pass, true, $iv);
    }
    private function decypher($data)
    {
        $iv = '1234567812345678';
        return openssl_decrypt($data, 'AES-256-CBC', '1234', true, $iv);
    }
}
