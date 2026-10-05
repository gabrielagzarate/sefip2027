<?php

require_once dirname(__FILE__) . '/../Entidad.php';
require_once dirname(__FILE__) . '/../VinUnidadResponsable.php';
require_once dirname(__FILE__) . '/enc_JerarquiaSefin.php';
require_once dirname(__FILE__) . '/VigenciaUsuario.php';

class UsuarioPrevio extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $usp_id; //Identificador de la tabla cat_UsuarioPrevio
    protected $uur_id; //Representación de la tabla cat_UsuarioPrevio
    protected $ue_id; //Representación de unidad ejecutora a la que pertenece el usuario
    protected $nvj_id; //cat_NivelJerarquico - Llave foránea - Nivel jerárquico al que pertenece el usuario
    protected $prf_id; //cat_Perfil - Llave foránea - Perfil al que pertenece el usuario
    protected $ejs_id; //Representación de la tabla ene el ejerccio correspondiente en enc_JerarquiaSefin. Se guardan los datos de la subsecretaria, unidad departamento y dirección.
    protected $usp_rfc; //RFC del usuario
    protected $usp_usuario; //Nombre de usuario
    protected $usp_clave; //Contraseña de usuario MD5
    protected $usp_claveAcceso; //Contraseña de usuario AES
    protected $usp_nombre; //Nombre del usuario
    protected $usp_paterno; //Apellido paterno del usuario
    protected $usp_materno; //Apellido materno del usuario
    protected $usp_sexo; //Sexo del usuario h/m
    protected $usp_puesto; //Puesto del usuario
    protected $usp_titulo; //Título o profesión del usuario
    protected $usp_correo; //Correo electrónico del usuario
    protected $usp_telefono; //Telefono del usuario
    protected $usp_extension; //Extensión de telefono del usuario
    protected $usp_nuevo; //El usuario es nuevo o ya existe
    protected $usp_ejercicio; // Ejercicio al que pertenece el usuario
    protected $usp_usu; //Usuario que aplica la creación del usuario o permisos
    protected $sac_id; //cat_UsuarioPrevio - Llave foránea- Identificador de la solcitud de acceso
    protected $usr_id; //Usuario que ingresa el registro
    protected $sts_id; // Estado: 1: Habilitado 2: Inhabilitado ----> cat_Estado

    /* Tabla de vinculación donde obtendremos gpo_id + ur_id */
    protected $vinUnidadResponsable;

    /* Tabla de encuadre donde obtendemos ssc_id, dir_id, uni_id y dep_id */
    protected $encJerarquiaSefin;

    /* Tabla de encuadre donde obtendemos las fechas de vigencia */
    protected $encVigencia;

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getUsp_id()
    {
        return $this->usp_id->getVal();
    }
    public function getUur_id()
    {
        return $this->uur_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getNvj_id()
    {
        return $this->nvj_id->getVal();
    }
    public function getPrf_id()
    {
        return $this->prf_id->getVal();
    }
    public function getEjs_id()
    {
        return $this->ejs_id->getVal();
    }
    public function getRfc()
    {
        return $this->usp_rfc->getVal();
    }
    public function getUsuario()
    {
        return $this->usp_usuario->getVal();
    }
    public function getClave()
    {
        return $this->usp_clave->getVal();
    }
    public function getClaveAcceso()
    {
        return $this->usp_claveAcceso->getVal();
    }
    public function getNombre()
    {
        return $this->usp_nombre->getVal();
    }
    public function getPaterno()
    {
        return $this->usp_paterno->getVal();
    }
    public function getMaterno()
    {
        return $this->usp_materno->getVal();
    }
    public function getSexo()
    {
        return $this->usp_sexo->getVal();
    }
    public function getPuesto()
    {
        return $this->usp_puesto->getVal();
    }
    public function getTitulo()
    {
        return $this->usp_titulo->getVal();
    }
    public function getCorreo()
    {
        return $this->usp_correo->getVal();
    }
    public function getTelefono()
    {
        return $this->usp_telefono->getVal();
    }
    public function getExtension()
    {
        return $this->usp_extension->getVal();
    }
    public function getNuevo()
    {
        return $this->usp_nuevo->getVal();
    }
    public function getEjercicio()
    {
        return $this->usp_ejercicio->getVal();
    }
    public function getUsu()
    {
        return $this->usp_usu->getVal();
    }
    public function getSac_id()
    {
        return $this->sac_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }

    // Obtenemos los datos de la vinculación ( vin_UnidadResponsable ) para que sea transparente para el usuario
    public function getGpo_id()
    {
        return $this->vinUnidadResponsable->getGpo_id();
    }
    public function getUr_id()
    {
        return $this->vinUnidadResponsable->getUr_id();
    }
    public function getVur_ejercicio()
    {
        return $this->vinUnidadResponsable->getVur_Ejercicio();
    }
    public function getVur_nombre()
    {
        return $this->vinUnidadResponsable->getVur_nombre();
    }
    public function getVur_urClave()
    {
        return $this->vinUnidadResponsable->getVur_urClave();
    }

    // Obtenemos los datos de la estructura organica (ctrl2019.enc_JerarquiaSefin)
    // de una manera transparente para el usuario
    public function getSsc_id()
    {
        return $this->encJerarquiaSefin->getSsc_id();
    }
    public function getDir_id()
    {
        return $this->encJerarquiaSefin->getDir_id();
    }
    public function getUni_id()
    {
        return $this->encJerarquiaSefin->getUni_id();
    }
    public function getDep_id()
    {
        return $this->encJerarquiaSefin->getDep_id();
    }

    // Obtenemos los datos de las fechas de vigencia
    public function getVgu_fechaInicio()
    {
        return $this->encVigencia->getVgu_fechaInicio();
    }
    public function getVgu_fechaFin()
    {
        return $this->encVigencia->getVgu_fechaFin();
    }
    public function getVgu_vigenciaFechaContrasenia()
    {
        return $this->encVigencia->getVgu_vigenciaFechaContrasenia();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    // Como uur_id es la llave para la vinculación, seteamos dos variables
    public function setUur_id($val)
    {
        $this->uur_id->setVal($val);
        $this->vinUnidadResponsable->setUur_id($val);
    }

    // La clave para la estructura organica...
    public function setEjs_id($val)
    {
        $this->ejs_id->setVal($val);
        $this->encJerarquiaSefin->setEjs_id($val);
    }

    // la clave para la vinculación con fechas de vigencia
    public function setUsu($val)
    {
        $this->usp_usu->setVal($val);
        $this->encVigencia->setUsr_id($val); // En la tabla cat_VigenciaUsuario el usr_id tiene el valor del usuario
    }

    public function setUsp_id($val)
    {
        $this->usp_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setNvj_id($val)
    {
        $this->nvj_id->setVal($val);
    }
    public function setPrf_id($val)
    {
        $this->prf_id->setVal($val);
    }
    public function setRfc($val)
    {
        $this->usp_rfc->setVal($val);
    }
    public function setUsuario($val)
    {
        $this->usp_usuario->setVal($val);
    }
    public function setClave($val)
    {
        $this->usp_clave->setVal($val);
    }
    public function setClaveAcceso($val)
    {
        $this->usp_claveAcceso->setVal($val);
    }
    public function setNombre($val)
    {
        $this->usp_nombre->setVal($val);
    }
    public function setPaterno($val)
    {
        $this->usp_paterno->setVal($val);
    }
    public function setMaterno($val)
    {
        $this->usp_materno->setVal($val);
    }
    public function setSexo($val)
    {
        $this->usp_sexo->setVal($val);
    }
    public function setPuesto($val)
    {
        $this->usp_puesto->setVal($val);
    }
    public function setTitulo($val)
    {
        $this->usp_titulo->setVal($val);
    }
    public function setCorreo($val)
    {
        $this->usp_correo->setVal($val);
    }
    public function setTelefono($val)
    {
        $this->usp_telefono->setVal($val);
    }
    public function setExtension($val)
    {
        $this->usp_extension->setVal($val);
    }
    public function setNuevo($val)
    {
        $this->usp_nuevo->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->usp_ejercicio->setVal($val);
    }
    public function setSac_id($val)
    {
        $this->sac_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staUsp_id($val)
    {
        $this->usp_id->staVal($val);
    }
    public function staUur_id($val)
    {
        $this->uur_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staNvj_id($val)
    {
        $this->nvj_id->staVal($val);
    }
    public function staPrf_id($val)
    {
        $this->prf_id->staVal($val);
    }
    public function staEjs_id($val)
    {
        $this->ejs_id->staVal($val);
    }
    public function staRfc($val)
    {
        $this->usp_rfc->staVal($val);
    }
    public function staUsuario($val)
    {
        $this->usp_usuario->staVal($val);
    }
    public function staClave($val)
    {
        $this->usp_clave->staVal($val);
    }
    public function staClaveAcceso($val)
    {
        $this->usp_claveAcceso->staVal($val);
    }
    public function staNombre($val)
    {
        $this->usp_nombre->staVal($val);
    }
    public function staPaterno($val)
    {
        $this->usp_paterno->staVal($val);
    }
    public function staMaterno($val)
    {
        $this->usp_materno->staVal($val);
    }
    public function staSexo($val)
    {
        $this->usp_sexo->staVal($val);
    }
    public function staPuesto($val)
    {
        $this->usp_puesto->staVal($val);
    }
    public function staTitulo($val)
    {
        $this->usp_titulo->staVal($val);
    }
    public function staCorreo($val)
    {
        $this->usp_correo->staVal($val);
    }
    public function staTelefono($val)
    {
        $this->usp_telefono->staVal($val);
    }
    public function staExtension($val)
    {
        $this->usp_extension->staVal($val);
    }
    public function staNuevo($val)
    {
        $this->usp_nuevo->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->usp_ejercicio->staVal($val);
    }
    public function staUsu($val)
    {
        $this->usp_usu->staVal($val);
    }
    public function staSac_id($val)
    {
        $this->sac_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(['tabla' => BD_USUARIO . '.cat_UsuarioPrevio']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->usp_id = new Atributo('numero', 'NO', 'PRI', 'usp_id');
        $this->uur_id = new Atributo('numero', 'NO', '', 'uur_id');
        $this->ue_id = new Atributo('numero', 'YES', '', 'ue_id');
        $this->nvj_id = new Atributo('numero', 'NO', 'MUL', 'nvj_id');
        $this->prf_id = new Atributo('cadena', 'NO', 'MUL', 'prf_id');
        $this->ejs_id = new Atributo('numero', 'YES', '', 'ejs_id');
        $this->usp_rfc = new Atributo('cadena', 'NO', '', 'usp_rfc');
        $this->usp_usuario = new Atributo('cadena', 'NO', '', 'usp_usuario');
        $this->usp_clave = new Atributo('cadena', 'NO', '', 'usp_clave');
        $this->usp_claveAcceso = new Atributo('blob', 'NO', '', 'usp_claveAcceso');
        $this->usp_nombre = new Atributo('cadena', 'NO', '', 'usp_nombre');
        $this->usp_paterno = new Atributo('cadena', 'NO', '', 'usp_paterno');
        $this->usp_materno = new Atributo('cadena', 'NO', '', 'usp_materno');
        $this->usp_sexo = new Atributo('cadena', 'NO', '', 'usp_sexo');
        $this->usp_puesto = new Atributo('cadena', 'NO', '', 'usp_puesto');
        $this->usp_titulo = new Atributo('cadena', 'NO', '', 'usp_titulo');
        $this->usp_correo = new Atributo('cadena', 'NO', '', 'usp_correo');
        $this->usp_telefono = new Atributo('cadena', 'NO', '', 'usp_telefono');
        $this->usp_extension = new Atributo('numero', 'NO', '', 'usp_extension');
        $this->usp_nuevo = new Atributo('numero', 'NO', '', 'usp_nuevo');
        $this->usp_ejercicio = new Atributo('numero', 'NO', '', 'usp_ejercicio');
        $this->usp_usu = new Atributo('numero', 'NO', '', 'usp_usu');
        $this->sac_id = new Atributo('numero', 'NO', 'MUL', 'sac_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
        $this->sts_id = new Atributo('numero', 'NO', '', 'sts_id');

        $this->vinUnidadResponsable = new VinUnidadResponsable($prm);
        $this->encJerarquiaSefin = new encJerarquiaSefin($prm);
        $this->encVigencia = new VigenciaUsuario($prm);

    }

    // Aquí se reescriben los metodos de Entidad, para poder hacer el select
    public function select($servidor = '')
    {
        parent::select();

        // OJO : ¿no deberíamos guardar vur_id y no uur_id en ésta tabla ?
        $this->vinUnidadResponsable->setUur_id($this->uur_id->getVal());

        // $this->vinUnidadResponsable->setVur_ejercicio( $_SESSION[_S]['ejercicioActual'] );

        // OJO: Aqui debe ser del sistema al que quieren acceso
        // se agregarán checklist porque pueden pedir un usuario para varios ejercicios
        // EN UNA MISMA SOLICITUD... ergo, el valor ya no es la variable del ejercicio actual
        // sino cada una de las ejercicios que están pidiendo

        $this->vinUnidadResponsable->setVur_ejercicio($_SESSION[_S]['ejercicioActual']);
        $this->vinUnidadResponsable->select();

        // OJO: Como NO tiene estos datos de estructura orgánica el nivel 2 (ur), tiene NULL en la base de datos
        // asi que aquí le ponemos 0... pero si hay un encuadre... ¿no debería tener un valor válido en la tabla?
        $paso = ($this->ejs_id->getVal() != null) ? $this->ejs_id->getVal() : 0;

        $this->encJerarquiaSefin->setEjs_id($paso);
        $this->encJerarquiaSefin->select();

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $usp = new UsuarioPrevio();
        if (isset($p->usp_id)) {
            $usp->setUsp_id($p->usp_id);
        }

        if (isset($p->uur_id)) {
            $usp->setUur_id($p->uur_id);
        }

        if (isset($p->ue_id)) {
            $usp->setUe_id($p->ue_id);
        }

        if (isset($p->nvj_id)) {
            $usp->setNvj_id($p->nvj_id);
        }

        if (isset($p->prf_id)) {
            $usp->setPrf_id($p->prf_id);
        }

        if (isset($p->ejs_id)) {
            $usp->setEjs_id($p->ejs_id);
        }

        if (isset($p->rfc)) {
            $usp->setRfc($p->rfc);
        }

        if (isset($p->usuario)) {
            $usp->setUsuario($p->usuario);
        }

        if (isset($p->clave)) {
            $usp->setClave($p->clave);
        }

        if (isset($p->claveAcceso)) {
            $usp->setClaveAcceso($p->claveAcceso);
        }

        if (isset($p->nombre)) {
            $usp->setNombre($p->nombre);
        }

        if (isset($p->paterno)) {
            $usp->setPaterno($p->paterno);
        }

        if (isset($p->materno)) {
            $usp->setMaterno($p->materno);
        }

        if (isset($p->sexo)) {
            $usp->setSexo($p->sexo);
        }

        if (isset($p->puesto)) {
            $usp->setPuesto($p->puesto);
        }

        if (isset($p->titulo)) {
            $usp->setTitulo($p->titulo);
        }

        if (isset($p->correo)) {
            $usp->setCorreo($p->correo);
        }

        if (isset($p->telefono)) {
            $usp->setTelefono($p->telefono);
        }

        if (isset($p->extension)) {
            $usp->setExtension($p->extension);
        }

        if (isset($p->nuevo)) {
            $usp->setNuevo($p->nuevo);
        }

        if (isset($p->ejercicio)) {
            $usp->setEjercicio($p->ejercicio);
        }

        if (isset($p->usu)) {
            $usp->setUsu($p->usu);
        }

        if (isset($p->sac_id)) {
            $usp->setSac_id($p->sac_id);
        }

        if (isset($p->usr_id)) {
            $usp->setUsr_id($p->usr_id);
        }

        if (isset($p->sts_id)) {
            $usp->setSts_id($p->sts_id);
        }

        // Marcos: 18/junio/2018
        // Si el nivel jerárquico es diferente a 1... seteamos la UE a null
        if ($p->nvj_id == '1') {
            $p->ue_id = null;
        }

        // Para la bitàcora...
        switch ($p->abc) {
            case 'a':$bitacora = 'NUEVA ';
                break;
            case 'b':$bitacora = 'ELIMINAR ';
                break;
            case 'c':$bitacora = 'MODIFICAR ';
                break;
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';

        $sql = '
            SET
            @usr_id = :usr_id,
            @bit_observacion = :observacion,
            @usr_ip = INET_ATON( :ip );
            ';

        $parametros = [
            ':usr_id'      => $_SESSION[_S]['usr_id'],
            ':observacion' => $bitacora . 'SOLICITUD',
            ':ip'          => $ip,
        ];
        $usp->setSql($sql);
        $usp->conectar();
        $usp->consultar($parametros);

        switch ($p->abc) {
            case 'a':
                $compNiv = $usp->getNvj_id();
                if (isset($compNiv) && $compNiv == 2) {
                    $usp->setUe_id(999);
                }
                $usp->insert(false);
                return $usp->getUltimoId();
                break;
            case 'b':$usp->delete(false);
                break;
            case 'c':
                $compNiv = $usp->getNvj_id();
                if (isset($compNiv) && $compNiv == 2) {
                    $usp->setUe_id(999);

                }
                $usp->update(false);
                break;
        }
    }

}
