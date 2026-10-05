<?php

/*

- Premisa básica: TODA la interacción con la base de datos DEBE estar en éste archivo
 */

require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';

// En tanto que las Entidades son maneras de accesar a la base de datos, deberían ir en el modelo
require_once dirname(__FILE__) . '/../../ent/usuarios/SolicitudAcceso.php';
require_once dirname(__FILE__) . '/../../ent/usuarios/UsuarioPrevio.php';
require_once dirname(__FILE__) . '/../../ent/usuarios/PermisosUsuarioPrevio.php';

class modeloTicket extends AccesoDatos
{
    public $ctrlEjercicioActual;
    public function __construct()
    {
        parent::__construct();
        $this->ctrlEjercicioActual = BD_CTRL;
    }
    // Obtenemos el sql que permitirá llenar la tabla de una Solicitud de Usuarios o Permisos
    public function dameSQLSolicitudes($esPerm = '')
    {
        $miniSql = '';
        $miniSql2 = '';
        // Si están solicitando PERMISOS
        if ($esPerm == 'Si') {
            $miniSql = ' AND usp_nuevo=2 ';
            $miniSql2 = 'Permiso';
            // Si están solicitando USUARIOS
        } else {
            $miniSql = ' AND usp_nuevo=1 AND usp_ejercicio= ' . APP_EJERCICIO;
            $miniSql2 = 'Usuario';
        }

        $sql = 'select sac_id,usp_id,concat(vur.gpo_id,LPAD( vur.ur_id, 2, 0 ) ) ur, concat(usp_nombre," ",usp_paterno," ",usp_materno) nombre,
            usp_puesto, usp_correo, "' . $miniSql2 . '" tipo, eso_id, usp_usuario AS usuario,
            case eso_id
            WHEN 1 THEN "GENERADO"
            WHEN 2 THEN "ENVIADO"
            WHEN 3 THEN "ACEPTADO"
            WHEN 4 THEN "RECHAZADO"
            WHEN 5 THEN "ELIMINADO"
            END
            AS Estado
            FROM '.BD_USUARIO.'.cat_UsuarioPrevio cup
            INNER JOIN '.BD_USUARIO.'.cat_SolicitudAcceso using (sac_id)
            INNER JOIN '.BD_SINPRES.'.vin_UnidadResponsable vur USING( uur_id )
            WHERE vur_ejercicio = ' . APP_EJERCICIO . ' AND
                ( cup.usr_id=' . $_SESSION[_S]['usr_id'] . ' OR
                ( cup.usp_usu = ' . $_SESSION[_S]['usr_id'] . ' AND cup.usp_nuevo = 1 )
                )
            ' . $miniSql . ';';

        return $sql;
    }

    // Marcos: 03/jul/2018
    // Nueva estrategia: vamos a buscar su solicitud de usuario original, y tomamos de la
    // solicitud de usuario los datos
    public function dameUsuarioReal($usr_id)
    {
        $this->conectar();
        $this->setSql('
            SELECT
                up.usp_id, up.sac_id, u.usr_usu
            FROM
                '.BD_USUARIO.'.cat_Usuario u
                INNER JOIN '.BD_USUARIO.'.cat_UsuarioPrevio up ON
                    ( u.usr_id = up.usp_usu )
            WHERE
                u.usr_id = ' . $usr_id . ' ORDER BY sac_id desc ;
        ');
        $this->consultar();
        return $this->extraer();
    }

    public function dameFechaInicioFin($usr_id)
    {
        $this->conectar();
        $this->setSql('
            SELECT
                DATE_FORMAT( usp_fechaInicio, "%d/%m/%Y" ) AS fechaInicio,
                DATE_FORMAT( usp_fechaFin, "%d/%m/%Y" ) AS fechaFin
            FROM
                '.BD_USUARIO.'.cat_PermisoUsuarioPrevio
            WHERE
                usp_id =' . $usr_id . '
                LIMIT 1;
        ');
        $this->consultar();
        return $this->extraer();
    }
    // Obtenemos una instancia de la Entidad UsuarioPrevio... podemos pasar:
    // un array
    public function &dameUsuarioPrevio($usp_id)
    {
        $usuarioPrevio = new UsuarioPrevio();
        // Sólo si mandaron un valor para $usp_id cargamos valores, de lo contrario regresamos
        // una Entidad vacia
        if ($usp_id > 0) {
            $usuarioPrevio->setUsp_id($usp_id);
            $usuarioPrevio->select();
        }
        return $usuarioPrevio;
    }

    public function &dameSolicitudAcceso($sac_id)
    {
        $solicitudAcceso = new SolicitudAcceso();
        // Sólo si mandaron un valor para $sac_id cargamos valores, de lo contrario regresamos
        // una Entidad vacia
        if ($sac_id > 0) {
            $solicitudAcceso->setSac_id($sac_id);
            $solicitudAcceso->setEjercicio(APP_EJERCICIO);
            $solicitudAcceso->select();
        }
        return $solicitudAcceso;
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar los Grupos
    public function dameSQLGrupo($prm, $gpo_id)
    {
        if ((isset($prm->esperm) && $prm->esperm != 'si') || (isset($prm->edPerm) && $prm->edPerm == 'si')) {
            return 'SELECT gpo_id, gpo_nombre uresponsable FROM ' . $this->ctrlEjercicioActual . '.cat_Grupo WHERE gpo_id=' . $gpo_id . ';';
        } elseif (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] <= 9) {
            return 'SELECT gpo_id, gpo_nombre uresponsable FROM ' . $this->ctrlEjercicioActual . '.cat_Grupo WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . ';';
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar las URs
    public function dameSQLUr($prm, $gpo_id, $ur_id)
    {
        if ((isset($prm->esperm) && $prm->esperm == 'si') || (isset($prm->edPerm) && $prm->edPerm == 'si')) {
            return 'SELECT ur_id, concat(ur_nombre) uresponsable FROM ' . $this->ctrlEjercicioActual . '.cat_UnidadResponsable WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ';';
        } elseif (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] <= 9) {
            return 'SELECT ur_id, concat(ur_nombre) uresponsable FROM ' . $this->ctrlEjercicioActual . '.cat_UnidadResponsable WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . ' AND ur_id=' . $_SESSION[_S]['ur_id'] . ';';
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar las Subsecretarías
    public function dameSQLSubsecretaria($bandera, $gpo_id, $ur_id)
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] >= 3) {
            if ($bandera == 0 && isset($_SESSION[_S]['ssc_id'])) {
                return 'SELECT ssc_id, ssc_nombre subsecretaria
                    FROM ' . $this->ctrlEjercicioActual . '.cat_Usuario u
                    LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
                    LEFT JOIN ' . $this->ctrlEjercicioActual . '.cat_Subsecretaria ss USING(ssc_id)
                    WHERE
                    u.usr_id = ' . $_SESSION[_S]['usr_id'] . ' AND
                    gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND
                    ur_id = ' . $_SESSION[_S]['ur_id'] . ';';
            } elseif ($bandera == 1 && isset($_SESSION[_S]['ssc_id'])) {
                return 'SELECT ssc_id, ssc_nombre subsecretaria
                    FROM ' . $this->ctrlEjercicioActual . '.cat_Usuario u
                    LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
                    LEFT JOIN ' . $this->ctrlEjercicioActual . '.cat_Subsecretaria ss USING(ssc_id)
                    WHERE
                    u.usr_id = ' . $_SESSION[_S]['usr_id'] . ' AND
                    gpo_id = ' . $gpo_id . ' AND
                    ur_id = ' . $ur_id . ';';
            }
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar las Direcciones
    public function dameSQLDireccion($bandera, $gpo_id, $ur_id)
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] >= 3) {
            if ($bandera == 0) {
                if (isset($_SESSION[_S]['dir_id'])) {
                    return 'SELECT dir_id, dir_nombre Direccion
                        FROM ' . $this->ctrlEjercicioActual . '.cat_Usuario u
                        LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
                        LEFT JOIN ' . $this->ctrlEjercicioActual . '.cat_Direccion d USING(ssc_id, dir_id)
                        WHERE
                        u.usr_id = ' . $_SESSION[_S]['usr_id'] . ' AND
                        gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND
                        ur_id = ' . $_SESSION[_S]['ur_id'] . ';';
                }
            } elseif ($bandera == 1) {
                if (isset($_SESSION[_S]['dir_id'])) {
                    return 'SELECT dir_id, dir_nombre Direccion
                        FROM ' . $this->ctrlEjercicioActual . '.cat_Usuario u
                        LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
                        LEFT JOIN ' . $this->ctrlEjercicioActual . '.cat_Direccion d USING(ssc_id, dir_id)
                        WHERE
                        u.usr_id = ' . $_SESSION[_S]['usr_id'] . ' AND
                        gpo_id = ' . $gpo_id . ' AND
                        ur_id = ' . $ur_id . ';';
                }
            }
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar las coordinaciones
    public function dameSQLCoordinador($bandera, $gpo_id, $ur_id)
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] >= 3) {
            if ($bandera == 0) {
                if ($_SESSION[_S]['nvj_id'] == 6) {
                    return 'SELECT uni_id, concat(uni_nombre) Direccion
                        FROM ' . $this->ctrlEjercicioActual . '.cat_Unidad
                        WHERE ssc_id = ' . $_SESSION[_S]['ssc_id'] . '
                        AND dir_id=' . $_SESSION[_S]['dir_id'] . '
                        ;';
                } elseif (isset($_SESSION[_S]['uni_id'])) {
                    return 'SELECT uni_id, concat(uni_nombre) Direccion
                        FROM ' . $this->ctrlEjercicioActual . '.cat_Unidad
                        WHERE ssc_id = ' . $_SESSION[_S]['ssc_id'] . '
                        AND dir_id=' . $_SESSION[_S]['dir_id'] . '
                        AND uni_id=' . $_SESSION[_S]['uni_id'] . '
                        ;';
                }
            } elseif ($bandera == 1 && isset($_SESSION[_S]['uni_id'])) {
                return 'SELECT dir_id, dir_nombre Direccion
                    FROM ' . $this->ctrlEjercicioActual . '.cat_Usuario u
                    LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
                    LEFT JOIN ' . $this->ctrlEjercicioActual . '.cat_Direccion d USING(ssc_id, dir_id)
                    WHERE
                    u.usr_id = ' . $_SESSION[_S]['usr_id'] . ' AND
                    gpo_id = ' . $gpo_id . ' AND
                    ur_id = ' . $ur_id . ';';
            }
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar los departamentos
    public function dameSQLDepartamento($bandera, $gpo_id, $ur_id)
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] >= 3 && $_SESSION[_S]['nvj_id'] <= 4) {
            if ($bandera == 0) {
                if ($_SESSION[_S]['nvj_id'] == 5) {
                    return 'SELECT dep_id, concat(dep_nombre) Direccion
                        FROM ' . $this->ctrlEjercicioActual . ".cat_Departamento
                        WHERE ssc_id = '" . $_SESSION[_S]['ssc_id'] . "'
                        AND dir_id=" . $_SESSION[_S]['dir_id'] . '
                        AND uni_id=' . $_SESSION[_S]['uni_id'] . '
                        AND sts_id=1;';
                } elseif (isset($_SESSION[_S]['dep_id'])) {
                    return 'SELECT dep_id, concat(dep_nombre) Direccion
                        FROM ' . $this->ctrlEjercicioActual . ".cat_Departamento
                        WHERE ssc_id = '" . $_SESSION[_S]['ssc_id'] . "'
                        AND dir_id=" . $_SESSION[_S]['dir_id'] . '
                        AND uni_id=' . $_SESSION[_S]['uni_id'] . '
                        AND dep_id=' . $_SESSION[_S]['dep_id'] . '
                        AND sts_id=1;';
                }
            } elseif ($bandera == 1) {
                if (isset($_SESSION[_S]['dep_id'])) {
                    return 'SELECT dir_id, dir_nombre Direccion
                        FROM ' . $this->ctrlEjercicioActual . '.cat_Usuario u
                        LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
                        LEFT JOIN ' . $this->ctrlEjercicioActual . '.cat_Direccion d USING(ssc_id, dir_id)
                        WHERE
                        u.usr_id = ' . $_SESSION[_S]['usr_id'] . ' AND
                        gpo_id = ' . $gpo_id . ' AND
                        ur_id = ' . $ur_id . '
                        AND d.sts_id=1;';
                }
            }
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar los Niveles Jerárquicos
    public function dameSQLNivelJerarquico($prm, $nvj_id)
    {
        if ((isset($prm->esperm) && $prm->esperm == 'si') || (isset($prm->edPerm) && $prm->edPerm == 'si')) {
            switch ($_SESSION[_S]['nvj_id']) {
                case 1:
                    $nj2 = ' nvj_id = ' . $nvj_id . ' ';
                    $nj = ' and nvj_id not in (4,5,6,7,8,9,10)';
                    break;
                case 2:
                    switch ($_SESSION[_S]['prf_id']) {
                        case 'A':
                        case 'T':
                        case 'C':
                            $nj2 = ' nvj_id = ' . $nvj_id . ' '; //solo usuarios de nivel 1 y 2
                            break;
                    }
                    $nj = ' and nvj_id not in (4,5,6,7,8,9) '; //menos usuarios de nivel 3 en adelante.
                    break;
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                    $nj2 = ' nvj_id = ' . $nvj_id . ' '; //usuarios de nivel menor al que tenga el usuario que esta dando de alta.
                    $nj = ' and nvj_id not in (0,1,2)'; //menos usuarios ur y ue,1,2
                    break;
                case 9:
                    $nj2 = ' nvj_id = ' . $nvj_id . ' ';
                    $nj = ' and nvj_id not in (1,2,5,6,7,8,9,10)';
                    break;
            }
        } else {
            switch ($_SESSION[_S]['nvj_id']) {
                case 1:
                    $nj2 = ' nvj_id <= ' . $_SESSION[_S]['nvj_id'] . ' ';
                    $nj = ' and nvj_id not in (4,5,6,7,8,9,10)';
                    break;
                case 2:
                    switch ($_SESSION[_S]['prf_id']) {
                        case 'A':
                        case 'T':
                        case 'C':
                            $nj2 = ' nvj_id <= ' . $_SESSION[_S]['nvj_id'] . ' '; //solo usuarios de nivel 1 y 2
                            break;
                            // Marcos: 29/jun/2018
                            // No venía en el código la definición de $nj2
                        default:
                            $nj2 = ' 1=1 ';
                            break;
                    }
                    $nj = ' and nvj_id not in (4,5,6,7,8,9) '; //menos usuarios de nivel 3 en adelante.
                    break;
                case 3:
                    $nj2 = ' nvj_id <= ' . $_SESSION[_S]['nvj_id'] . ' '; //usuarios de nivel menor al que tenga el usuario que esta dando de alta.
                    $nj = ' and nvj_id not in (0,1,2)'; //menos usuarios ur y ue,1,2
                    break;
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                    $nj2 = ' nvj_id < ' . $_SESSION[_S]['nvj_id'] . ' '; //usuarios de nivel menor al que tenga el usuario que esta dando de alta.
                    $nj = ' and nvj_id not in (0,1,2)'; //menos usuarios ur y ue,1,2
                    break;
                case 9:
                    $nj2 = ' nvj_id <= ' . $_SESSION[_S]['nvj_id'] . ' ';
                    $nj = ' and nvj_id not in (1,2,5,6,7,8,9,10)';
                    break;
            }
        }
        return 'SELECT nvj_id, concat(nvj_nombre) nivel FROM '.BD_USUARIO.'.cat_NivelJerarquico  where ' . $nj2 . ' ' . $nj . ' ';
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar los perfiles
    public function dameSQLPerfil($prm, $prf_id)
    {
        if ((isset($prm->esperm) && $prm->esperm == 'si') || (isset($prm->edPerm) && $prm->edPerm == 'si')) {
            if (!(isset($prf_id)) && $prf_id == '') {
                return "SELECT prf_id, concat(prf_nombre) perfil FROM ".BD_USUARIO.".cat_Perfil WHERE prf_id='" . $_SESSION[_S]['prf_id'] . "'";
            } else {
                return "SELECT prf_id, concat(prf_nombre) perfil FROM ".BD_USUARIO.".cat_Perfil WHERE prf_id='" . $prf_id . "'";
            }
        } elseif (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] <= 2) {
            return "SELECT prf_id, concat(prf_nombre) perfil FROM ".BD_USUARIO.".cat_Perfil WHERE prf_id='" . $_SESSION[_S]['prf_id'] . "'";
        } elseif (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] >= 3) {
            return "SELECT prf_id, concat(prf_nombre) perfil FROM ".BD_USUARIO.".cat_Perfil WHERE prf_id='C' or prf_id='0'";
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar el nombre del responsable
    public function dameSQLResponsable($bandera, $gpo_id, $ur_id, $nombreJefe)
    {
        // Si el nivel jerárquico del usuario de sesión es mayor o igual a 3
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] >= 3) {
            $miniSql = '';
            // Si el usuario actual es subsecretario, tomamos ese dato... si no sería un superior y ssc_id sería nulo
            $miniSql .= isset($_SESSION[_S]['ssc_id']) ? ' AND ssc_id = ' . $_SESSION[_S]['ssc_id'] . '' : ' AND ssc_id is NULL';
            // Si el usuario actual es director, tomamos ese dato... si no sería un superior y dir_id sería nulo
            $miniSql .= isset($_SESSION[_S]['dir_id']) ? ' AND dir_id = ' . $_SESSION[_S]['dir_id'] . '' : ' AND dir_id is NULL';

            if (($_SESSION[_S]['nvj_id'] >= 3) && ($_SESSION[_S]['nvj_id'] <= 4)) {
                $miniSql .= ' AND nvj_id = 5';
                $miniSql .= isset($_SESSION[_S]['uni_id']) ? ' AND uni_id = ' . $_SESSION[_S]['uni_id'] . '' : ' AND uni_id is NULL';

            }
            if ($_SESSION[_S]['nvj_id'] == 5) {
                $miniSql .= ' AND nvj_id = 5';
                $miniSql .= isset($_SESSION[_S]['uni_id']) ? ' AND uni_id = ' . $_SESSION[_S]['uni_id'] . '' : ' AND uni_id is NULL';

            }
            if ($_SESSION[_S]['nvj_id'] == 6) {
                $miniSql .= ' AND nvj_id = 6';
                $miniSql .= ' AND uni_id=0';
                $miniSql .= ' AND dep_id=0';
            }
            if ($_SESSION[_S]['nvj_id'] == 7) {
                $miniSql .= ' AND nvj_id = 7';
            }
            if ($_SESSION[_S]['nvj_id'] == 8) {
                $miniSql .= ' AND nvj_id = 8';
            }
            if ($_SESSION[_S]['nvj_id'] == 9) {
                $miniSql .= ' AND nvj_id = 9';
            }

            return "SELECT UPPER(CONCAT(usr_nombre,' ',usr_paterno,' ', usr_materno)) nombre
                FROM " . $this->ctrlEjercicioActual . '.cat_Usuario u
                INNER JOIN ".BD_USUARIO.".cat_Usuario usr USING( usr_id )
                LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING( ejs_id )
                WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . '
                AND ur_id=' . $_SESSION[_S]['ur_id'] . '
                ' . $miniSql . '
                ;';

            // Si existe la variable global de nivel jerárquico y es igual a 1 o 2
        } elseif (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 1 || $_SESSION[_S]['nvj_id'] == 2) {
            // Si es un alta...
            if ($bandera == 0) {
                return 'SELECT CONCAT(ur_nombreAdministrativo) nombre FROM ' . $this->ctrlEjercicioActual . '.aux_UnidadResponsable WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . " AND ur_nombreAdministrativo !=''
                    AND ur_id=" . $_SESSION[_S]['ur_id'] . ' ;';
                // Si es un cambio...
            } elseif ($bandera == 1) {
                return 'SELECT CONCAT(ur_nombreAdministrativo) nombre FROM ' . $this->ctrlEjercicioActual . '.aux_UnidadResponsable WHERE gpo_id=' . $gpo_id . '
                    AND ur_id=' . $ur_id . " AND ur_nombreAdministrativo = '" . $nombreJefe . "';";
            }
        }
    }

    // Regresamos la instrucción SQL que usará un combo para mostrar el nombre del titular
    public function dameSQLDirectorTitular($bandera, $gpo_id, $ur_id, $nombreJefe)
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] >= 3) {
            $miniSql = '';
            if (($_SESSION[_S]['nvj_id'] == 3)) {
                $miniSql .= ' AND nvj_id = 6';
                $miniSql .= isset($_SESSION[_S]['ssc_id']) ? ' AND ssc_id = ' . $_SESSION[_S]['ssc_id'] . '' : ' AND ssc_id is NULL';
                $miniSql .= isset($_SESSION[_S]['dir_id']) ? ' AND dir_id = ' . $_SESSION[_S]['dir_id'] . '' : ' AND dir_id is NULL';
            }
            if ($_SESSION[_S]['nvj_id'] == 4) {
                $miniSql .= ' AND nvj_id = 6';
                $miniSql .= isset($_SESSION[_S]['ssc_id']) ? ' AND ssc_id = ' . $_SESSION[_S]['ssc_id'] . '' : ' AND ssc_id is NULL';
                $miniSql .= isset($_SESSION[_S]['dir_id']) ? ' AND dir_id = ' . $_SESSION[_S]['dir_id'] . '' : ' AND dir_id is NULL';
                $miniSql .= ' AND uni_id=0';
                $miniSql .= ' AND dep_id=0';
            }
            if ($_SESSION[_S]['nvj_id'] == 5) {
                $miniSql .= ' AND nvj_id = 6';
                $miniSql .= isset($_SESSION[_S]['ssc_id']) ? ' AND ssc_id = ' . $_SESSION[_S]['ssc_id'] . '' : ' AND ssc_id is NULL';
                $miniSql .= isset($_SESSION[_S]['dir_id']) ? ' AND dir_id = ' . $_SESSION[_S]['dir_id'] . '' : ' AND dir_id is NULL';
            }

            if ($_SESSION[_S]['nvj_id'] == 6) {
                $miniSql .= ' AND nvj_id = 6';
                $miniSql .= isset($_SESSION[_S]['ssc_id']) ? ' AND ssc_id = ' . $_SESSION[_S]['ssc_id'] . '' : ' AND ssc_id is NULL';
                $miniSql .= isset($_SESSION[_S]['dir_id']) ? ' AND dir_id = ' . $_SESSION[_S]['dir_id'] . '' : ' AND dir_id is NULL';
                $miniSql .= ' AND uni_id =0';
                $miniSql .= ' AND dep_id =0';
            }

            return "SELECT UPPER(CONCAT(usr_nombre,' ',usr_paterno,' ', usr_materno)) nombre
                FROM " . $this->ctrlEjercicioActual . '.cat_Usuario u
                INNER JOIN ".BD_USUARIO.".cat_Usuario usr USING( usr_id )
                LEFT JOIN ' . $this->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING( ejs_id )
                WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . '
                AND ur_id=' . $_SESSION[_S]['ur_id'] . '
                ' . $miniSql . '
                AND sts_id=1;';
        } elseif (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 1 || $_SESSION[_S]['nvj_id'] == 2) {
            if ($bandera == 0) {
                return 'SELECT CONCAT(ur_nombreAdministrativo) nombre FROM ' . $this->ctrlEjercicioActual . '.aux_UnidadResponsable
                    WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . " AND ur_nombreAdministrativo !=''
                    AND ur_id=" . $_SESSION[_S]['ur_id'] . ';';
            } elseif ($bandera == 1) {
                return 'SELECT CONCAT(ur_nombreAdministrativo) nombre
                    FROM ' . $this->ctrlEjercicioActual . '.aux_UnidadResponsable
                    JOIN ' . $this->ctrlEjercicioActual . '.cat_UnidadResponsable using( gpo_id, ur_id )
                    WHERE gpo_id=' . $gpo_id . '
                    AND ur_id=' . $ur_id . " AND ur_nombreAdministrativo = '" . $nombreJefe . "' AND sts_id=1;";
            }
        }
    }

    // Regresamos una array de datos con los permisos que tiene el usuario actual
    public function dameDataPermisosUsrActual()
    {
        $this->conectar();
        // Obtenemos TODOS los permisos sistema-menu-opcion-tarea que tiene el usuario PADRE
        $this->setSql(
            'SELECT sis_id,sis_nombre, men_id, men_titulo, opc_id, opc_titulo, tar_id,tar_nombre,temp.usr_id
            FROM
                    ' . $this->ctrlEjercicioActual . '.cat_Sistema
                JOIN ' . $this->ctrlEjercicioActual . '.cat_Opcion opc USING (sis_id)
                JOIN ' . $this->ctrlEjercicioActual . '.cat_Menu USING (sis_id, men_id)
                JOIN ' . $this->ctrlEjercicioActual . '.cat_Permisos USING (sis_id, men_id, opc_id)
                JOIN ' . $this->ctrlEjercicioActual . '.cat_Tareas USING (tar_id)
                JOIN ( SELECT * FROM ' . $this->ctrlEjercicioActual . '.cat_PermisosUsuarios
                WHERE usr_id = ' . $_SESSION[_S]['usr_id'] . ' UNION ALL SELECT * FROM ' . $this->ctrlEjercicioActual . '.cat_PermisosPendientes WHERE usr_id = ' . $_SESSION[_S]['usr_id'] . ') temp USING (sis_id, men_id, opc_id, tar_id)
                WHERE
                temp.usr_id = ' . $_SESSION[_S]['usr_id'] . '
                group by sis_id,men_id,opc_id,tar_id'
        );

        $this->consultar();
        $res = [];
        while ($r = $this->extraer()) {
            array_push($res, $r);
        }
        return $res;
    }

    // Regresamos una array de datos con los permisos del usuario hijo
    public function dameDataPermisosUsrHijo($prm, $bandera, $usuario, $gpo_id, $ur_id, &$permisos1, &$permisos2, &$permisos3, &$fechaInicio, &$fechaFinal)
    {
        if ((isset($prm->esperm) && $prm->esperm == 'si') || (isset($prm->edPerm) && $prm->edPerm == 'si')) {
            $this->conectar();
            // Si es una modificación en una solicitud de usuario,
            // o es una solicitud de permisos de usuarios autorizados...
            if ((isset($prm->edPerm) && $prm->edPerm == 'si') || (isset($prm->esperm) && $prm->esperm == 'si')) {
                // Si es una modificación a los permisos que ya tien0e en su solicitud...
                if ((isset($prm->esperm) && $prm->esperm == 'si')) {
                    $this->setSql('
                        select sis_id,men_id,opc_id,tar_id from
                        (
                        select sis_id,men_id,opc_id,tar_id from ' . $this->ctrlEjercicioActual . '.cat_PermisosUsuarios
                        INNER JOIN ' . $this->ctrlEjercicioActual . '.cat_Usuario cu USING(usr_id)
                        WHERE cu.usr_usuario="' . $usuario . '"
                        AND cu.gpo_id=' . $gpo_id . '
                        AND cu.ur_id=' . $ur_id . '
                        union all
                        select sis_id,men_id,opc_id,tar_id from ' . $this->ctrlEjercicioActual . '.cat_PermisosPendientes
                        INNER JOIN ' . $this->ctrlEjercicioActual . '.cat_Usuario cu USING(usr_id)
                                                WHERE cu.usr_usuario="' . $usuario . '"
                                                AND cu.gpo_id=' . $gpo_id . '
                                                AND cu.ur_id=' . $ur_id . '
                        )as x
                        group by sis_id,men_id,opc_id,tar_id;
                    ');
                    // Si es una NUEVA solicitud de Permisos, el usuario sólo debe ver aquellos permisos que tiene
                    // el usuario que lo dio de alta, QUE AÚN NO TENGA...
                    // Jordy dixit
                } else {
                    // Si es una MODIFICACIÓN de una solicitud de PERMISOS...
                    // Lo sabremos porque no vendrá usr_id, y sí vendrá usp_id
                    if (!isset($prm->usr_id) && isset($prm->usp_id)) {

                        $datosOK = new AccesoDatos();
                        $datosOK->conectar();

                        // Averiguamos los datos finales del usuario padre y del usuario hijo
                        // Como estamos tramitando permisos, ya DEBEN existir datos finales para ambos
                        $datosOK->setSql('SELECT usp_id, usr_id FROM '.BD_USUARIO.'.cat_UsuarioPrevio WHERE sac_id = ' . $prm->sac_id . ';');
                        $datosOK->consultar();
                        $padre = $datosOK->extraer();

                        $usr_padre = $padre['usr_id'];
                        $usr_hijo = $padre['usp_id'];

                        $this->setSql('SELECT uPadre.sis_id, uPadre.men_id, uPadre.opc_id, uPadre.tar_id
                            FROM
                                ' . $this->ctrlEjercicioActual . '.cat_PermisosUsuarios uPadre
                                JOIN ' . $this->ctrlEjercicioActual . '.cat_Opcion opc USING(sis_id, men_id, opc_id)
                                LEFT JOIN
                                ( SELECT sis_id, men_id, opc_id, tar_id, usr_id
                                    FROM '.BD_USUARIO.'.cat_PermisoUsuarioPrevio
                                    WHERE usp_id = ' . $usr_hijo . '
                                ) AS uActual USING(sis_id, men_id, opc_id, tar_id)
                            WHERE
                                uPadre.usr_id = ' . $usr_padre . '
                                AND opc.sts_id = 1
                                /* AND uActual.usr_id IS NULL */
                            ;
                        ');
                        // Si es una alta
                    } else {
                        $this->setSql('SELECT uPadre.sis_id, uPadre.men_id, uPadre.opc_id, uPadre.tar_id
                            FROM
                                ' . $this->ctrlEjercicioActual . '.cat_PermisosUsuarios uPadre
                                JOIN ' . $this->ctrlEjercicioActual . '.cat_Opcion opc USING(sis_id, men_id, opc_id)
                                LEFT JOIN
                                ( SELECT sis_id, men_id, opc_id, tar_id, usr_id
                                    FROM ' . $this->ctrlEjercicioActual . '.cat_PermisosUsuarios
                                    WHERE usr_id = ' . $prm->usr_id . '
                                ) AS uActual USING(sis_id, men_id, opc_id, tar_id)
                            WHERE
                                uPadre.usr_id = ' . $prm->usr_usu . '
                                AND opc.sts_id = 1
                                AND uActual.usr_id IS NULL
                            ;
                        ');
                    }
                }
                // Aquí entraría cuando fuera una ALTA de solicitud de usuario
                // Recuperamos entonces todos los permisos que tiene el usuario que está registrando un
                // nuevo usuario, ya que sólo podrá asignar permisos sobre aquellos sistema - menu - opcion - tareas
                // a los que el mismo tenga derechos
            } else {
                $this->setSql('
                    SELECT sis_id, men_id,opc_id,tar_id
                    FROM ' . $this->ctrlEjercicioActual . '.cat_PermisosUsuarios
                    WHERE usr_id=' . $prm->usr_id . ';
                ');
            }

            $this->consultar();
            $nuevosRegistrosPermisos = $this->getNumeroRegistros();
            $conVal = 0;
            while ($permisosTemporal = $this->extraer()) {
                // Permisos2 siempre tendrá el último sis+men+opc+tar que se procese
                $permisos2['sis_id'] = $permisosTemporal['sis_id'];
                $permisos2['men_id'] = $permisosTemporal['men_id'];
                $permisos2['opc_id'] = $permisosTemporal['opc_id'];
                $permisos2['tar_id'] = $permisosTemporal['tar_id'];
                // permisos3, al indicarle un valor que aumenta, acumula todos los
                // registros que se vayan procesando
                $permisos3[$conVal] = $permisos2;
                $conVal++;
            }
            $this->desconectar();
        }

        // Si viene desde una solicitud de Usuario ... y es una modificación...
        if (isset($prm->usp_id) && $bandera == 1) {
            $conVal = 0;
            $this->conectar();
            $this->setSql('SELECT sis_id, men_id,opc_id,tar_id,
                DATE_FORMAT( usp_fechaInicio, "%d/%m/%Y" ) AS fechaInicio,
                DATE_FORMAT( usp_fechaFin, "%d/%m/%Y" ) AS fechaFin
                FROM '.BD_USUARIO.'.cat_PermisoUsuarioPrevio
                WHERE usp_id=' . $prm->usp_id . ';
            ');

            $this->consultar();
            while ($permisosTemporal = $this->extraer()) {
                $permisos2['sis_id'] = $permisosTemporal['sis_id'];
                $permisos2['men_id'] = $permisosTemporal['men_id'];
                $permisos2['opc_id'] = $permisosTemporal['opc_id'];
                $permisos2['tar_id'] = $permisosTemporal['tar_id'];

                $fechaInicio = $permisosTemporal['fechaInicio'];
                $fechaFin = $permisosTemporal['fechaFin'];

                $permisos1[$conVal] = $permisos2;
                $conVal++;
            }
            $this->desconectar();
        }
    }

    // Determinamos si el nombre de usuario calculado ya existe o no
    // 1: ya existe   0: no existe
    public function valNombre($prm)
    {
        $numeroRegistros = 0;
        if ($prm->rfc != '') {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                SELECT usr_id FROM '.BD_CTRL.'.cat_Usuario WHERE usr_id  IN(
                SELECT usr_id FROM '.BD_USUARIO.'.cat_Usuario WHERE usr_nombre = "' . $prm->nom . '" and usr_paterno="' . $prm->ap . '" and usr_materno="' . $prm->am . '"' . 'and usr_rfc="' . $prm->rfc . '"
                );');
            $conexion->consultar();
            $numeroRegistros = $conexion->getNumeroRegistros();
            $conexion->desconectar();
        }
        echo json_encode((($numeroRegistros > 0) ? 1 : 0));
    }

    public function validaDatosUsuario($usuario)
    {
        $this->conectar();
        $this->setSql('SELECT usr_usuario FROM ' . $this->ctrlEjercicioActual . '.cat_Usuario WHERE usr_usuario = "' . $usuario . '"' . ' AND gpo_id=' . $_SESSION[_S]['gpo_id'] . ' AND ur_id=' . $_SESSION[_S]['ur_id']);
        $this->consultar();
        $this->desconectar();
        echo json_encode((($this->getNumeroRegistros() > 0) ? 1 : 0));
    }

    /**
     * guarda el log de los paramentros enviados a las funciones
     * @param prm objeto compuesto por los datos del POST del Request
     * @return void
     */
    private function log($prm)
    {
        if (!file_exists('./log')) {
            mkdir('./log');
        }
        $rutalog = 'log/SolAccesos';

        $file = fopen($rutalog . '.log', 'a');

        $tempdata = '';
        $hoy = date('Y-m-d H-i-s');
        $id = date('YmdHis');
        foreach ($prm as $field => $value) {
            if (!is_object($value)) {
                $tempdata .= '|' . $field . '=' . $value;
            } else {
                foreach ($value as $name => $val) {
                    $tempdata .= '|' . $name . '=' . $val;
                }
            }
        }

        fwrite($file, $tempdata . PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog . '.log');
        $peso = ($peso / 1024) / 1024;
        if ($peso > 8) {
            $hoy = date('dma');
            $rutamodificada = $rutalog . $hoy . '.log';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog . 'log', 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }
    }
    // Guardamos una nueva solicitud, o una modificación a una solicitud existente
    public function abcSolicitud($prm)
    {
        switch ($prm->abc) {
            case 'a':
                // Si es una Solicitud de Permisos...
                if (isset($prm->esPermiso) && ($prm->esPermiso == 'si')) {
                    // Debe venir el campo REQUERIMIENTO de la solicitud
                    if (!(isset($prm->requerimiento))) {
                        return ['resultado' => 'error', 'mensaje' => 'Falta el requerimiento'];
                        // Si viene la variable permisos...
                        // Supongo que es para evitar que nos metan gol... porque el primero IF controló eso...
                    } elseif (isset($prm->permisos)) {
                        // Si nos envían el parámetro usr_id, NO LO PELAMOS y le ponemos fijo
                        // el del usuario que está entrando a ésta pantalla
                        $prm->usr_id = $_SESSION[_S]['usr_id'];

                        // El usr_id es el usuario actual, y ése lo asigna automáticamente la entidad
                        unset($prm->usr_id);
                        // Al estar dando de alta una nueva solicitud, no tiene sentido pasarle un # de solicitud
                        unset($prm->sac_id);

                        $prm->sac_fechaAlta = date('Y/m/d h:i:s');
                        $prm->sac_sefip = '1';
                        $prm->ejercicio = APP_EJERCICIO;
                        $prm->uur_id = $this->dameUurActual();

                        $prm->sac_ejercicio = APP_EJERCICIO;
                        $prm->sac_tipo = 'S';
                        $idSolicitud = SolicitudAcceso::abc($prm);
                        $prm->sac_id = $idSolicitud;
                        /* $sac_ticket = SolicitudAcceso::abc($prm);
                        $prm->sac_ticket = $sac_ticket;*/


                        unset($prm->usp_id);
                        $prm->usp_ejercicio = APP_EJERCICIO;

                        $this->conectar();
                        $this->setSql("SELECT AES_ENCRYPT(:clave, 'SEFIP2027') as cifr;");
                        $parametro = [':clave' => $prm->clave];
                        $this->consultar($parametro);
                        $fc = $this->extraer();
                        $cAcceso = $fc['cifr'];

                        $prm->claveAcceso = $cAcceso;
                        $prm->fechaInicio = date('Y/m/d h:i:s');
                        $prm->fechaFin = date('Y/m/d h:i:s');
                        $idUsuarioPrevio = UsuarioPrevio::abc($prm);

                        $permisos = new stdClass();
                        $auxiliar = explode(';', $prm->permisos);
                        if (count($auxiliar) == '1') {
                            $auxiliar = [];
                            $auxiliar = explode(':', $prm->permisos);
                            $permisos->abc = 'a';
                            $permisos->sis_id = $auxiliar[0];
                            $permisos->men_id = $auxiliar[1];
                            $permisos->opc_id = $auxiliar[2];
                            $permisos->tar_id = $auxiliar[3];
                            $permisos->usp_id = $idUsuarioPrevio;
                            $permisos->ejercicio = APP_EJERCICIO;
                            $permisos->usp_ejercicio = APP_EJERCICIO;

                            $permisos->fechaInicio = $prm->fechaInicio;
                            $permisos->fechaFin = $prm->fechaFin;

                            PermisoUsuarioPrevio::abc($permisos);
                        } else {
                            for ($i = 0; $i < count($auxiliar); $i++) {
                                $auxiliar2 = explode(':', $auxiliar[$i]);
                                $permisos = new stdClass();
                                $permisos->abc = 'a';
                                $permisos->sis_id = $auxiliar2[0];
                                $permisos->men_id = $auxiliar2[1];
                                $permisos->opc_id = $auxiliar2[2];
                                $permisos->tar_id = $auxiliar2[3];
                                $permisos->usp_id = $idUsuarioPrevio;
                                $permisos->ejercicio = APP_EJERCICIO;
                                $permisos->usp_ejercicio = APP_EJERCICIO;

                                $permisos->fechaInicio = $prm->fechaInicio;
                                $permisos->fechaFin = $prm->fechaFin;

                                PermisoUsuarioPrevio::abc($permisos);
                                unset($permisos);
                                $auxiliar2 = [];
                            }
                        }
                        return ['resultado' => 'ok_1', 'mensaje' => 'Solicitud Agregada'];
                    } else {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Falta seleccionar al menos un permiso'];
                    }
                } elseif (isset($prm->permisos)) { //de momento se silencia, toda esta parte es para la primer insercion
                    if (!(isset($prm->prf_id))) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Falta el Perfil'];
                    } elseif (!(isset($prm->rfc))) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Falta el rfc'];
                    } elseif (!(isset($prm->requerimiento))) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Falta el requerimiento'];
                    } elseif (!(isset($prm->materno)) && !(isset($prm->materno)) && !(isset($prm->materno)) && ($prm->nombre == ' ')) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Faltan datos del usuario'];
                    } elseif ($prm->clave == '' || $prm->confirmar == '') {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Las contraseñas no coinciden'];
                    } elseif ($prm->clave != $prm->confirmar) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Las contraseñas son requeridas'];
                    } else {
                        if (isset($prm->usr_id)) {
                            $prm->usr_id = $_SESSION[_S]['usr_id'];
                        }

                        $this->conectar();
                        $this->setSql("SELECT AES_ENCRYPT(:clave, 'SEFIP2027') as cifr, sha2(:clave, 256) as has;");
                        $parametro = [':clave' => $prm->clave];
                        $this->consultar($parametro);
                        $fc = $this->extraer();
                        $cAcceso = $fc['cifr'];
                        $clave = $fc['has'];
                        $this->desconectar();
                        $prm->clave = $clave; // clave del usuario en el formulario
                        $prm->claveAcceso = $cAcceso;

                        $prm->sac_fechaAlta = date('Y/m/d h:i:s'); // fecha de la solicitud o modificacion
                        $prm->eso_id = '1'; // estado de la solicitud
                        $prm->sac_sefip = '1'; //sistema al que se quiere hacer la solicitud

                        $prm->ejercicio = APP_EJERCICIO;
                        $prm->uur_id = $this->dameUurActual();
                        $prm->sts_id = 1; // HABILITADO

                        // Marcos: 15/junio/2018
                        // La base de datos no acepta nul, pero según las reglas aplica nul
                        // así que si viene vacío, le ponemos un espacio... no es nul... pero si es nul
                        if ($prm->nvj_id >= '3') {
                            if ($prm->responsable == '') {
                                $prm->responsable = ' ';
                            }
                        }

                        $prm->sac_ejercicio = APP_EJERCICIO;
                        $prm->sac_tipo = 'S';
                        $idSolicitud = SolicitudAcceso::abc($prm);
                        $prm->nuevo = '1'; //si el usuario es nuevo es 1 si no es un 2
                        $prm->usu = '0'; // como aun no se a activado el usuario es 0
                        $prm->sac_id = $idSolicitud;
                        $prm->usp_ejercicio = APP_EJERCICIO;
                        $idUsuarioPrevio = UsuarioPrevio::abc($prm);
                        $numero = strlen($prm->permisos);
                        $auxiliar = [];
                        $auxiliar2 = [];

                        //Castro Jordy 20-03-18 Se ocupa la entidad para todos los permisos
                        $permisos = new stdClass();
                        $auxiliar = explode(';', $prm->permisos);
                        if (count($auxiliar) == '1') {
                            $auxiliar = [];
                            $auxiliar = explode(':', $prm->permisos);
                            $permisos->abc = 'a';
                            $permisos->sis_id = $auxiliar[0];
                            $permisos->men_id = $auxiliar[1];
                            $permisos->opc_id = $auxiliar[2];
                            $permisos->tar_id = $auxiliar[3];
                            $permisos->usp_id = $idUsuarioPrevio;
                            $permisos->usp_ejercicio = APP_EJERCICIO;
                            $permisos->ejercicio = APP_EJERCICIO;

                            $permisos->fechaInicio = $prm->fechaInicio;
                            $permisos->fechaFin = $prm->fechaFin;

                            PermisoUsuarioPrevio::abc($permisos);
                        } else {
                            for ($i = 0; $i < count($auxiliar); $i++) {
                                $auxiliar2 = explode(':', $auxiliar[$i]);
                                $permisos = new stdClass();
                                $permisos->abc = 'a';
                                $permisos->sis_id = $auxiliar2[0];
                                $permisos->men_id = $auxiliar2[1];
                                $permisos->opc_id = $auxiliar2[2];
                                $permisos->tar_id = $auxiliar2[3];
                                $permisos->usp_id = $idUsuarioPrevio;
                                $permisos->usp_ejercicio = APP_EJERCICIO;
                                $permisos->ejercicio = APP_EJERCICIO;

                                $permisos->fechaInicio = $prm->fechaInicio;
                                $permisos->fechaFin = $prm->fechaFin;
                                PermisoUsuarioPrevio::abc($permisos);
                                unset($permisos);
                                $auxiliar2 = [];
                            }
                        }
                        return ['resultado' => 'ok_2', 'mensaje' => 'Solicitud Agregada'];
                    }
                }

                break;
            case 'c':
                if (!(isset($prm->permisos) && $prm->permisos != '')) {
                    return ['resultado' => 'err_1', 'mensaje' => 'Debes seleccionar un permiso'];
                } elseif (isset($prm->esPermiso) && ($prm->esPermiso == 'si')) {
                    if (!isset($prm->permisos)) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Falta seleccionar al menos un permiso'];
                    } else {
                        if (isset($prm->usr_id)) {
                            $prm->usr_id = $_SESSION[_S]['usr_id'];
                        }
                        $prm->sac_fechaAlta = date('Y/m/d h:i:s');
                        $prm->ejercicio = APP_EJERCICIO;
                        $prm->sts_id = 1; // HABILITADO
                        $prm->sac_ejercicio = APP_EJERCICIO;
                        $prm->usp_ejercicio = APP_EJERCICIO;
                        $prm->sac_tipo = 'S';
                        $sac_ticket = SolicitudAcceso::abc($prm);
                        $prm->sac_ticket = $sac_ticket;
                        $idSolicitud = SolicitudAcceso::abc($prm);
                        //$prm->nvj_id = "2";
                        $idUsuarioPrevio = UsuarioPrevio::abc($prm);
                        $eliminar = new AccesoDatos();
                        $eliminar->conectar();
                        $eliminar->setSql('
                            DELETE
                            FROM '.BD_USUARIO.'.cat_PermisoUsuarioPrevio
                            WHERE usp_id=' . $prm->usp_id . ';
                        ');
                        $eliminar->consultar();
                        $eliminar->desconectar();
                        $permisos = new stdClass();
                        //Cambiar esta parte por que sea el numero total del arreglo auxiliar
                        $auxiliar = explode(';', $prm->permisos);

                        if (count($auxiliar) == '1') {
                            $auxiliar = [];
                            $auxiliar = explode(':', $prm->permisos);
                            $permisos->abc = 'a';
                            $permisos->sis_id = $auxiliar[0];
                            $permisos->men_id = $auxiliar[1];
                            $permisos->opc_id = $auxiliar[2];
                            $permisos->tar_id = $auxiliar[3];
                            $permisos->usp_id = $prm->usp_id;
                            $permisos->usp_ejercicio = APP_EJERCICIO;
                            $permisos->ejercicio = APP_EJERCICIO;

                            $permisos->fechaInicio = $prm->fechaInicio;
                            $permisos->fechaFin = $prm->fechaFin;
                            $this->log($permisos);
                            PermisoUsuarioPrevio::abc($permisos);
                        } else {
                            for ($i = 0; $i < count($auxiliar); $i++) {
                                $auxiliar2 = explode(':', $auxiliar[$i]);
                                $permisos = new stdClass();
                                $permisos->abc = 'a';
                                $permisos->sis_id = $auxiliar2[0];
                                $permisos->men_id = $auxiliar2[1];
                                $permisos->opc_id = $auxiliar2[2];
                                $permisos->tar_id = $auxiliar2[3];
                                $permisos->usp_id = $prm->usp_id;
                                $permisos->usp_ejercicio = APP_EJERCICIO;
                                $permisos->ejercicio = APP_EJERCICIO;
                                $permisos->fechaInicio = $prm->fechaInicio;
                                $permisos->fechaFin = $prm->fechaFin;

                                $this->log($permisos);

                                PermisoUsuarioPrevio::abc($permisos);
                                unset($permisos);
                                $auxiliar2 = [];
                            }
                        }
                        return ['resultado' => 'ok_3', 'mensaje' => 'Solicitud guardada'];
                    }
                } elseif (isset($prm->permisos)) {
                    if ($prm->clave == '' || $prm->confirmar == '') {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Las contraseñas son requeridas'];
                    } elseif ($prm->clave != $prm->confirmar) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Las contraseñas no son iguales'];
                    } elseif (strlen($prm->clave) < 6) {
                        return ['resultado' => 'err_msg', 'mensaje' => 'Las contraseñas no cumplen con la longitud mínima'];
                    } else {
                        $prm->usr_id = $_SESSION[_S]['usr_id'];
                        //Actualizacion de la solicitud de acceso
                        $prm->sac_fechaAlta = date('Y/m/d h:i:s');

                        $prm->ejercicio = APP_EJERCICIO;
                        $prm->sts_id = 1; // HABILITADO
                        $prm->sac_ejercicio = APP_EJERCICIO;
                        $prm->sac_tipo = 'S';
                        $idSolicitud = SolicitudAcceso::abc($prm);
                        //Actualizacion del usuario previo

                        $this->conectar();
                        $this->setSql('SELECT sha2(:clave, 256) as has;');
                        $parametro = [':clave' => $prm->clave];
                        $this->consultar($parametro);
                        $fc = $this->extraer();
                        $clave = $fc['has'];
                        $this->desconectar();

                        $prm->clave = $clave;
                        $prm->usp_ejercicio = APP_EJERCICIO;
                        $idUsuarioPrevio = UsuarioPrevio::abc($prm);
                        //Actualizacion de permisos

                        // se eliminaran todos los registros perteneciente a la solicitud de usuario en la parte de permisos
                        // y se agregara de nuevo con entidades
                        $eliminar = new AccesoDatos();
                        $eliminar->conectar();
                        $eliminar->setSql('
                            DELETE
                            FROM '.BD_USUARIO.'.cat_PermisoUsuarioPrevio
                            WHERE usp_id=' . $prm->usp_id . ';
                        ');

                        $eliminar->consultar();
                        $eliminar->desconectar();
                        $permisos = new stdClass();
                        //Cambiar esta parte por que sea el numero total del arreglo auxiliar
                        $auxiliar = explode(';', $prm->permisos);
                        if (count($auxiliar) == '1') {
                            $auxiliar = [];
                            $auxiliar = explode(':', $prm->permisos);
                            $permisos->abc = 'a';
                            $permisos->sis_id = $auxiliar[0];
                            $permisos->men_id = $auxiliar[1];
                            $permisos->opc_id = $auxiliar[2];
                            $permisos->tar_id = $auxiliar[3];
                            $permisos->usp_id = $prm->usp_id;
                            $permisos->usp_ejercicio = APP_EJERCICIO;
                            $permisos->ejercicio = APP_EJERCICIO;

                            $permisos->fechaInicio = $prm->fechaInicio;
                            $permisos->fechaFin = $prm->fechaFin;

                            PermisoUsuarioPrevio::abc($permisos);
                        } else {
                            for ($i = 0; $i < count($auxiliar); $i++) {
                                $auxiliar2 = explode(':', $auxiliar[$i]);
                                $permisos = new stdClass();
                                $permisos->abc = 'a';
                                $permisos->sis_id = $auxiliar2[0];
                                $permisos->men_id = $auxiliar2[1];
                                $permisos->opc_id = $auxiliar2[2];
                                $permisos->tar_id = $auxiliar2[3];
                                $permisos->usp_id = $prm->usp_id;
                                $permisos->usp_ejercicio = APP_EJERCICIO;
                                $permisos->ejercicio = APP_EJERCICIO;
                                $permisos->fechaInicio = $prm->fechaInicio;
                                $permisos->fechaFin = $prm->fechaFin;

                                PermisoUsuarioPrevio::abc($permisos);
                                unset($permisos);
                                $auxiliar2 = [];
                            }
                        }
                        return ['resultado' => 'ok_3', 'mensaje' => 'Solicitud actualizada'];
                    }
                } else {
                    return ['resultado' => 'err_msg', 'mensaje' => 'Falta seleccionar al menos un permiso'];
                }
                break;
        }
    }

    // Una vez enviada la solicitud de autorización, actualizamos la pantalla
    public function enviarSolicitudParaAutorizacion($prm)
    {
        $actualiza = new stdClass();
        $actualiza->abc = 'c';
        $actualiza->sac_id = $prm->sac_id;
        $actualiza->eso_id = '2';

        SolicitudAcceso::abc($actualiza);

        return true;
    }

    // Metodo para eliminar la solicitud de usuario
    public function eliminaSolicitud($solicitud_acceso_id)
    {
        // Marcos: 02/jul/2018
        // Jordi dixit: No habrá eliminación, sólo le cambiaremos el status a 5 - SOLICITUD OCULTA
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->conectar();
        $this->setSql('SET @usr_ip = INET_ATON( "' . $ip . '" ), @usr_id = ' . $_SESSION[_S]['usr_id'] . ', @bit_observacion = "ELIMINAR SOLICITUD"');
        $this->consultar();

        $this->setSql('UPDATE '.BD_USUARIO.'.cat_SolicitudAcceso SET eso_id = 5 WHERE sac_id=' . $solicitud_acceso_id . ';');
        $this->consultar();
        $this->desconectar();

        return ['resultado' => 'ok_2', 'mensaje' => 'Solicitud Eliminada'];
    }

    //Castro Jordy 14-03-2018 Metodo para sobre escribir la unidad ejecutora de la solicitud de Usuario
    public function dameSqlRellenarUnidadEjecutora($prm)
    {
        if (isset($prm->agregar)) {
            if ($prm->agregar == 'si') {
                if ($_SESSION[_S]['nvj_id'] <= 2) {
                    return 'SELECT ue_id, concat (ue_nombre) as uEjecutora FROM ' . $this->ctrlEjercicioActual . '.cat_UnidadEjecutora WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . ' AND ur_id=' . $_SESSION[_S]['ur_id'] . '';
                } else {
                    return 'SELECT ue_id, concat (ue_nombre) as uEjecutora FROM ' . $this->ctrlEjercicioActual . '.cat_UnidadEjecutora ';
                }
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    // Regresamos el sql con el que se llenará la tabla de solicitudes de usuario
    public function dameSqlTablaUsuarios()
    {
        $tempWhere = '';
        if (isset($_SESSION[_S]['nvj_id'])) {
            switch ($_SESSION[_S]['nvj_id']) {
                case 1:
                case 2:
                case 3:
                    $tempWhere .= ' AND u.usr_usuario NOT LIKE ("' . $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] . '%")';
                    // no break
                case 4:

                    break;
                case 5:

                    break;
                case 6:
                    $tempWhere .= ' AND ssc_id=' . $_SESSION[_S]['ssc_id'] . ' AND dir_id=' . $_SESSION[_S]['dir_id'] . ' AND uni_id is not NULL AND dep_id is not NULL ';
                    break;
                case 7:
                    $tempWhere .= ' AND ssc_id=' . $_SESSION[_S]['ssc_id'] . ' AND dir_id is not NULL AND uni_id is not NULL AND dep_id is not NULL ';
                    break;
            }
        }

        return 'SELECT u.usr_id, CONCAT(gpo_id,ur_id) ur,CONCAT(usr_nombre," ",usr_paterno," ",usr_materno) nombre, "" AS dep_id
            FROM '.BD_USUARIO.'.cat_Usuario up
            INNER JOIN ' . $this->ctrlEjercicioActual . '.cat_Usuario u USING( usr_id )
            WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . ' AND ur_id=' . $_SESSION[_S]['ur_id'] . '  AND sts_id=1 ' . $tempWhere . ';';
    }

    /*Castro Jordy 10/04/2018 Estos metodos se dejaron faltan corroborar si funcionan como deberian*/
    public function dameSqlRecargarUr($gpo_id)
    {
        return 'SELECT gpo_id, concat(ur_nombre) uresponsable FROM ' . $this->ctrlEjercicioActual . '.cat_UnidadResponsable WHERE gpo_id=' . $gpo_id . ';';
    }
    public function recargarDireccion($ssc_id)
    {
        return 'SELECT dir_id, dir_nombre Direccion FROM ' . $this->ctrlEjercicioActual . '.cat_Direccion WHERE  ssc_id=' . $ssc_id . ';';
    }
    public function recargarUnidad($dir_id)
    {
        return 'SELECT uni_id, concat(uni_nombre) unidad FROM ' . $this->ctrlEjercicioActual . '.cat_Unidad WHERE dir_id=' . $dir_id . ';';
    }
    public function recargarDepartamento($ssc_id, $dir_id, $uni_id)
    {
        return 'SELECT dep_id , concat(dep_nombre) departamento FROM ' . $this->ctrlEjercicioActual . '.cat_Departamento WHERE sts_id = 1 AND uni_id=' . $uni_id . ' AND ssc_id=' . $ssc_id . ' AND dir_id=' . $dir_id . ';';
    }
    public function recargarJefe($grupo, $responsable)
    {
        if (isset($grupo)) {
            return 'SELECT concat(ur_nombreAdministrativo) nombre FROM ' . $this->ctrlEjercicioActual . '.aux_UnidadResponsable WHERE ur_nombreAdministrativo="' . $responsable . '" AND gpo_id=' . $_SESSION[_S]['gpo_id'] . ';';
        } else {
            return 'SELECT uni_id, concat(uni_nombre) unidad FROM ' . $this->ctrlEjercicioActual . '.cat_Unidad WHERE dir_id=' . $responsable . ';';
        }
    }

    // Para llenar la tabla de actualización de permisos
    public function dameSqlLlenaUpdPermiso()
    {
        return 'select usr_id, concat(usr_nombre,\' \', usr_paterno, \' \', usr_materno, \'(\',usr_usuario,\')\') nombre,usr_puesto ,usr_correo from cat_Usuario where usr_usu=' . $_SESSION[_S]['usr_id'] . ' and usr_nombre not in ("--","-") order by 2;';
    }

    // Regresamos una array de datos con los permisos que tiene el usuario específico
    // identificado por su usr_id
    public function dameDataPermisosByUsrID($usr_id)
    {
        $this->conectar();
        // Obtenemos TODOS los permisos sistema-menu-opcion-tarea que tiene el usuario PADRE
        $this->setSql(
            '
            SELECT distinct sis_id,sis_nombre, men_id, men_titulo, opc_id, opc_titulo, tar_id, if(sis_id="I","HABILITAR",tar_nombre) tar_nombre, nanais
                FROM cat_Sistema
                JOIN cat_Opcion opc USING(sis_id)
                JOIN cat_Menu USING(sis_id,men_id)
                JOIN cat_Permisos USING(sis_id,men_id,opc_id)
                JOIN cat_Tareas USING(tar_id)
                JOIN (
                    SELECT sis_id,men_id,opc_id,tar_id, usr_id, nanais
                    FROM cat_PermisosUsuarios pu
                    LEFT JOIN (
                        SELECT sis_id,men_id,opc_id,tar_id, usr_id nanais
                        FROM cat_PermisosUsuarios perm
                        WHERE perm.usr_id="' . $usr_id . '"
                    ) X USING(sis_id,men_id,opc_id,tar_id)
                    WHERE pu.usr_id="' . $_SESSION[_S]['usr_id'] . '"
                ) x USING(sis_id,men_id,opc_id,tar_id)
                where opc.sts_id=1
                ORDER BY sis_id,men_id,opc_id,tar_id;'
        );

        $this->consultar();
        $res = [];
        while ($r = $this->extraer()) {
            array_push($res, $r);
        }
        return $res;
    }

    // Marcos: 08/jun/2018
    // Validamos en la tabla temporal y en la definitiva si el usuario que intentan registra
    // ya existe, siguiendo las siguientes reglas de negocio:
    /*
    1. La validación de usuario estará limitada al grupo y la UR
    2. Validamos en la tabla temporal (PORQUE ASUMIMOS QUE TODO PASO POR ESTA TABLA, y NO encontraremos en la definitiva ningún usaurio que no esté aquí):
    a) SOLO VALIDAMOS REGISTROS QUE **NO** TENGAN EN SU SOLICITUD EL ESTADO 4 - CANCELADO

     * SI LO ENCONTRAMOS

    b) Si no se repite el nombre + paterno + materno, validamos que el RFC no exista aún
    c) Si se repite el nombre + paterno + materno, revisamos si el RFC es el mismo
    - Si el RFC es diferente, el usuario se acepta, aplicando las Reglas de Negocio para armar nombre de usuario
    - Si es el mismo, se rechaza

     * SI NO LO ENCONTRAMOS

    b) Aplicamos las Reglas de Negocio para armar nombre de usuario

    3. Armamos el nombre de usuario usando las siguiente reglas:
    a) Se toma la primera letra del nombre + apellido paterno + primera letra del apellido materno
    b) Se busca en la base de datos provisional y definitiva
    c) Si no existe
     * Fin, avanzamos con el proceso
    Si YA existe
     * Tomamos las 2 primeras letras el nombre + apellido paterno + primera letra del apellido materno
     * vamos el punto 3.b)
     */
    public function validaExisteUnUsuario($gpo_id, $ur_id, $nombre, $paterno, $materno, $rfc)
    {
        // Buscamos en la base de datos nombre + paterno + materno
        // OJO: Sólo solicitudes con estado 4 - CANCELADO... ya que otro estado significa que está
        //      en proceso en otra solicitud o ya fué autorizado
        $sql = "SELECT usp_nombre, usp_paterno, usp_materno, usp_rfc
            FROM ".BD_USUARIO.".cat_UsuarioPrevio
            JOIN ".BD_USUARIO.".cat_SolicitudAcceso USING(sac_id)
            JOIN ".BD_SINPRES.".vin_UnidadResponsable USING(uur_id)
            WHERE gpo_id = $gpo_id AND ur_id = $ur_id AND TRIM(usp_nombre) = $nombre
                AND TRIM( usp_paterno ) = $paterno AND TRIM(usp_materno) = $materno
                AND usp_rfc = $rfc
                AND eso_id <> 4;";

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {
            return false;
        }
        return true;
    }

    // Regresamos el valor del encuadre uur_id para la clasifiación administrativa y el ejercicio actual
    public function dameUurActual()
    {
        $sql = 'SELECT uur_id FROM '.BD_SINPRES.'.vin_UnidadResponsable WHERE gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'] . ' AND vur_Ejercicio = ' . APP_EJERCICIO;

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            $uur = $this->extraer();
            return $uur['uur_id'];
        } else {
            return '';
        }
    }

    // Determinamos si el correo electronico ya existe o no
    // 1: ya existe   0: no existe 2: el telefono está asociado  a más de un usuario
    // 3: la información del formulario no se precarga
    public function validaNuevoCorreo($prm)
    {
        $bandera = 0;
        $usr_id = 0;
        $nvj = 0;
        $prf = 0;
        $existeNvj = false;
        $existePrf = false;
        if ($prm->correo != '') {
            //Validar si existe un usuario activo asociado al correo electrónico capturado dentro de la misma Unidad Responsable (UR
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('SELECT usr_correo 
                FROM '.BD_USUARIO.'.cat_Usuario
                INNER JOIN cat_Usuario USING(usr_id)
                WHERE usr_correo = "' . $prm->correo . '"
                AND gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' 
                AND ur_id = ' . $_SESSION[_S]['ur_id'] . '
                AND sts_id=1');
            $conexion->consultar();
            $numeroRegistros = $conexion->getNumeroRegistros();
            $conexion->desconectar();

            if ($numeroRegistros == 0) {
                //Validar que el correo electrónico está asociado a un usuario activo
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('SELECT usr_id, usr_telefono, nvj_id, prf_id
                    FROM '.BD_USUARIO.'.cat_Usuario
                    WHERE usr_correo = "' . $prm->correo . '"
                    AND sts_id=1');
                $conexion->consultar();
                $numeroRegistros = $conexion->getNumeroRegistros();
                if ($numeroRegistros == 1) {
                    //Validar que el número telefónico asociado al usuario sea único dentro de la misma UR
                    $datos = $conexion->extraer();
                    $usr_id = $datos['usr_id'];
                    $nvj = $datos['nvj_id'];
                    $prf = $datos['prf_id'];
                    if ($datos['usr_telefono'] != '') {
                        $conexion = new AccesoDatos();
                        $conexion->conectar();
                        $conexion->setSql('SELECT usr_telefono 
                            FROM cat_Usuario
                            INNER JOIN '.BD_USUARIO.'.cat_Usuario USING(usr_id)
                            WHERE usr_telefono = "' . $datos['usr_telefono'] . '"
                            AND gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' 
                            AND ur_id = ' . $_SESSION[_S]['ur_id'] . '
                            AND sts_id=1;
                        ');
                        $conexion->consultar();
                        $numeroRegistros = $conexion->getNumeroRegistros();
                        $bandera = ($numeroRegistros > 0) ? 2 : $bandera;
                        if ($bandera == 0) {
                            $conexion->setSql($_SESSION[_S]['prm']['nivelJerarquicoSQL']);
                            $conexion->consultar();
                            while($r = $conexion->extraer()){
                                if ($r['nvj_id'] == $nvj) {
                                    $existeNvj = true;
                                    break;
                                }
                            }
                            $conexion->setSql($_SESSION[_S]['prm']['perfilSQL']);
                            $conexion->consultar();
                            while($r = $conexion->extraer()){
                                if ($r['prf_id'] == $prf) {
                                    $existePrf = true;
                                    break;
                                }
                            }
                            //Valida que exista el nivel jerarquico y el perfil para precargar los datos del usuario
                            $bandera = (!$existeNvj || !$existePrf) ? 3 : $bandera;
                        }
                    }
                }
                $conexion->desconectar();
            } elseif ($numeroRegistros > 0){
                //El correo electrónico está asociado a más de un usuario
                $bandera = 1;
            }
        }
        
        $respuesta = [
            'bandera' => $bandera,
            'usu' => $usr_id
        ];
        echo json_encode($respuesta);
    }

    // Determinamos si el telefono ya existe o no
    // 1: ya existe   0: no existe
    public function validaNuevoTelefono($prm)
    {
        $numeroRegistros = 0;
        if ($prm->telefono != '' || $prm->telefono !=0) {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('SELECT usr_telefono 
                FROM cat_Usuario
                INNER JOIN '.BD_USUARIO.'.cat_Usuario USING(usr_id)
                WHERE usr_telefono = "' . $prm->telefono . '"
                AND gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' 
                AND ur_id = ' . $_SESSION[_S]['ur_id'] . '
                AND sts_id=1;
            ');
            $conexion->consultar();
            $numeroRegistros = $conexion->getNumeroRegistros();
            $conexion->desconectar();
        }
        echo json_encode((($numeroRegistros > 0) ? 1 : 0));
    }

    // Obtenemos los datos del usuario
    public function dameDatosUsuario($usr_id)
    {
        $this->conectar();
        $this->setSql('
            SELECT
                usr_titulo, usr_nombre, usr_paterno, usr_materno, usr_correo,
                usr_sexo, usr_puesto, usr_telefono, usr_extension, nvj_id,
                prf_id, usr_rfc
            FROM
                '.BD_USUARIO.'.cat_Usuario
            WHERE
                usr_id = ' . $usr_id . ';
        ');
        $this->consultar();
        $this->desconectar();       
        echo json_encode($this->extraer());
    }

}


// Fin del código
