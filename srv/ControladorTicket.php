<?php

/**
 * User: FPEREZD
 * Date: 20/01/2022
 * Time: 12:00
 * Update: 20/01/2022

 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/WebResponse.php';

require_once dirname(__FILE__) . '/Modelos/modeloTicket.php';
require_once dirname(__FILE__) . '/Vistas/VistaTicket.php';

class ControladorTicket extends WebResponse
{
    public $vista;
    public $modelo;
    public $ctrlEjercicioActual;

    public function __construct()
    {
        parent::__construct();

        $this->pantalla = 'srv/ControladorTicket.php';
        $this->ctrlEjercicioActual = 'ctrl' . APP_EJERCICIO;

        switch ($this->metodo) {
            case 'main':
                $this->main();
                break;
            case 'AddTicket':
                $this->AddNewTicket();
                break;
            case 'abcSolicitud':
                $this->abcSolicitud();
                break;
            case 'recargarUr':
                $this->recargarUr();
                break;
            case 'recargarDireccion':
                $this->recargarDireccion();
                break;
            case 'recargarUnidad':
                $this->recargarUnidad();
                break;
            case 'recargarDepartamento':
                $this->recargarDepartamento();
                break;
            case 'recargarJefe':
                $this->recargarJefe();
                break;
            case 'validaDatosUsuario':
                $this->validaDatosUsuario();
                break;
            case 'valNombre':
                $this->valNombre();
                break;
            case 'enviarSolicitudParaAutorizacion':
                $this->enviarSolicitudParaAutorizacion();
                break;
            case 'updPermisos':
                $this->updPermisos();
                break;
            case 'llenaUpdPermiso':
                $this->llenaUpdPermiso();
                break;
            case 'llenaTablaUsuarios':
                $this->addPermisosUsuarios();
                break;
            case 'solPermisos':
                $this->solPermisos();
                break;
            case 'guardarPermisos':
                $this->guardarPermisos();
                break;
            case 'rellenarUnidadEjecutora':
                $this->rellenarUnidadEjecutora();
                break;
            case 'eliminaSolicitud':
                $this->eliminaSolicitud();
                break;
            case 'validaNuevoUsuario':
                $this->validaNuevoUsuario($nombre = '', $paterno = '', $materno = '', $rfc = '');
                break;
            case 'validaNuevoCorreo':
                $this->validaNuevoCorreo();
                break;
            case 'validaNuevoTelefono':
                $this->validaNuevoTelefono();
                break;
            case 'dameDatosUsuario':
                $this->dameDatosUsuario();
                break;
        }
    }

    // Pantalla principal, tanto para solicitudes de Usuario como de Permisos
    private function main()
    {
        // ¿Estamos en Solicitudes de Permisos o de Usuarios ?
        $esPermisos = (isset($this->prm->esperm) ? $this->prm->esperm : '');

        // Obtenemos la consulta para obtener los datos a visualizar
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->dameSQLSolicitudes($esPermisos);

        // Mostramos la pantalla
        $this->vista = new vistaTicket();
        $this->vista->dibujaPantallaPrincipal($this->prm, $sql);
    }

    // Mostramos la pantalla para dibujar un nuevo ticket, o modificar los datos de uno existente
    private function AddNewTicket()
    {
        // Obtenemos la información
        $this->modelo = new modeloTicket();
        $abc = '';
        $bandera = 0;
        $clave = '';
        //Esta comparacion permite decidir entre la insercion o la edicion.
        // Si es una solicitud de permiso... $bandera vale 0
        if (isset($this->prm->esperm) && $this->prm->esperm == 'si') {
            $abc = 'a';
            $bandera = 0;

            // Obtenemos los datos que nos faltan a partir del usuario real
            $usuarioReal = $this->modelo->dameUsuarioReal($this->prm->usr_id);

            // y luego obtenemos los datos de la solicitud y del usuario
            $usuarioPrevio = &$this->modelo->dameUsuarioPrevio($usuarioReal['usp_id']);
            $solicitudAcceso = &$this->modelo->dameSolicitudAcceso($usuarioReal['sac_id']);
            $fechaInicio = date('d/m/Y');
            $fechaFinal = '31/12/' . APP_EJERCICIO;

            // Complementamos...
            $usuarioPrevio->setNuevo('2');
            $usuarioPrevio->setUsu('0');
            $solicitudAcceso->setEso_id('');

            // Si es una modificación a una solicitud de acceso...
            // Si es una solicitud de acceso, bandera vale 1
        } elseif (isset($this->prm->usp_id) && isset($this->prm->sac_id)) {
            $abc = 'c';
            $bandera = 1;

            $usuarioPrevio = &$this->modelo->dameUsuarioPrevio($this->prm->usp_id);
            $solicitudAcceso = &$this->modelo->dameSolicitudAcceso($this->prm->sac_id);
            $fechaInicio = date('d/m/Y');
            $fechaFinal = '31/12/' . APP_EJERCICIO;

            $clave = 'select AES_DECRYPT(usp_claveAcceso,"SEFIP2027") as pass from ctrlUsuario.cat_UsuarioPrevio
            where usp_id= :usp_id';
            $parametros = ['usp_id' => $this->prm->usp_id];

            $this->setSql($clave);
            $this->conectar();
            $this->consultar($parametros);
            $claveTemp = $this->extraer();
            $clave = $claveTemp['pass'];

            // Por default, es una nueva solicitud de usuario
        } else {
            $abc = 'a';
            $bandera = 0;

            $usuarioPrevio = &$this->modelo->dameUsuarioPrevio(0);
            $solicitudAcceso = &$this->modelo->dameSolicitudAcceso(0);
            $fechaInicio = date('d/m/Y');
            $fechaFinal = '31/12/' . APP_EJERCICIO;
            $clave = $this->generaPass();
        }

        // Obtehemos los SQL que se usarán en los combos de la pantalla
        $grupoSQL = $this->modelo->dameSQLGrupo($this->prm, $usuarioPrevio->getGpo_id());
        $urSQL = $this->modelo->dameSQLUr($this->prm, $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id());
        $subSecretariaSQL = $this->modelo->dameSQLSubsecretaria($bandera, $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id());
        $direccionSQL = $this->modelo->dameSQLDireccion($bandera, $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id());
        $coordinadorSQL = $this->modelo->dameSQLCoordinador($bandera, $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id());
        $departamentoSQL = $this->modelo->dameSQLDepartamento($bandera, $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id());
        $nivelJerarquicoSQL = $this->modelo->dameSQLNivelJerarquico($this->prm, $usuarioPrevio->getNvj_id());
        $_SESSION[_S]['prm']['nivelJerarquicoSQL'] = $nivelJerarquicoSQL;
        $perfilSQL = $this->modelo->dameSQLPerfil($this->prm, $usuarioPrevio->getPrf_id());
        $_SESSION[_S]['prm']['perfilSQL'] = $perfilSQL;
        $responsableSQL = $this->modelo->dameSQLResponsable($bandera, $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id(), $solicitudAcceso->getJefe());
        $directorTitularSQL = $this->modelo->dameSQLDirectorTitular($bandera, $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id(), $solicitudAcceso->getJefe());

        // Obtenemos los permisos que tiene el usuario ACTUAL
        $dataPermisosPadre = $this->modelo->dameDataPermisosUsrActual();
        // Obtenemos los permisos del usuario Hijo... que es el que estamos modificando como Nuevo Usuario
        // o solicitándole permisos
        $permisos1 = $permisos2 = $permisos3 = [];

        $this->modelo->dameDataPermisosUsrHijo($this->prm, $bandera, $usuarioPrevio->getUsuario(), $usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id(), $permisos1, $permisos2, $permisos3, $fechaInicio, $fechaFinal);

        // Mostramos la información en pantalla
        $this->vista = new vistaTicket();
        $this->vista->muestraPantallaRegistro(
            $abc,
            $bandera,
            $usuarioPrevio,
            $solicitudAcceso,
            $grupoSQL,
            $urSQL,
            $subSecretariaSQL,
            $direccionSQL,
            $coordinadorSQL,
            $departamentoSQL,
            $nivelJerarquicoSQL,
            $perfilSQL,
            $responsableSQL,
            $directorTitularSQL,
            $dataPermisosPadre,
            $permisos1,
            $permisos2,
            $permisos3,
            $fechaInicio,
            $fechaFinal,
            $clave
        );
    }

    // Validamos en la base de datos si el nombre de usuario (calculado automáticamente por el sistema)
    // ya existe o no
    private function valNombre()
    {
        $this->modelo = new modeloTicket();
        return $this->modelo->valNombre($this->prm);
    }

    /* verifica si un usuario ya existe en la tabla cat_UsuariosPrevio */
    private function validaDatosUsuario()
    {
        $this->modelo = new modeloTicket();
        return $this->modelo->validaDatosUsuario($this->prm->usp_usuario);
    }

    // Guardamos una nueva solicitud / una modificación a una existente, tanto de usuarios
    // como de permisos
    private function abcSolicitud()
    {
        // Creamos el objeto para las vistas que usaremos
        $this->vista = new vistaTicket();
        if ($this->prm->errorCorreo === false) {
            $arrResultado['resultado'] = 'err_msg';
            $arrResultado['mensaje'] = 'Favor de agregar un correo válido y/o institucional';
            $this->vista->muestraMensaje($arrResultado);
            return false;
        } elseif ($this->prm->errorTelefono === false) {
            $arrResultado['mensaje'] = "El número de teléfono está asociado a más de un usuario, regularice la información para poder continuar";
            if($this->prm->telefono == 0) {
                $arrResultado['mensaje'] = "No cuenta con un número de teléfono, regularice la información para poder continuar";
            }
            $arrResultado['resultado'] = 'err_msg';
            $this->vista->muestraMensaje($arrResultado);
            return false;
        }
        if ($this->prm->telefono == 0) {
            $arrResultado['mensaje'] = "No cuenta con un número de teléfono, regularice la información para poder continuar";
            $arrResultado['resultado'] = 'err_msg';
            $this->vista->muestraMensaje($arrResultado);
            return false;
        }
        // Validamos que hayan seleccionado al menos un permiso, checando que la variable
        // que los contiene no venga vacía
        if (!isset($this->prm->permisos) || empty($this->prm->permisos)) {
            $this->vista->avisoFaltanPermisos();
            return;
        }
        // Formatos para las fechas
        if (isset($this->prm->fechaInicio)) {
            $this->prm->fechaInicio = $this->formateaFecha($this->prm->fechaInicio);
        }
        if (isset($this->prm->fechaFin)) {
            $this->prm->fechaFin = $this->formateaFecha($this->prm->fechaFin);
        }

        // Aplicamos a la base de datos...
        $this->modelo = new modeloTicket();
        // Regresará un array [ resultado = "", mensaje = "" ]... y en base al código del resultado
        // reaccionará la vista para avistarle al usuario

        $arrResultado = $this->modelo->abcSolicitud($this->prm);

        // Mostramos el resultado en pantalla
        $this->vista->muestraMensaje($arrResultado);
    }

    // Metodo que cambia el estado de la solicitud
    // Cuando se ENVIA la solicitud, se cambia status a 2 - ENVIADA
    public function enviarSolicitudParaAutorizacion()
    {
        $this->modelo = new modeloTicket();
        $this->modelo->enviarSolicitudParaAutorizacion($this->prm);
        $esPermisos = (isset($this->prm->esperm) ? $this->prm->esperm : '');

        // Obtenemos la consulta para obtener los datos a visualizar
        $sql = $this->modelo->dameSQLSolicitudes($esPermisos);

        // Mostramos la pantalla
        $this->vista = new vistaTicket();
        $this->vista->enviarSolicitudParaAutorizacion($this->prm, $sql);

        // Mostramos la pantalla
        $this->vista->dibujaPantallaPrincipal($this->prm, $sql);
    }

    // Metodo para eliminar la solicitud de usuario
    private function eliminaSolicitud()
    {
        $usuario_id = $this->prm->usp_id;
        $solicitud_acceso_id = $this->prm->sac_id;
        if (!empty($usuario_id) && !empty($solicitud_acceso_id)) {
            $this->modelo = new modeloTicket();
            $arrResultado = $this->modelo->eliminaSolicitud($solicitud_acceso_id);

            // Mostramos la pantalla
            $this->vista = new vistaTicket();
            $this->vista->muestraMensaje($arrResultado);
        } else {
            $this->vista->muestraMensaje(['resultado' => 'err_msg', 'mensaje' => 'No hay suficientes elementos para realizar la eliminación']);
        }
    }

    // Pendiente de averiguar qué hace ésto...
    public function guardarPermisos()
    {
        // Mostramos la pantalla
        $this->vista = new vistaTicket();
        $this->vista->muestraMensaje(['guardarPermisos', 'Ticket agregado correctamente']);
    }

    //Castro Jordy 14-03-2018 Metodo para sobre escribir la unidad ejecutora de la solicitud de Usuario
    private function rellenarUnidadEjecutora()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->dameSqlRellenarUnidadEjecutora($this->prm);

        $this->vista = new vistaTicket();
        $this->vista->rellenarUnidadEjecutora($sql);
    }

    //Castro Jordy 2/04/18 Metodo para los permisos
    private function updPermisos()
    {
        $this->vista = new vistaTicket();
        $this->vista->updPermisos();
    }

    // Para adicionar permisos de usuarios
    private function addPermisosUsuarios()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->dameSqlTablaUsuarios($this->prm);

        $this->vista = new vistaTicket();
        $this->vista->addPermisosUsuarios($this->prm, $sql);
    }

    private function generaPass()
    {
        //Se define una cadena de caractares
        // $cadena = "1234567890";
        $cadena = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890@$!%*?&#_-';
        //Obtenemos la longitud de la cadena de caracteres
        $longitudCadena = strlen($cadena);

        //Se define la variable que va a contener la contraseña
        $pass = '';
        //Se define la longitud de la contraseña, en mi caso 10, pero puedes poner la longitud que quieras
        $longitudPass = 10;

        //Creamos la contraseña
        for ($i = 1; $i <= $longitudPass; $i++) {
            //Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
            $pos = rand(0, $longitudCadena - 1);

            //Vamos formando la contraseña en cada iteraccion del bucle, añadiendo a la cadena $pass la letra correspondiente a la posicion $pos en la cadena de caracteres definida.
            $pass .= substr($cadena, $pos, 1);
        }
        return $pass;
    }

    /*Castro Jordy 10/04/2018 Estos metodos se dejaron faltan corroborar si funcionan como deberian*/
    private function recargarUr()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->dameSqlRecargarUr($this->prm->gpo_id);

        $this->vista = new vistaTicket();
        $this->vista->recargarUr($sql);
    }

    private function recargarDireccion()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->recargarDireccion($this->prm->ssc_id);
        $this->vista = new vistaTicket();
        $this->vista->recargarDireccion($sql);
    }

    private function recargarUnidad()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->recargarUnidad($this->prm->dir_id);

        $this->vista = new vistaTicket();
        $this->vista->recargarUnidad($sql);
    }

    private function recargarDepartamento()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->recargarDepartamento($this->prm->ssc_id, $this->prm->dir_id, $this->prm->uni_id);

        $this->vista = new vistaTicket();
        $this->vista->recargarDepartamento($sql);
    }

    private function recargarJefe()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->recargarJefe($this->prm->grupo, $this->prm->responsable);

        $this->vista = new vistaTicket();
        $this->vista->recargarJefe($sql);
    }

    private function llenaUpdPermiso()
    {
        $this->modelo = new modeloTicket();
        $sql = $this->modelo->dameSqlLlenaUpdPermiso();

        $this->vista = new vistaTicket();
        $this->vista->llenaUpdPermiso($this->prm, $sql);
    }

    // Solicitud de Permisos para los usuarios
    public function solPermisos()
    {
        // Obtenemos los permisos que tiene el usuario ACTUAL
        $this->modelo = new modeloTicket();
        $dataPermisos = $this->modelo->dameDataPermisosByUsrID($this->prm->usr_id);

        $this->vista = new vistaTicket();
        $this->vista->llenaUpdPermiso($this->prm, $dataPermisos);
    }

    // Marcos: 08/jun/2018
    // Validamos en la tabla temporal y en la definitiva si el usuario que intentan registrar ya existe
    public function validaNuevoUsuario($nombre, $paterno, $materno, $rfc)
    {
        // Obtenemos los datos de la clasificación administrativa actual para gpo_id y ur_id
        $gpo_id_actual = $_SESSION[_S]['clasAdm-gpo'];
        $ur_id_actual = $_SESSION[_S]['clasAdm-ur'];
        // Obtenemos los datos del usuario
        $nombre = (isset($this->prm->nombre) && $this->prm->nombre != '') ? $this->prm->nombre : '';
        $paterno = (isset($this->prm->paterno) && $this->prm->paterno != '') ? $this->prm->paterno : '';
        $materno = (isset($this->prm->materno) && $this->prm->materno != '') ? $this->prm->materno : '';
        $rfc = (isset($this->prm->rfc) && $this->prm->rfc != '') ? $this->prm->rfc : '';

        // Validamos que TODOS los datos traigan valors
        if (trim($nombre) == '' || trim($paterno) == '' || trim($materno) == '' || trim($rfc) == '') {
            return false;
        }

        // Buscamos si ya existe y recuperamos sus datos
        $this->modelo = new modeloTicket();
        $yaExiste = $this->modelo->validaExisteUnUsuario($gpo_id_actual, $ur_id_actual, $nombre, $paterno, $materno, $rfc);

        // Si no lo encontramos por esos valores, no existe aún y pueden continuar con el registro
        if (!$yaExiste) {
            return true;
        }

        // Si llegamos aquí es porque sí existe, y avisamos
        $this->vista = new vistaTicket();
        $this->vista->avisaUsuarioYaExiste($rfc, $nombre, $paterno, $materno);
        return false;
    }

    // Marcos: 12/jun/2018
    // Regresamos el valor del encuadre uur_id para la clasifiación administrativa y el ejercicio actual
    public function dameUurActual()
    {
        // Buscamos si ya existe y recuperamos sus datos
        $this->modelo = new modeloTicket();
        return $this->modelo->dameUurActual();
    }

    // Convertimos una fecha de php a la fecha que maneja mySql
    // evidentemente la fecha debe venir en formato dia-mes-año
    public function date_to_mysqldate($fecha)
    {
        if (!strcmp($fecha, 'now()')) {
            return $fecha;
        } elseif ($fecha != '') {
            // Por si escriben guiones en lugar de diagonales, reemplazamos parejo
            $fecha = str_replace('-', '/', $fecha);
            $trozos = explode('/', $fecha, 3);
            if (count($trozos) == 3) {
                if (strlen($trozos[0]) == 2) {
                    return $trozos[2] . '/' . $trozos[1] . '/' . $trozos[0];
                } else { /*si ya viene en formato año, mes, dia*/
                    return $trozos[0] . '/' . $trozos[1] . '/' . $trozos[2];
                }
            } else {
                return 'NULL';
            }
        }
        return 'NULL';
    }
    // Marcos: 13/jul/2018
    // Formatos para las fechas
    // Si la fecha de vigencia está vacía o no es válida, grabamos null en la tabla
    private function formateaFecha($fecha)
    {
        $fechaIni = str_replace('-', '/', $fecha);
        $trozos = explode('/', $fechaIni, 3);

        if ($fechaIni == '' || count($trozos) != 3) {
            return 'NULL';
        } else {
            if (strlen($trozos[0]) == 2) {
                // si viene como dia, mes, año
                $fechaIniValida = checkdate($trozos[1], $trozos[0], $trozos[2]);
            } else { /* si ya viene en formato año, mes, dia */
                // si viene como año, mes, dia
                $fechaIniValida = checkdate($trozos[1], $trozos[2], $trozos[0]);
            }
            if ($fechaIniValida === true) {
                return $this->date_to_mysqldate($fechaIni);
            } else {
                return 'NULL';
            }
        }
    }

    private function validaNuevoCorreo()
    {
        $this->modelo = new modeloTicket();
        return $this->modelo->validaNuevoCorreo($this->prm);
    }

    private function validaNuevoTelefono()
    {
        $this->modelo = new modeloTicket();
        return $this->modelo->validaNuevoTelefono($this->prm);
    }

    private function dameDatosUsuario()
    {
        $this->modelo = new modeloTicket();
        return $this->modelo->dameDatosUsuario($this->prm->usr);
    }
}

new ControladorTicket();

// Fin del código
