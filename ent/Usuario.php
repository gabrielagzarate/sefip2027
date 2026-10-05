<?php

require_once dirname(__FILE__) . "/Entidad.php";

class Usuario extends Entidad
{
    /*******************************************************
     * ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $usr_id; // Identificador del unico del Usuario
    protected $usuario; // Nombre de Usuario con el que accede al sistema
    protected $clave; // Contraseña de paso para el acceso al Sistema
    protected $rfc; // Registro Federal de Contribuyentes del Usuario
    protected $titulo; // Titulo academico del Usuario
    protected $nombre; // Nombre de Pila del Usuario
    protected $paterno; // Apellido Paterno del Ususario
    protected $materno; // Apellido Materno del Usuario
    protected $correo; // Correo electronico del Usuario
    protected $imagen; // Ruta de la Imagen o foto del Usuario
    protected $sexo; // Identificador del genero del usuario
    // F: Femenino
    // M: Masculino
    protected $puesto; // Nombre del puesto que ocupa
    protected $telefono; // Telefono del usuario
    protected $extension; // Extensión de telefono del usuario
    protected $nvj_id; // Identificador del nivel jerarquico del usuario. Depende de cat_NivelJerarquico
    protected $prf_id; // Identificador del perfil del Ususario. depende de cat_Perfil
    protected $sts_id; //
    protected $usu; //

    /* Tabla de encuadre donde obtendemos ue_id */
    protected $ue_id;

    /*******************************************************
     * METODOS GET
     ******************************************************/

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    public function getUsuario()
    {
        return $this->usuario->getVal();
    }

    public function getClave()
    {
        return $this->clave->getVal();
    }

    public function getRfc()
    {
        return $this->rfc->getVal();
    }

    public function getTitulo()
    {
        return $this->titulo->getVal();
    }

    public function getNombre()
    {
        return $this->nombre->getVal();
    }

    public function getPaterno()
    {
        return $this->paterno->getVal();
    }

    public function getMaterno()
    {
        return $this->materno->getVal();
    }

    public function getCorreo()
    {
        return $this->correo->getVal();
    }

    public function getImagen()
    {
        return $this->imagen->getVal();
    }

    public function getSexo()
    {
        return $this->sexo->getVal();
    }

    public function getPuesto()
    {
        return $this->puesto->getVal();
    }

    public function getTelefono()
    {
        return $this->telefono->getVal();
    }

    public function getExtension()
    {
        return $this->extension->getVal();
    }

    public function getNvj_id()
    {
        return $this->nvj_id->getVal();
    }

    public function getPrf_id()
    {
        return $this->prf_id->getVal();
    }

    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }

    public function getUsu()
    {
        return $this->usu->getVal();
    }

    public function getUe_id()
    {
        return null;
    }

    /*******************************************************
     * METODOS SET
     ******************************************************/
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    public function setUsuario($val)
    {
        $this->usuario->setVal($val);
    }

    public function setClave($val)
    {
        $this->clave->setVal($val);
    }

    public function setRfc($val)
    {
        $this->rfc->setVal($val);
    }

    public function setTitulo($val)
    {
        $this->titulo->setVal($val);
    }

    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }

    public function setPaterno($val)
    {
        $this->paterno->setVal($val);
    }

    public function setMaterno($val)
    {
        $this->materno->setVal($val);
    }

    public function setCorreo($val)
    {
        $this->correo->setVal($val);
    }

    public function setImagen($val)
    {
        $this->imagen->setVal($val);
    }

    public function setSexo($val)
    {
        $this->sexo->setVal($val);
    }

    public function setPuesto($val)
    {
        $this->puesto->setVal($val);
    }

    public function setTelefono($val)
    {
        $this->telefono->setVal($val);
    }

    public function setExtension($val)
    {
        $this->extension->setVal($val);
    }

    public function setNvj_id($val)
    {
        $this->nvj_id->setVal($val);
    }

    public function setPrf_id($val)
    {
        $this->prf_id->setVal($val);
    }

    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }

    public function setUsu($val)
    {
        $this->usu->setVal($val);
    }

    public function setUe_id()
    {
    }

    /*******************************************************
     * METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    public function staUsuario($val)
    {
        $this->usuario->staVal($val);
    }

    public function staClave($val)
    {
        $this->clave->staVal($val);
    }

    public function staRfc($val)
    {
        $this->rfc->staVal($val);
    }

    public function staTitulo($val)
    {
        $this->titulo->staVal($val);
    }

    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }

    public function staPaterno($val)
    {
        $this->paterno->staVal($val);
    }

    public function staMaterno($val)
    {
        $this->materno->staVal($val);
    }

    public function staCorreo($val)
    {
        $this->correo->staVal($val);
    }

    public function staImagen($val)
    {
        $this->imagen->staVal($val);
    }

    public function staSexo($val)
    {
        $this->sexo->staVal($val);
    }

    public function staPuesto($val)
    {
        $this->puesto->staVal($val);
    }

    public function staTelefono($val)
    {
        $this->telefono->staVal($val);
    }

    public function staExtension($val)
    {
        $this->extension->staVal($val);
    }

    public function staNvj_id($val)
    {
        $this->nvj_id->staVal($val);
    }

    public function staPrf_id($val)
    {
        $this->prf_id->staVal($val);
    }

    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }

    public function staUsu($val)
    {
        $this->usu->staVal($val);
    }

    public function staUe_id()
    {
    }

    /*******************************************************
     * CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {

        parent::__construct(array("tabla" => BD_USUARIO . ".cat_Usuario"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->usr_id = new Atributo("numero", "NO", "PRI", "usr_id");
        $this->usuario = new Atributo("cadena", "NO", "MUL", "usr_usuario");
        $this->clave = new Atributo("cadena", "NO", "", "usr_clave");
        $this->rfc = new Atributo("cadena", "NO", "", "usr_rfc");
        $this->titulo = new Atributo("cadena", "NO", "", "usr_titulo");
        $this->nombre = new Atributo("cadena", "NO", "", "usr_nombre");
        $this->paterno = new Atributo("cadena", "NO", "", "usr_paterno");
        $this->materno = new Atributo("cadena", "NO", "", "usr_materno");
        $this->correo = new Atributo("cadena", "NO", "", "usr_correo");
        $this->imagen = new Atributo("cadena", "NO", "", "usr_imagen");
        $this->sexo = new Atributo("cadena", "NO", "", "usr_sexo");
        $this->puesto = new Atributo("cadena", "NO", "", "usr_puesto");
        $this->telefono = new Atributo("cadena", "NO", "", "usr_telefono");
        $this->extension = new Atributo("numero", "NO", "", "usr_extension");
        $this->nvj_id = new Atributo("numero", "NO", "MUL", "nvj_id");
        $this->prf_id = new Atributo("cadena", "YES", "MUL", "prf_id");
        $this->sts_id = new Atributo("numero", "NO", "MUL", "sts_id");
        $this->usu = new Atributo("numero", "NO", "MUL", "usr_usu");
    }

    /*******************************************************
     * FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $usr = new Usuario();

        if (isset($p->usr_id)) {
            $usr->setUsr_id($p->usr_id);
        }

        if (isset($p->usuario)) {
            $usr->setUsuario($p->usuario);
        }

        if (isset($p->usr_clave) && $p->usr_clave != '') {
            $usr->setClave($p->usr_clave);
        }

        if (isset($p->rfc)) {
            $usr->setRfc($p->rfc);
        }

        if (isset($p->titulo)) {
            $usr->setTitulo($p->titulo);
        }

        if (isset($p->nombre)) {
            $usr->setNombre($p->nombre);
        }

        if (isset($p->paterno)) {
            $usr->setPaterno($p->paterno);
        }

        if (isset($p->materno)) {
            $usr->setMaterno($p->materno);
        }

        if (isset($p->correo)) {
            $usr->setCorreo($p->correo);
        }

        if (isset($p->imagen)) {
            $usr->setImagen($p->imagen);
        }

        if (isset($p->sexo)) {
            $usr->setSexo($p->sexo);
        }

        if (isset($p->puesto)) {
            $usr->setPuesto($p->puesto);
        }

        if (isset($p->telefono)) {
            $usr->setTelefono($p->telefono);
        }

        if (isset($p->extension)) {
            $usr->setExtension($p->extension);
        }

        if (isset($p->nvj_id)) {
            $usr->setNvj_id($p->nvj_id);
        }

        if (isset($p->prf_id)) {
            $usr->setPrf_id($p->prf_id);
        }

        if (isset($p->sts_id)) {
            $usr->setSts_id($p->sts_id);
        }

        if (isset($p->usu)) {
            $usr->setUsu($p->usu);
        }

        // Para la bitàcora...
        switch ($p->abc) {
            case "a":
                $bitacora = "NUEVA ";
                break;
            case "b":
                $bitacora = "ELIMINAR ";
                break;
            case "c":
                $bitacora = "MODIFICAR ";
                break;
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $usr->conectar();
        $usr->setSql('SET @usr_ip = INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
        $usr->consultar([$ip, $_SESSION[_S]['usr_id'], $bitacora . 'SOLICITUD']);

        switch ($p->abc) {
            case "a":
                $usr->insert(false);
                return $usr->getUltimoId();
                break;
            case "b":
                $usr->delete(false);
                break;
            case "c":
                $usr->update(false);
                break;
        }
    }
}
