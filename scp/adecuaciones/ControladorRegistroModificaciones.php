<?php

/**
 * Description of ControladorRegistroModificaciones
 *
 * @author ?
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 11-Oct-2019.
 *update: Ejercicio 2020
 * Comment: -----
 * Objective:
- Altas de Folios
- Editar Folio
- Historial del Folio
- Agregar Movimientos
- Modificar Movimiento
- Eliminar Movimientos
- Carga Masiva
- Validacion de Folios
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de lectura, alta de folios.
 * */

//inicio de sesion y llamada a las librerias
session_start();
ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuaciones.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaAdecuaciones.php';
require_once dirname(__FILE__) . '/../../lib/scp/emailNotificaciones.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/dibujaProgFinanciamiento.php';
require_once dirname(__FILE__) . '/../../lib/FicheroSefip.php';
require_once dirname(__FILE__) . '/../../ent/scp/sp_AutorizarModificacion2026.php'; #se deja a lo ultimo
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scp/MaestroModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/DetalleModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/Presupuesto.php';
require_once dirname(__FILE__) . '/../../ent/scp/OrigenOperacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraMaestro.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraDetalle.php';
require_once dirname(__FILE__) . '/../../ent/scp/TipoModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/TipoMovimiento.php';
require_once dirname(__FILE__) . '/../../ent/scp/EstadoModificacion.php';
require_once dirname(__FILE__) . '/../../ent/PartidaEspecifica.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../../srv/TipoCompra.php';
require_once dirname(__FILE__) . '/../../lib/scp/emailNotificacionesP.php';
require_once dirname(__FILE__) . '/../../lib/dameProgramaFinanciamiento2.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaActividadMir.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoMetaActividadMir.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraDetalleModificacionMeta.php';

class ControladorRegistroModificaciones extends WebResponse
{
    public $sql;
    private $vista;
    private $model;
    private $email;
    private $perfil;
    private $nvj;
    private $gpo;
    private $ur_id;
    private $usr_id;
    private $ue_id;
    private $departamento;
    private $unidad;
    private $direccion;
    private $subSecretaria;
    private $adm_grupo;
    private $adm_ur;
    private $adm_prg;
    private $adm_spr;
    private $adm_py;
    private $adm_oa;
    private $adm_ue;
    private $adm_epg;
    private $alta_folioUr;
    private $alta_folioRh;
    private $alta_folioSefin;
    private $prmCargaMasiva;
    private $ip;
    private $ur;
    private $ue;
    private $drh;
    private $presupuesto;
    private $planeacion;
    private $escritura;
    private $nivelRevision;
    private $omo_id;
    private $mod_gpo_id;
    private $mod_ur_id;
    private $gpo_id;
    public $mod_id;
    public $emailP;
    public $tri_id;

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        $this->vista = new vistaAdecuaciones();
        $this->model = new modelAdecuaciones();
        $this->email = new emailNotificaciones();
        $this->emailP = new emailNotificacionesP();

        // Variables de sesión
        $this->perfil = isset($_SESSION[_S]['prf_id']) ? $_SESSION[_S]['prf_id'] : false;
        $this->nvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->gpo = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : false;
        $this->ur_id = isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : false;
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;
        $this->ue_id = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : false;
        $this->departamento = isset($_SESSION[_S]['dep_id']) ? $_SESSION[_S]['dep_id'] : false;
        $this->unidad = isset($_SESSION[_S]['uni_id']) ? $_SESSION[_S]['uni_id'] : false;
        $this->direccion = isset($_SESSION[_S]['dir_id']) ? $_SESSION[_S]['dir_id'] : false;
        $this->subSecretaria = isset($_SESSION[_S]['ssc_id']) ? $_SESSION[_S]['ssc_id'] : false;

        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->adm_prg = isset($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : false;
        $this->adm_spr = isset($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : false;
        $this->adm_py = isset($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : false;
        $this->adm_oa = isset($_SESSION[_S]['clasAdm-oa']) ? $_SESSION[_S]['clasAdm-oa'] : false;
        $this->adm_ue = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : false;
        $this->adm_epg = isset($_SESSION[_S]['clasAdm-epg']) ? $_SESSION[_S]['clasAdm-epg'] : false;
        $this->alta_folioUr = isset($_SESSION[_S]['prm']['S']['1']['1']['7']) ? true : false;
        $this->alta_folioRh = isset($_SESSION[_S]['prm']['S']['1']['1']['82']) ? true : false;
        $this->alta_folioSefin = isset($_SESSION[_S]['prm']['S']['1']['1']['14']) ? true : false;
        $this->prmCargaMasiva = isset($_SESSION[_S]['prm']['S']['1']['1']['23']) ? true : false;
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        // Verificamos que tipo de usuario es
        $this->ur = ($this->nvj != false && $this->nvj == 2 && $this->alta_folioUr == true ? true : false);
        $this->ue = ($this->nvj != false && $this->nvj == 1 ? true : false);
        $this->drh = ($this->alta_folioRh == true ? true : false);
        $this->presupuesto = ($this->subSecretaria != false && $this->subSecretaria == 3 ? true : false);
        $this->planeacion = ($this->subSecretaria != false && $this->subSecretaria == 2 ? true : false);
        // Verificamos los permisos de escritura
        $this->escritura = ($this->alta_folioUr == true ||
            ($this->alta_folioSefin == true && isset($this->prm->nivelRevision) &&
                $this->prm->nivelRevision == 'alta') || $this->alta_folioRh == true ? true : false);
        $this->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '' ? $this->prm->nivelRevision : '');
        $this->omo_id = (isset($this->prm->omo_id) && $this->prm->omo_id != '' ? $this->prm->omo_id : '');
        $this->mod_gpo_id = (isset($this->prm->mod_gpo_id) && $this->prm->mod_gpo_id != '' ? $this->prm->mod_gpo_id : '');
        $this->mod_ur_id = (isset($this->prm->mod_ur_id) && $this->prm->mod_ur_id != '' ? $this->prm->mod_ur_id : '');
        $this->mod_id = (isset($this->prm->mod_id) && $this->prm->mod_id != '' ? $this->prm->mod_id : '');
        $this->gpo_id = !($this->ur || $this->ue) ? $this->adm_grupo : $this->gpo;
        $this->ur_id = !($this->ur || $this->ue) ? $this->adm_ur : $this->ur_id;

        $hoy = date('Y-m-d');
        if ($hoy >= APP_EJERCICIO . '-01-01-' && $hoy <= APP_EJERCICIO . '-03-31-') {
            $this->tri_id = 1;
        } elseif ($hoy >= APP_EJERCICIO . '-04-01-' && $hoy <= APP_EJERCICIO . '-06-30-') {
            $this->tri_id = 2;
        } elseif ($hoy >= APP_EJERCICIO . '-07-01-' && $hoy <= APP_EJERCICIO . '-09-30-') {
            $this->tri_id = 3;
        } elseif ($hoy >= APP_EJERCICIO . '-10-01-' && $hoy <= APP_EJERCICIO . '-12-31-') {
            $this->tri_id = 4;
        }

        switch ($this->metodo) {
            case 'tablaUrs':
                $this->tablaUrs();
                break; //Metodo que muestra la lista de urs para recursos humanos y sefin, y apartir de ello realizar un folio.
            case 'llenaTablaUrs':
                $this->llenaTablaUrs();
                break;
            case 'tablaFolios':
                $this->tablaFolios();
                break; //Metodo que muestra la lista de folios creados por la UR, R.H Y SEFIN
            case 'llenaTablaFolios':
                $this->llenaTablaFolios();
                break;
            case 'addModFolio':
                $this->addModFolio();
                break; //Metodo para agregar nuevo folio de la Dependencia, R.H Y SEFIN
            case 'abcFolios':
                $this->abcFolios();
                break; //Metodo que guarda la informacion del folio de origen A,B,C
            case 'agregaTipoModificacion':
                $this->agregaTipoModificacion();
                break; //Metodo para agregar el tipo de modificacion respecto a la modalidad elegida
            case 'agregaTipoFolio':
                $this->agregaTipoFolio();
                break; //Metodo para agregar el tipo de folio respecto a la modalidad elegida
            case 'tablaModificaciones':
                $this->tablaModificaciones();
                break; //Metodo para mostrar las modificaciones o movimientos agregados al folio por la UR, D.R.H Y SEFIN
            case 'llenaTablaModificaciones':
                $this->llenaTablaModificaciones();
                break;
            case 'addModModificacion':
                $this->addModModificacion();
                break; //Metodo para agregar modificaciones o movimientos al folio por la UR Y RH.
            case 'addModPrevision':
                $this->addModPrevision();
                break; //Metodo que agrega modificaciones al folio  de origen A,B,C en SEFIN, asi como la compensacion del folio si es necesario.
            case 'muestraPresupuesto':
                $this->muestraPresupuesto();
                break; //Metodo para mostrar informacion referente a presupuesto segun la clave presupuestal elegida.
            case 'abcModificacion':
                $this->abcModificacion();
                break; //Metodo para guardar la informacion del movimiento del folio de origen A,B,C
            case 'revisarFolio':
                $this->revisarFolio();
                break; // Metodo para enviar a revision el folio por la ur, y asi mismo hacer la respectiva validación.
            case 'tablaFoliosRead':
                $this->tablaFoliosRead();
                break; //Medodo para visualizar los folios en estado de validacion y autorizacion UR y validacion y autorizacion SEFIN
            case 'llenatablaFoliosRead':
                $this->llenatablaFoliosRead();
                break;
            case 'validacionFolio':
                $this->validacionFolio();
                break; //Metodo que sirve para revisar, validar y autorizar folios internos y enviar folios externos.
            case 'rechazarFolio':
                $this->rechazarFolio();
                break; //Metodo que sirve para rechazar folios en el modulo UR y sefin
            case 'tablaHistorialFolio':
                $this->tablaHistorialFolio();
                break; // tabla de historial del folio
            case 'addMensajeRechazo':
                $this->addMensajeRechazo();
                break;
            case 'modalOficioAut':
                $this->modalOficioAut();
                break;
            case 'uploadOficioAut':
                $this->uploadOficioAut();
                break;
            case 'uploadRepInterno':
                $this->uploadRepInterno();
                break;
            case 'downloadFile':
                $this->downloadFile();
                break;
            case 'eliminarMovimientos':
                $this->eliminarMovimientos();
                break;
            case 'mostrarResultadoPartida':
                $this->mostrarResultadoPartida();
                break;
            case 'elegirTipoFolio':
                $this->elegirTipoFolio();
                break;
            case 'addFirma': //Metodo que muestra las firmas
                $this->addFirma();
                break;
            case 'firmas': //Muestra las firmas
                $this->firmas(); //
                break;
            case 'abcFirmas': //Registra las firmas
                $this->abcFirmas();
                break;
            case 'verificarFolio':
                $this->verificarFolio();
                break;
            case 'borrarDatosModificados':
                $this->borrarDatosModificados();
                break;
            case 'resumenAutorizacion':
                $this->resumenAutorizacion();
                break;
            case 'validaMovtoUE':
                $this->validaMovtoUE();
                break;
            case 'modificarMetas':
                $this->modificarMetas();
                break;
            case 'listaMetas':
                $this->listaMetas();
                break;
            case 'metas':
                $this->metas(); //Muestra la Información calendarizada de la meta
                break;
            case 'actualizarMetas':
                $this->actualizarMetas();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    } // Fin constructor

    /*METODO QUE MUESTRA LAS OPCIONES SEGUN LOS PERMISOS, NIVEL Y PERFIL DEL USUARIO, UR Y SEFIN*/
    private function main()
    {
        $botones = $this->vista->vwBotones('menuPrincipal');
        $this->mixHtml($botones);
        $this->agregaTools('');
        $this->mixJs('js/charts/highcharts.src.js');
        $this->mixJs('js/charts/highcharts-3d.src.js');
        $this->mixJs('js/charts/modules/data.js');
        $this->mixJs('js/charts/modules/drilldown.js');
        $this->mixScript('
            $("#divContenedorGeneralModal").html("");

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            $(".altafoliour").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($this->perfil != false && ($this->perfil == 'S' ||
                    $this->perfil == 'U') ? 'tablaUrs' : 'tablaFolios') . '"
                    ' . ($this->perfil != false && ($this->perfil == 'S' ||
                    $this->perfil == 'U') || $this->nvj >= 3 ? ',nivelRevision:"alta"' : '') . '
                });
                return false;
            });

            $(".validafoliour").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($this->perfil != false && ($this->perfil == 'S' ||
                    $this->perfil == 'U') ? 'tablaUrs' : 'tablaFoliosRead') . '",
                    nivelRevision:"validacionUR"
                });
                return false;
            });

            $(".autorizafoliour").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($this->perfil != false && ($this->perfil == 'S' ||
                    $this->perfil == 'U') ? 'tablaUrs' : 'tablaFoliosRead') . '",
                        nivelRevision:"autorizacionUR"
                    });
                return false;
            });

            $(".altafoliosefin").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaUrs",
                    nivelRevision:"alta"
                });
                return false;
            });

            $(".revisafoliosefin").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaUrs",
                    nivelRevision:"revisionSEFIN"
                });
                return false;
            });

            $(".validafoliosefin").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaUrs",
                    nivelRevision:"validacionSEFIN"
                });
                return false;
            });

            $(".autorizafoliosefin").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaUrs",
                    nivelRevision:"autorizacionSEFIN"
                });
                return false;
            });
        ');
    } //Fin main

    /*MÉTODO QUE CAMBIA EL VALOR DE LA VARIABLE DE SESIÓN DE LA CLASIFICACION ADMINISTRATIVA*/
    private function cambiarUR($ur)
    {
        if (!empty($ur) && is_object($ur)) {
            $script = '$("#combour").val("' . $ur->gpo_id . $ur->ur_id . '").change();';
        } else {
            $script = 'notify("No se establecieron los valores correctamente","error");';
        }

        $this->mixScript($script);
    } // Fin cambiarUR

    /*MÉTODO QUE CONSULTA A LA ENTIDAD UR RESPOSABLE */
    private function getUR($gpo, $ur)
    {
        $objUr = new stdClass();
        if (!empty($gpo) && !empty($ur)) {
            $objUr = new UnidadResponsable();
            $objUr->setGpo_id($gpo);
            $objUr->setUr_id($ur);
            $objUr->select();
        }
        return $objUr;
    } // Fin getUR

    /*METODO QUE DETERMINA SI ES MODO LECTURA*/
    private function lectura($mod, $esUR)
    {
        $estadosUR = [1, 3];
        $estadosSEFIN = [6, 8, 11, 17];
        $lectura = false;
        if ($mod->getMod_id() != '') {
            if ($esUR && !in_array($mod->getEmo_id(), $estadosUR)) {
                $lectura = true;
            } elseif (!$esUR && !in_array($mod->getEmo_id(), $estadosSEFIN)) {
                $lectura = true;
            }
        } else {
            $lectura = false;
        }
        return $lectura;
    } // Fin lectura

    /*MÉTODO QUE VA A HACER UN MIXSCRIPT SEGÚN LO ENVIADO*/
    private function pintaScript($s)
    {
        if (!empty($s)) {
            $this->mixScript('' . $s . '');
        }
    } //Fin pintaScript

    //METODO QUE MUESTRA UN LISTADO DE UR'S DE RECURSOS HUMANOS O DE SEFIN
    private function tablaUrs()
    {
        $script = 'desbloquearClasAdmin();';
        $this->pintaScript($script);
        if ($this->ur) {
            $this->omo_id = 'A';
        }
        if ($this->drh) {
            $this->omo_id = 'C';
        }
        if ($this->presupuesto) {
            $this->omo_id = 'B';
        }
        if ($this->planeacion) {
            $this->omo_id = 'E';
        }

        $html = '';
        $nivelRevision = $this->nivelRevision != '' ? $this->nivelRevision : '';
        $btnNuevo = $this->vista->vwBotones('nuevoFolio', '', $nivelRevision);
        $btnNuevo->click = '
            $("#div_clave").show();
            if ($("#cveFin_id").val()!="" && $("#cveFin_id").val()!=0 && $("#cveFin_id").val()!=undefined) {
                comunica("' . $this->pantalla . '",{
                    metodo:"addModFolio",
                    ecf_id:$("#cveFin_id").val(),
                    omo_id:"' . $this->omo_id . '",
                    mod_gpo_id:$("#combour").find("option:selected").attr("gpo_id"),
                    mod_ur_id:$("#combour").find("option:selected").attr("ur_id"),
                    cambiaUR:true
                    ' . (!empty($nivelRevision) ? ',nivelRevision:"' . $nivelRevision . '"' : '') . '
                });
            }else{
                notify("Selecciona una clave de financiamiento","error");
                v = $("#cveFin_id").parent();
                v.css("color","#900").css("border","1px solid rgba(100,0,0,.5)")
                .css("background-color","rgba(255,0,0,.3)")
                .effect("pulsate", {}, 300);
            }
        ';
        $btnNuevo = is_object($btnNuevo) ? $this->ponBoton($btnNuevo) : '';
        $btnNuevo = $nivelRevision == 'alta' && !$this->drh ? $btnNuevo : '';
        $btnRegresar = $this->vista->vwBotones('regresar', '', $nivelRevision);
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"main"
            });
        ';
        $btnRegresar = is_object($btnRegresar) ? $this->ponBoton($btnRegresar) : '';

        $esSefin = $this->presupuesto || $this->planeacion;

        //MOSTRAR TABLA DE LAS URS CON EL NUMERO DE FOLIOS PENDIENTE DE REVISIÓN
        $pantalla = $this->pantalla;
        $tabla = $this->vista->vwTablaUrs($nivelRevision, $esSefin, $pantalla);

        if ($esSefin) {
            $cveFin = new stdClass();
            $cveFin->gpo_id = $this->gpo_id;
            $cveFin->ur_id = $this->ur_id;
            $cveFin->filtro = 'presupuesto';

            $html .= ClasificacionFinanciamiento::creaComboCveFin($cveFin);
        }

        $this->mixHtml('<div id = "div_clave" class="col-md-12 col-xs-12 mb-4">' . $html . '</div>
            <div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>
        ');
        $this->prm->omo_id = $this->omo_id;
        $this->llenaTablaUrs();
        $this->agregaTools($btnRegresar . ' ' . $btnNuevo);

        if ($esSefin && $this->nivelRevision != 'alta') {
            $script = '
                if($("#div_clave").length > 0) {
                    $("#div_clave").hide();
                }
            ';
        }

        $script .=
        ClasificacionAdministrativa::onChangeUr('
            if($("#div_clave").length >0) {
                var objFolio = {
                    metodoCveFin:"cargaComboCveFin",
                    filtro: "presupuesto",
                    gpo_id:$(this).find("option:selected").attr("gpo_id"),
                    ur_id:$(this).find("option:selected").attr("ur_id")
                }
                comunica("srv/ClasificacionFinanciamiento.php",objFolio);
            }
        ');

        $this->mixScript($script);
    } // Fin tablaUrs

    /*METODO QUE MUESTRA LAS URS CON FOLIOS PENDIENTES DE REVISION O SI ES LA DRH MUESTRA AQUELLAS QUE SON CONTROLADAS POR ESTA DIRECCIÓN*/
    private function llenaTablaUrs()
    {
        $origen = $sql = '';

        if ($this->drh) {
            $origen = 'C';
            $sql = $this->model->getConsultaTablasUrs('drh', $this->nivelRevision);
        }
        if ($this->presupuesto) {
            $origen = 'B';
            $sql = $this->model->getConsultaTablasUrs('presupuesto', $this->nivelRevision);
        }
        if ($this->planeacion) {
            $origen = 'E';
            $sql = $this->model->getConsultaTablasUrs('planeacion', $this->nivelRevision);
        }

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Ur';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".verUr").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($this->nivelRevision == 'alta'
                    || $this->nivelRevision == 'revisionSEFIN' ?
                    'tablaFolios' : 'tablaFoliosRead') . '",
                    ' . (($origen != '') ?
                    'omo_id:"' . $origen . '",' : 'omo_id:$(this).attr("omo_id"),') . '
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    cambiaUR:true
                    ' . (!empty($this->nivelRevision) ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
            });
        ');
    } // Fin llenaTablaUrs

    /*METODO QUE MUESTRA LA LISTA DE FOLIOS CREADOS POR LA UR*/
    private function tablaFolios()
    {
        $script = 'bloquearClasAdmin();';
        $this->pintaScript($script);
        $html = '';
        $esUR = $this->ur || $this->ue || $this->drh;
        $cambiaUR = isset($this->prm->cambiaUR);
        $nuevo = $this->vista->vwBotones('nuevoFolio');

        // SI ES LA DIRECCIÓN DE RECURSOS HUMANOS SE ENVIAN LOS PARAMETROS DE LA UR CON LA QUE VA A CREAR EL FOLIO
        if (($this->drh || $this->presupuesto || $this->planeacion) && $cambiaUR) {
            $this->gpo_id = $this->prm->mod_gpo_id;
            $this->ur_id = $this->prm->mod_ur_id;

            $cambiarUR = new stdClass();
            $cambiarUR->gpo_id = $this->gpo_id;
            $cambiarUR->ur_id = $this->ur_id;
            $this->cambiarUR($cambiarUR);
        }

        if ($this->ur) {
            # code...
            $this->omo_id = 'A';
        } elseif ($this->presupuesto) {
            # code...
            $this->omo_id = 'B';
        } if ($this->planeacion) {
            # code...
            $this->omo_id = 'E';
        } elseif ($this->drh) {
            # code...
            $this->omo_id = 'C';
        }

        $objUr = $this->getUR($this->gpo_id, $this->ur_id);

        if ($esUR) {
            $nuevo->click = '
                if( $("#cveFin_id").val() != 0 && $("#cveFin_id").val() != "" ){
                    comunica("' . $this->pantalla . '",{
                        metodo:"addModFolio",
                        ecf_id:$("#cveFin_id").val()
                        ' . (($this->omo_id != '') ?
                        ',omo_id:"' . $this->omo_id . '"' : ",omo_id:'A'") . '
                        ' . (($this->nivelRevision != '') ?
                        ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        ' . (($this->gpo_id != '') ?
                        ',mod_gpo_id:' . $this->gpo_id . '' : '') . '
                        ' . (($this->ur_id != '') ?
                        ',mod_ur_id:"' . $this->ur_id . '"' : '') . '
                    });
                }else{
                    notify("Selecciona una clave de financiamiento","error");
                    v = $("#cveFin_id").parent();
                    v.css("color","#900").css("border","1px solid rgba(100,0,0,.5)")
                    .css("background-color","rgba(255,0,0,.3)")
                    .effect("pulsate", {}, 300);
                }
                return false;
            ';
        } else {
            $nuevo->click = '
                $("#div_clave").css("display","block");
                if( $("#cveFin_id").val() != 0 && $("#cveFin_id").val() != "" ){
                    comunica("' . $this->pantalla . '",{
                        metodo:"addModFolio",
                        ecf_id:$("#cveFin_id").val()
                        ' . (($this->omo_id != '') ?
                        ',omo_id:"' . $this->omo_id . '"' : ",omo_id:'A'") . '
                        ' . (($this->nivelRevision != '') ?
                        ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        ' . (($this->mod_gpo_id) ?
                        ',mod_gpo_id:' . $this->mod_gpo_id . '' : '') . '
                        ' . (($this->mod_ur_id != '') ?
                        ',mod_ur_id:"' . $this->mod_ur_id . '"' : '') . '
                    });
                }else{
                    notify("Selecciona una clave de financiamiento","error");
                    v = $("#cveFin_id").parent();
                    v.css("color","#900").css("border","1px solid rgba(100,0,0,.5)")
                    .css("background-color","rgba(255,0,0,.3)")
                    .effect("pulsate", {}, 300);
                }
                return false;
            ';
        }

        $nuevo = ($this->escritura) ? $this->ponBoton($nuevo) : '';
        $regresar = $this->vista->vwBotones('regresar');

        if ($this->ur || $this->ue) {
            $regresar->click = '
                comunica("' . $this->pantalla . '",{metodo:"main"});
            ';
        } else {
            $regresar->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaUrs"
                    ' . (isset($this->prm->nivelRevision) ?
                    ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ';
        }

        $regresar = $this->ponBoton($regresar);
        $excel = $this->vista->vwBotones('exportaExcel');
        $excel = $this->ponBoton($excel);
        $pdf = $this->vista->vwBotones('exportaPdf');
        $pdf = $this->ponBoton($pdf);
        $botones = $regresar . ' ' . $nuevo . ' ' . $pdf . ' ' . $excel;

        $tabla = new stdClass();
        $tabla->id = 'tbl-Folios';
        $tabla->titulo = 'Adecuaciones Presupuestales';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        unset($param);

        //Si es Unidad responsable o la dirección de recursos humanos mostramos la clave de financiamiento, sino la UR que se está revisando
        $param = new stdClass();
        $param->mostrar = ($this->ur || $this->ue || $this->drh) ? 'cvefin' : 'ur';
        $param->parametrosExtra = new stdClass();
        $param->parametrosExtra->prm = $this->prm;
        $param->parametrosExtra->objUR = $objUr;
        $resTools = $this->vista->vwToolsTablaFolios($param);
        $tabla->tools = ($param->mostrar == 'cvefin') ? ClasificacionFinanciamiento::creaComboCveFin($resTools) : $resTools;
        if ($param->mostrar == 'cvefin') {
            $tabla->comunica->ecf_id = '{$("#cveFin_id option:selected").val()}';
        }

        $tabla->comunica->nivelRevision = (!empty($this->nivelRevision) ? $this->nivelRevision : '');
        $tabla->comunica->omo_id = (!empty($this->omo_id) ? $this->omo_id : '');
        $tabla->comunica->metodo = 'llenaTablaFolios';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        //NUEVA FUNCIONALIDAD PARA FOLIOS BLOQUEADOS
        if ($esUR) {
            $tabla->multiseleccionable = true;
            $tabla->prmSeleccion = ['omo_id', 'mod_gpo_id', 'mod_ur_id', 'mod_id'];
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = 'blocked';
            $tabla->casoSeleccionable->valor = '2';
            $tabla->casoSeleccionable->campo = 'excenta';
            $tabla->casoSeleccionable->valor = '2';
            $tabla->casoSeleccionable->colorEnFalso = 'rgba(255, 0, 0,0.1)';
        }

        $tr = [];
        $th = new stdClass();
        $th->width = '10px';
        $th->texto = 'Ver / Editar ';
        array_push($tr, $th);

        if ($esUR) {
            //Imprimir folio
            $th = new stdClass();
            $th->texto = 'Imprimir';
        } else {
            //Si el nivel es revision entonces muestra la opcion de imprimir.
            if ($this->nivelRevision == 'revisionSEFIN') {
                $th = new stdClass();
                $th->texto = 'Imprimir';
                array_push($tr, $th);
            }
            //Origen del folio
            $th = new stdClass();
            $th->texto = 'Origen';
        }
        array_push($tr, $th);

        //Número de folio
        $th = new stdClass();
        $th->texto = 'Folio';
        array_push($tr, $th);
        //Modalidad del folio
        $th = new stdClass();
        $th->texto = 'Modalidad';
        array_push($tr, $th);
        //Tipo de modificación
        $th = new stdClass();
        $th->texto = 'Tipo de Adecuación';
        array_push($tr, $th);
        //Justificación del folio
        $th = new stdClass();
        $th->texto = 'Justificación';
        array_push($tr, $th);
        //Fecha
        $th = new stdClass();
        $th->texto = 'Fecha';
        array_push($tr, $th);

        if ($esUR) {
            //Solicitar revisión
            $th = new stdClass();
            $th->texto = 'Solicitar revisión';
        } else {
            //Clave de financiamiento
            $th = new stdClass();
            $th->texto = 'Clave de financiamiento';
        }
        array_push($tr, $th);
        //Estado del folio
        $th = new stdClass();
        $th->texto = 'Estado';
        array_push($tr, $th);

        //Detalle del folio (movimientos agregados)
        $th = new stdClass();
        $th->texto = 'Detalle';
        array_push($tr, $th);

        $trbody = [];
        //contenido ver
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';
        $td->casos->tipoCondicion = '||';
        //Sefin debe ver el boton de editar cuando el folio este en edo 6 y 11, la dependencia en edo 1 y 3
        if ($esUR) {
            $td->casos->valor = '1,3';
        } else {
            if ($this->planeacion) {
                $td->casos->valor = '6,11,17';
            }
            if ($this->presupuesto) {
                $td->casos->valor = '8,11,17';
            }
        }

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'gris';
        $td->casos->verdadero->icono = 'pencil';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'origenModificacion';
        $td->casos->verdadero->attr->mod_gpo_id = 'mod_gpo_id';
        $td->casos->verdadero->attr->mod_ur_id = 'mod_ur_id';
        $td->casos->verdadero->attr->mod_id = 'mod_id';
        $td->casos->verdadero->attr->enc_oop = 'enc_oop';
        $td->casos->verdadero->attr->ecf_id = 'ecf_id';
        $td->casos->verdadero->attr->cff_id = 'cff_id';
        $td->casos->verdadero->class = 'addModFolio btn-xs';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->icono = 'eye';
        $td->casos->falso->color = 'gris';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->omo_id = 'origenModificacion';
        $td->casos->falso->attr->mod_gpo_id = 'mod_gpo_id';
        $td->casos->falso->attr->mod_ur_id = 'mod_ur_id';
        $td->casos->falso->attr->mod_id = 'mod_id';
        $td->casos->falso->attr->enc_oop = 'enc_oop';
        $td->casos->falso->attr->ecf_id = 'ecf_id';
        $td->casos->falso->attr->cff_id = 'cff_id';
        $td->casos->falso->class = 'addModFolio btn-xs';
        array_push($trbody, $td);

        //Para poder imprimir el folio en la dependencia, este debe estar en estado 1,3,6,8,11.
        //Si es sefin planeación 6,11 y egresos 8,11.
        if ($esUR) {
            //Impresión solo en estado 1,3,6,8,11
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'emo_id';
            $td->casos->tipoCondicion = '||';
            $td->casos->valor = '1,3,6,8,11';
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->color = 'rojo';
            $td->casos->verdadero->icono = 'file-pdf';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->omo_id = 'origenModificacion';
            $td->casos->verdadero->attr->mod_gpo_id = 'mod_gpo_id';
            $td->casos->verdadero->attr->mod_ur_id = 'mod_ur_id';
            $td->casos->verdadero->attr->mod_id = 'mod_id';
            $td->casos->verdadero->attr->emo_id = 'emo_id';
            $td->casos->verdadero->attr->mmd_id = 'mmd_id';
            $td->casos->verdadero->class = 'imprimir btn-xs';
            array_push($trbody, $td);
        } else {
            //SEFIN
            if ($this->nivelRevision == 'revisionSEFIN') {
                //Impresión solo en estado 1,3,6,11,17
                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = 'emo_id';
                $td->casos->tipoCondicion = '||';
                if ($this->planeacion) {
                    $td->casos->valor = '6,11,17';
                }
                if ($this->presupuesto) {
                    $td->casos->valor = '8,11,17';
                }
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->color = 'rojo';
                $td->casos->verdadero->icono = 'file-pdf';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->omo_id = 'origenModificacion';
                $td->casos->verdadero->attr->mod_gpo_id = 'mod_gpo_id';
                $td->casos->verdadero->attr->mod_ur_id = 'mod_ur_id';
                $td->casos->verdadero->attr->mod_id = 'mod_id';
                $td->casos->verdadero->attr->emo_id = 'emo_id';
                $td->casos->verdadero->attr->mmd_id = 'mmd_id';
                $td->casos->verdadero->attr->mod_tipoMovimiento = 'mod_tipoMovimiento';
                $td->casos->verdadero->class = 'addFirma btn-xs';
                array_push($trbody, $td);
            }
            //Numero de folio
            $td = new stdClass();
            $td->campoTxt = 'omo_nombre';
            array_push($trbody, $td);
        }
        //Numero de folio
        $td = new stdClass();
        $td->campoTxt = 'mod_id';
        array_push($trbody, $td);
        //Modalidad del folio
        $td = new stdClass();
        $td->campoTxt = 'mmd_id';
        array_push($trbody, $td);
        //Tipo de modificación
        $td = new stdClass();
        $td->campoTxt = 'tmd_nombre';
        array_push($trbody, $td);
        //Justificación
        $td = new stdClass();
        $td->campoTxt = 'mod_concepto';
        array_push($trbody, $td);
        //Fecha de creación del folio
        $td = new stdClass();
        $td->campoTxt = 'mod_fechaFolio';
        $td->formato = 'fecha';
        array_push($trbody, $td);

        if ($esUR) {
            //Solicitar revisión solo en estado 1 y 3
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'emo_id';
            $td->casos->tipoCondicion = '||';
            $td->casos->valor = '1,3';
            //Solicitado que se inhabilitara.
            $td->casos->ands = [];
            $and = new stdClass();
            $and->campo = 'habilitado';
            $and->operador = '==';
            $and->valor = 1;
            $and->valorTipo = 'numero';
            array_push($td->casos->ands, $and);
            $td->casos->ors = [];
            $or = new stdClass();
            $or->campo = 'excenta';
            $or->operador = '==';
            $or->valor = 1;
            $or->valorTipo = 'numero';
            array_push($td->casos->ors, $or);
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->icono = 'paper-plane';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->omo_id = 'origenModificacion';
            $td->casos->verdadero->attr->mod_gpo_id = 'mod_gpo_id';
            $td->casos->verdadero->attr->mod_ur_id = 'mod_ur_id';
            $td->casos->verdadero->attr->mod_id = 'mod_id';
            $td->casos->verdadero->class = 'revisarFolio btn btn-outline-info btn-xs';
            $td->casos->falso = new stdClass();
            $td->casos->falso->campoTxt = 'revisar';
            array_push($trbody, $td);
        } elseif (!$esUR) {
            //Clave de financiamiento
            $td = new stdClass();
            $td->campoTxt = 'cfi_clave';
            array_push($trbody, $td);
        }
        //Estado de folio
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'emo_color';
        $td->color = 'emo_color';
        $td->campoTxt = 'emo_id';
        $td->lbl = 'emo_nombre';
        $td->title = 'emo_nombre';
        array_push($trbody, $td);

        //Ir a detalle de folio
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->color = 'gris';
        $td->icono = 'pencil';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
        $td->attr->enc_oop = 'enc_oop';
        $td->attr->cff_id = 'cff_id';
        $td->class = 'detalleFolio btn-xs';

        array_push($trbody, $td);
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $mensaje = '';
        // Solo visualizan este mensaje las UR'S el 20 ultimo de cada bimestre
        if ($this->ur || $this->ue) {
            $fechas = [APP_EJERCICIO . '-03-20', APP_EJERCICIO . '-06-20', APP_EJERCICIO . '-09-20', APP_EJERCICIO . '-12-20'];
            $notificacion = $this->vista->notificacion('alerta', $fechas);
            if (is_string($notificacion)) {
                $mensaje .= $notificacion;
            }
        }
        //MENSAJE DE BLOQUEO
        if ($esUR) {
            $notificacion = $this->vista->notificacion('bloqueo');
            if (is_string($notificacion)) {
                $mensaje .= $notificacion;
            }
        }

        if (!$esUR) {
            $cveFin = new stdClass();
            $cveFin->gpo_id = $this->adm_grupo;
            $cveFin->ur_id = $this->adm_ur;
            $cveFin->filtro = 'presupuesto';
            $html .= ClasificacionFinanciamiento::creaComboCveFin($cveFin);
        }

        if (($this->presupuesto || $this->planeacion) && $this->nivelRevision == 'alta') {
            $script = ' $("#div_clave").show(); ';
            $this->mixScript($script);
        }

        $this->mixHtml('
            <div id = "div_clave" class="col-md-12 col-xs-12" style = "display:none">' . $html . '</div>
            <div class="col-md-12 col-xs-12">' . $mensaje . '
                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');

        $this->mixHtml(
            $this->agregaTools($botones) . '
            <form id="reporte" method="post" action="" target="_blank">
                <input type="hidden" id="omo_id" name="omo_id" value=""/>
                <input type="hidden" id="mod_gpo_id" name="mod_gpo_id" value=""/>
                <input type="hidden" id="mod_ur_id" name="mod_ur_id" value=""/>
                <input type="hidden" id="mod_id" name="mod_id" value=""/>
                <input type="hidden" id="mod_tipoMovimiento" name="mod_tipoMovimiento" value=""/>
            </form>

            <div class="row">
                <fieldset id="addMetas" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="justificacionMetas" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="modalPregunta" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
        '
        );
        //se agrega para el ejercicio 2019
        $this->mixScript('
            $("#btn_excel").hide();
            $("#btn_pdf").hide();
        ');

        if (!$esUR) {
            $this->llenaTablaFolios();
        }

        $this->mixScript('
            $("' . (($this->nvj == 1) ? ''
            : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
            ' . (($esUR) ? '
                if($("#comboCvefin").length && $("#cveFin_id").val()!="" && $("#cveFin_id").val()!=0){
                    ' . $this->wrLlamaLlenaTabla('tbl-Folios') . '
                }
                $("#cveFin_id").change(function(){
                    if($("#cveFin_id").val()!="" && $("#cveFin_id").val()!=0){
                        ' . $this->wrLlamaLlenaTabla('tbl-Folios') . '
                    }else{
                        /* notify("Selecciona una clave de financiamiento","error");
                        v = $("#cveFin_id").parent();
                        v.css("color","#900").css("border","1px solid rgba(100,0,0,.5)")
                        .css("background-color","rgba(255,0,0,.3)")
                        .effect("pulsate", {}, 300);*/
                    }
                });
                ' . ((isset($this->prm->ecf_id) && $this->prm->ecf_id != '') ?
            '$("#comboCvefin").val("' . $this->prm->ecf_id . '").change();' : '') . '
            ' : '') . '
            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault(); $("#btnRegresarIndex").click();
                e.stopPropagation();
            });
        ');
    } // Fin tablaFolios

    /* MÉTODO QUE CONTIENE LA CONSULTA QUE LLENA EL LISTADO DE FOLIOS */
    private function llenaTablaFolios()
    {
        $esUR = $this->ur || $this->ue;
        $sql = '';
        $envia = '';

        if ($esUR) {
            $sql = $this->model->getConsultaTabla('ur', $this->prm);
            $envia = 'ur';
        } else {
            if ($this->drh) {
                $sql = $this->model->getConsultaTabla('drh', $this->prm);
                $envia = 'drh';
            }
            if ($this->planeacion) {
                $sql = $this->model->getConsultaTabla('planeacion', $this->prm);
                $envia = 'planeacion';
            }
            if ($this->presupuesto) {
                $sql = $this->model->getConsultaTabla('presupuesto', $this->prm);
                $envia = 'presupuesto';
            }
        }

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Folios';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
        $metodo = (($esUR) ? 'verificarFolio' : 'revisarFolio');

        //mostrar los botones de reporte excel y pdf
        $this->mixScript('
            $("#btn_excel").show();
            $("#btn_pdf").show();
        ');

        $this->mixScript('
            $(".addModFolio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addModFolio",
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    omo_id:$(this).attr("omo_id"),
                    mod_id:$(this).attr("mod_id"),
                    enc_oop:$(this).attr("enc_oop"),
                    cff_id:$(this).attr("cff_id")
                    ' . (($this->nivelRevision != '') ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $(".addFirma").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addFirma",
                    emo_id:$(this).attr("emo_id"),
                    mmd_id:$(this).attr("mmd_id"),
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    mod_tipoMovimiento:$(this).attr("mod_tipoMovimiento"),
                });
                return false;
            });

            $(".detalleFolio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    enc_oop:$(this).attr("enc_oop"),
                    cff_id:$(this).attr("cff_id")
                    ' . (($this->nivelRevision != '') ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $(".revisarFolio").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . $metodo . '",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id")
                    ' . (($this->nivelRevision != '') ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $(".imprimir").click(function(){
                if($(this).attr("mmd_id")=="E"){
                    $("#reporte").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesPresupuestalesExternas.php");
                }else{
                    $("#reporte").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesPresupuestalesInternas.php");
                }
                $("#omo_id").val($(this).attr("omo_id"));
                $("#mod_gpo_id").val($(this).attr("mod_gpo_id"));
                $("#mod_ur_id").val($(this).attr("mod_ur_id"));
                $("#mod_id").val($(this).attr("mod_id"));
                $("#mod_tipoMovimiento").val($(this).attr("mod_tipoMovimiento"));
                $("#reporte").submit();
            });
        ');

        $this->mixScript('
            $("#btn_pdf").click(function(e){
                e.preventDefault();
                var form = $("<form>")
                    .attr("action","rep/doc/scp/adecuaciones/rep_FoliosCapturados.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_gpo_id").attr("value", "' . $this->gpo_id . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_ur_id").attr("value", ' . $this->ur_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", $("#cveFin_id").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "envia").attr("value", "' . $envia . '"))
                $("body").append(form);
                form.submit();
            });

            $("#btn_excel").click(function(e){
                e.preventDefault();
                var form = $("<form>")
                    .attr("action","rep/excel/scp/adecuaciones/exp_FoliosCapturados.php")
                    .attr("method", "post")
                    .attr("id", "enviaPost")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_gpo_id").attr("value", "' . $this->gpo_id . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_ur_id").attr("value", ' . $this->ur_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", $("#cveFin_id").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "envia").attr("value", "' . $envia . '"))
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    } // Fin llenaTablaFolios

    /*METODO PARA AGREGAR NUEVO FOLIO DE LA DEPENDENCIA*/
    private function addModFolio()
    {
        $parametros = [];
        $metas = ''; //SE VISUALIZAN LAS METAS A MODIFICAR SI EL ESTADO DEL FOLIO ES 2 PARA LA UR o 6 PARA SEFIN
        $objUr = $this->getUR($this->gpo_id, $this->ur_id);
        $esUR = ($this->ue || $this->ur || $this->drh) ? true : false;
        //Inicialización de variables
        $mensajeRechazo = $unidadResponsable = $claveFinanciamiento = '';
        //INSTANCIANDO LA ENTIDAD MAESTRO MODIFICACION
        $mod = new MaestroModificacion($this->prm);
        //INSTANCIANDO LA ENTIDAD DEL ENCUADRE ORIGEN OPERACION
        $origenOp = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        //CONSULTAR SI ESTAMOS EN MODO LECTURA
        $lectura = $this->lectura($mod, $esUR);
        //VERIFICAMOS SI ES ALTA O CAMBIO (ACTUALIZACION)
        $abc = ($mod->getMod_id() != '') ? 'c' : 'a';
        //BLOQUEAMOS LA CLASIFICACION ADMINISTRATIVA
        $script = 'bloquearClasAdmin();';
        $this->pintaScript($script);

        //BOTONES
        $btnGuardar = $this->vista->vwBotones('guardarFolio');
        $btnEliminar = $this->vista->vwBotones('eliminarFolio');
        $btnRegresar = $this->vista->vwBotones('regresar');
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"' . (
            ($this->nivelRevision == 'alta' || $this->nivelRevision == ''
                || $this->nivelRevision == 'revisionSEFIN') ? 'tablaFolios' : 'tablaFoliosRead'
        ) . '"
                ' . (isset($this->prm->ecf_id) ? ',ecf_id:"' . $this->prm->ecf_id . '"' : '') . '
                ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                ' . ($this->gpo_id != '' ? ',mod_gpo_id:' . $this->gpo_id . '' : '') . '
                ' . ($this->ur_id != '' ? ',mod_ur_id:"' . $this->ur_id . '"' : '') . '
                ' . ($this->nivelRevision != '' ? ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnRegresar = $this->ponBoton($btnRegresar);
        $btnHistorial = $this->vista->vwBotones('historial');
        $btnHistorial = $this->ponBoton($btnHistorial);
        //INPUT
        $numeroFolio = $this->vista->vwInput('numeroFolio', $mod, $esUR);
        $justificacion = $this->vista->vwInput('justificacion', $mod, $esUR);
        $fecha = $this->vista->vwInput('fecha', $mod, $esUR);
        $fecha->readonly = true;
        $oficioSolicitud = $this->vista->vwInput('oficioSolicitud', $mod, $esUR);
        $oficioAutorizacion = $this->vista->vwInput('oficioAutorizacion', $mod, $esUR);
        //COMBOS
        $modalidadModificacion = $this->vista->vwCombo('modalidadAdecuacion', $mod, $origenOp, $this->omo_id);
        $unidadEjecutora = $this->vista->vwCombo('unidadEjecutora', $mod, $origenOp, $this->omo_id);

        if ($this->ue && is_object($unidadEjecutora)) {
            if ($this->adm_grupo . $this->adm_ur == 116) {
                $this->conectar();
                $sql = '
                    SELECT u.ue_id,u.ue_nombre FROM cat_UnidadEjecutora u
                    INNER JOIN presupuesto p USING(gpo_id,ur_id,ue_id)
                    WHERE u.gpo_id = ? and u.ur_id = ? AND u.ue_id = ?
                    GROUP BY u.gpo_id,u.ur_id,u.ue_id ORDER BY u.ue_id;';
                $parametros = [
                    $this->adm_grupo,
                    $this->adm_ur,
                    $this->ue_id,
                ];
                $this->setSql($sql);
                $this->consultar($parametros);
                $this->desconectar();
                $row = $this->extraer();
                if (!$row) {
                    $this->mixScript('
                        $("#btnGuardarFolio").prop( "disabled", true );
                    ');
                }
            }
            $unidadEjecutora->selecciona = $this->ue_id;
            $unidadEjecutora->disabled = true;
        }

        switch ($abc) {
            case 'a':
                $observacion = ' capturado';
                break;
            case 'c':
                $observacion = ' actualizado';
                break;
        }

        //CONSULTA PARA VERIFICAR SI SE PUEDE ELIMINAR EL FOLIO
        $elimina = $this->model->getEliminaFolio($mod);
        //SI ES UNA UR O LA DIRECCION DE RECURSOS HUMANOS
        if ($esUR) {
            //PARAMETROS BLOQUEADOS PARA LA UR
            $numeroFolio->readonly = true;
            $btnGuardar->click = '
                var sub = (($("#Subsecretaria").length) ? $("#Subsecretaria").find("option:selected").val():1);
                var mov = (($("#mov").length) ? $("#mov").find("option:selected").val():"null");
                comunica("' . $this->pantalla . '",{
                    metodo:"abcFolios",
                    abc:"' . $abc . '",
                    omo_id:$("#tmd_id").find("option:selected").attr("omo_id"),
                    ' . ($mod->getMod_id() != '' ?
                    'mod_id:$("#folio").val(),' : '') . '
                    Subsecretaria:sub,
                    mov:mov,
                    tmd_id:$("#tmd_id").find("option:selected").attr("tmd_id"),
                    tfm_id:$("#tfm_id").find("option:selected").attr("tfm_id"),
                    mmd_id:$("#mmd_id").find("option:selected").val(),
                    concepto:$("#concepto").val(),
                    oop:$("#tfm_id").find("option:selected").attr("enc_oop")
                    ' . ($this->adm_grupo . $this->adm_ur == 116 ?
                    ',mod_ue_id:$("#mod_ue_id").val()' : '') . '
                    ' . (isset($this->prm->ecf_id) && !empty($this->prm->ecf_id) ?
                    ',ecf_id:"' . $this->prm->ecf_id . '"' : ',ecf_id:$("#cveFin_id").val()') . '
                    ' . ($mod->getMod_gpo_id() != '' ?
                    ',mod_gpo_id:"' . $mod->getMod_gpo_id() . '"' : '') . '
                    ' . ($mod->getMod_ur_id() != '' ?
                    ',mod_ur_id:"' . $mod->getMod_ur_id() . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) ?
                    ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    ' . (isset($observacion) ?
                    ',observacion:"' . $observacion . '"' : '') . '
                    ' . ($mod->getOrigen() != '' ?
                    ',mod_origen:"' . $mod->getOrigen() . '"' : ',mod_origen:$("[name=esRefrendo]:checked").val()') . '
                },true);
                return false;
            ';

            //CONSULTA SI EXISTE BLOQUEO EN FOLIO
            $bloqueoFolio = $this->model->getConsultaBloqueoFolio($mod);
            //CONSULTA MOTIVO RECHAZO
            $consultaMotivoRechazo = $this->model->getMotivoRechazo($mod, 'ur');

            $excenta = $this->model->getExcenta($mod);
            //CONSULTA SI EXISTE UN BLOQUEO DE FOLIOS
            if ($bloqueoFolio && $excenta == 2) {
                $btnGuardar = $btnEliminar = '';
            }
            //CONSULTAR SI SE PUEDEN MOSTRAR LOS BOTONES
            if ($lectura && $excenta == 2) {
                $btnGuardar = '';
            }
            //CONSULTAR SI TIENE MOVIMIENTOS
            if ($elimina['registros']) {
                $btnEliminar = '';
            }
            if ($abc == 'c') {
                //ESPACIO PARA APARTADO DE METAS DESDE UR
                $this->prm->pregunta = 1;
                if ($this->verificarFolio($this->prm)) {
                    $this->prm->lectura = $mod->getEmo_id() != 1 && $mod->getEmo_id() != 3;
                } else {
                    //CONSULTA SI REALIZO MODIFICACION DE METAS
                    $this->conectar();
                    $sql = '
                        SELECT trim(replace(bit_observacion,"\"","")) AS bit_observacion
                        FROM scp_MaestroModificacion m
                        JOIN cat_EstadoModificacion em using(emo_id)
                        JOIN ' . BD_BITACORA . '.bit_MaestroModificacion b using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                        WHERE m.omo_id= ? AND m.mod_gpo_id= ? AND m.mod_ur_id= ? AND m.mod_id= ? AND b.emo_id=?
                        ORDER BY b.usr_fecha desc, b.bit_mod';
                    $parametros = [
                        $this->prm->omo_id,
                        $this->prm->mod_gpo_id,
                        $this->prm->mod_ur_id,
                        $this->prm->mod_id,
                        2,
                    ];
                    $this->setSql($sql);
                    $this->consultar($parametros);
                    $this->desconectar();
                    $j = $this->extraer();

                    if ($j != null && $j['bit_observacion'] != '') {
                        $metas = '
                            <h5>Motivo por el cual no se realizó la modificación de metas</h5><br/>
                            <div class="alert alert-info alert-dismissable" align="left">
                                <strong>
                                    ' . $j['bit_observacion'] . '
                                </strong>
                            </div>
                        ';
                    }
                } // Fin else

                //MOTIVO DE RECHAZO
                if ($consultaMotivoRechazo != null) {
                    $mensajeRechazo = $this->vista->vwMensajeRechazo((string) $consultaMotivoRechazo['bit_observacion']);
                }
            } else {
                $btnHistorial = '';
                $numeroFolio = $fecha = '';
            }
            $oficioSolicitud = $oficioAutorizacion = '';
        } else {
            $numeroFolio->datosExtra->omo_id = $this->omo_id;
            //PARA MOSTRAR LA UNIDAD RESPONSABLE DE LA QUE SE ESTÁ CAPTURANDO EL FOLIO
            $prmUR = new stdClass();
            $prmUR->mostrar = 'ur';
            $prmUR->parametrosExtra = new stdClass();
            $prmUR->parametrosExtra->prm = $this->prm;
            $prmUR->parametrosExtra->objUR = $objUr;
            $unidadResponsable = $this->vista->vwToolsTablaFolios($prmUR);
            //PARA MOSTRAR LA CLAVE DE FINANCIAMIENTO DE LA UR QUE SE DARÁ DE ALTA EL FOLIO
            $prmCve = new stdClass();
            $prmCve->mostrar = 'cvefin';
            $prmCve->parametrosExtra = new stdClass();
            $prmCve->parametrosExtra->prm = $this->prm;
            $claveFinanciamiento = $this->vista->vwToolsTablaFolios($prmCve);
            $claveFinanciamiento->lectura = true;
            $claveFinanciamiento = ClasificacionFinanciamiento::creaComboCveFin($claveFinanciamiento);

            $btnGuardar->click = '
                var sub = (($("#Subsecretaria").length) ? $("#Subsecretaria").find("option:selected").val():1);
                var mov = (($("#mov").length) ? $("#mov").find("option:selected").val():"null");
                comunica("' . $this->pantalla . '",{
                    metodo:"abcFolios",
                    abc:"' . $abc . '",
                    omo_id:$("#tmd_id").find("option:selected").attr("omo_id"),
                    mod_id:$("#folio").val(),
                    Subsecretaria:sub,
                    mov:mov,
                    tmd_id:$("#tmd_id").find("option:selected").attr("tmd_id"),
                    tfm_id:$("#tfm_id").find("option:selected").attr("tfm_id"),
                    mmd_id:$("#mmd_id").find("option:selected").val(),
                    concepto:$("#concepto").val(),
                    oficioSolicitudSefin:$("#oficioSolicitudSefin").val(),
                    oficioSefin:$("#OficioSefin").val(),
                    oop:$("#tfm_id").find("option:selected").attr("enc_oop"),
                    nivelRevision:"' . $this->prm->nivelRevision . '"
                    ' . ($this->adm_grupo . $this->adm_ur == 116 ?
                    ',mod_ue_id:$("#mod_ue_id").val()' : '') . '
                    ' . (isset($this->prm->ecf_id) && !empty($this->prm->ecf_id) ?
                    ',ecf_id:"' . $this->prm->ecf_id . '"' : ',ecf_id:$("#cveFin_id").val()') . '
                    ' . ($mod->getMod_gpo_id() != '' ?
                    ',mod_gpo_id:"' . $mod->getMod_gpo_id() . '"' : '') . '
                    ' . ($mod->getMod_ur_id() != '' ?
                    ',mod_ur_id:"' . $mod->getMod_ur_id() . '"' : '') . '
                    ' . (isset($observacion) ?
                    ',observacion:"' . $observacion . '"' : '') . '
                    ' . ($abc == 'a' ? ',altafolio: 1' : ',altafolio: 0') . '
                    ' . ($mod->getOrigen() != '' ?
                    ',mod_origen:"' . $mod->getOrigen() . '"' : ',mod_origen:$("[name=esRefrendo]:checked").val()') . '
                },true);
                return false;';

            //SI ES PRESUPUESTO O PLANEACIÓN
            $consultaMotivoRechazo = $this->model->getMotivoRechazo($mod, 'sefin');
            $excenta = $this->model->getExcenta($mod);
            //CONSULTAR SI SE PUEDEN MOSTRAR LOS BOTONES
            if ($lectura && $excenta == 2) {
                $btnGuardar = '';
            }
            if ($elimina['registros']) {
                $btnEliminar = '';
            }
            if ($abc == 'c') {
                //ESPACIO PARA APARTADO DE METAS DESDE SEFIN
                if ($origenOp->getOmo_id() == 'A') {
                    $this->prm->pregunta = 1;
                    if ($this->verificarFolio($this->prm)) {
                        $this->prm->lectura = true;
                    } else {
                        $this->conectar();
                        $sql = '
                            SELECT trim(replace(bit_observacion,"\"","")) as bit_observacion
                            FROM scp_MaestroModificacion m
                            JOIN cat_EstadoModificacion em using(emo_id)
                            JOIN ' . BD_BITACORA . '.bit_MaestroModificacion b using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                            WHERE m.omo_id= ?
                            AND m.mod_gpo_id= ?
                            AND m.mod_ur_id= ?
                            AND m.mod_id= ? and b.emo_id= ?
                            ORDER BY b.usr_fecha desc, b.bit_mod
                            LIMIT 1;';
                        $parametros = [
                            $this->prm->omo_id,
                            $this->prm->mod_gpo_id,
                            $this->prm->mod_ur_id,
                            $this->prm->mod_id,
                            2,
                        ];
                        $this->setSql($sql);
                        $this->consultar($parametros);
                        $this->desconectar();
                        $j = $this->extraer();
                        if ($j['bit_observacion'] != '') {
                            $metas = '
                                <h5>Motivo por el cual no se realizó la modificación de metas</h5><br/>
                                <div class="alert alert-info alert-dismissable" align="left">
                                    <strong>
                                        ' . $j['bit_observacion'] . '
                                    </strong>
                                </div>
                            ';
                        }
                    }
                }
                //MOTIVO DE RECHAZO
                if ($consultaMotivoRechazo != null) {
                    $mensajeRechazo = $this->vista->vwMensajeRechazo((string)$consultaMotivoRechazo['bit_observacion']);
                }
            } else {
                $btnHistorial = '';
            }
        }

        $numeroFolio = is_object($numeroFolio) ? $this->ponInput($numeroFolio) : '';
        $fecha = is_object($fecha) ? $this->ponInput($fecha) : '';
        $justificacion = is_object($justificacion) ? $this->ponTextArea($justificacion) : '';
        $oficioSolicitud = is_object($oficioSolicitud) ? $this->ponInput($oficioSolicitud) : '';
        $oficioAutorizacion = is_object($oficioAutorizacion) ? $this->ponInput($oficioAutorizacion) : '';
        $modalidadModificacion = is_object($modalidadModificacion) ? $this->ponCombo($modalidadModificacion) : '';
        $unidadEjecutora = is_object($unidadEjecutora) ? $this->ponCombo($unidadEjecutora) : '';
        $deshabilitarRadios = ($elimina['registros']) ? ',deshabilitado:true' : ''; //deshabilitar radios de tipo modificacion y de tipo de folio.

        $radioRefrendo = new StdClass();
        $radioRefrendo->values = ['S' => 'No','R' => 'Si'];
        $radioRefrendo->class = 'esRefrendo';
        $radioRefrendo->name = 'esRefrendo';
        $radioRefrendo->selecciona = 'S';

        $formulario =
            $mensajeRechazo . '
            <div class = "row">
                ' . $unidadResponsable . '
            </div>
            <div class = "row">
                ' . $claveFinanciamiento . '
            </div>
            <form id = "Folio">
                <legend><span style = "font-size:14px;" class="badge bg-secondary">Datos Generales</span></legend>
                <div class="d-flex flex-row col-md-12 col-xs-12 mb-4">
                    <div style="display:none" id="refrendo">
                        <div class="d-flex flex-row col-md-8 col-xs-8 ms-3">
                            <legend>¿La presente adecuación presupuestal pertenece a algún refrendo?</legend>
                            <fieldset class="col-md-3 col-xs-3">
                                ' . $this->ponRadioFlex($radioRefrendo) . '
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class = "row mb-4">
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $numeroFolio . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $fecha . '
                    </fieldset>
                    <fieldset id = "filaUe" class="col-md-4 col-xs-4">
                        ' . $unidadEjecutora . '
                    </fieldset>
                </div>
                <div class="d-flex flex-row col-md-12 col-xs-12 mb-4">
                        <fieldset class="col-md-2 col-xs-2">
                            ' . $modalidadModificacion . '
                        </fieldset>
                        <div id="tmd_tfm" class="d-flex flex-row col-md-8 col-xs-8 ms-3">
                        </div>
                </div>
                <div class = "row mb-4">
                    <div class = "col-md-6 col-xs-6">
                        ' . $justificacion . '
                    </div>
                    <div class = "col-md-3 col-xs-3">
                        ' . $oficioAutorizacion . '
                    </div>
                    <fieldset class = "col-md-3 col-xs-3">
                        ' . $oficioSolicitud . '
                    </fieldset>
                </div>
            </form>
            ' . (isset($this->prm->lectura) || $metas != '' ? '
                <legend><span style = "font-size:14px;" class="badge bg-primary">Metas</span> <label style="font-size:12px; color:red;"> (Si el folio modificó metas se mostrara los detalle de las mismas, en caso contrario, la justificación)</label>
                </legend>
                <fieldset id ="metas" class="col-md-12 col-xs-12">
                    ' . $metas . '
                </fieldset>
            ' : '') . '
            ' . (
                ($esUR) ? '
                <span style = "font-size:14px;" >
                    <a id="info" style="color:red;">
                        Que tipo de folio elegir...
                    </a>
                    <br/>
                </span>' : ''
            ) . '
            ';

        $this->mixHtml($formulario);
        if (isset($this->prm->lectura)) {
            $this->modificarMetas();
        }

        $btnGuardar = is_object($btnGuardar) ? $this->ponBoton($btnGuardar) : '';
        $btnEliminar = is_object($btnEliminar) ? $this->ponBoton($btnEliminar) : '';
        $this->agregaTools($btnRegresar . ' ' . $btnGuardar . ' ' . $btnEliminar . ' ' . $btnHistorial);

        $this->mixScript('
            $("#info").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"elegirTipoFolio"
                });
            });

            if($("#tmd_tfm").length){
                comunica("' . $this->pantalla . '",{
                    metodo:"agregaTipoModificacion",
                    mmd_id: $("#mmd_id").find("option:selected").val()
                    ' . $deshabilitarRadios . '
                    ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ?
                    ',omo_id:"' . $this->prm->omo_id . '"' : " ,omo_id:'A' ") . '
                    ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ?
                    ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                    ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ?
                    ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                    ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ?
                    ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                });
            }

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault(); $("#btnRegresarTablaFolios").click(); e.stopPropagation();
            });
            $("#btnEliminarFolio").click(function(){
                alertify.confirm("ELIMINAR FOLIO","¿ Realmente desea eliminar el folio <h5 style=\"display:inline\"></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"abcFolios",
                            abc:"b",
                            omo_id:$("#tmd_id").find("option:selected").attr("omo_id"),
                            mod_id:$("#folio").val()
                            ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ?
                            ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                            ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ?
                            ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                            ' . (isset($this->prm->ecf_id) && !empty($this->prm->ecf_id) ?
                            ',ecf_id:"' . $this->prm->ecf_id . '"' : '') . '
                            ' . (isset($this->prm->nivelRevision) ?
                            ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            ,observacion: " eliminado"
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-success");
                    btnAceptar.classList.add("btn-outline-success");
                }
                    const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                if (cancela) {
                    cancela.classList.remove("btn-danger");
                    cancela.classList.add("btn-outline-danger");
                }

            });

            $("#mmd_id").change(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"agregaTipoModificacion",
                    mmd_id: $(this).find("option:selected").val()
                    ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ?
                    ',omo_id:"' . $this->prm->omo_id . '"' : " ,omo_id:'A' ") . '
                    ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ?
                    ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                    ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ?
                    ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                    ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ?
                    ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                    ' . (isset($this->prm->mod_origen) && !empty($this->prm->mod_origen) ?
                    ',mod_origen:"' . $this->prm->mod_origen . '"' : '') . '
                });
                return false;
            });

            $("#btnHistorial").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo:"tablaHistorialFolio"
                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->mod_gpo_id) ? ',mod_gpo_id:' . $this->prm->mod_gpo_id . '' : '') . '
                    ' . (isset($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                    ' . (isset($this->prm->mod_id) ? ',mod_id:' . $this->prm->mod_id . '' : '') . '
                    ' . (($origenOp->getOop() != '') ? ',enc_oop:' . $this->prm->enc_oop . '' : '') . '
                });
            });
        ');
    } // Fin addModFolio

    //METODO QUE AGREGA EL TIPO DE ADECUACION
    private function agregaTipoModificacion()
    {
        $mod = new MaestroModificacion($this->prm);
        $origenOperacion = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $this->setContenedor('tmd_tfm');
        $comboTipoAdecuacion = '';

        if ($this->ur || $this->ue || $this->drh) {
            $comboTipoAdecuacion = $this->vista->vwTipoAdecuacion('ur', $this->prm, $mod);
        }
        if ($this->presupuesto || $this->planeacion) {
            $comboTipoAdecuacion = $this->vista->vwTipoAdecuacion('sefin', $this->prm, $mod);
        }

        $comboTipoAdecuacion->change = '
            console.info($(this).val())
            if ($(this).val() =="D") {
                $("#refrendo").show();
            } else {
                $("#refrendo").hide();
            }
            switch($(this).find("option:selected").attr("mmd_id")){
                case "I":
                    comunica("' . $this->pantalla . '",{
                        metodo:"agregaTipoFolio",
                        tmd_id: $("#tmd_id").find("option:selected").val()
                        ' . (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop) ?
                        ',enc_oop:"' . $this->prm->enc_oop . '"' : '') . '
                        ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ?
                        ',omo_id:"' . $this->prm->omo_id . '"' : 'A') . '
                        ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ?
                        ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                        ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ?
                        ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                        ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ?
                        ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                        ' . (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ?
                        ',mmd_id:"' . $this->prm->mmd_id . '"' : '') . '
                        ' . (isset($this->prm->deshabilitado) && !empty($this->prm->deshabilitado) ?
                        ',deshabilitado:"' . $this->prm->deshabilitado . '"' : '') . '
                    });
                    break;
                case "E":
                    comunica("' . $this->pantalla . '",{
                        metodo:"agregaTipoFolio",
                        tmd_id: $("#tmd_id").find("option:selected").val(),
                        agregaTipo:(($("#tmd_id").find("option:selected").val() == "B") ?
                        $("#tmd_id").find("option:selected").val():0)
                        ' . (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop) ?
                        ',enc_oop:"' . $this->prm->enc_oop . '"' : '') . '
                        ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ?
                        ',omo_id:"' . $this->prm->omo_id . '"' : 'A') . '
                        ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ?
                        ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                        ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ?
                        ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                        ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ?
                        ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                        ' . (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ?
                        ',mmd_id:"' . $this->prm->mmd_id . '"' : '') . '
                        ' . (isset($this->prm->deshabilitado) && !empty($this->prm->deshabilitado) ?
                        ',deshabilitado:"' . $this->prm->deshabilitado . '"' : '') . '
                    });
                    break;
            }
            return false;
        ';

        $comboTipoAdecuacion = is_object($comboTipoAdecuacion) ? $this->ponCombo($comboTipoAdecuacion) : '';
        $this->mixHtml('
            <fieldset  class="col-md-3 col-xs-3">
                ' . $comboTipoAdecuacion .
            '</fieldset>
        ');
        //ENVIAMOS PARAMETROS NECESARIOS
        $this->prm->omo_id = ($this->omo_id != '') ? $this->omo_id : 'A';
        $this->prm->mmd_id = (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id)) ?
        $this->prm->mmd_id : 'E';
        //PINTAMOS COMBO DE TIPO FOLIO
        $this->agregaTipoFolio();

        $this->mixScript('
            ' . (($origenOperacion->getTmd_id() != '') ?
            '$("#tmd_id").change();' : '') . '
        ');
    } // Fin agregaTipoModificacion

    //METODO QUE AGREGA EL TIPO DE FOLIO, SUBSECRETARIA Y TIPO MOVIMIENTO
    private function agregaTipoFolio()
    {
        $mod = new MaestroModificacion($this->prm);
        $origenOperacion = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $comboTipoFolio = $comboSubsecretaria = $comboTipoMovimiento = $html = $htmlfolio = '';
        $mmd_id = (isset($this->prm->mmd_id) && $this->prm->mmd_id != '') ? $this->prm->mmd_id : '';

        if (isset($this->prm->tmd_id) || (isset($this->prm->agregaTipo) && $this->prm->agregaTipo == 'B')) {
            $this->setContenedor('tf');
        }
        if ($this->ur || $this->ue || $this->drh) {
            $comboTipoFolio = $this->vista->vwTipoFolio('ur', $this->prm, $mod);
            $comboSubsecretaria = $this->vista->vwSubsecretaria('ur', $this->prm, $mod);
        }
        if ($this->presupuesto || $this->planeacion) {
            $comboTipoFolio = $this->vista->vwTipoFolio('sefin', $this->prm, $mod);
            if ($this->presupuesto) {
                $comboSubsecretaria = $this->vista->vwSubsecretaria('presupuesto', $this->prm, $mod);
            }
            if ($this->planeacion) {
                $comboSubsecretaria = $this->vista->vwSubsecretaria('planeacion', $this->prm, $mod);
            }
        }
        if ($origenOperacion->getTfm_id() != '' && isset($this->prm->deshabilitado)) {
            $comboTipoFolio->disabled = true;
        }
        //PINTA COMBO TIPO MOVIMIENTO
        if (isset($this->prm->agregaTipo) && $this->prm->agregaTipo == 'B') {
            $comboTipoMovimiento = $this->vista->vwCombo('tipoMovimiento', $mod, $origenOperacion, $origenOperacion->getOmo_id());
            if ($mod->getTipoMovimiento() != '' && isset($this->prm->deshabilitado)) {
                $comboTipoMovimiento->disabled = true;
            }
            $comboTipoMovimiento = is_object($comboTipoMovimiento) ? $this->ponCombo($comboTipoMovimiento) : '';
            $html .= '
                <div class="col-md-3 col-xs-3 ms-3">
                    ' . $comboTipoMovimiento .
                '</div>
            ';
        }
        //PINTA COMBO SUBSECRETARIA
        if (!$this->drh && $mmd_id != 'I') {
            $comboSubsecretaria = is_object($comboSubsecretaria) ? $this->ponCombo($comboSubsecretaria) : '';
            $html .= '<div class="col-md-4 col-xs-4 ms-3">
                ' . $comboSubsecretaria .
                '</div>';
        }
        //PINTA COMBO TIPO FOLIO
        $comboTipoFolio = is_object($comboTipoFolio) ? $this->ponCombo($comboTipoFolio) : '';
        $htmlfolio .= '
            <fieldset class="col-md-4 col-xs-4 ms-3">
                ' . $comboTipoFolio .
            '</fieldset>
        ';
        //PINTA TODOS LOS COMBOS DE TIPO MOVIMIENTO, SUBSECRETARIA Y TIPO FOLIO
        $this->mixHtml('
            <div id="tf" class="col-md-11 col-xs-11 d-flex flex-row">
                ' . $html . '
                ' . $htmlfolio . '
            </div>
        ');
    } // Fin agregaTipoFolio

    //METODO QUE GUARDA LA INFORMACION DEL FOLIO
    private function abcFolios()
    {
        $parametros = [];
        $observacion = '';
        $abc = $this->prm->abc;

        if ($this->ur || $this->ue || $this->drh) {
            $this->prm->mod_gpo_id = $this->adm_grupo;
            $this->prm->mod_ur_id = $this->adm_ur;
            $this->prm->emo_id = 1;
        } else {
            $this->prm->mod_gpo_id = (isset($this->prm->mod_gpo_id) ? $this->prm->mod_gpo_id : $this->adm_grupo);
            $this->prm->mod_ur_id = (isset($this->prm->mod_ur_id) ? $this->prm->mod_ur_id : $this->adm_ur);
            $this->prm->emo_id = 17;
        }

        //CONSULTA INFORMACIÓN DEL FOLIO
        $mod = new MaestroModificacion($this->prm);
        $elimina = $this->model->getEliminaFolio($mod);

        switch ($abc) {
            case 'a':
                $this->prm->tipoMovimiento = ($this->prm->mov == 'null') ? '' : $this->prm->mov;
                $this->prm->fechaFolio = date('Y-m-d');
                $this->prm->concepto = str_replace(['+', '%2C', '%2F'], [' ', ',', '/'], $this->prm->concepto);
                $this->prm->origen = $this->prm->mod_origen;
                $this->prm->altafolio = true;
                $observacion = ' capturado';
                break;
            case 'b':
                $this->prm->emo_id = $mod->getEmo_id();
                $this->prm->fechaFolio = $mod->getFechaFolio();
                $this->prm->concepto = $mod->getConcepto();
                $this->prm->origen = $mod->getOrigen();
                $this->prm->clave = $mod->getClave();
                if ($elimina['registros'] == 0) {
                    $observacion = ' eliminado';
                } else {
                    $this->mixScript('notify("El folio no puede ser eliminado, existen movimientos");');
                }
                break;
            case 'c':
                $this->prm->tipoMovimiento = ($this->prm->mov == 'null') ? '' : $this->prm->mov;
                $this->prm->fechaFolio = $mod->getFechaFolio();
                $this->prm->emo_id = $mod->getEmo_id();
                $this->prm->origen = $mod->getOrigen();
                $this->prm->clave = $mod->getClave();
                $this->prm->concepto = str_replace(['+', '%2C', '%2F'], [' ', ',', '/'], $this->prm->concepto);
                $observacion = ' actualizado';
                break;
        } // Fin switch

        if ($observacion != '') {
            if ($abc == 'b') {
                //Elimina las firmas
                $sql = 'DELETE FROM ' . BD_CTRL . '.scp_ModificacionFirma
                    WHERE omo_id= ?
                    AND mod_gpo_id= ? AND mod_ur_id = ?
                    AND mod_id = ?;';
                $parametros = [
                    $mod->getOmo_id(),
                    $mod->getMod_gpo_id(),
                    $mod->getMod_ur_id(),
                    $mod->getMod_id(),
                ];
                $elimina = new AccesoDatos();
                $elimina->conectar();
                $elimina->setSql($sql);
                $elimina->consultar($parametros);
                $elimina->desconectar();
            }

            $this->prm->mod_id = MaestroModificacion::abc($this->prm);

            if ($mod->getMod_id() != '') {
                $this->prm->mod_id = $mod->getMod_id();
            }
            if ($abc == 'a') {
                $mod = new MaestroModificacion($this->prm);
                $this->prm->origen = $mod->getOrigen();
                $this->prm->clave = $mod->getClave();
            }

            $observacion = 'Folio ' . $this->prm->mod_id . $observacion;
            $this->prm->observacion = $observacion;
            $this->prm->ip = ($this->ip != false && $this->ip != '::1') ?
            AccesoDatos::getIP() : '127.0.0.1';
            $this->bitacora('M', $this->prm);
            $this->mixScript('notify("' . $observacion . '","success");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios"
                    ' . (isset($this->prm->omo_id) ?
                    ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->ecf_id) ?
                    ',ecf_id:"' . $this->prm->ecf_id . '"' : '') . '
                    ' . (isset($this->prm->mod_gpo_id) ?
                    ',mod_gpo_id:' . $this->prm->mod_gpo_id . '' : '') . '
                    ' . (isset($this->prm->mod_ur_id) ?
                    ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ');
        }
    } // Fin abcFolios

    //METODO PARA MOSTRAR LAS MODIFICACIONES O MOVIMIENTOS AGREGADOS AL FOLIO POR LA UR
    private function tablaModificaciones()
    {
        $script = 'bloquearClasAdmin();';
        //INICIALIZACIÓN DE VARIABLES DE LOS BOTONES
        $btnNuevoMovimiento = $btnVobo = $btnEliminarMovimientos = $btnExcel = $btnPdf = '';
        $btnRegresar = $btnValidar = $btnRechazar = $btnCargaMasiva = '';
        $this->pintaScript($script);
        $esUR = $this->ur || $this->ue || $this->drh;
        //INSTANCIA DE LAS ENTIDADES
        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        //CONSULTAMOS SI ES UN FOLIO DE RECURSO ESTATAL PNE (TRASPASO INTERINSTITUCIONAL CON MOVIMIENTO DE AMPLIACIÓN O REDUCCIÓN
        $recursoPNE = $this->model->getConsultaFolioPNE($mod);
        //VERIFICAMOS SI ESTAMOS EN MODO LECTURA
        $lectura = $this->lectura($mod, $esUR);
        //VERIFICAMOS LA EXISTENCIA DE UN BLOQUEO DE FOLIO
        $bloqueo = $this->model->getConsultaBloqueoFolio($mod) && $esUR;
        //CONSULTAMOS SI EL FOLIO TIENE MOVIMIENTOS
        $tieneMovimientos = $this->model->getEliminaFolio($mod);
        $excenta = $this->model->getExcenta($mod);
        //BOTONES
        $btnNuevoMovimiento = $this->vista->vwBotones('nuevoMovimiento');
        $btnNuevoMovimiento->value = 'Nuevo';
        $btnNuevoMovimiento->click = '
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            comunica("' . $this->pantalla . '",{
                metodo:"addModModificacion",
                omo_id:"' . $mod->getOmo_id() . '",
                mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                mod_id:' . $mod->getMod_id() . '
                ' . (isset($this->prm->cff_id) ?
                ',cff_id:"' . $this->prm->cff_id . '"' : '') . '
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnNuevoMovimiento = ($lectura && $excenta['excenta'] == 2) ? '' : $btnNuevoMovimiento;
        $btnNuevoMovimiento = ($bloqueo && $excenta['excenta'] == 2) ? '' : $btnNuevoMovimiento;

        $btnVobo = $this->vista->vwBotones('VoBo');
        $btnVobo->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"validacionFolio",
                omo_id:"' . $mod->getOmo_id() . '",
                mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                mod_id:' . $mod->getMod_id() . ',
                recPNE:true,
                vobo:' . $mod->getVobo() . '
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnVobo = is_object($btnVobo) ? $this->ponBoton($btnVobo) : '';
        $btnPdf = $this->vista->vwBotones('exportaPdf');
        $btnExcel = $this->vista->vwBotones('exportaExcel');
        $btnEliminarMovimientos = $this->vista->vwBotones('eliminarMovimientos');
        $btnEliminarMovimientos->click = '
            alertify.confirm("ELIMINAR MOVIMIENTOS","¿ Realmente desea eliminar todos los movimientos del folio ? ¡¡¡Una vez realizada esta acción no se puede deshacer!!!<h5 style=\"display:inline\"></h5>",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"eliminarMovimientos",
                        omo_id:"' . $mod->getOmo_id() . '",
                        mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                        mod_ur_id:"' . $mod->getMod_ur_id() . '",
                        mod_id:' . $mod->getMod_id() . '
                        ' . ($this->nivelRevision != '' ?
                        ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    });
                },
                function(){}
            ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
            if (btnAceptar) {
                btnAceptar.classList.remove("btn-success");
                btnAceptar.classList.add("btn-outline-success");
            }
                const cancela = document.querySelector(".ajs-button.btn.btn-danger");
            if (cancela) {
                cancela.classList.remove("btn-danger");
                cancela.classList.add("btn-outline-danger");
            }
        ';
        $btnEliminarMovimientos = $tieneMovimientos['registros'] ? $btnEliminarMovimientos : '';
        $btnPdf = $tieneMovimientos['registros'] ? $btnPdf : '';
        $btnExcel = $tieneMovimientos['registros'] ? $btnExcel : '';
        $btnEliminarMovimientos = is_object($btnEliminarMovimientos) ? $this->ponBoton($btnEliminarMovimientos) : '';
        $btnPdf = is_object($btnPdf) ? $this->ponBoton($btnPdf) : '';
        $btnExcel = is_object($btnExcel) ? $this->ponBoton($btnExcel) : '';

        if ($this->ur || $this->ue) {
            switch ($mod->getEmo_id()) {
                case 1:
                case 3: // en estado 1 y 3 capturado ó rechazado por ur
                    $btnEliminarMovimientos = $btnEliminarMovimientos;
                    break;
                default:
                    $btnEliminarMovimientos = '';
                    break;
            }
        }

        $btnRegresar = $this->vista->vwBotones('regresar');
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"tablaFolios",
                omo_id:"' . $mod->getOmo_id() . '",
                mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                ecf_id:"' . $mod->getEcf_id() . '"
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';

        $btnValidar = $this->vista->vwBotones('validar');
        $btnValidar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"validacionFolio",
                omo_id:"' . $mod->getOmo_id() . '",
                mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                mod_id:' . $mod->getMod_id() . ',
                valPNE:' . ($recursoPNE ?
                $recursoPNE : 0) . '
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnValidar = is_object($btnValidar) ? $this->ponBoton($btnValidar) : '';
        $btnRechazar = $this->vista->vwBotones('rechazar');
        $btnRechazar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"addMensajeRechazo",
                omo_id:"' . $mod->getOmo_id() . '",
                mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                mod_id:' . $mod->getMod_id() . ',
                pne:' . ($recursoPNE ? $recursoPNE : 0) . '
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnRechazar = is_object($btnRechazar) ? $this->ponBoton($btnRechazar) : '';
        $btnCargaMasiva = $this->vista->vwBotones('cargaMasiva');
        // se cambio el llado para 2018 (separación de la carga masiva)
        $btnCargaMasiva->click = '
            comunica("scp/cargaAdecuaciones/Controlador_CargaMasiva.php",{
                omo_id:"' . $mod->getOmo_id() . '",
                mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                mod_id:"' . $mod->getMod_id() . '"
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
        ';
        $btnCargaMasiva = $lectura || !$this->prmCargaMasiva && $excenta['excenta'] == 2 ? '' : $btnCargaMasiva;
        $btnCargaMasiva = $esUR && $bloqueo && $excenta['excenta'] == 2 ? '' : $btnCargaMasiva;
        $btnCargaMasiva = is_object($btnCargaMasiva) ? $this->ponBoton($btnCargaMasiva) : '';

        if (!$esUR) {
            //EVALUAMOS EL NIVEL DE REVISION DE SEFIN
            switch ($this->nivelRevision) {
                case 'alta':
                    $btnVobo = $btnValidar = $btnRechazar = '';
                    $btnNuevoMovimiento->click = '
                        $("div[aria-describedby=cargaDatosProgFin]").html("");
                        comunica("' . $this->pantalla . '",{
                            metodo:"addModPrevision",
                            omo_id:"' . $mod->getOmo_id() . '",
                            mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                            mod_ur_id:"' . $mod->getMod_ur_id() . '",
                            mod_id:' . $mod->getMod_id() . '
                            ' . (isset($this->prm->cff_id) ?
                            ',cff_id:"' . $this->prm->cff_id . '"' : '') . '
                            ' . ($this->nivelRevision != '' ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        });
                        return false;
                    ';
                    $btnNuevoMovimiento = ($this->presupuesto && $mod->getSubsecretaria() == 1)
                    || ($this->planeacion && $mod->getSubsecretaria() == 2) ? $btnNuevoMovimiento : '';

                    $btnCargaMasiva = ($this->presupuesto && $mod->getSubsecretaria() == 1)
                    || ($this->planeacion && $mod->getSubsecretaria() == 2) ? $btnCargaMasiva : '';
                    break;
                case 'revisionSEFIN':
                    $btnCargaMasiva = $btnEliminarMovimientos = '';
                    $btnNuevoMovimiento->value = 'Nueva Previsión';
                    $btnNuevoMovimiento->attr = new stdClass();
                    $btnNuevoMovimiento->attr->prev = true;
                    $btnNuevoMovimiento->click = '
                        $("div[aria-describedby=cargaDatosProgFin]").html("");
                        comunica("' . $this->pantalla . '",{
                            metodo:"addModPrevision",
                            omo_id:"' . $mod->getOmo_id() . '",
                            mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                            mod_ur_id:"' . $mod->getMod_ur_id() . '",
                            mod_id:' . $mod->getMod_id() . ',
                            prevision:true,
                            prev:$(this).attr("prev")
                            ' . ($this->nivelRevision != '' ? ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        });
                        return false;
                    ';
                    //SI NO ES UNA RECALENDARIZACION O UNA TRASPASO INTRAINSTITUCIONAL NO LLEVAN PREVISIÓN
                    $btnNuevoMovimiento = !($enc_oop->getTmd_id() == 'A' || $enc_oop->getTmd_id() == 'C') ? $btnNuevoMovimiento : '';

                    if ($this->planeacion) {
                        //SI ES UN ANALISTA
                        if ($this->nvj == 3 && $mod->getVobo() == 3) {
                            $btnVobo = ($this->nvj == 3 && $mod->getVobo() == 3) ? $btnVobo : '';
                        } elseif ($this->nvj == 4 && $mod->getVobo() == 1) {
                            //SI ES UN JEFE DE DEPARTAMENTO
                            $btnVobo = ($this->nvj == 4 && $mod->getVobo() == 1) ? $btnVobo : '';
                        }
                        //SI ES UN FOLIO DE RECURSO ESTATAL PNE (TRASPASO INTERINSTITUCIONAL CON MOVIMIENTO DE AMPLIACIÓN O REDUCCIÓN
                        $btnVobo = $recursoPNE ? $btnVobo : '';
                        $btnValidar = $recursoPNE ? '' : $btnValidar;
                        $btnRechazar = ($mod->getOmo_id() != 'B' && $mod->getOmo_id() != 'E' &&
                        $mod->getOmo_id() != 'F') ? $btnRechazar : '';

                    } elseif ($this->presupuesto) {
                        $btnVobo = '';
                        /*SI NO ES UN FOLIO DE RECURSO ESTATAL PNE (TRASPASO INTERINSTITUCIONAL CON MOVIMIENTO DE AMPLIACIÓN O REDUCCIÓN,
                        O NO ES UN FOLIO DE LA SUBSECRETARIA DE PRESUPUESTO
                         */
                        $btnNuevoMovimiento = !($recursoPNE || $mod->getSubsecretaria() == 1) ?
                        '' : $btnNuevoMovimiento;
                        $btnValidar = !($recursoPNE || $mod->getSubsecretaria() == 1) ?
                        '' : $btnValidar;
                        $btnRechazar = ($mod->getOmo_id() != 'B') ? $btnRechazar : '';
                    }
                    break;
                default:
                    $btnValidar = $btnRechazar = $btnEliminarMovimientos = $btnVobo = '';
                    $btnCargaMasiva = '';
                    $btnRegresar->click = '
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFoliosRead",
                            omo_id:"' . $mod->getOmo_id() . '",
                            mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                            mod_ur_id:"' . $mod->getMod_ur_id() . '",
                            ecf_id:"' . $mod->getEcf_id() . '"
                            ' . ($this->nivelRevision != '' ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        });
                        return false;
                    ';
                    break;
            } // Fin switch
        } else {
            switch ($this->nivelRevision) {
                case 'validacionUR':
                case 'autorizacionUR':
                    $btnRegresar->click = '
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFoliosRead",
                            ecf_id:"' . $mod->getEcf_id() . '",
                            mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                            mod_ur_id:"' . $mod->getMod_ur_id() . '",
                            omo_id:"' . $mod->getOmo_id() . '"
                            ' . ($this->nivelRevision != '' ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        });
                        return false;
                    ';
                    $btnEliminarMovimientos = '';
                    break;
                default:
                    break;
            } // Fin switch
            //ES UNIDAD RESPONSABLE O DIRECCIÓN DE RECURSOS HUMANOS
            $btnValidar = $btnRechazar = $btnVobo = '';
        }

        $btnRegresar = is_object($btnRegresar) ? $this->ponBoton($btnRegresar) : '';

        $btnNuevoMovimiento = is_object($btnNuevoMovimiento) ? $this->ponBoton($btnNuevoMovimiento) : '';
        //MOSTRAMOS LA INFORMACIÓN DEL FOLIO
        $html = $this->vista->vwInformacionFolio($mod);
        //DEFINICIÓN DE LA TABLA DE MOVIMIENTOS DEL FOLIO
        $pantalla = $this->pantalla;
        $tabla = $this->vista->vwTablaMovimientos($mod, $this->nivelRevision, $esUR, $this->planeacion, $this->presupuesto, $pantalla);
        $tabla->tools = $html;
        $tabla->pie = $this->muestraTotalFolio($mod, $enc_oop);
        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');
        $this->llenaTablaModificaciones($mod);
        //SE AGREGAN LOS BOTONES
        $this->agregaTools($btnRegresar . ' ' . $btnNuevoMovimiento . ' ' . $btnValidar . ' ' . $btnRechazar . ' ' . $btnVobo . ' ' . $btnEliminarMovimientos . ' ' . $btnCargaMasiva . ' ' . $btnPdf . ' ' . $btnExcel);
        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaFolios").click(); e.stopPropagation(); });
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });
            // Nombres de campo necesarios para la pantalla de selección de encuadres
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";
        ');
    } // Fin tablaModificaciones

    /*METODO QUE LLENA LA TABLA DE LOS MOVIMIENTOS DEL FOLIO*/
    private function llenaTablaModificaciones($mod = '')
    {
        $sql = '';
        $envia = '';
        $esUR = $this->ur || $this->ue;
        $esSEFIN = $this->presupuesto || $this->planeacion;

        if ($mod == '') {
            $mod = new MaestroModificacion($this->prm);
        }
        if ($esUR) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('ur', $mod);
            $envia = 'ur';
        }
        if ($this->drh) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('drh', $mod);
            $envia = 'drh';
        }
        if ($esSEFIN) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('sefin', $mod);
            $envia = 'sefin';
        }

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Modificaciones';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".addModModificacion").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($esUR ?
                    'addModModificacion' : 'addModPrevision') . '",
                    omo_id:$(this).attr("omo_id"),
                    mod_id:$(this).attr("mod_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    gpo_id:$(this).attr("gpo_id"),
                    ur_id:$(this).attr("ur_id"),
                    ue_id:$(this).attr("ue_id"),
                    epg_id:$(this).attr("epg_id"),
                    pg_id:$(this).attr("pg_id"),
                    pe_id:$(this).attr("pe_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    tcp_id:$(this).attr("tcp_id"),
                    prev:$(this).attr("prev")
                    ' . ($this->nivelRevision == 'revisionSEFIN' ?
                    ',prevision:true' : '') . '
                    ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });
        ');

        $this->mixScript('
            $("#btn_pdf").click(function(e){
                e.preventDefault();
                var form1 = $("<form>")
                    .attr("action","rep/doc/scp/adecuaciones/rep_MovtoFolios.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "omo_id").attr("value", "' . $mod->getOmo_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_gpo_id").attr("value", "' . $mod->getMod_gpo_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_ur_id").attr("value", "' . $mod->getMod_ur_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_id").attr("value", "' . $mod->getMod_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "enc_oop").attr("value", "' . $mod->getOop() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "envia").attr("value", "' . $envia . '"))
                $("body").append(form1);
                form1.submit();
            });

            $("#btn_excel").click(function(e){
                e.preventDefault();
                console.log("' . $mod->getOop() . '");
                var form1 = $("<form>")
                    .attr("action","rep/excel/scp/adecuaciones/exp_MovtoFolios.php")
                    .attr("method", "post")
                    .append($("<input>").attr("type", "hidden").attr("name", "omo_id").attr("value", "' . $mod->getOmo_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_gpo_id").attr("value", "' . $mod->getMod_gpo_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_ur_id").attr("value", "' . $mod->getMod_ur_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "mod_id").attr("value", "' . $mod->getMod_id() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "enc_oop").attr("value", "' . $mod->getOop() . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "envia").attr("value", "' . $envia . '"))
                $("body").append(form1);
                form1.submit();
            });
        ');
    } // Fin llenaTablaModificaciones

    // METODO PARA AGREGAR MODIFICACIONES O MOVIMIENTOS AL FOLIO POR LA UR.
    private function addModModificacion()
    {
        //INICIALIZACIÓN DE VARIABLES
        $valorEpg = $nombreEpg = $html = $script = $encLectura = '';
        $mod_ue_id = $script = $html = $tipo = '';
        $esUR = $this->ur || $this->ue || $this->drh;

        //INSTANCIA DE ENTIDADES
        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $modClave = new DetalleModificacion($this->prm);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);
        $excenta = $this->model->getExcenta($mod);
        //VERIFICAMOS SI ES ALTA O MODIFICACIÓN
        $abc = $modClave->getOmo_id() != '' ? 'c' : 'a';
        //VERIFICAMOS SI ES MODO LECTURA
        $lectura = $this->lectura($mod, $esUR);
        //CONSULTA SI EXISTE UN BLOQUEO
        $bloqueo = $this->model->getConsultaBloqueoFolio($mod);
        //CONSULTA SI SE PUEDE ELIMINAR EL MOVIMIENTO
        $eliminar = $abc == 'c' && !$bloqueo && !$lectura;
        // BOTONES
        $btnGuardar = $this->vista->vwBotones('guardarModificacion', $mod);
        $btnGuardar = ($lectura || $bloqueo) && $excenta['excenta'] == 2 ? '' : $btnGuardar;
        $btnGuardar = is_object($btnGuardar) ? $this->ponBoton($btnGuardar) : '';
        $btnEliminar = $this->vista->vwBotones('eliminarMovimiento', $mod);
        //se modifica el confirm por bootbox.dialog ejercicio 2020
        $btnEliminar->click = '
            alertify.confirm("ELIMINAR MOVIMIENTO","¿ Realmente desea eliminar el movimiento <h5 style=\"display:inline\"></h5> ?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcModificacion",
                        abc: "b",
                        omo_id:"' . $mod->getOmo_id() . '",
                        mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                        mod_ur_id:"' . $mod->getMod_ur_id() . '",
                        mod_id:' . $mod->getMod_id() . '
                        ' . (($modClave->getGpo_id() != '') ? ',gpo_id:"' . $modClave->getGpo_id() . '"' : '') . '
                        ' . (($modClave->getUr_id() != '') ? ',ur_id:"' . $modClave->getUr_id() . '"' : '') . '
                        ' . (($modClave->getUe_id() != '') ? ',ue_id:"' . $modClave->getUe_id() . '"' : '') . '
                        ' . (($modClave->getEpg_id() != '') ? ',epg_id:"' . $modClave->getEpg_id() . '"' : '') . '
                        ' . (($modClave->getPg_id() != '') ? ',pg_id:' . $modClave->getPg_id() . '' : '') . '
                        ' . (($modClave->getPe_id() != '') ? ',pe_id:"' . $modClave->getPe_id() . '"' : '') . '
                        ' . (($modClave->getEcf_id() != '') ? ',ecf_id:"' . $modClave->getEcf_id() . '"' : '') . '
                        ' . ((isset($this->prm->tcp_id)) ? ',tcp_id:"' . $this->prm->tcp_id . '"' : '') . '
                        ' . (($modClave->getMov_id() != '') ? ',mov_id:"' . $modClave->getMov_id() . '"' : '') . '
                        ' . (($this->nivelRevision != '') ? ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    });
                },
                function(){}
            ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
            if (btnAceptar) {
                btnAceptar.classList.remove("btn-success");
                btnAceptar.classList.add("btn-outline-success");
            }
                const cancela = document.querySelector(".ajs-button.btn.btn-danger");
            if (cancela) {
                cancela.classList.remove("btn-danger");
                cancela.classList.add("btn-outline-danger");
            }
        ';
        $btnEliminar = !$eliminar ? '' : $btnEliminar;
        $btnEliminar = is_object($btnEliminar) ? $this->ponBoton($btnEliminar) : '';
        $btnRegresar = $this->vista->vwBotones('regresar', $mod);
        $btnRegresar->click = '
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            event.stopPropagation();
            comunica("' . $this->pantalla . '",{
                metodo:"tablaModificaciones",
                ecf_id:"' . $mod->getEcf_id() . '",
                mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                omo_id:"' . $mod->getOmo_id() . '",
                mod_id:' . $mod->getMod_id() . ',
                enc_oop:"' . $mod->getOop() . '"
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnRegresar = is_object($btnRegresar) ? $this->ponBoton($btnRegresar) : '';
        //VARIABLES
        //CLASIFICACION ADMINISTRATIVA
        $clasAdmin = new stdClass();
        //TIPO DE COMPRA
        $tipoCompra = new stdClass();
        //OBJETO DEL GASTO
        $objGasto = new stdClass();
        //CLAVE DE FINANCIAMIENTO
        $cveFin = new stdClass();
        //PARAMETROS DE PRESUPUESTO
        $infPre = new stdClass();
        $infPre->gpo_id = $mod->getMod_gpo_id();
        $infPre->ur_id = $mod->getMod_ur_id();
        $infPre->ue_id = $modClave->getUe_id();
        $infPre->epg_id = $modClave->getEpg_id();
        $infPre->pg_id = $modClave->getPg_id();
        $infPre->pe_id = $modClave->getPe_id();
        $infPre->ecf_id = $modClave->getEcf_id();

        if ((($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A')
            || $enc_oop->getTmd_id() == 'D')
            && !UnidadResponsable::excentaComprasConsolidadas($infPre)) {
            $infPre->tcp_id = '';
        } else {
            $infPre->tcp_id = $modClave->getTcp_id();
        }
        //INSTANCIAMOS PRESUPUESTO
        $pre = new Presupuesto($infPre);

        $folio = new stdClass();
        $folio->id = 'folio';
        $folio->formato = 'entero';
        $folio->enDiv = true;
        $folio->value = (int) $mod->getMod_id();
        $folio->required = true;
        $folio->disabled = true;

        $modalidad = new stdClass();
        $modalidad->id = 'modalidadM';
        $modalidad->enDiv = true;
        $modalidad->value = ($enc_oop->getMmd_id() == 'E' ? 'EXTERNAS' : 'INTERNAS');
        $modalidad->required = true;
        $modalidad->disabled = true;

        $tipoModificacion = new stdClass();
        $tipoModificacion->id = 'tmd_id';
        $tipoModificacion->enDiv = true;
        $tipoModificacion->value = $tipoMod->getNombre();
        $tipoModificacion->required = true;
        $tipoModificacion->disabled = true;

        $objMov = new stdClass();
        $objMov->omo_id = $mod->getOmo_id();
        $objMov->mod_gpo_id = $mod->getMod_gpo_id();
        $objMov->mod_ur_id = $mod->getMod_ur_id();
        $objMov->mod_id = $mod->getMod_id();
        $objMov->mov_id = $modClave->getMov_id();
        $objMov->tcp_id = $modClave->getTcp_id();

        //INPUTS
        $inputImporteDistribuir = $this->vista->vwInput('importeaDistribuir', $mod, $esUR);
        $inputImporteDistribuir->value = $modClave->getImporte();

        //SI ES UN CALENDARIO DESHABILITAMOS EL INPUT DEL IMPORTE A DISTRIBUIR, YA QUE TOMA EL VIGENTE ANUAL DE LA CLAVE
        if ($enc_oop->getTmd_id() == 'C') {
            $inputImporteDistribuir->value = $pre->getVigente();
            $inputImporteDistribuir->disabled = true;
        }

        $inputImporteDistribuir = (is_object($inputImporteDistribuir) ? $this->ponInput($inputImporteDistribuir) : '');
        $inputImportexDistribuir = $this->vista->vwInput('importePorDistribuir', $mod, $esUR);
        $inputImportexDistribuir = (is_object($inputImportexDistribuir) ? $this->ponInput($inputImportexDistribuir) : '');

        $form = new stdClass();
        $form->id = 'Modificaciones';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcModificacion';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->mod_gpo_id = $mod->getMod_gpo_id();
        $form->datosExtra->mod_ur_id = $mod->getMod_ur_id();
        $form->datosExtra->omo_id = $mod->getOmo_id();
        $form->datosExtra->mod_id = $mod->getMod_id();
        $form->datosExtra->ecf_id = $mod->getEcf_id();
        $form->datosExtra->meses = '"{ valueMeses();}"';
        $form->datosExtra->importe = '"{ $("#importe").val();}"';
        if (!empty($this->nivelRevision)) {
            $form->datosExtra->nivelRevision = $this->nivelRevision;
        }

        //SI ES UNIDAD RESPONSABLE O LA DIRECCIÓN DE RECURSOS HUMANOS
        if ($esUR) {
            switch ($enc_oop->getTmd_id()) {
                case 'B':
                    if ($mod->getTipoMovimiento() == 'A') {
                        $modClave->setTcp_id('');
                    }
                    break;
                case 'D':
                    $modClave->setTcp_id('');
                    break;
                default:
                    break;
            }

            $clasAdmin->gpo_id = $mod->getMod_gpo_id();
            $clasAdmin->ur_id = $mod->getMod_ur_id();

            if ($this->gpo . $this->ur_id == 116) {
                $clasAdmin->ue_id = $mod->getMod_ue_id();
                $mod_ue_id = $mod->getMod_ue_id();
            } else {
                $clasAdmin->ue_id = $modClave->getUe_id();
            }

            $clasAdmin->ecf_id = $mod->getEcf_id();
            $clasAdmin->form = 'Modificaciones';
            $clasAdmin->toa_id = $mod->getSubsecretaria();
            $clasAdmin->filtro = 'porUrClaveFinanciamiento';

            $objGasto->filtro = 'modificaciones';
            $objGasto->tmd_id = $enc_oop->getTmd_id();
            $objGasto->tfm_id = $enc_oop->getTfm_id();
            $objGasto->mmd_id = $enc_oop->getMmd_id();
            $objGasto->ecf_id = $mod->getEcf_id();
            $objGasto->omo_id = $mod->getOmo_id();
            $objGasto->tipoMovimiento = $mod->getTipoMovimiento();
            if ($this->drh) {
                $objGasto->tcp_id = 'D';
            }

            $cveFin->gpo_id = $this->adm_grupo;
            $cveFin->ur_id = $this->adm_ur;
            $cveFin->ecf_id = $mod->getEcf_id();
            $cveFin->filtro = 'presupuesto';
            $cveFin->lectura = true;
            $cveFin->controlador = 'modificaciones';

            switch ($abc) {
                case 'a':
                    $clasAdmin->change = '
                        comunica("srv/ObjetoGasto.php",{
                            metodoObjGto:"cargaPg",
                            filtro:"modificaciones",
                            ' . (($this->drh) ? 'tcp_id:"D",' : '') . '
                            tmd_id:"' . $enc_oop->getTmd_id() . '",
                            tfm_id:"' . $enc_oop->getTfm_id() . '",
                            mmd_id:"' . $enc_oop->getMmd_id() . '",
                            tipoMovimiento:"' . $mod->getTipoMovimiento() . '",
                            omo_id:"' . $mod->getOmo_id() . '",
                            ecf_id:"' . $mod->getEcf_id() . '"
                        });
                    ';
                    $tcp = ($this->drh) ? ['tcp_id' => 'D'] : [];
                    $parametros = [
                        'metodo'         => 'cargaPg',
                        'pantalla'       => $this->pantalla,
                        'filtro'         => 'porUrClaveFinanciamiento',
                        'omo_id'         => $this->prm->omo_id,
                        'mod_gpo_id'     => $this->prm->mod_gpo_id,
                        'mod_ur_id'      => $this->prm->mod_ur_id,
                        'mod_id'         => $this->prm->mod_id,
                        'ecf_id'         => $mod->getEcf_id(),
                        'mod_ue_id'      => $mod_ue_id,
                        'ssc_id'         => $mod->getSubsecretaria(),
                        'abc'            => 'a',
                        'toa_id'         => $mod->getSubsecretaria(),
                        'tmd_id'         => $enc_oop->getTmd_id(),
                        'tfm_id'         => $enc_oop->getTfm_id(),
                        'mmd_id'         => $enc_oop->getMmd_id(),
                        'tipoMovimiento' => $mod->getTipoMovimiento(),
                        'nivelRevision'  => $this->nivelRevision,
                        $tcp,
                    ];

                    //$html .= DibujarPantallaBuscarProgramaFinanciamientoHTML();
                    $html .= dameProgramaFinanciamiento2::ponModalEncuadre($parametros);
                    //$script = DibujarPantallaBuscarProgramaFinanciamientoJS($filtro = 'porUrClaveFinanciamiento', $mod->getEcf_id(), $mod_ue_id, $mod->getSubsecretaria());
                    $encLectura = '';
                    $tipo = $enc_oop->getTmd_id() . $mod->getTipoMovimiento();
                    switch ($tipo) {
                        case 'BA':
                        case 'D':
                        case 'DN':
                            $objGasto->changePe = '
                                comunica("' . $this->pantalla . '",{
                                    metodo:"muestraPresupuesto",
                                    tmd_id:"' . $enc_oop->getTmd_id() . '",
                                    tipoMovimiento:"' . $mod->getTipoMovimiento() . '",
                                    pg_id:$("#pg_id").val(),
                                    pe_id:$("#pe_id").val(),
                                    ecf_id:"' . $mod->getEcf_id() . '"
                                });
                            ';
                            break;
                        case 'BR':
                        default:
                            $objGasto->changePe = '
                                if($("#pg_id").val()!="" && $("#pe_id").val()!=""){
                                    comunica("srv/TipoCompra.php",{
                                        metodoTipoCompra:"cargaTipoCompra",
                                        omo_id:"' . $enc_oop->getOmo_id() . '",
                                        mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                                        mod_ur_id:"' . $mod->getMod_ur_id() . '",
                                        mod_id:' . $mod->getMod_id() . ',
                                        tmd_id:"' . $enc_oop->getTmd_id() . '",
                                        pg_id:$("#pg_id").val(),
                                        pe_id:$("#pe_id").val(),
                                        ecf_id:"' . $mod->getEcf_id() . '"
                                        ' . (($this->drh) ?
                                        ',tcp_id2:"D"' : '') . '
                                        ' . (($mod->getTipoMovimiento != '') ?
                                ',tipoMovimiento:"' . $mod->getTipoMovimiento() . '"' : '') . '
                                    });
                                }
                            ';
                            break;
                    }
                    break;
                case 'c':
                    $this->conectar();
                    $this->setSql('
                        SELECT prg_id,spr_id,py_id,oa_id
                        FROM enc_Programatico where epg_id = ?;
                    ');
                    $this->consultar([$modClave->getEpg_id()]);
                    $this->desconectar();
                    $epg = $this->extraer();
                    $clasAdmin->prg_id = $epg['prg_id'];
                    $clasAdmin->spr_id = $epg['spr_id'];
                    $clasAdmin->py_id = $epg['py_id'];
                    $clasAdmin->oa_id = $epg['oa_id'];
                    $clasAdmin->lectura = true;

                    // PONEMOS LA CLASIFICACIÓN ADMINISTRATIVA REGISTRADA
                    $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($modClave->getEpg_id());
                    $valorEpg = $mod->getMod_gpo_id() . $mod->getMod_ur_id() . '.' . $modClave->getUe_id() . '.' . $this->adm_prg . '.' . $this->adm_spr . '.' . $this->adm_py . '.' . $this->adm_oa;
                    $encLectura = ' disabled ';
                    $objGasto->pg_id = $modClave->getPg_id();
                    $objGasto->pe_id = $modClave->getPe_id();
                    $objGasto->lectura = true;
                    $tipoCompra->tcp_id = $modClave->getTcp_id();
                    $form->datosExtra->tcp_id = $modClave->getTcp_id();
                    //BLOQUEAMOS LA CLASIFICACION ADMINISTRATIVA PARA NO PERMITIR CAMBIOS
                    $script .= 'bloquearClasAdmin();';
                    $this->prm->tmd_id = $enc_oop->getTmd_id();
                    //se crea el template al cargar la página
                    $this->mixScript('
                        $.template( "tmpl-tbody-tabla-tablaPresupuesto",
                        "<tr class=\'odd\'><td class=\'sorting_1\'>${mes}</td>' .
                        '<td id=\'${mes}_disp\' nmes = \'${nmes}\' class=\'sorting_1 moneda  vigente ${mes}\' mesNombre=\'${mes}\' >${vigente}</td>' .
                        '<td id=\'${mes}_dev\' class=\'sorting_1 moneda devengado ${mes}\'>${devengado}</td>' .
                        '<td id=\'${mes}_dispo\' class=\'sorting_1 moneda disponible ${mes}\'>${disponible}</td>' .
                        '<td class=\'sorting_1\'>' .
                        '<input value=\'${modificacion}\' type=\'text\' style=\'width:100px;\' ' .
                        'class=\'modificacion mes moneda validaModificacion \' onBlur = \'validaModificacion()\' ' .
                        'id=\'modificacion-${mes}\' name=\'meses\' mes=\'${mes}\' data-prompt-position=\'topRight:0,-10\' >' .
                        '</td></tr>");
                ');

                    //ENVIAMOS A LA FUNCION MUESTRA PRESUPUESTO
                    $this->muestraPresupuesto(['presupuesto' => $pre, 'modificacion' => $modClave]);
                    break;
            }
        }

        $script .= '' . ClasificacionAdministrativa::muestraClasAdmin($clasAdmin) . '';

        //ESTILOS DE TABLA DE PRESUPUESTO
        $this->mixStyle('
            #tbody-tabla-tablaPresupuesto tr:hover{
                background-color:#EAEAEA !important;
            }
        ');
        $form = new form($form);

        $html .= '
            <form id="Modificaciones">
            <div class="row">
                <fieldset class="col-md-12 col-xs-12 mb-4">
                    <legend>Enc. Programático <span id="comoSeUsa" style="text-decoration: underline; color:red; cursor: pointer;">¿ cómo se usa ?</span></legend>
                    <label>Encuadre Programático</label><br>
                    <div class="row">
                        <fieldset class="col-md-3 col-xs-3">
                            <input type="text" value="' . $valorEpg . '" maxlength="20" ' . $encLectura . ' placeholder="Encuadre (F1/F2)" name="encProg" id="encProg" class=" form-control F1Catalogo" required="true">
                        </fieldset>
                        <fieldset class="col-md-9 col-xs-9">
                            <input type="text" value="' . $nombreEpg . '" disabled placeholder="Nombre Obra/Actividad" name="nomProg" id="nomProg" class=" form-control">
                        </fieldset>
                    </div>
                </fieldset>
                <br/>
                <fieldset class="col-md-12 col-xs-12 mb-4">
                    ' . ObjetoGasto::ponObjetoGasto($objGasto) . '
                </fieldset>
            </div>
            <div id="contenedorCFModal"></div>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-6">
                <legend>Clave de Financiamiento</legend>
                    ' . ClasificacionFinanciamiento::creaComboCveFin($cveFin) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
        ';
        if (!(($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A')
            || $enc_oop->getTmd_id() == 'D')) {
            $html .= TipoCompra::getTipoCompra($tipoCompra);
        }
        $html .= '
            </fieldset>
        </div>
        <label id="ocultarGraph" style="position:left; color:green;">Ocultar Graficas</label>
        <div class="row" id="ocultaGrafica">
            <fieldset class="col-md-12 col-xs-12">
                <legend>Graficas de Referencia</legend>
                <fieldset class="col-md-12 col-xs-12">
                    <div id = "grafica" name = "grafica" style="position:inherid; height:200px;"/>
                </fieldset>
                <!--<fieldset class="col-md-6 col-xs-6">
                    <div id = "grafica1" name = "grafica1" style="position:inherid; height:200px;"/>
                </fieldset>-->
            </fieldset>
        </div>
        <br/><br/>';
        $mov = $this->ponRadio(TipoMovimiento::objMovimiento($objMov));
        $html .= $this->vista->vwInformacionFolioRegistro($mod, $mov);
        $html .= '<div class="row"><!--calendario-->
            <fieldset class="col-md-12 col-xs-12">
                <div class="panel panel-default">
                    <!-- Default panel contents -->
                    <div class="panel-heading"><b style="font-size:17px;">Calendario presupuestado</b></div>
                        <div class="panel-body">
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        ' . $inputImporteDistribuir . '
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        ' . $inputImportexDistribuir . '
                                    </fieldset>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    <!-- Table -->
                    <table id="pres" class=" table table-striped" style="border-collapse:inherit !important">
                        <thead>
                            <tr id="encabezado" >
                                <th style="width:200px">Mes</th>
                                <th style="width:250px">Importe Actual</th>
                                <th style="width:250px">Importe Devengado</th>
                                <th style="width:250px">Importe Disponible</th>
                                <th style="width:250px align:center">Distribución</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-tabla-tablaPresupuesto">
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
        </form>
        <!--fin calendario-->
        ';

        $form->mixHtml($html);
        $this->agregaTools($btnRegresar . ' ' . $btnGuardar . ' ' . $btnEliminar);
        //PINTAMOS EL FORMULARIO
        $this->mixHtml($form->ponForm());

        $script .= '
            $("#ocultarGraph").click(function(){
                $("#ocultaGrafica").toggle();
                if($("#ocultaGrafica").is(":visible")){
                    $("#ocultarGraph").text("Ocultar Gráficas");
                }else{
                    $("#ocultarGraph").text("Mostrar Gráficas");
                }
            });
            function validaTotMovimiento(field)
            {
                valido = true;
                var importe = 0;
                var total = 0;
                importe = parseFloat( unformatNumber( $(field).val() ).toFixed(2) );
                console.log("importe: "+importe);
                $(".modificacion").each(function(){ total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 ); });
                console.log("total: "+total);
                if( importe.toFixed(2) != total.toFixed(2) ){
                    error(field,"El importe no coincide con la suma de los meses. Favor de verificar");
                    valido = false;
                }else if( importe.toFixed(2)<= 0 ){
                    error(field,"El importe no debe ser 0");
                    valido = false;
                }
                return valido;
            }

            function validaModificacion(mov,mod){
                if(mov != "" && mov != undefined  && mod !="" && mod != undefined ){
                    if(mod =="INTERNAS")
                        mod = "I";
                    else if(mod== "EXTERNAS")
                        mod = "E";
                } else {
                    if ( $("#modalidadM").val() =="INTERNAS"){
                        mod = "I";
                    }else{
                        mod = "E";
                    }
                    $("input:radio[name=mov_id]").each(function(){
                        if( $(this).is(":checked")){
                            mov = $(this).val();
                        }
                    });
                }

                vigente = 0;
                calendario = 0;
                mes=0;

                // VALIDA EL DISPONIBLE POR MES
                $(".vigente").each(function(){
                    fechaactual  = new Date();
                    fechaactual  = fechaactual.getMonth();
                    valido = true;
                    vig =  unformatNumber($(this).text());
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mesNombre");
                    nmes = $(this).attr("nmes");
                    mes= mes.replace("_disp","");
                    dev =  unformatNumber($("#"+ mes +"_dev").text());
                    disp = vig - dev;
                    cap = ( $("#modificacion-" +  mes ).val());
                    cap = unformatNumber(cap);
                    dispos = unformatNumber($("#"+ mes +"_dispo").text());
                    n = mes.charAt(0).toUpperCase() + mes.slice(1);
                    valorCalendario = unformatNumber($("#modificacion-"+n).val());

                    switch (mov){
                        case "C":
                            if ( cap > 0 && cap < dev ){
                                notify("Error en el mes de "+ mesnombre + " deja sin cobertura el devengado","error");
                                console.log("modificacion-mes"+$("#modificacion-"+n));
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                // $("#modificacion-"+n).addClass("alert alert-danger");
                                valido = false;
                                return false;
                            }else{
                                // $("#modificacion-"+n).removeClass("alert alert-danger");
                                $("#modificacion-"+n).css("background-color","#FFF !important");
                                valido = true;
                            }
                            break;
                        case "R":
                            if ( cap > dispos ){
                                notify("Error en el mes de "+ mesnombre + " no puede reducir mas alla del disponible por reducir","error");
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                valido = false;
                                return false;
                            }else
                            $("#modificacion-"+n).css("background-color","#FFF !important");
                                switch (mod){
                                    case "I":
                                        if(nmes > fechaactual){
                                            if(cap !=0){
                                                notify("No puede  reducir en el mes de  " + mes,"error" );
                                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                                valido = false;
                                                return false;
                                            }else
                                                $("#modificacion-"+n).css("background-color","#FFF !important");
                                        }
                                        break;
                                    case "E":
                                        break;
                                    default:
                                        notify("error en  la modalidad de la modificacion","error");
                                        valido = false;
                                        return false;
                                    break;
                                }
                            break;
                        case "A":
                            if(mod=="I" && nmes < fechaactual){
                                if( cap != 0 ){
                                    notify("No puede ampliar en el mes de  "+ mesnombre + " ya que es un mes pasado","error");
                                    $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                    return false;
                                    valido = false;
                                    return;
                                }else
                                    $("#modificacion-"+n).css("background-color","#FFF !important");
                            }
                            break;
                    }
                    vigente += vig;
                    calendario  += valorCalendario;
                });
                if ( vigente.toFixed(2) != calendario.toFixed(2)  && mov =="C" ){
                    notify("El calendario no coincide con el vigente","error");
                    valido = false;
                    return false;
                }
                console.log("importe total"+importe);
                console.log("calendario"+calendario+"sum cal."+$("#modificacion-"+ mes ).val());
                var total = 0;
                importe = parseFloat( unformatNumber( $("#importe").val() ).toFixed(2) );
                $(".modificacion").each(function(){
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mes");
                    mes.replace("modificacion-","");
                    total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 );
                    if ( unformatNumber($(this).val()) < 0 ){
                        notify("El importe del mes " + mesnombre + " no puede ser negativo ","error");
                        valido = false;
                        return false;
                    }
                });
                if( importe.toFixed(2) != total.toFixed(2) ){
                    notify("El importe no coincide con la suma de los meses. Favor de verificar","error");
                    valido = false;
                    return false;
                }
                return valido;
            }
        ';

        $script .= '
            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
                //location.reload();
                $("nav").menusinpres("clickMenu");
            });
            $("#encProg").blur(function() {
                if($("#encProg").val() != ""){
                    comunica("srv/ObjetoGasto.php",{
                        metodoObjGto:"cargaPg",
                        filtro:"modificaciones",
                        ' . (($this->drh) ?
                        'tcp_id:"D",' : '') . '
                        tmd_id:"' . $enc_oop->getTmd_id() . '",
                        tipoMovimiento:"' . $mod->getTipoMovimiento() . '",
                        tfm_id:"' . $enc_oop->getTfm_id() . '",
                        mmd_id:"' . $enc_oop->getMmd_id() . '",
                        omo_id:"' . $mod->getOmo_id() . '",
                        ecf_id:"' . $mod->getEcf_id() . '"
                    });
                    comunica("' . $this->pantalla . '",{
                        metodo: "validaMovtoUE",
                        mod_ue_id: "' . $mod->getMod_ue_id() . '"
                    });
                }
                return false;
            });

            $("#SitemaActivo").hide();
            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
        ';

        $script .= '
            $.template( "tmpl-tbody-tabla-tablaPresupuesto",
            "<tr class=\'odd\'><td class=\'sorting_1\'>${mes}</td>' .
        '<td id=\'${mes}_disp\' nmes = \'${nmes}\' class=\'sorting_1 moneda  vigente ${mes}\' mesNombre=\'${mes}\' >${vigente}</td>' .
        '<td id=\'${mes}_dev\' class=\'sorting_1 moneda devengado ${mes}\'>${devengado}</td>' .
        '<td id=\'${mes}_dispo\' class=\'sorting_1 moneda disponible ${mes}\'>${disponible}</td>' .
        '<td class=\'sorting_1\'>' .
        '<input value=\'${modificacion}\' type=\'text\' style=\'width:100px;\' ' .
        'class=\'modificacion mes moneda validaModificacion \' onBlur = \'validaModificacion()\' ' .
        'id=\'modificacion-${mes}\' name=\'meses\' mes=\'${mes}\' data-prompt-position=\'topRight:0,-10\' >' .
        '</td></tr>");
            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                Mousetrap.unbind("ctrl+enter");
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            function valueMeses(){
                meses = new Array();
                $(".modificacion").each(function(index,val){
                        meses[index]= unformatNumber($(this).val());
                    });
                return meses;
            }
            $("input:radio[name=mov_id]").change(function(){
                validaModificacion($(this).val(),$("#modalidadM").val());
            });
        ';

        $this->mixScript($script);
    } // Fin addModModificacion

    //METODO PARA VALIDAR SI LA UE  CORRESPONDE CON EL USUARIO
    public function validaMovtoUE()
    {
        if ($this->gpo . $this->ur_id == 116 && $this->nvj == 1) {
            if (($this->ue_id != $this->prm->mod_ue_id)) {
                $this->mixScript('
                    notify("La UE no corresponde a la UE del usuario");
                    $("#encProg").val("");
                    $("#nomProg").val("");
                ');
            }
        }
    } // Fin validaMovtoUE

    /*METODO PARA AGREGAR MODIFICACIONES O MOVIMIENTOS POR EL PERFIL SEFIN*/
    private function addModPrevision()
    {
        //Metodo que agrega previsiones para el caso de la compensacion de folios en la fase
        //de revision y agrega nuevos movimientos al folio de origen A,B,C,D.
        /*se instancia el folio de modificacion para tener la informacion disponible*/
        $script = 'bloquearClasAdmin();';
        $this->pintaScript($script);
        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $modClave = new DetalleModificacion($this->prm);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);
        $abc = 'a';
        $sql = $previsionesPlan = '';

        if ($modClave->getOmo_id() != '' && $modClave->getMod_gpo_id() != '' && $modClave->getMod_ur_id() != '' &&
            $modClave->getMod_id() != '' && $modClave->getGpo_id() != '' && $modClave->getUr_id() != ''
            && $modClave->getUe_id() != '' && $modClave->getEpg_id() != '' && $modClave->getPg_id() != ''
            && $modClave->getPe_id() != '' && $modClave->getEcf_id() != '' && $modClave->getTcp_id() != '') {
            $abc = 'c';
            $sql = 'SELECT ' .
                'pv.gpo_id, pv.ur_id, pv.ue_id, pv.epg_id, pv.pg_id, pv.pe_id,' .
                "pv.ecf_id, pv.tcp_id, p.pre_clave,p.pre_claveCompleta, pe.pe_nombre ,pv.pre_exenta
                FROM presupuesto p
                INNER JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN cat_PartidaEspecifica pe USING (pg_id,pe_id)
                WHERE
                    pv.gpo_id = {$modClave->getGpo_id()} AND pv.ur_id = {$modClave->getUr_id()} " .
                " AND pv.ue_id = {$modClave->getUe_id()} AND pv.epg_id = {$modClave->getEpg_id()} " .
                " AND pv.pg_id = {$modClave->getPg_id()} AND pv.pe_id = '{$modClave->getPe_id()}' " .
                " AND pv.ecf_id = {$modClave->getEcf_id()} AND pv.tcp_id = '{$modClave->getTcp_id()}' ";
        } else {
            /*cuando se añade un movimiento*/
            $sql = 'SELECT ' .
                'pv.gpo_id, pv.ur_id, pv.ue_id, pv.epg_id, pv.pg_id, pv.pe_id,' .
                'pv.ecf_id, pv.tcp_id, p.pre_clave, p.pre_claveCompleta, pe.pe_nombre ,pv.pre_exenta
                FROM presupuesto p
                INNER JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN cat_PartidaEspecifica pe USING (pg_id,pe_id)
                INNER JOIN enc_Programatico using(epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                INNER JOIN cat_ObraActividad using(gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
            ';

            /*si el origen es B, se excluyen las partidas que esten en uso que no sean previsiones*/
            $left =
                " LEFT JOIN(
                SELECT
                    distinct m.mod_gpo_id, m.mod_ur_id, d.gpo_id, d.ur_id, d.ue_id, d.epg_id, d.pg_id, d.pe_id, m.ecf_id, tcp_id
                FROM scp_MaestroModificacion m
                    JOIN scp_DetalleModificacion d USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                WHERE d.gpo_id = {$mod->getMod_gpo_id()} AND d.ur_id = {$mod->getMod_ur_id()} " .
                " AND d.ecf_id = {$mod->getEcf_id()} " .
                " AND d.tcp_id != 'P' AND m.emo_id not in (14,15,16)
                ) as t USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id ) ";

            /*para excluir las previsiones ya utilizadas en el folio*/
            $left2 =
                " LEFT JOIN(
                SELECT
                    distinct gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id
                FROM scp_DetalleModificacion
                WHERE omo_id = '{$mod->getOmo_id()}' AND mod_gpo_id = {$mod->getMod_gpo_id()} AND mod_ur_id = {$mod->getMod_ur_id()} AND mod_id = {$mod->getMod_id()}
            ) as q USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id ,tcp_id ) ";

            $and = '';

            switch ($this->subSecretaria) {
                case 2: //planeación
                    $previsionesPlan = ' AND pre_planeacion = 1';
                    $and = ' and toa_id=2 ';
                    break;
                case 3: //egresos
                    $previsionesPlan = '';
                    $and = ' and toa_id=1 ';
                    //Se agregó 3 porque algunas previsiones estan etiquetadas como gastos de operación
                    //y otras como previsiones.
                    break;
            } // Fin switch

            $idp = ''; // REVISAR MIENTRAS SE PONE PARA REALIZAR UNA OPERACIÓN DE URGENCIA SOLICITADO POR EL LIC. ELISEO.

            switch ($mod->getOmo_id()) {
                /*Solo movimientos de la Ur*/
                case 'C': //D.R.H
                case 'D': //D.R.M
                case 'A': //Dependencia
                    switch ($this->prm->nivelRevision) {
                        case 'alta':
                            $sql .= $left;
                            $sql .=
                                "where p.gpo_id = {$mod->getMod_gpo_id()} and p.ur_id = {$mod->getMod_ur_id()} and p.tcp_id != 'P' " .
                                ' and t.pe_id is null ' .
                                " and ecf_id = {$mod->getEcf_id()} ";
                            $sql .= $and . $idp;
                            break;
                            //Solo se compensará con previsiones de la 115 y con la misma clave de
                            //financiamiento.
                        case 'revisionSEFIN':
                            $sql .= $left2 .
                                "WHERE q.pe_id is null AND gpo_id = '1' AND ur_id = '15' $previsionesPlan AND tcp_id = 'P'";
                            break;
                    }
                    break;
                case 'B': //EGRESOS
                case 'E': //PLANEACION
                case 'F': //REFRENDO
                    switch ($enc_oop->getTmd_id()) {
                        //ampl o red liq, NOTA: Un "traspaso" entre previsiones con diferente clave de
                        //financiamiento se realizará mediante una ampliación y una reducción.
                        case 'D':case 'E':
                            $sql .= ' WHERE ';
                            $sql .= " gpo_id = {$mod->getMod_gpo_id()} and ur_id = {$mod->getMod_ur_id()} AND ecf_id = {$mod->getEcf_id()} ";
                            $sql .= (($mod->getMod_gpo_id() . $mod->getMod_ur_id() != 115) ? $and : '');
                            break;
                        case 'C': //calendario
                            $sql .= $left . ' WHERE ';
                            $sql .= " t.pe_id is null AND p.tcp_id != 'P' AND p.gpo_id = {$mod->getMod_gpo_id()} AND p.ur_id = {$mod->getMod_ur_id()} " .
                                " AND ecf_id = {$mod->getEcf_id()} ";
                            $sql .= $and . $idp;
                            break;
                            //traspaso intrainstitucional: Solo previsiones de la 115 con la misma clave de financiamiento
                            //ó C y D de la UR.
                        case 'A': //traspaso intrainstitucional
                            $sql .= $left . ' WHERE ';
                            $sql .=
                                ' t.pe_id is null
                                AND
                                    ' . (($mod->getMod_gpo_id() . $mod->getMod_ur_id() == 115) ? "
                                    (( p.gpo_id = '1' AND p.ur_id = '15' AND p.tcp_id = 'P' )
                                    OR ( p.gpo_id = {$mod->getMod_gpo_id()} AND p.ur_id = {$mod->getMod_ur_id()} " .
                                " AND ecf_id = {$mod->getEcf_id()}
                                    ))
                                    "
                                : "( p.gpo_id = {$mod->getMod_gpo_id()} AND p.ur_id = {$mod->getMod_ur_id()} " .
                                " AND ecf_id = {$mod->getEcf_id()}
                                    )
                                    ") . '';
                            $sql .= (($mod->getMod_gpo_id() . $mod->getMod_ur_id() != 115) ? $and : '') . $idp;
                            break;
                        case 'B': //Traspaso interinstitucional: Solo se hace a las claves C, D de la UR
                            switch ($this->prm->nivelRevision) {
                                case 'alta':
                                    switch ($mod->getTipoMovimiento()) {
                                        case 'A':
                                            $sql .= $left . ' WHERE ';
                                            $sql .=
                                                " t.pe_id is null
                                                AND p.gpo_id = {$mod->getMod_gpo_id()} AND p.ur_id = {$mod->getMod_ur_id()} " .
                                                " AND ecf_id = {$mod->getEcf_id()}
                                            ";
                                            $sql .= $and . $idp;
                                            break;
                                        case 'R': // Reduccion interinstitucional: Solo se puede hacer a previsiones de la Ur
                                            //una C o D
                                            $sql .= $left . ' WHERE ';
                                            $sql .=
                                                " t.pe_id is null
                                            AND (
                                                ( p.gpo_id = {$mod->getMod_gpo_id()} AND p.ur_id = {$mod->getMod_ur_id()} AND p.tcp_id = 'P' ) OR
                                                ( p.gpo_id = {$mod->getMod_gpo_id()} AND p.ur_id = {$mod->getMod_ur_id()} " .
                                                " AND ecf_id = {$mod->getEcf_id()}
                                                )
                                            )";
                                            $sql .= $and . $idp;
                                            break;
                                    } // fin switch
                                    break;
                                    //Solo se compensará con previsiones de la 115 y con la misma clave
                                    //de financiamiento.
                                case 'revisionSEFIN':
                                    $sql .= $left2 .
                                        "WHERE q.pe_id is null AND gpo_id = '1' AND ur_id = '15' $previsionesPlan AND tcp_id = 'P'";
                                    break;
                            } // fin switch
                            break;
                    } //Fin switch
                    break;
            } // Fin switch
        } //Fin else

        $btnGuardarModificacion = new stdClass();
        $btnGuardarModificacion->id = 'btnGuardarModificacion';
        $btnGuardarModificacion->class = 'btn btn-outline-succes me-1';
        $btnGuardarModificacion->icon = 'fa-solid fa-floppy-disk me-2';
        $btnGuardarModificacion->text = 'Guardar';
        $btnGuardarModificacion->type = 'submit';
        $btnGuardarModificacion->value = 'Guardar';
        $btnGuardarModificacion->form = 'ModificacionesSefin';
        $btnGuardarModificacion->color = 'verde';
        //VERIFICAR
        $btnGuardarMod = '';

        switch ($this->prm->nivelRevision) {
            case 'alta':
                switch ($this->subSecretaria) {
                    case 2: //planeación
                        $btnGuardarMod = ((($mod->getEmo_id() == 6 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && $modClave->getTcp_id() != 'P') ? $this->ponBoton($btnGuardarModificacion) : '');
                        break;
                    case 3: //egresos
                        if ($mod->getSubsecretaria() == 1) {
                            $btnGuardarMod = ((($mod->getEmo_id() == 8 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && $modClave->getTcp_id() != 'P') ? $this->ponBoton($btnGuardarModificacion) : '');
                        }
                        break;
                } //fin switch
                break;
            case 'revisionSEFIN':
                switch ($this->subSecretaria) {
                    case 2: //planeación
                        $btnGuardarMod = ((isset($this->prm->prevision) && ($mod->getEmo_id() == 6 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && ($modClave->getTcp_id() == '' || $modClave->getTcp_id() == 'P')) ? $this->ponBoton($btnGuardarModificacion) : '');
                        break;
                    case 3: //egresos
                        if ($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() != '' && $mod->getVobo() == 2) {
                            $btnGuardarMod = $this->ponBoton($btnGuardarModificacion);
                        } else {
                            if ($mod->getSubsecretaria() == 1) {
                                $btnGuardarMod = ((isset($this->prm->prevision) && ($mod->getEmo_id() == 8 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && ($modClave->getTcp_id() == '' || $modClave->getTcp_id() == 'P')) ? $this->ponBoton($btnGuardarModificacion) : '');
                            }
                        }
                        break;
                } // Fin switch
                break;
        } //Fin switch

        $btnEliminarModificacion = new stdClass();
        $btnEliminarModificacion->id = 'btnEliminarModificacion';
        $btnEliminarModificacion->class = 'btn-test btn-outline-danger me-1';
        $btnEliminarModificacion->icon = 'fa-solid fa-trash me-2';
        $btnEliminarModificacion->text = 'Eliminar';
        $btnEliminarModificacion->type = 'submit';
        $btnEliminarModificacion->value = 'Eliminar';

        //VERIFICAR
        $btnEliminarMod = '';
        switch ($this->prm->nivelRevision) {
            case 'alta':
                switch ($this->subSecretaria) {
                    case 2: //planeacion
                        $btnEliminarMod = (($abc != 'a' && ($mod->getEmo_id() == 6 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && $modClave->getTcp_id() != 'P') ? $this->ponBoton($btnEliminarModificacion) : '');
                        break;
                    case 3: //egresos
                        if ($mod->getSubsecretaria() == 1) {
                            $btnEliminarMod = (($abc != 'a' && ($mod->getEmo_id() == 8 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && $modClave->getTcp_id() != 'P') ? $this->ponBoton($btnEliminarModificacion) : '');
                        }
                        break;
                } // fin switch
                break;
            case 'revisionSEFIN':
                switch ($this->subSecretaria) {
                    case 2: //planeación
                        $btnEliminarMod = (($abc != 'a' && isset($this->prm->prevision) && ($mod->getEmo_id() == 6 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && $modClave->getTcp_id() == 'P') ? $this->ponBoton($btnEliminarModificacion) : '');
                        break;
                    case 3: //egresos
                        if ($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() != '' && $mod->getVobo() == 2) {
                            $btnEliminarMod = $this->ponBoton($btnEliminarModificacion);
                        } else {
                            if ($mod->getSubsecretaria() == 1) {
                                $btnEliminarMod = (($abc != 'a' && isset($this->prm->prevision) && ($mod->getEmo_id() == 8 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) && $modClave->getTcp_id() == 'P') ? $this->ponBoton($btnEliminarModificacion) : '');
                            }
                        }
                        break;
                } //fin switch
                break;
        } //Fin switch

        $btnRegresarTablaModificacion = new stdClass();
        $btnRegresarTablaModificacion->id = 'btnRegresarTablaModificacion';
        $btnRegresarTablaModificacion->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresarTablaModificacion->class = 'btn-outline-secondary regresar me-1';
        $btnRegresarTablaModificacion->text = 'Regresar';
        $btnRegresarTablaModificacion->value = 'Regresar';
        $btnRegresarTablaModificacion->color = 'gris';

        $folio = new stdClass();
        $folio->id = 'folio';
        $folio->enDiv = true;
        $folio->value = $mod->getMod_id();
        $folio->required = true;
        $folio->disabled = true;

        $modalidad = new stdClass();
        $modalidad->id = 'modalidadM';
        $modalidad->enDiv = true;
        $modalidad->value = ($enc_oop->getMmd_id() == 'E' ? 'EXTERNAS' : 'INTERNAS');
        $modalidad->required = true;
        $modalidad->disabled = true;

        $tipoModificacion = new stdClass();
        $tipoModificacion->id = 'tmd_id';
        $tipoModificacion->enDiv = true;
        $tipoModificacion->value = $tipoMod->getNombre();
        $tipoModificacion->required = true;
        $tipoModificacion->disabled = true;

        $objMov = new stdClass();
        $objMov->omo_id = $mod->getOmo_id();
        $objMov->mod_gpo_id = $mod->getMod_gpo_id();
        $objMov->mod_ur_id = $mod->getMod_ur_id();
        $objMov->mod_id = $mod->getMod_id();
        $objMov->mov_id = $modClave->getMov_id();
        $objMov->tcp_id = (isset($this->prm->prev) && $this->prm->prev == true ? 'P' : $modClave->getTcp_id());
        if (isset($this->prm->nivelRevision) && $this->prm->nivelRevision == 'alta') {
            $objMov->altaSefin = true;
        }
        //Parametro para identificar cuando sefin agrega un movimiento y mostrar ampliacion o reduccion segun sea el caso.

        /*Input importe del movimiento*/
        $importeaDistribuir = new stdClass();
        $importeaDistribuir->id = 'importe';
        $importeaDistribuir->label = 'Importe a distribuir';
        $importeaDistribuir->formato = 'moneda';
        $importeaDistribuir->required = true;
        $importeaDistribuir->class = 'validaTotMovimiento';
        $importeaDistribuir->value = $modClave->getImporte();
        if ($enc_oop->getTmd_id() == 'C') {
            $importeaDistribuir->disabled = true;
        }

        /*Input importe por dist del movimiento*/
        $importePorDistribuir = new stdClass();
        $importePorDistribuir->id = 'importeb';
        $importePorDistribuir->label = 'Saldo por distribuir';
        $importePorDistribuir->formato = 'moneda';
        $importePorDistribuir->disabled = 'disabled';

        $infPre = new stdClass();
        if ($modClave->getTcp_id() == 'P') {
            /*cuando se edita una previsión, muestra la información del detalle*/
            $infPre->gpo_id = $modClave->getGpo_id();
            $infPre->ur_id = $modClave->getUr_id();
        } else {
            $infPre->gpo_id = $mod->getMod_gpo_id();
            $infPre->ur_id = $mod->getMod_ur_id();
        }
        $infPre->ue_id = $modClave->getUe_id();
        $infPre->epg_id = $modClave->getEpg_id();
        $infPre->pg_id = $modClave->getPg_id();
        $infPre->pe_id = $modClave->getPe_id();
        $infPre->ecf_id = $modClave->getEcf_id();
        $infPre->tcp_id = $modClave->getTcp_id();
        $pre = new Presupuesto($infPre);

        $cboPrev = new stdClass();
        $cboPrev->id = 'cboPre';
        $cboPrev->sql = $sql;
        $cboPrev->soloText = true;
        $cboPrev->comoAtr = 'pre_clave,pre_claveCompleta,tcp_id,gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,pre_exenta';
        $cboPrev->camposVal = 'pre_clave';
        $cboPrev->camposText = 'pre_claveCompleta,tcp_id,pe_nombre';
        $cboPrev->search = true;
        $cboPrev->required = true;

        if ($abc != 'c') {
            $cboPrev->change = '
                comunica("' . $this->pantalla . '",{
                    metodo:"muestraPresupuesto",
                    prevision:true,
                    gpo_id:$("#cboPre :selected").attr("gpo_id"),
                    ur_id:$("#cboPre :selected").attr("ur_id"),
                    ue_id:$("#cboPre :selected").attr("ue_id"),
                    epg_id:$("#cboPre :selected").attr("epg_id"),
                    pg_id:$("#cboPre :selected").attr("pg_id"),
                    pe_id:$("#cboPre :selected").attr("pe_id"),
                    ecf_id:$("#cboPre :selected").attr("ecf_id"),
                    tcp_id:$("#cboPre :selected").attr("tcp_id"),
                    tmd_id:"' . $enc_oop->getTmd_id() . '",
                    tipoMovimiento:"' . $mod->getTipoMovimiento() . '"
                });'
            ;
        }

        $form = new stdClass();
        $form->id = 'ModificacionesSefin';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcModificacion';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->omo_id = $mod->getOmo_id();
        $form->datosExtra->mod_gpo_id = $mod->getMod_gpo_id();
        $form->datosExtra->mod_ur_id = $mod->getMod_ur_id();
        $form->datosExtra->mod_id = $mod->getMod_id();
        $form->datosExtra->meses = '"{ valueMeses();}"';
        $form->datosExtra->importe = '"{ $("#importe").val();}"';
        $form->datosExtra->gpo_id = '"{( $("#cboPre :selected").attr("gpo_id") !="" ) ? $("#cboPre :selected").attr("gpo_id") : ""}"';
        $form->datosExtra->ur_id = '"{( $("#cboPre :selected").attr("ur_id") !="" ) ? $("#cboPre :selected").attr("ur_id"):""}"';
        $form->datosExtra->ue_id = '"{( $("#cboPre :selected").attr("ue_id") !="" ) ? $("#cboPre :selected").attr("ue_id"):""}"';
        $form->datosExtra->epg_id = '"{( $("#cboPre :selected").attr("epg_id") !="") ? $("#cboPre :selected").attr("epg_id"):""}"';
        $form->datosExtra->pg_id = '"{( $("#cboPre :selected").attr("pg_id") !="" ) ? $("#cboPre :selected").attr("pg_id"):""}"';
        $form->datosExtra->pe_id = '"{( $("#cboPre :selected").attr("pe_id") !=""  ) ? $("#cboPre :selected").attr("pe_id"):""}"';
        $form->datosExtra->ecf_id = '"{( $("#cboPre :selected").attr("ecf_id")!=""  ) ? $("#cboPre :selected").attr("ecf_id"):""}"';
        $form->datosExtra->tcp_id = '"{( $("#cboPre :selected").attr("tcp_id") !="" ) ? $("#cboPre :selected").attr("tcp_id"):""}"';
        $form->datosExtra->tipoMovimiento = $mod->getTipoMovimiento();
        if (isset($this->prm->nivelRevision)) {
            $form->datosExtra->nivelRevision = $this->prm->nivelRevision;
        }

        $form = new form($form);

        $html = '
            <form id="ModificacionesSefin">
                <div class="row mb-4">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Clave Presupuestal</legend>
                        <div class="row col-md-12 col-xs-12">
                            ' . $this->ponCombo($cboPrev) . '
                        </div>
                    </fieldset>
                </div>
                <label id="ocultarGraph" style="position:left; color:green;">Ocultar Graficas</label>
                <div class="row mb-4" id="ocultaGrafica">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Graficas de Referencia</legend>
                        <fieldset class="col-md-6 col-xs-6">
                            <div id = "grafica" name = "grafica" style="position:inherid; height:200px;"/>
                        </fieldset>
                        <!--<fieldset class="col-md-6 col-xs-6">
                            <div id = "grafica1" name = "grafica1" style="position:inherid; height:200px;"/>
                        </fieldset>-->
                    </fieldset>
                </div>
                <br/><br/>
                <div class="row mb-4">
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Folio</legend>
                        ' . $this->ponInput($folio) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Modalidad</legend>
                        ' . $this->ponInput($modalidad) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo de adecuación</legend>
                        ' . $this->ponInput($tipoModificacion) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo de movimiento</legend>
                        ' . $this->ponRadio(TipoMovimiento::objMovimiento($objMov)) . '
                    </fieldset>
                </div>
                <div class="row"><!--calendario-->
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="panel panel-default">
                        <!-- Default panel contents -->
                            <div class="panel-heading"><h2>Calendario presupuestado</h2></div>
                                <div class="panel-body">
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6">
                                            ' . $this->ponInput($importeaDistribuir) . '
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">
                                            ' . $this->ponInput($importePorDistribuir) . '
                                        </fieldset>
                                    </div>
                                </div>
                                <!-- Table -->
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Importe Actual</th>
                                            <th>Importe Devengado</th>
                                            <th>Importe Disponible</th>
                                            <th>Distribución</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-tabla-tablaPresupuesto"></tbody>
                                </table>
                        </div>
                    </fieldset>
                </div><!--fin calendario-->
            </form>'
        ;
        $form->mixHtml($html);
        $this->mixHtml($form->ponForm());
        $this->agregaTools($this->ponBoton($btnRegresarTablaModificacion) . ' ' . $btnGuardarMod . ' ' . $btnEliminarMod);

        $script .= '
            $("#ocultarGraph").click(function(){
                $("#ocultaGrafica").toggle();
                if($("#ocultaGrafica").is(":visible")){
                    $("#ocultarGraph").text("Ocultar Gráficas");
                }else{
                    $("#ocultarGraph").text("Mostrar Gráficas");
                }
            });
        ';

        $script .= '
            function validaModificacion(mov,mod){
                if (mov != "" && mov != undefined  && mod != "" && mod != undefined ){
                    if(mod == "INTERNAS")
                        mod = "I";
                    else if(mod== "EXTERNAS")
                        mod = "E";
                } else {
                    if ( $("#modalidadM").val() =="INTERNAS"){
                        mod = "I";
                    } else {
                        mod = "E";
                    }
                    $("input:radio[name=mov_id]").each(function(){
                        if( $(this).is(":checked")){
                            mov = $(this).val();
                        }
                    });
                }

                vigente = 0;
                calendario = 0;
                // VALIDA EL DISPONIBLE POR MES
                $(".vigente").each(function(){
                    fechaactual = new Date();
                    fechaactual = fechaactual.getMonth();
                    valido = true;
                    vig = unformatNumber($(this).text());
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mesNombre");
                    nmes = $(this).attr("nmes");
                    mes= mes.replace("_disp","");
                    dev = unformatNumber($("#"+ mes +"_dev").text());
                    disp = vig - dev;
                    cap = ( $("#modificacion-" +  mes ).val());
                    cap = unformatNumber(cap);
                    dispos = unformatNumber($("#"+ mes +"_dispo").text());
                    n = mes.charAt(0).toUpperCase() + mes.slice(1);
                    valorCalendario = unformatNumber($("#modificacion-"+n).val());

                    switch (mov){
                        case "C":
                            if ( cap > 0 && cap < dev ){
                                notify("Error en el mes de "+ mesnombre + " deja sin cobertura el devengado","error");
                                console.log("modificacion-mes"+$("#modificacion-"+n));
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                valido = false;
                                return false;
                            }else{
                                $("#modificacion-"+n).css("background-color","#FFF !important");
                                valido = true;
                            }
                            break;
                        case "R":
                            if ( cap > dispos ){
                                notify("Error en el mes de "+ mesnombre + " no puede reducir mas alla del disponible por reducir","error");
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                valido = false;
                                return false;
                            }else
                                $("#modificacion-"+n).css("background-color","#FFF !important");
                            switch (mod){
                                case "I":
                                    if(nmes > fechaactual){
                                        if(cap !=0){
                                            notify("No puede  reducir en el mes de  " + mes,"error" );
                                            $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                            valido = false;
                                            return false;
                                        }else
                                            $("#modificacion-"+n).css("background-color","#FFF !important");
                                    }
                                    break;
                                case "E":
                                    break;
                                    default:
                                        notify("error en  la modalidad de la modificacion","error");
                                        valido = false;
                                        return false;
                                    break;
                            }
                            break;
                        case "A":
                                if(mod=="I" && nmes < fechaactual){
                                    if( cap != 0 ){
                                        notify("No puede ampliar en el mes de  "+ mesnombre + " ya que es un mes pasado","error");
                                        $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                        return false;
                                        valido = false;
                                        return;
                                    }else
                                        $("#modificacion-"+n).css("background-color","#FFF !important");
                                }
                        break;
                    }
                    vigente += vig;
                    calendario += valorCalendario;
                });
                if ( vigente.toFixed(2) != calendario.toFixed(2)  && mov =="C" ){
                    notify("El calendario no coincide con el vigente","error");
                    valido = false;
                    return false;
                }
                console.log("importe total"+importe);
                console.log("calendario"+calendario+"sum cal."+$("#modificacion-"+ mes ).val());
                var total = 0;
                importe = parseFloat( unformatNumber( $("#importe").val() ).toFixed(2) );
                $(".modificacion").each(function(){
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mes");
                    mes.replace("modificacion-","");
                    total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 );

                    if ( unformatNumber($(this).val()) < 0 ){
                        notify("El importe del mes " + mesnombre + " no puede ser negativo ","error");
                        valido = false;
                        return false;
                    }
                });
                if( importe.toFixed(2) != total.toFixed(2) ){
                    notify("El importe no coincide con la suma de los meses. Favor de verificar","error");
                    valido = false;
                    return false;
                }
                return valido;
            }
        ';

        $script .= '
            $.template( "tmpl-tbody-tabla-tablaPresupuesto",
            "<tr class=\'odd\'><td class=\'sorting_1\'>${mes}</td>' .
            '<td id=\'${mes}_disp\' nmes = \'${nmes}\' class=\'sorting_1 moneda  vigente ${mes}\' mesNombre=\'${mes}\' >${vigente}</td>' .
            '<td id=\'${mes}_dev\' class=\'sorting_1 moneda devengado ${mes}\'>${devengado}</td>' .
            '<td id=\'${mes}_dispo\' class=\'sorting_1 moneda disponible ${mes}\'>${disponible}</td>' .
            '<td class=\'sorting_1\'>' .
            '<input value=\'${modificacion}\' type=\'text\' style=\'width:100px;\' ' .
            'class=\'modificacion mes moneda validaModificacion \' onBlur = \'validaModificacion()\' ' .
            'id=\'modificacion-${mes}\' name=\'meses\' mes=\'${mes}\' data-prompt-position=\'topRight:0,-10\' >' .
            '</td></tr>");

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaModificacion").click(); e.stopPropagation(); });
            $("#btnRegresarTablaModificacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    ecf_id:"' . $mod->getEcf_id() . '",
                    mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    enc_oop:"' . $mod->getOop() . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $("#btnEliminarModificacion").click(function(){
                alertify.confirm("Eliminar",String.fromCharCode(191)+"Realmente desea eliminar el movimiento ?",
                    function(){
                        var objMod = {
                            metodo:"abcModificacion",
                            abc: "b"
                            ' . (($modClave->getOmo_id() != '') ? ',omo_id:"' . $modClave->getOmo_id() . '"' : '') . '
                            ' . (($modClave->getMod_gpo_id() != '') ? ',mod_gpo_id:' . $modClave->getMod_gpo_id() . '' : '') . '
                            ' . (($modClave->getMod_ur_id() != '') ? ',mod_ur_id:"' . $modClave->getMod_ur_id() . '"' : '') . '
                            ' . (($modClave->getMod_id() != '') ? ',mod_id:' . $modClave->getMod_id() . '' : '') . '
                            ' . (($modClave->getGpo_id() != '') ? ',gpo_id:' . $modClave->getGpo_id() . '' : '') . '
                            ' . (($modClave->getUr_id() != '') ? ',ur_id:"' . $modClave->getUr_id() . '"' : '') . '
                            ' . (($modClave->getUe_id() != '') ? ',ue_id:"' . $modClave->getUe_id() . '"' : '') . '
                            ' . (($modClave->getEpg_id() != '') ? ',epg_id:"' . $modClave->getEpg_id() . '"' : '') . '
                            ' . (($modClave->getPg_id() != '') ? ',pg_id:' . $modClave->getPg_id() . '' : '') . '
                            ' . (($modClave->getPe_id() != '') ? ',pe_id:"' . $modClave->getPe_id() . '"' : '') . '
                            ' . (($modClave->getEcf_id() != '') ? ',ecf_id:"' . $modClave->getEcf_id() . '"' : '') . '
                            ' . ((isset($this->prm->tcp_id)) ? ',tcp_id:"' . $this->prm->tcp_id . '"' : '') . '
                            ' . (($modClave->getMov_id() != '') ? ',mov_id:"' . $modClave->getMov_id() . '"' : '') . '
                            ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '' ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        }
                        comunica("' . $this->pantalla . '", objMod );
                    },undefined
                ).set("defaultFocus","cancel")
                .set("labels", {
                    ok: "Aceptar", cancel: "Cancelar"
                });
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-success");
                    btnAceptar.classList.add("btn-outline-success");
                }
                const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                if (cancela) {
                    cancela.classList.remove("btn-danger");
                    cancela.classList.add("btn-outline-danger");
                }
            });

            function valueMeses(){
                meses = new Array();
                $(".modificacion").each(function(index,val){
                    meses[index]= unformatNumber($(this).val());
                });
                return meses;
            }
            $("input:radio[name=mov_id]").change(function(){
                validaModificacion($(this).val(),$("#modalidadM").val());
            });
        ';

        $this->mixScript($script);

        if ($abc == 'c') {
            $this->muestraPresupuesto(['presupuesto' => $pre, 'modificacion' => $modClave]);
        }
    } // Fin addModPrevision

    /*METODO QUE MUESTRA A DETALLE LOS MOMENTOS CONTABLES C, D Y V DE PRESUPUESTO*/
    private function muestraPresupuesto()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : +
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        if (is_null($prm)) {
            $prm = $this->prm;
        }

        $esUR = $this->ur || $this->ue;

        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $tablaPresupuesto = [];
        $script = '';
        $modificacion = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        //VERIFICAMOS SI ES UNA PREVISIÓN
        $prevision = isset($prm->prevision) && $prm->prevision != '' ? true : false;
        //VERIFICAMOS SI ES UNA INSTANCIA DE PRESUPUESTO
        $instanciaPresupuesto = isset($prm->presupuesto) ? $prm->presupuesto : '';
        $instanciaPresupuesto = $instanciaPresupuesto instanceof Presupuesto;

        $instanciaDetalleMod = isset($prm->modificacion) && $prm->modificacion instanceof DetalleModificacion;

        $noEsAmpliacion = isset($prm->tmd_id) && !(($prm->tmd_id == 'B' && isset($prm->tipoMovimiento)
            && $prm->tipoMovimiento == 'A') || $prm->tmd_id == 'D');

        $pre = new stdClass();
        if (!$instanciaPresupuesto && isset($prm->pg_id) && $prm->pg_id != ''
            && isset($prm->pe_id) && $prm->pe_id != '') {
            if ($prevision) {
                //PREVISION
                $pre->gpo_id = $prm->gpo_id;
                $pre->ur_id = $prm->ur_id;
                $pre->ue_id = $prm->ue_id;
                $pre->epg_id = $prm->epg_id;
                $pre->pg_id = $prm->pg_id;
                $pre->pe_id = $prm->pe_id;
                $pre->ecf_id = $prm->ecf_id;
                $pre->tcp_id = $prm->tcp_id;

            } else {
                //CLAVE NORMAL
                $pre->gpo_id = $this->adm_grupo;
                $pre->ur_id = $this->adm_ur;
                $pre->ue_id = $this->adm_ue;
                $pre->epg_id = $this->adm_epg;
                $pre->pg_id = $prm->pg_id;
                $pre->pe_id = $prm->pe_id;
                $pre->ecf_id = $prm->ecf_id;
            }
            //SI NO ES UNA AMPLIACION
            if ($noEsAmpliacion) {
                $tipoCompra = isset($prm->tcp_id) ? $prm->tcp_id : '';
                if ($tipoCompra != '') {
                    $pre->tcp_id = $prm->tcp_id;
                }
            }
            //INSTANCIAMOS PRESUPUESTO
            $prm->presupuesto = new Presupuesto($pre);
            //SI ES UNA CALENDARIZACIÓN
            if ($prm->tmd_id == 'C') {
                $script .= '
                    $("#importe").val( formatNumber("' . $prm->presupuesto->getVigente() . '") );
                ';
            }
        }

        //ES UNA INSTANCIA DE PRESUPUESTO
        $instanciaPresupuesto = isset($prm->presupuesto) && $prm->presupuesto instanceof Presupuesto;
        if ($instanciaPresupuesto) {
            $script .= $this->vista->vwGrafica1($prm->presupuesto, $meses);
            //PARA MOSTRAR LA COLUMNA DE DISTRIBUCIÓN, A PARTIR DE LO CAPTURADO O SI ES UN CALENDARIO MOSTRAR EL CALENDARIO DE PRESUPUESTO
            if ($instanciaDetalleMod) {
                $modificacion = $prm->modificacion->getMes()->getArrayMeses();
            } elseif (isset($prm->tmd_id) && $prm->tmd_id == 'C') {
                $modificacion = $prm->presupuesto->getMesVigente()->getArrayMeses();
            }
            $pre->gpo_id = $prm->presupuesto->getGpo_id();
            $pre->ur_id = $prm->presupuesto->getUr_id();
            $pre->ue_id = $prm->presupuesto->getUe_id();
            $pre->epg_id = $prm->presupuesto->getEpg_id();
            $pre->pg_id = $prm->presupuesto->getPg_id();
            $pre->pe_id = $prm->presupuesto->getPe_id();
            $pre->ecf_id = $prm->presupuesto->getEcf_id();
            if ($prm->presupuesto->getTcp_id() != '') {
                $pre->tcp_id = $prm->presupuesto->getTcp_id();
            }
            //ENVIAMOS LOS PARAMETROS A LA FUNCIÓN PARA OBTENER LOS VALORES DE LA COLUMNA DEL CALENDARIO PRESUPUESTARIO
            $presupuesto = $this->model->getPresupuestoModificaciones($pre, '', $modificacion);
            //CREAMOS LA TABLA DE PRESUPUESTO CON LOS VALORES DEVUELTOS DE LA FUNCION
            foreach ($presupuesto as $key => $mesCorto) {
                $mes = [
                    'mes'          => $mesCorto->mes,
                    'vigente'      => $mesCorto->vigente,
                    'devengado'    => $mesCorto->devengado,
                    'disponible'   => $mesCorto->disp,
                    'modificacion' => $mesCorto->modificacion,
                    'nmes'         => $mesCorto->nmes,
                ];
                array_push($tablaPresupuesto, $mes);
            }
            $script .= '$("#tbody-tabla-tablaPresupuesto").html("");';
            $script .= (count($tablaPresupuesto) > 0 ? '$.tmpl( "tmpl-tbody-tabla-tablaPresupuesto", ' . json_encode($tablaPresupuesto) . ' ).appendTo("#tbody-tabla-tablaPresupuesto");' : '');
        } else {
            //NO SE MOSTRARÁ NADA
            $script .= '
                $("#tbody-tabla-tablaPresupuesto").html("");
                $("#importe").val(0.00);
                $("#importeb").val(0.00);
            ';
        }

        $script .= '
            function paradistribuir(){
                var importe = 0;
                var total = 0;
                importe = parseFloat( unformatNumber($("#importe").val() ).toFixed(2) );
                $(".mes").each(function(){
                    total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 );
                });
                $("#importeb").val(formatNumber((importe-total).toFixed(2)));
            };
        ';

        $script .= '
            $(".moneda").each(function(){
                $(this).text( formatNumber( $(this).text(),"$ "));
            });

            $(".modificacion").each(function(){
                $(this).val(formatNumber($(this).val(),"$ "));
            });

            $(".modificacion").blur(paradistribuir);
            paradistribuir();

            $(".modificacion").blur(function(){
            $(this).val(formatNumber($(this).val()));
                paradistribuir();
            });

            $("#importe").blur(function(){
                $("#importe").val(formatNumber($(this).val()));
                paradistribuir();
            });
        ';

        $script .= (($esUR) ? 'validaModificacion();' : '');
        $this->mixScript($script);
    } // Fin muestraPresupuesto

    /*METODO QUE GUARDA LOS MOVIMIENTOS DEL FOLIO*/
    private function abcModificacion()
    {
        $parametros = [];
        $mensaje = $mensajeError = '';
        $men = '';
        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);

        switch ($this->prm->abc) {
            case 'a':
            case 'c':
                $this->prm->meses = explode(',', $this->prm->meses);
                $this->prm->gpo_id = (isset($this->prm->gpo_id) && !empty($this->prm->gpo_id)) ?
                $this->prm->gpo_id : $this->adm_grupo;
                $this->prm->ur_id = (isset($this->prm->ur_id) && !empty($this->prm->ur_id)) ?
                $this->prm->ur_id : $this->adm_ur;
                $this->prm->epg_id = (isset($this->prm->epg_id) && !empty($this->prm->epg_id)) ?
                $this->prm->epg_id : $this->adm_epg;
                $this->prm->ue_id = (isset($this->prm->ue_id) && !empty($this->prm->ue_id)) ?
                $this->prm->ue_id : $this->adm_ue;

                $mes = new Meses(['prefix' => 'dmo_', 'tipo' => 'moneda']);
                $mes->setArrayMeses($this->prm->meses);
                $this->prm->mes = $mes;

                $modClave = new DetalleModificacion($this->prm);
                // pasamos los valores temporales, para poder realizar la validacion de la clave
                $modClave->setMes($mes);
                if (isset($this->prm->tcp_id)) {
                    $modClave->setTcp_id($this->prm->tcp_id);
                }
                $modClave->setImporte($this->prm->importe);
                // realizamos la validacion
                $objMmd = new stdClass();
                $objMmd->mmd = $enc_oop->getMmd_id();
                $objMmd->importe = $this->prm->importe;
                $modResult = $modClave->validaModificacion($objMmd);

                $clave = new stdClass();
                $clave->gpo_id = $this->prm->gpo_id;
                $clave->ur_id = $this->prm->ur_id;
                $clave->ue_id = $this->prm->ue_id;
                $clave->epg_id = $this->prm->epg_id;
                $clave->pg_id = $this->prm->pg_id;
                $clave->pe_id = $this->prm->pe_id;
                $clave->ecf_id = $this->prm->ecf_id;
                if (isset($this->prm->tcp_id)) {
                    $clave->tcp_id = $this->prm->tcp_id;
                }

                $calendarioUsuario = new stdClass();
                $calendarioUsuario->ene = $this->prm->meses[0];
                $calendarioUsuario->feb = $this->prm->meses[1];
                $calendarioUsuario->mar = $this->prm->meses[2];
                $calendarioUsuario->abr = $this->prm->meses[3];
                $calendarioUsuario->may = $this->prm->meses[4];
                $calendarioUsuario->jun = $this->prm->meses[5];
                $calendarioUsuario->jul = $this->prm->meses[6];
                $calendarioUsuario->ago = $this->prm->meses[7];
                $calendarioUsuario->sep = $this->prm->meses[8];
                $calendarioUsuario->oct = $this->prm->meses[9];
                $calendarioUsuario->nov = $this->prm->meses[10];
                $calendarioUsuario->dic = $this->prm->meses[11];
                $calendarioUsuario->importe = $this->prm->importe;

                $validaMod = $this->model->getValidaModificaciones($this->prm->mov_id, $enc_oop->getMmd_id(), $calendarioUsuario, $clave);

                if ($validaMod->valido == true && count($modResult) == 0) {
                    switch ($this->prm->omo_id) {
                        case 'A': //dependencia
                        case 'C': //Direccion de recursos humanos
                        case 'D': //Direccion de recursos materiales
                            if (((isset($this->prm->tcp_id) && $this->prm->tcp_id != 'P') || ($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A') || $enc_oop->getTmd_id() == 'D') && $this->nvj < 3) {
                                //Si el tipo de compra es D o C ó El tipo de modificacion sea ampliacion.
                                switch ($enc_oop->getTmd_id()) {
                                    case 'D': //Ampliacion liquida (:O no se se funcione ya no hice pruebas)
                                    case 'B': //traspaso interinstitucional
                                        if (($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A') || $enc_oop->getTmd_id() == 'D') {
                                            $pre = new Presupuesto();
                                            $pre->setGpo_id($modClave->getGpo_id());
                                            $pre->setUr_id($modClave->getUr_id());
                                            $pre->setUe_id($modClave->getUe_id());
                                            $pre->setEpg_id($modClave->getEpg_id());
                                            $pre->setPg_id($modClave->getPg_id());
                                            $pre->setPe_id($modClave->getPe_id());
                                            $pre->setEcf_id($modClave->getEcf_id());
                                            $pe = new PartidaEspecifica(['pg_id' => $this->prm->pg_id, 'pe_id' => $this->prm->pe_id]);
                                            if (UnidadResponsable::excentaComprasConsolidadas(['gpo_id' => $this->prm->mod_gpo_id, 'ur_id' => $this->prm->mod_ur_id, 'pe_id' => $this->prm->pe_id, 'ecf_id' => $this->prm->ecf_id])) {
                                                $this->prm->tcp_id = 'D';
                                                $pre->setTcp_id('D');
                                                //y si no existe la clave presupuestal, se da de alta.
                                                if ($pre->validaClavePresupuestal() <= 0) {
                                                    //insertamos la clave presupuestal de tipo directa
                                                    $pre->conectar();
                                                    $pre->setSql(
                                                        'SET @usr_ip =INET_ATON(?),
                                                        @usr_id =?,
                                                        @bit_observacion ="PRESUPUESTO REGISTRADO DESDE PANTALLA S_1_1";'
                                                    );
                                                    $parametros = [
                                                        $this->ip,
                                                        $this->usr_id,
                                                    ];
                                                    $pre->consultar($parametros);
                                                    $pre->insert(false);
                                                }
                                                DetalleModificacion::abc($this->prm);
                                            } elseif (!UnidadResponsable::excentaComprasConsolidadas(['gpo_id' => $this->prm->mod_gpo_id, 'ur_id' => $this->prm->mod_ur_id, 'pe_id' => $this->prm->pe_id, 'ecf_id' => $this->prm->ecf_id])) {
                                                /*Sirve para acumular el importe de la calendarizacion del movimiento, por el % de Compras directas*/
                                                $importeCd = 0;
                                                ////////////////////Compras directas
                                                //si el monto de directas es mayor que cero
                                                if ($pe->getComprasDirectas() > 0) {
                                                    $pre->setTcp_id('D');
                                                    //y si no existe la clave presupuestal, se da de alta.
                                                    if ($pre->validaClavePresupuestal() <= 0) {
                                                        //insertamos la clave presupuestal de tipo directa
                                                        $pre->conectar();
                                                        $pre->setSql(
                                                            'SET @usr_ip =INET_ATON(?),
                                                            @usr_id =?,
                                                            @bit_observacion ="PRESUPUESTO REGISTRADO DESDE PANTALLA S_1_1";'
                                                        );
                                                        $parametros = [
                                                            $this->ip,
                                                            $this->usr_id,
                                                        ];
                                                        $pre->consultar($parametros);
                                                        $pre->insert(false);
                                                    }
                                                    $mesesD = $this->prm->meses;
                                                    //se actualizan los importes de compras directas
                                                    foreach ($mesesD as $k => $v) {
                                                        $mesesD[$k] = Math::redondear($v * $pe->getComprasDirectas(), 2);
                                                        $importeCd += $mesesD[$k];
                                                    }
                                                    /*Hacemos una copia de los parámetros recibidos*/
                                                    $prmD = $this->prm;
                                                    $prmD->importe = $modClave->getImporte();
                                                    /*actualizamos en la copia, los importes de la modificacion*/
                                                    $mes->setArrayMeses($mesesD);
                                                    /* asignamos la copia */
                                                    $prmD->mes = $mes;
                                                    $prmD->tcp_id = 'D';
                                                    /*calculamos el porcentaje*/
                                                    $prmD->importe = Math::redondear($prmD->importe * $pe->getComprasDirectas(), 2);

                                                    /*comparamos el acumulado, con el importe y si difieren, asignamos el acumulado al importe*/
                                                    if (bccomp($importeCd, $prmD->importe, 2) != 0) {
                                                        $prmD->importe = $importeCd;
                                                    }

                                                    DetalleModificacion::abc($prmD);
                                                } // Fin if
                                                /////////////////// Compras consolidadas //////////////////////////
                                                if ($pe->getComprasConsolidadas() > 0) {
                                                    $pre->setTcp_id('C');
                                                    //y si no existe la clave presupuestal, se da de alta.
                                                    if ($pre->validaClavePresupuestal() <= 0) {
                                                        //insertamos la clave presupuestal de tipo consolidada
                                                        $pre->conectar();
                                                        $pre->setSql(
                                                            'SET @usr_ip =INET_ATON(?),
                                                            @usr_id = ?,
                                                            @bit_observacion ="PRESUPUESTO REGISTRADO DESDE PANTALLA S_1_1";'
                                                        );
                                                        $parametros = [
                                                            $this->ip,
                                                            $this->usr_id,
                                                        ];
                                                        $pre->consultar($parametros);
                                                        $pre->insert(false);
                                                    }
                                                    $mesesC = $this->prm->meses;
                                                    //se actualizan los importes de compras directas
                                                    foreach ($mesesC as $k => $v) {
                                                        $mesesC[$k] = $mesesC[$k] - Math::redondear($v * $pe->getComprasDirectas(), 2);
                                                    }
                                                    /*Hacemos una copia de los parámetros recibidos*/
                                                    $prmC = $this->prm;
                                                    /*actualizamos en la copia, los importes de la modificacion*/
                                                    $mes->setArrayMeses($mesesC);
                                                    /* asignamos la copia */
                                                    $prmC->mes = $mes;
                                                    $prmC->tcp_id = 'C';
                                                    /*calculamos el importe, haciendo una resta del total - el acumulado de las compras directas*/
                                                    $prmC->importe = Math::redondear($modClave->getImporte() - $importeCd, 2);
                                                    /*Usamos el metodo estático, para guardar la parte de consolidadas*/
                                                    DetalleModificacion::abc($prmC);
                                                } // Fin if
                                            } else {
                                                $pre->setTcp_id('D');
                                                //y si no existe la clave presupuestal, se da de alta.
                                                if ($pre->validaClavePresupuestal() <= 0) {
                                                    //insertamos la clave presupuestal de tipo consolidada
                                                    $pre->conectar();
                                                    $pre->setSql(
                                                        'SET @usr_ip =INET_ATON(?),
                                                        @usr_id = ?,
                                                        @bit_observacion ="PRESUPUESTO REGISTRADO DESDE PANTALLA S_1_1";'
                                                    );
                                                    $parametros = [
                                                        $this->ip,
                                                        $this->usr_id,
                                                    ];
                                                    $pre->consultar($parametros);
                                                    $pre->insert(false);
                                                }
                                                $this->prm->tcp_id = 'D';
                                                DetalleModificacion::abc($this->prm);
                                            }
                                        } else {
                                            DetalleModificacion::abc($this->prm);
                                        }
                                        break;
                                    case 'A': //traspasos intrainstitucionales
                                    case 'C': // calendarios
                                    case 'E': // reduccion
                                        DetalleModificacion::abc($this->prm);
                                        break;
                                } // Fin switch
                            } else {
                                DetalleModificacion::abc($this->prm);
                            }
                            break;
                        case 'B': //sefin
                        case 'E': //sefin
                        case 'F': //sefin
                            DetalleModificacion::abc($this->prm);
                            break;
                    } // Fin switch

                    $clave = $modClave->getGpo_id() . $modClave->getUr_id() . $modClave->getUe_id() . $modClave->getEpg_id() . $modClave->getPg_id() . $modClave->getPe_id() . $modClave->getEcf_id() . $modClave->getTcp_id();

                    if ($this->prm->abc == 'a') {
                        $mensaje = 'Movimiento agregado con clave presupuestal:' . $clave . '';
                        $men = 'Movimiento agregado ';
                    } else {
                        $mensaje = 'Movimiento actualizado con clave presupuestal:' . $clave . '';
                        $men = 'Movimiento actualizado ';
                    }
                } else {
                    if (count($modResult) > 0) {
                        foreach ($modResult as $key => $valor) {
                            $mensajeError = $valor['numero'] . '-' . $valor['mensaje'] . ' ';
                        }
                        $this->mixScript('notify("' . $mensajeError . '","error",false);');
                    } else {
                        $this->mixScript('notify("' . $validaMod->mensaje . '","error",false);');
                    }
                }
                break;
            case 'b':
                $clave = $this->prm->gpo_id . $this->prm->ur_id . $this->prm->ue_id . $this->prm->epg_id . $this->prm->pg_id . $this->prm->pe_id . $this->prm->ecf_id;
                $clave .= (isset($this->prm->tcp_id) ? $this->prm->tcp_id : '');
                switch ($this->prm->omo_id) {
                    case 'A': //dependencia
                    case 'C': //Direccion de recursos humanos
                    case 'D': //Direccion de recursos materiales
                        switch ($this->nvj) {
                            case 1:case 2: //UR Y UE
                                switch ($mod->getEmo_id()) {
                                    case 1:case 3: //capturado o rechazado por UR
                                        DetalleModificacion::abc($this->prm);
                                        $mensaje = 'Movimiento eliminado con clave presupuestal:' . $clave . '';
                                        $men = 'Movimiento eliminado ';
                                        break;
                                    default:
                                        $this->mixScript('
                                            notify("No se puede eliminar el movimiento","error");
                                        ');
                                        break;
                                } // Fin switch
                                break;
                            default: //SEFIN
                                switch ($this->subSecretaria) {
                                    case 2: //planeación
                                        switch ($mod->getEmo_id()) {
                                            case 6:case 11:case 17: //enviado a sefin o rechazado con jefe de depto
                                                DetalleModificacion::abc($this->prm);
                                                $mensaje = 'Movimiento eliminado con clave presupuestal:' . $clave . '';
                                                $men = 'Movimiento eliminado';
                                                break;
                                            default:
                                                $this->mixScript('
                                                    notify("No se puede eliminar el movimiento","error");
                                                ');
                                                break;
                                        } // Fin switch
                                        break;
                                    case 3: //egresos
                                        switch ($mod->getEmo_id()) {
                                            case 8:case 11:case 17: //enviado a sefin o rechazado con jefe de depto
                                                DetalleModificacion::abc($this->prm);
                                                $mensaje = 'Movimiento eliminado con clave presupuestal:' . $clave . '';
                                                $men = 'Movimiento eliminado ';
                                                break;
                                            default:
                                                $this->mixScript('
                                                    notify("No se puede eliminar el movimiento","error");
                                                ');
                                                break;
                                        } // Fin switch
                                        break;
                                } //fin switch
                                break;
                        } // Fin switch
                        break;
                    case 'B': //sefin
                    case 'E': //sefin
                    case 'F': //sefin
                        switch ($this->subSecretaria) {
                            case 2: //planeación
                                //enviado a sefin o rechazado con jefe de depto
                                if ($mod->getEmo_id() == 6 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) {
                                    DetalleModificacion::abc($this->prm);
                                    $mensaje = 'Movimiento eliminado con clave presupuestal:' . $clave . '';
                                    $men = 'Movimiento eliminado';
                                } else {
                                    $this->mixScript('notify("No se puede eliminar el movimiento","error");');
                                }
                                break;
                            case 3: //egresos
                                if ($mod->getEmo_id() == 8 || $mod->getEmo_id() == 11 || $mod->getEmo_id() == 17) {
                                    DetalleModificacion::abc($this->prm);
                                    $mensaje = 'Movimiento eliminado con clave presupuestal:' . $clave . '';
                                    $men = 'Movimiento eliminado ';
                                } else {
                                    $this->mixScript('notify("No se puede eliminar el movimiento","error");');
                                }
                                break;
                        } // Fin switch
                        break;
                } // Fin switch
                break;
        } // Fin switch

        if ($mensaje != '') {
            $this->prm->observacion = $mensaje;
            $this->prm->ip = ($this->ip != false && $this->ip != '::1') ? AccesoDatos::getIP() : '127.0.0.1';
            $this->prm->emo_id = $mod->getEmo_id();
            $this->bitacora('D', $this->prm);
            $this->mixScript('notify("' . $men . '");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    enc_oop:' . $mod->getOop() . '
                    ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ');
        } // Fin if
    } // Fin abcModificacion

    /*METODO QUE ACTUALIZA EL ESTADO DEL FOLIO A 2: FOLIO REVISADO POR UR*/
    private function revisarFolio()
    {
        // ELIMINA LAS METAS MODIFICADAS
        if (isset($this->prm->borrar)) {
            $this->borrarDatosModificados();
        }
        $script = $html = $btnRegresar = '';
        $mod = new MaestroModificacion($this->prm);
        /* Instanciamos el estado de la modificacion*/
        $emod = new EstadoModificacion();
        $emod->setEmo_id(2); //Enviado a revision por Ur
        $emod->select();
        /* Inicamos la bitacora de la modificacion*/
        $prm = new stdClass();
        $prm->emo_id = $emod->getEmo_id();
        $prm->omo_id = $mod->getOmo_id();
        $prm->mod_gpo_id = $mod->getMod_gpo_id();
        $prm->mod_ur_id = $mod->getMod_ur_id();
        $prm->mod_id = $mod->getMod_id();
        $prm->tipoMovimiento = $mod->getTipoMovimiento();
        $prm->observacion = (isset($this->prm->justificacion) ?
            trim(str_replace('"', ' ', $this->prm->justificacion)) : $emod->getNombre());
        $prm->ip = ($this->ip != false && $this->ip != '::1') ?
        AccesoDatos::getIP() : '127.0.0.1';

        $datos = $this->model->getValidaFolio($mod, false, '');

        // VERIFICAMOS SI LA VALIDACIÓN NOS REGRESO ALGUN ERROR
        if (count($datos) == 0) {
            //INSERTAMOS A LA BITACORA EL CAMBIO REALIZADO
            $this->prm->emo_id = $emod->getEmo_id();
            $this->prm->observacion = $prm->observacion;
            $this->prm->oop = $mod->getOop();
            $this->prm->ecf_id = $mod->getEcf_id();
            $this->prm->tipoMovimiento = $mod->getTipoMovimiento();
            $this->prm->clave = $mod->getClave();
            $this->prm->concepto = $mod->getConcepto();
            $this->prm->fechaFolio = $mod->getFechaFolio();
            $this->prm->oficioSefin = $mod->getOficioSefin();
            $this->prm->oficioSolicitudSefin = $mod->getOficioSolicitudSefin();
            $this->prm->origen = $mod->getOrigen();
            $this->prm->Subsecretaria = $mod->getSubsecretaria();
            $this->prm->Vobo = $mod->getVobo();
            $this->prm->carga = $mod->getCarga();
            $this->prm->ip = $prm->ip;
            $this->bitacora('M', $this->prm);
            $this->prm->abc = 'c';
            //INSTANCIAMOS EL MAESTRO PARA ACTUALIZAR EL ESTADO DEL FOLIO
            MaestroModificacion::abc($this->prm);
            $script .= '
                notify("Folio ' . $mod->getMod_id() . ' revisado");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    ecf_id:' . $mod->getEcf_id() . '
                    ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
            ';
        } else {
            if (count($datos) > 1) {
                //VISTA QUE MOSTRARÁ EN FORMA DE TABLA LOS ERRORES OBTENIDOS
                $html .= $this->vista->vwMuestraTablaError($datos);
                $btnRegresar = $this->vista->vwBotones('regresar', $mod, $this->nivelRevision);
                $btnRegresar->click = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaFolios",
                        omo_id:"' . $mod->getOmo_id() . '",
                        mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                        mod_ur_id:"' . $mod->getMod_ur_id() . '",
                        mod_id:' . $mod->getMod_id() . ',
                        ecf_id:' . $mod->getEcf_id() . '
                        ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    });
                ';
                $btnRegresar = $this->ponBoton($btnRegresar);
                $this->agregaTools($btnRegresar);
            } else {
                // SI SOLO ES UN ERROR ENTONCES UNICAMENTE SE HACE UN NOTIFY
                $script .= 'notify("' . utf8Encode('Error ' . $datos[1]['numero'] . '. ' . $datos[1]['mensaje']) . '","error",false);';
            } // Fin else
        } // Fin else

        $this->mixHtml($html);
        $this->mixScript($script);
    } // Fin revisarFolio

    /*METODO QUE CAMBIA DE ESTADO EL FOLIO A PARTIR DEL ESTADO 2*/
    private function validacionFolio()
    {
        $parametros = [];
        $vobo = $notify = $men = '';
        $ban = 1;
        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        /* Instanciamos el estado de la modificacion*/
        $emod = new EstadoModificacion();
        $datos = $this->model->getValidaFolio($mod, false, '');
        $recursoPNE = isset($this->prm->recPNE) ? true : false;

        // SI NO ES UN FOLIO DE RECURSO PNE
        if (!$recursoPNE) {
            if (count($datos) == 0) {
                switch ($this->nivelRevision) {
                    case 'validacionUR':
                        $emod->setEmo_id(4);
                        $emod->select();
                        $mod->setEmo_id($emod->getEmo_id());
                        // add marcaba error en bitacoras se agregan triggers para 2020
                        $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_1 CAMBIO DE ESTADO';
                        $mod->conectar();
                        $mod->setSql(
                            '
                            SET @bit_observacion = ?,
                            @usr_ip =INET_ATON(?),
                            @usr_id = ?;'
                        );
                        $parametros = [
                            $observacion,
                            $this->ip,
                            $this->usr_id,
                        ];
                        $mod->consultar($parametros);
                        $mod->update(false);
                        $mod->desconectar();

                        $this->mixScript('
                            notify("Folio ' . $mod->getMod_id() . ' enviado a validación.");
                            comunica("' . $this->pantalla . '",{
                                metodo:"llenatablaFoliosRead",
                                mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                                mod_ur_id:"' . $this->prm->mod_ur_id . '"
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            });
                        ');
                        break;
                    case 'autorizacionUR':
                        switch ($enc_oop->getMmd_id()) {
                            case 'E':
                                switch ($mod->getSubsecretaria()) {
                                    case 1: //egresos
                                        $emod->setEmo_id(8);
                                        $men = 'Presupuesto';
                                        break;
                                    case 2: //planeación
                                        $emod->setEmo_id(6);
                                        $men = 'Planeación';
                                        break;
                                }
                                $emod->select();
                                $mod->setEmo_id($emod->getEmo_id());
                                // add marcaba error en bitacoras se agregan triggers para 2020
                                $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_1 CAMBIO DE ESTADO';
                                $mod->conectar();
                                $mod->setSql(
                                    '
                                    SET @bit_observacion = ?,
                                    @usr_ip =INET_ATON(?),
                                    @usr_id = ?;'
                                );
                                $parametros = [
                                    $observacion,
                                    $this->ip,
                                    $this->usr_id,
                                ];
                                $mod->consultar($parametros);
                                $mod->update(false);
                                $mod->desconectar();

                                $this->mixScript('
                                    notify("Folio ' . $mod->getMod_id() . ' enviado a ' . $men . '.");
                                    comunica("' . $this->pantalla . '",{
                                        metodo:"llenatablaFoliosRead",
                                        mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                                        mod_ur_id:"' . $this->prm->mod_ur_id . '"
                                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                                    });
                                '); //Enviado a revision por egresos o planeación
                                break;
                            case 'I':
                                $ban = 0;

                                $storeProcedure = new sp_AutorizarModificacion();
                                $storeProcedure->setOmo_id($mod->getOmo_id());
                                $storeProcedure->setMod_gpo_id($mod->getMod_gpo_id());
                                $storeProcedure->setMod_ur_id($mod->getMod_ur_id());
                                $storeProcedure->setMod_id($mod->getMod_id());
                                $storeProcedure->setMod_Subsecretaria(1);
                                $storeProcedure->setSis_id('SCP');
                                $storeProcedure->setEjercicio(APP_EJERCICIO);
                                $storeProcedure->setBit_observacion('SE AUTORIZA FOLIO DE ADECUACIÓN PRESUPUESTAL INTERNA DESDE PANTALLA S_1_1');
                                $r = $storeProcedure->execute();
                                $emod->setEmo_id(16); //Autorizado por la UR
                                if (!$storeProcedure->getBandera() && !is_null($storeProcedure->getBandera())) {
                                    $this->mixScript(' notify("ERROR ' . $storeProcedure->getMensaje() . '","error",false);');
                                } else {
                                    // se agrega visualizacion de pdf al autorizar
                                    $this->mixScript('
                                        notify("Folio ' . $mod->getMod_id() . ' autorizado por UR.","success",false);
                                        comunica("' . $this->pantalla . '",{
                                            metodo:"llenatablaFoliosRead",
                                            mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                                            mod_ur_id:"' . $this->prm->mod_ur_id . '"
                                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                                        });
                                    '); //Autorizado por la UR

                                    $this->mixScript('
                                        var form = $("<form>")
                                            .attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesPresupuestalesInternas.php")
                                            .attr("method", "post")
                                            .attr("target", "_blank")
                                            .append($("<input>").attr("type", "hidden").attr("name", "omo_id").attr("value", "' . $this->prm->omo_id . '"))
                                            .append($("<input>").attr("type", "hidden").attr("name", "mod_gpo_id").attr("value", ' . $this->prm->mod_gpo_id . '))
                                            .append($("<input>").attr("type", "hidden").attr("name", "mod_ur_id").attr("value", ' . $this->prm->mod_ur_id . '))
                                            .append($("<input>").attr("type", "hidden").attr("name", "mod_id").attr("value", "' . $this->prm->mod_id . '"))
                                            .append($("<input>").attr("type", "hidden").attr("name", "bandera").attr("value", "autorizar"))
                                        $("body").append(form);
                                        form.submit();
                                    ');
                                }
                                break;
                        } //Fin switch
                        break;
                    case 'revisionSEFIN':
                        if ($this->prm->valPNE) {
                            $emod->setEmo_id(12);
                            $vobo = 2;
                            $notify = 'Folio ' . $mod->getMod_id() . ' validado por SEFIN';
                        } else {
                            $emod->setEmo_id(10); //Revision por jefe de departamento
                            $vobo = 3;
                            $notify = 'Folio ' . $mod->getMod_id() . ' revisado por SEFIN';
                        }

                        $emod->select();
                        $mod->setEmo_id($emod->getEmo_id());
                        $mod->setVobo($vobo);

                        //se agrega validación para verificar si existe el pdf generado con firma
                        if ($enc_oop->getMmd_id() == 'E') {
                            $existeFirma = $this->validaFirmaAndDocument($mod->getClave());
                            if ($existeFirma == true || $this->subSecretaria == 2) {
                                //add marcaba error en bitacoras se agregan para 2020
                                $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_1 REVISION';
                                $mod->conectar();
                                $mod->setSql(
                                    '
                                    SET @bit_observacion = ?,
                                    @usr_ip =INET_ATON(?),
                                    @usr_id = ?;'
                                );
                                $parametros = [
                                    $observacion,
                                    $this->ip,
                                    $this->usr_id,
                                ];
                                $mod->consultar($parametros);
                                $mod->update(false);
                                $mod->desconectar();
                                if ($this->planeacion) {
                                    $this->actualizaEstadoEnSI($mod->getMod_id(), $emod->getEmo_id());
                                }
                            } else {
                                $notify = 'El documento no contiene Firmas o no se ha generado el PDF';
                            }
                        } //Fin getMmd_id
                        $this->mixScript('
                            notify("' . $notify . '");
                            comunica("' . $this->pantalla . '",{
                                metodo:"tablaFolios",
                                mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                                mod_ur_id:"' . $this->prm->mod_ur_id . '"
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            });
                        ');
                        break;
                    case 'validacionSEFIN':
                        $emod->setEmo_id(12); //Revision por jefe de unidad
                        $emod->select();
                        $mod->setEmo_id($emod->getEmo_id());
                        //Se actualiza para 2020 ya que marca error por los traiggers
                        $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_1 validacionSEFIN';
                        $mod->conectar();
                        $mod->setSql(
                            '
                            SET @bit_observacion = ?,
                            @usr_ip =INET_ATON(?),
                            @usr_id = ?;'
                        );
                        $parametros = [
                            $observacion,
                            $this->ip,
                            $this->usr_id,
                        ];
                        $mod->consultar($parametros);
                        $mod->update(false);
                        $mod->desconectar();
                        if ($this->planeacion) {
                            $this->actualizaEstadoEnSI($mod->getMod_id(), $emod->getEmo_id());
                        }
                        $this->mixScript('
                            notify("Folio ' . $mod->getMod_id() . ' validado por SEFIN");
                            comunica("' . $this->pantalla . '",{
                                metodo:"llenatablaFoliosRead",
                                mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                                mod_ur_id:"' . $this->prm->mod_ur_id . '"
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            });
                        ');
                        break;
                    case 'autorizacionSEFIN':
                        switch ($mod->getSubsecretaria()) {
                            case 1: //egresos
                                $emod->setEmo_id(14);
                                break;
                            case 2: //planeación
                                $emod->setEmo_id(15);
                                break;
                        } // Fin switch

                        $ban = 0;
                        $storeProcedure = new sp_AutorizarModificacion();
                        $storeProcedure->setOmo_id($mod->getOmo_id());
                        $storeProcedure->setMod_gpo_id($mod->getMod_gpo_id());
                        $storeProcedure->setMod_ur_id($mod->getMod_ur_id());
                        $storeProcedure->setMod_id($mod->getMod_id());
                        $storeProcedure->setMod_Subsecretaria($mod->getSubsecretaria());
                        $storeProcedure->setSis_id('SCP');
                        $storeProcedure->setEjercicio(APP_EJERCICIO);
                        $storeProcedure->setBit_observacion('SE AUTORIZA FOLIO DE ADECUACIÓN PRESUPUESTAL EXTERNA DESDE PANTALLA S_1_1');
                        $r = $storeProcedure->execute();

                        if (!$r->bandera && !is_null($r->bandera)) {
                            $this->mixScript(' notify("ERROR ' . $r->mensaje . '","error",false);');
                        } else {
                            if (($mod->getMod_gpo_id() == 1 && $mod->getMod_ur_id() == 15) && ($this->prm->contadorPrev > 0)) {
                                if ($mod->getSubsecretaria() == 2) {
                                    $mail = $this->email->configEmail($mod, $this->prm->cveFinan);
                                }

                                if ((isset($mail) && $mail != 'Exito')) {
                                    $this->mixScript('notify("Ocurrio un error al enviar el correo!!", "error");');
                                } else {
                                    $this->mixScript('notify("Correo enviado con exito!!", "success");');
                                }
                            } // Fin if notificaciones

                            if ($this->prm->contadorPrevP > 0) {
                                if ($mod->getSubsecretaria() == 1) {
                                    $mailP = $this->emailP->configEmailP($mod, ($this->prm->cfi_clave . ' - ' . $this->prm->cfi_nombre));
                                }
                                if ((isset($mailP) && $mailP != 'Exito')) {
                                    $this->mixScript('notify("Ocurrio un error al enviar el correo!!", "error");');
                                } else {
                                    $this->mixScript('notify("Correo enviado con exito!!", "success");');
                                }
                            }

                            if ($this->planeacion) {
                                $parametros = [
                                    $mod->getOmo_id(),
                                    $mod->getMod_gpo_id(),
                                    $mod->getMod_ur_id(),
                                    $mod->getMod_id(),
                                    $this->usr_id,
                                    'REGISTRO INSERTADO DESDE SEFIP/SCP/Adecuaciones Presupuestarias/validacionFolio - ControladorRegistroModificaciones->validacionFolio()',
                                ];
                                $conexion = new AccesoDatos();
                                $conexion->setServerDefault('siip_sefip');
                                $conexion->conectar();
                                $conexion->setSql('{call programacion.usp_RegistrarFolioAdecuacionPresupuestal (?,?,?,?,?,?)}');
                                $conexion->consultar($parametros);
                                $conexion->desconectar();
                            }
                            $mod->select();
                            $this->mixScript('
                                notify("Folio ' . $mod->getMod_id() . ' autorizado por SEFIN Operación número: ' . $mod->getOperacion() . ' \n Oficio de Sefin: ' . $mod->getOficioSefin() . ' ","success",false);
                                comunica("' . $this->pantalla . '",{
                                    metodo:"llenatablaFoliosRead",
                                    mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                                    mod_ur_id:"' . $this->prm->mod_ur_id . '"
                                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                                });
                            ');
                        } //Fin else bandera
                        break;
                } // Fin switch

                $this->prm->emo_id = $emod->getEmo_id();
                $this->prm->omo_id = $mod->getOmo_id();
                $this->prm->mod_gpo_id = $mod->getMod_gpo_id();
                $this->prm->mod_ur_id = $mod->getMod_ur_id();
                $this->prm->mod_id = $mod->getMod_id();
                $this->prm->observacion = $emod->getNombre();
                $this->prm->oop = $mod->getOop();
                $this->prm->ecf_id = $mod->getEcf_id();
                $this->prm->tipoMovimiento = $mod->getTipoMovimiento();
                $this->prm->concepto = $mod->getConcepto();
                $this->prm->clave = $mod->getClave();
                $this->prm->fechaFolio = $mod->getFechaFolio();
                $this->prm->oficioSefin = $mod->getOficioSefin();
                $this->prm->oficioSolicitudSefin = $mod->getOficioSolicitudSefin();
                $this->prm->origen = $mod->getOrigen();
                $this->prm->Subsecretaria = $mod->getSubsecretaria();
                $this->prm->Vobo = $mod->getVobo();
                $this->prm->carga = $mod->getCarga();
                $this->prm->ip = ($this->ip != false && $this->ip != '::1') ? AccesoDatos::getIP() : '127.0.0.1';

                if ($ban == 1) {
                    $this->bitacora('M', $this->prm);
                }
            } else {
                if (count($datos) > 1) {
                    $td = '';
                    foreach ($datos as $key => $valor) {
                        $td .= '<tr><td style="text-align:center">' . $valor['numero'] . '</td><td>' . $valor['mensaje'] . '</td></tr>';
                    }

                    $html = '
                        <table style="width:100%" class="table table-striped table-bordered"><tr class="warning" role="row">
                            <thead>
                            <tr>
                                <th style="text-align:center">Error</th>
                                <th style="text-align:center">Descripción</th>
                            </tr>
                            </thead>
                            <tbody>
                                ' . $td . '
                            </tbody>
                        </table>
                    ';

                    $btnregresa = new stdClass();
                    $btnregresa->id = 'btnregresa';
                    $btnregresa->icon = 'fa-solid fa-circle-left me-2';
                    $btnregresa->text = 'Regresar';
                    $btnregresa->value = 'Regresar';
                    $btnregresa->class = 'btn btn-outline-secondary me-1';
                    $btnregresa->type = 'button';

                    $this->agregaTools($this->ponBoton($btnregresa));
                    $this->mixHtml($html);
                    $this->mixScript('
                        $("#btnregresa").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"tablaFoliosRead",
                                mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                                mod_ur_id:"' . $this->prm->mod_ur_id . '"
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            });
                        })
                    ');
                } else {
                    $this->mixScript('notify("' . utf8Encode('Error ' . $datos[1]['numero'] . '. ' . $datos[1]['mensaje']) . '","error",false);');
                } //Fin else count datos >1
            } //Fin else count datos = 0
        } else {
            //visto bueno por planeación
            switch ($this->nvj) {
                case 3: //analista
                    $emod->setEmo_id(6);
                    $vobo = 1;
                    break;
                case 4: //jefe de departamento
                    $emod->setEmo_id(8);
                    $vobo = 2;
                    break;
            } //Fin switch
            $notify = 'Visto bueno del folio ' . $mod->getMod_id() . '';
            $emod->select();
            $mod->setEmo_id($emod->getEmo_id());
            $mod->setVobo($vobo);
            // add marcaba error en bitacoras se agregan triggers para 2020
            $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_1 PLANEACIÓN';
            $mod->conectar();
            $mod->setSql(
                '
                SET @bit_observacion = ?,
                @usr_ip =INET_ATON(?),
                @usr_id = ?;'
            );
            $parametros = [
                $observacion,
                $this->ip,
                $this->usr_id,
            ];
            $mod->consultar($parametros);
            $mod->update(false);
            $mod->desconectar();

            $this->mixScript('
                notify("' . $notify . '");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                    mod_ur_id:"' . $this->prm->mod_ur_id . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ');
        } //Fin else recursoPNE
    } // Fin validacionFolio

    private function validaFirmaAndDocument($clave)
    {
        $parametros = [];
        $i = date('m');

        $sql = '
            SELECT omo_id,mod_gpo_id,mod_ur_id,mod_id
            FROM ' . BD_CTRL . '.scp_ModificacionFirma
            WHERE omo_id = ? AND mod_gpo_id = ?
            AND mod_ur_id = ? AND mod_id = ?;
        ';
        $parametros = [
            $this->prm->omo_id,
            $this->prm->mod_gpo_id,
            $this->prm->mod_ur_id,
            $this->prm->mod_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();
        $numRow = $this->getNumeroRegistros();
        $directorio = '/ArchivosSefip/SCP' . APP_EJERCICIO . '/' . $this->prm->mod_gpo_id . $this->prm->mod_ur_id . '/adecuaciones/';
        if ($_SERVER['SERVER_ADDR'] == '::1' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
            $directorio = $_SERVER['DOCUMENT_ROOT'] . '/ArchivosSefip/SCP' . APP_EJERCICIO . '/' . $this->prm->mod_gpo_id . $this->prm->mod_ur_id . '/adecuaciones/';
        }
        while ($i >= 1) {
            if (file_exists($directorio . str_pad($i, 2, '0', STR_PAD_LEFT) . '/autorizacionFolio/externas/' . $clave . '.pdf') && $numRow > 0) {
                return true;
            }
            $i--;
        }
        return false;
    } // Fin function validaFirmaAndDocument

    /*METODO QUE CAMBIA DE ESTADO AL FOLIO EN EL RECHAZO*/
    private function rechazarFolio()
    {
        $parametros = [];
        $mod = new MaestroModificacion($this->prm);
        /*Instanciamos el estado de la modificacion*/
        $emod = new EstadoModificacion();
        $mensaje = true;

        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                if ($this->prm->pne == 0) {
                    $emod->setEmo_id(3);
                    $emod->select();
                    $mod->setEmo_id($emod->getEmo_id());
                    // add marcaba error en bitacoras se agregan triggers para 2020
                    $observacion = 'FOLIO RECHAZADO DESDE PANTALLA S_1_1 VALIDACION UR';
                    $mod->conectar();
                    $mod->setSql(
                        '
                        SET @bit_observacion = ?,
                        @usr_ip =INET_ATON(?),
                        @usr_id = ?;'
                    );
                    $parametros = [
                        $observacion,
                        $this->ip,
                        $this->usr_id,
                    ];
                    $mod->consultar($parametros);
                    $mod->update(false);
                    $mod->desconectar();

                    $this->mixScript('
                        notify("Folio ' . $mod->getMod_id() . ' rechazado");
                        comunica("' . $this->pantalla . '",{
                            metodo:"llenatablaFoliosRead",
                            mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                            mod_ur_id:"' . $mod->getMod_ur_id() . '",
                            omo_id:"' . $mod->getOmo_id() . '",
                            mod_id:' . $mod->getMod_id() . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        });
                    ');
                } // Fin if pne
                break;
            case 'autorizacionUR':
                $emod->setEmo_id(5);
                $emod->select();
                $mod->setEmo_id($emod->getEmo_id());
                // add marcaba error en bitacoras se agregan triggers para 2020
                $observacion = 'FOLIO RECHAZADO DESDE PANTALLA S_1_1 AUTORIZACION UR';
                $mod->conectar();
                $mod->setSql(
                    '
                    SET @bit_observacion = ?,
                    @usr_ip =INET_ATON(?),
                    @usr_id = ?;'
                );
                $parametros = [
                    $observacion,
                    $this->ip,
                    $this->usr_id,
                ];
                $mod->consultar($parametros);
                $mod->update(false);
                $mod->desconectar();

                $this->mixScript('
                    notify("Folio ' . $mod->getMod_id() . ' rechazado");
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenatablaFoliosRead",
                        mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                        mod_ur_id:"' . $mod->getMod_ur_id() . '",
                        omo_id:"' . $mod->getOmo_id() . '",
                        mod_id:' . $mod->getMod_id() . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    });
                ');
                break;
            case 'revisionSEFIN':
                $consultaFolio = new AccesoDatos();
                $consultaFolio->conectar();
                $consultaFolio->setSql(
                    '
                    SELECT tcp_id FROM scp_DetalleModificacion
                    WHERE omo_id= ?
                    and mod_gpo_id= ?
                    and mod_ur_id= ?
                    and mod_id= ? and tcp_id= ?;'
                );
                $parametros = [
                    $mod->getOmo_id(),
                    $mod->getMod_gpo_id(),
                    $mod->getMod_ur_id(),
                    $mod->getMod_id(),
                    'P',
                ];
                $consultaFolio->consultar($parametros);
                $consultaFolio->desconectar();

                if ($consultaFolio->getNumeroRegistros() > 0) {
                    $this->mixScript('
                        notify("Favor de eliminar la previsión del folio: ' . $mod->getMod_id() . '","warning");
                    ');
                    $mensaje = false;
                } // Fin if getNumeroRegistros

                switch ($mod->getSubsecretaria()) {
                    case 1: //egresos
                        $emod->setEmo_id(9);
                        break;
                    case 2: //planeación
                        if ($this->prm->pne == 1) {
                            $emod->setEmo_id(6);
                        } else {
                            $emod->setEmo_id(7);
                        }
                        break;
                } // Fin switch

                $emod->select();
                $mod->setEmo_id($emod->getEmo_id());
                $mod->setVobo(3);

                if ($mensaje) {
                    // marcaba error en bitacoras se agregan triggers EN EL EJERCICIO 2020
                    $observacion = 'FOLIO RECHAZADO DESDE PANTALLA S_1_1 REVISION SEFIN';
                    $mod->conectar();
                    $mod->setSql(
                        '
                        SET @bit_observacion = ?,
                        @usr_ip =INET_ATON(?),
                        @usr_id = ?;'
                    );
                    $parametros = [
                        $observacion,
                        $this->ip,
                        $this->usr_id,
                    ];
                    $mod->consultar($parametros);
                    $mod->update(false);
                    $mod->desconectar();

                    $this->mixScript('
                        notify("Folio ' . $mod->getMod_id() . ' rechazado");
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFolios",
                            mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                            mod_ur_id:"' . $this->prm->mod_ur_id . '",
                            omo_id:"' . $mod->getOmo_id() . '",
                            mod_id:' . $mod->getMod_id() . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        });
                    ');
                } // Fin mensaje
                break;
            case 'validacionSEFIN':
                $emod->setEmo_id(11); //Revision por jefe de departamento
                $emod->select();
                $mod->setEmo_id($emod->getEmo_id());
                // add marcaba error en bitacoras se agregan triggers para 2020
                $observacion = 'FOLIO RECHAZADO DESDE PANTALLA S_1_1 VALIDACION SEFIN';
                $mod->conectar();
                $mod->setSql(
                    '
                    SET @bit_observacion = ?,
                    @usr_ip =INET_ATON(?),
                    @usr_id = ?;'
                );
                $parametros = [
                    $observacion,
                    $this->ip,
                    $this->usr_id,
                ];
                $mod->consultar($parametros);
                $mod->update(false);
                $mod->desconectar();

                $this->mixScript('
                    notify("Folio ' . $mod->getMod_id() . ' rechazado");
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenatablaFoliosRead",
                        mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                        mod_ur_id:"' . $this->prm->mod_ur_id . '",
                        omo_id:"' . $mod->getOmo_id() . '",
                        mod_id:' . $mod->getMod_id() . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    });
                ');
                break;
            case 'autorizacionSEFIN':
                if ($this->prm->pne == 1) {
                    $emod->setEmo_id(6);
                } else {
                    $emod->setEmo_id(13);
                }
                $emod->select();
                $mod->setEmo_id($emod->getEmo_id());
                // add marcaba error en bitacoras se agregan triggers para 2020
                $observacion = 'FOLIO RECHAZADO DESDE PANTALLA S_1_1 AUTORIZACION SEFIN';
                $mod->conectar();
                $mod->setSql(
                    '
                    SET @bit_observacion = ?,
                    @usr_ip =INET_ATON(?),
                    @usr_id = ?;'
                );
                $parametros = [
                    $observacion,
                    $this->ip,
                    $this->usr_id,
                ];
                $mod->consultar($parametros);
                $mod->update(false);
                $mod->desconectar();

                $this->mixScript('
                    notify("Folio ' . $mod->getMod_id() . ' rechazado");
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenatablaFoliosRead",
                        mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                        mod_ur_id:"' . $this->prm->mod_ur_id . '",
                        omo_id:"' . $mod->getOmo_id() . '",
                        mod_id:' . $mod->getMod_id() . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    });
                ');
                break;
        } // Fin switch

        $this->prm->emo_id = $emod->getEmo_id();
        $this->prm->omo_id = $mod->getOmo_id();
        $this->prm->mod_gpo_id = $mod->getMod_gpo_id();
        $this->prm->mod_ur_id = $mod->getMod_ur_id();
        $this->prm->mod_id = $mod->getMod_id();
        $this->prm->observacion = (isset($this->prm->observacion)) ? $this->prm->observacion : $emod->getNombre();
        $this->prm->oop = $mod->getOop();
        $this->prm->ecf_id = $mod->getEcf_id();
        $this->prm->tipoMovimiento = $mod->getTipoMovimiento();
        $this->prm->concepto = $mod->getConcepto();
        $this->prm->clave = $mod->getClave();
        $this->prm->fechaFolio = $mod->getFechaFolio();
        $this->prm->oficioSefin = $mod->getOficioSefin();
        $this->prm->oficioSolicitudSefin = $mod->getOficioSolicitudSefin();
        $this->prm->origen = $mod->getOrigen();
        $this->prm->Subsecretaria = $mod->getSubsecretaria();
        $this->prm->Vobo = $mod->getVobo();
        $this->prm->carga = $mod->getCarga();
        $this->prm->ip = ($this->ip != false && $this->ip != '::1') ? AccesoDatos::getIP() : '127.0.0.1';

        if ($mensaje) {
            $this->bitacora('M', $this->prm);
        }
    } // Fin rechazarFolio

    /*METODO QUE MUESTRA LISTADO DE FOLIOS PARA VALIDAR/AUTORIZAR, ES DE SOLO LECTURA*/
    private function tablaFoliosRead()
    {
        switch ($this->nvj) { //Decide al metodo que irá al dar clic en regresar
            case 1:case 2: //UR y Ue
                if ($this->nvj == 2 && $this->perfil != false && $this->perfil == 'S' && $this->perfil == 'U') { //si es usuario de la Direccion de R.H. irá a tablaUrs
                    $metodo = 'tablaUrs';
                    $this->mixScript('$("#combour").val("' . $this->prm->mod_gpo_id . $this->prm->mod_ur_id . '").change();');
                } else {
                    $metodo = 'main';
                } // Fin else nvj and perfil
                break;
            default: //Sefin
                $metodo = 'tablaUrs';
                $this->mixScript('$("#combour").val("' . $this->prm->mod_gpo_id . $this->prm->mod_ur_id . '").change();');
                break;
        } //Fin switch

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->class = 'btn-outline-secondary';

        $tabla = new stdClass();
        $tabla->titulo = 'ADECUACIONES PRESUPUESTALES';
        $tabla->id = 'tbl-Revision';

        if ($this->nvj >= 3 || ($this->perfil != false && ($this->perfil == 'S' || $this->perfil == 'U'))) {
            $objUr = new UnidadResponsable();
            $objUr->setGpo_id($this->prm->mod_gpo_id);
            $objUr->setUr_id($this->prm->mod_ur_id);
            $objUr->select();
            $unidadResponsable = $objUr->getClave() . '-' . $objUr->getNombre();
            $tabla->tools = '
                <fieldset class="col-md-12 col-xs-12 md-4">
                    <legend>' . $unidadResponsable . '</legend>
                </fieldset>
            ';
        } // Fin nvj,perfil

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenatablaFoliosRead';
        $tabla->comunica->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '') ? $this->prm->nivelRevision : '';

        if ($this->perfil == 'S' || $this->perfil == 'U' || $this->nvj >= 3) {
            $tabla->comunica->mod_gpo_id = (isset($this->prm->mod_gpo_id) ? $this->prm->mod_gpo_id : '');
            $tabla->comunica->mod_ur_id = (isset($this->prm->mod_ur_id) ? $this->prm->mod_ur_id : '');
        } // Fin nvj,perfil

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Ver Folio';
        $th->width = '5px';
        array_push($tr, $th);

        if ($this->nvj >= 3) {
            $th = new stdClass();
            $th->texto = 'Origen';
            $th->width = '30px';
            array_push($tr, $th);
        } // Fin if nvj

        $th = new stdClass();
        $th->texto = 'Folio';
        $th->width = '10px';
        array_push($tr, $th);

        // Modalidad del folio
        $th = new stdClass();
        $th->texto = 'Modalidad';
        $th->width = '40px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Tipo de Adecuación';
        $th->width = '250px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Justificación';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Fecha';
        $th->width = '30px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave de Financiamiento';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        switch ($this->nivelRevision) {
            // COLUMNA DE VALIDACION/AUTORIZACION
            case 'validacionUR':
                $th->texto = 'Enviar a Validación';
                break;
            case 'autorizacionUR':
                switch ($this->perfil) {
                    case 'S':case 'U': // Si es de la direccion de R.H.
                        $th->texto = 'Enviar a SEFIN';
                        break;
                    default: // Si es la UR.
                        $th->texto = 'Autorizar Folio Interno/Enviar a SEFIN Folio Externo';
                        break;
                } // Fin switch
                break;
            case 'validacionSEFIN':
                $th->texto = 'Solicitar Validación SEFIN';
                break;
            case 'autorizacionSEFIN':
                $th->texto = 'Autorizar SEFIN';
                break;
        } //Fin switch

        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        switch ($this->nivelRevision) {
            // COLUMNA DE RECHAZO
            case 'validacionUR':
                $th->texto = 'Rechazar Validación';
                break;
            case 'autorizacionUR':
                switch ($this->perfil) {
                    case 'S':case 'U': // Si es de la direccion de R.H.
                        $th->texto = 'Rechazar Envio';
                        break;
                    default: // Si es la UR.
                        $th->texto = 'Rechazar Folio Interno/Rechazar Envio';
                        break;
                } // Fin switch
                break;
            case 'validacionSEFIN':
                $th->texto = 'Rechazar Validación SEFIN';
                break;
            case 'autorizacionSEFIN':
                $th->texto = 'Rechazar Autorización SEFIN';
                break;
        } // Fin switch
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Estado';
        $th->width = '120px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Detalle Folio';
        $th->width = '60px';
        array_push($tr, $th);

        $trbody = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
        $td->attr->enc_oop = 'enc_oop';
        $td->attr->ecf_id = 'ecf_id';
        $td->class = 'FolioRead btn-xs';
        array_push($trbody, $td);

        if ($this->nvj >= 3) {
            $td = new stdClass();
            $td->campoTxt = 'omo_nombre';
            array_push($trbody, $td);
        } // Fin nvj

        $td = new stdClass();
        $td->campoTxt = 'mod_id';
        array_push($trbody, $td);

        //Modalidad del folio
        $td = new stdClass();
        $td->campoTxt = 'mmd_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tmd_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mod_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mod_fechaFolio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';
        $clase = 'validacionFolio';

        switch ($this->nivelRevision) { //COLUMNA DE VALIDACION/AUTORIZACION
            case 'validacionUR':
                $td->casos->valor = 2;
                $td->casos->ands = [];
                $and = new stdClass();
                $and->campo = 'habilitado';
                $and->operador = '==';
                $and->valor = 1;
                $and->valorTipo = 'numero';
                array_push($td->casos->ands, $and);
                $td->casos->ors = [];
                $or = new stdClass();
                $or->campo = 'excenta';
                $or->operador = '==';
                $or->valor = 1;
                $or->valorTipo = 'numero';
                array_push($td->casos->ors, $or);
                $clase = 'validacionFolio';
                break;
            case 'autorizacionUR':
                $td->casos->valor = 4;
                $td->casos->ands = [];
                $and = new stdClass();
                $and->campo = 'habilitado';
                $and->operador = '==';
                $and->valor = 1;
                $and->valorTipo = 'numero';
                array_push($td->casos->ands, $and);
                $td->casos->ors = [];
                $or = new stdClass();
                $or->campo = 'excenta';
                $or->operador = '==';
                $or->valor = 1;
                $or->valorTipo = 'numero';
                array_push($td->casos->ors, $or);
                $clase = 'validacionFolio';
                break;
            case 'validacionSEFIN': //validacion de jefe de departamento
                $td->casos->valor = 10;
                $clase = 'validacionFolio';
                switch ($this->subSecretaria) {
                    case 2: //planeacion
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mod_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 2;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        break;
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mod_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 1;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        break;
                } // Fin switch
                break;
            case 'autorizacionSEFIN': //autorizacion de jefe de unidad
                $td->casos->valor = 12;
                $clase = 'validacionFolio';
                switch ($this->subSecretaria) {
                    case 2: //planeacion
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mod_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 2;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        break;
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mod_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 1;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        $td->casos->ors = [];
                        $ors = new stdClass();
                        $ors->campo = 'pne';
                        $ors->operador = '==';
                        $ors->valor = 1;
                        $ors->valorTipo = 'numero';
                        array_push($td->casos->ors, $ors);
                        break;
                } // Fin switch
                break;
        } //Fin switch

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'paper-plane';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'origenModificacion';
        $td->casos->verdadero->attr->mod_gpo_id = 'mod_gpo_id';
        $td->casos->verdadero->attr->mod_ur_id = 'mod_ur_id';
        $td->casos->verdadero->attr->mod_id = 'mod_id';
        $td->casos->verdadero->attr->mmd_id = 'mmd_id';
        $td->casos->verdadero->class = $clase . ' btn btn-outline-info btn-xs';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';

        switch ($this->nivelRevision) { //COLUMNA DE RECHAZO
            case 'validacionUR':
                $td->casos->valor = 5;
                $clase = 'rechazarFolio';
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->icono = 'remove';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->omo_id = 'origenModificacion';
                $td->casos->falso->attr->mod_gpo_id = 'mod_gpo_id';
                $td->casos->falso->attr->mod_ur_id = 'mod_ur_id';
                $td->casos->falso->attr->mod_id = 'mod_id';
                $td->casos->falso->class = $clase . ' btn-xs';
                break;
            case 'autorizacionUR':
                $td->casos->valor = '7,9';
                $clase = 'rechazarFolio';
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->icono = 'remove';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->omo_id = 'origenModificacion';
                $td->casos->falso->attr->mod_gpo_id = 'mod_gpo_id';
                $td->casos->falso->attr->mod_ur_id = 'mod_ur_id';
                $td->casos->falso->attr->mod_id = 'mod_id';
                $td->casos->falso->class = $clase . ' btn-xs';
                break;
            case 'validacionSEFIN': //rechazo jefe de departamento
                $td->casos->valor = 13;
                $clase = 'rechazarFolio';
                switch ($this->subSecretaria) {
                    case 2: //planeacion
                        $td->casos->ors = [];
                        $ors = new stdClass();
                        $ors->campo = 'mod_Subsecretaria';
                        $ors->operador = '==';
                        $ors->valor = 2;
                        $ors->valorTipo = 'numero';
                        array_push($td->casos->ors, $ors);
                        break;
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mod_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 1;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);

                        $td->casos->ors = [];
                        $ors = new stdClass();
                        $ors->campo = 'mod_Subsecretaria';
                        $ors->operador = '==';
                        $ors->valor = 1;
                        $ors->valorTipo = 'numero';
                        array_push($td->casos->ors, $ors);
                        break;
                } // Fin switch
                break;
            case 'autorizacionSEFIN': //rechazo jefe de unidad
                $td->casos->valor = 12;
                $clase = 'rechazarFolio';
                switch ($this->subSecretaria) {
                    case 2: //planeacion
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mod_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 2;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        break;
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mod_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 1;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        $td->casos->ors = [];
                        $ors = new stdClass();
                        $ors->campo = 'pne';
                        $ors->operador = '==';
                        $ors->valor = 1;
                        $ors->valorTipo = 'numero';
                        array_push($td->casos->ors, $ors);
                        break;
                }
                break;
        } // Fin switch

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'xmark';
        $td->casos->verdadero->color = 'rojo';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'origenModificacion';
        $td->casos->verdadero->attr->mod_gpo_id = 'mod_gpo_id';
        $td->casos->verdadero->attr->mod_ur_id = 'mod_ur_id';
        $td->casos->verdadero->attr->mod_id = 'mod_id';
        $td->casos->verdadero->attr->pne = 'pne';
        $td->casos->verdadero->class = $clase . ' btn-xs';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'emo_color';
        $td->color = 'emo_color';
        $td->campoTxt = 'emo_id';
        $td->lbl = 'emo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mod_id = 'mod_id';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->enc_oop = 'enc_oop';
        $td->class = 'tablaModificacionesRead btn-xs';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->agregaTools($this->ponBoton($btnRegresar));
        $this->mixHtml('
            <div class="row">
                <fieldset id="MensajeUR" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="modalOficio" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="Mensaje" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
        ');

        $this->mixScript('
            ' . $this->wrLlamaLlenaTabla('tbl-Revision') . '
            $("' . (($this->nvj == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresar").click(); e.stopPropagation(); });
            $("#btnRegresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . $metodo . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
                return false;
            });
        ');
    } // Fin tablaFoliosRead

    /*METODO QUE LLENA LA TABLA DE LISTADO DE FOLIOS PARA VALIDAR/AUTORIZAR*/
    private function llenatablaFoliosRead()
    {
        $script = $and = '';
        $endScript = $where = '';

        switch ($this->prm->nivelRevision) {
            case 'validacionUR': //Dependencia o la Direccion de recursos humanos.
                if ($this->perfil != false && $this->perfil != 'S' && $this->perfil != 'U') {
                    $where .= "
                        WHERE omo_id = 'A'
                        AND emo_id IN ( 2, 5 )
                    ";
                    $and = ' WHERE gpo_id = ' . $this->adm_grupo . ' AND ur_id = ' . $this->adm_ur . ' ';
                    $script = '
                        if($(this).attr("mmd_id")=="I"){
                            comunica("' . $this->pantalla . '",{
                                metodo:"modalOficioAut",
                                omo_id:$(this).attr("omo_id"),
                                mod_gpo_id:$(this).attr("mod_gpo_id"),
                                mod_ur_id:$(this).attr("mod_ur_id"),
                                mod_id:$(this).attr("mod_id")
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            });
                        }else{
                    ';
                    $endScript = '}';
                } else {
                    $omo = '';
                    $and = ' WHERE gpo_id = ' . $this->prm->mod_gpo_id . ' AND ur_id = ' . $this->prm->mod_ur_id . ' ';
                    switch ($this->perfil) {
                        case 'S':
                            $omo = 'C';
                            break;
                        case 'U':
                            $omo = 'D';
                            break;
                    } //fio switch
                    $where .= "
                        WHERE omo_id = '" . $omo . "'
                        AND emo_id IN ( 2, 5 )
                    ";
                } //Fin else perfil
                break;
            case 'autorizacionUR':
                if ($this->perfil != false && $this->perfil != 'S' && $this->perfil != 'U') {
                    $where .= "
                        WHERE omo_id = 'A'
                        AND emo_id IN (4,7,9)
                    ";
                    $and = ' WHERE gpo_id = ' . $this->adm_grupo . ' AND ur_id = ' . $this->adm_ur . ' ';
                    $script = '
                        if($(this).attr("mmd_id")=="I"){
                            comunica("' . $this->pantalla . '",{
                                metodo:"modalOficioAut",
                                omo_id:$(this).attr("omo_id"),
                                mod_gpo_id:$(this).attr("mod_gpo_id"),
                                mod_ur_id:$(this).attr("mod_ur_id"),
                                mod_id:$(this).attr("mod_id")
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            });
                        }else{
                    ';
                    $endScript = '}';
                } else {
                    $and = ' WHERE gpo_id = ' . $this->prm->mod_gpo_id . ' AND ur_id = ' . $this->prm->mod_ur_id . ' ';
                    $omo = '';
                    switch ($this->perfil) {
                        case 'S':
                            $omo = 'C';
                            break;
                        case 'U':
                            $omo = 'D';
                            break;
                    } //Fin switch
                    $where .= "
                        WHERE omo_id = '" . $omo . "'
                        AND emo_id IN (4,7,9)
                    ";
                } // Fin else perfil
                break;
            case 'validacionSEFIN': //estado 10 para mandarlo a autorizar y 13 si lo rechazaron
                $where .= '
                    WHERE emo_id IN (10,13) ';
                $where .= (($this->subSecretaria != false && $this->subSecretaria == 2) ? ' and (c.toa_id=2 or (c.toa_id in (1,2) and tcp_id="P" ) ) and mod_Subsecretaria=2  ' : ' and c.toa_id in (1,2) ');
                $and = (($this->subSecretaria != false && $this->subSecretaria == 2) ? ' WHERE gpo_id = ' . $this->prm->mod_gpo_id . ' AND ur_id = ' . $this->prm->mod_ur_id . ' ' : ' WHERE gpo_id = ' . $this->prm->mod_gpo_id . ' AND ur_id = ' . $this->prm->mod_ur_id . ' ');
                break;
            case 'autorizacionSEFIN': //estado 12 para mandarlo a la autorizacion final
                $where .= '
                    WHERE emo_id IN (12) ';
                $where .= (($this->subSecretaria != false && $this->subSecretaria == 2) ? ' and (c.toa_id=2 or (c.toa_id in (1,2) and tcp_id="P" ) ) and mod_Subsecretaria=2  ' : ' and c.toa_id in (1,2) ');
                $and = (($this->subSecretaria != false && $this->subSecretaria == 2) ? ' WHERE gpo_id = ' . $this->prm->mod_gpo_id . ' AND ur_id = ' . $this->prm->mod_ur_id . ' ' : ' WHERE gpo_id = ' . $this->prm->mod_gpo_id . ' AND ur_id = ' . $this->prm->mod_ur_id . ' ');
                break;
        } // Fin switch

        $fechaActual = date('Y-m-d H:i:00', time());
        $sql = "
            SELECT
                folio.*,if((activate.siFecha=1 and '" . $fechaActual . "' between activate.pum_fechaInicial and activate.pum_fechaFinal) or activate.noFecha=1 ,1,2) habilitado FROM (
                SELECT r.gpo_id as mod_gpo_id,r.ur_id as mod_ur_id, so.cff_id, om.omo_nombre, (r.mod_id*1) mod_id, r.mod_concepto, r.mod_fechaFolio, cf.cfi_clave,r.enc_oop, r.ecf_id,
                tmd_nombre, em.emo_id, em.emo_nombre, em.emo_color, op.mmd_id, mod_Subsecretaria, op.tfm_id,
                if(r.omo_id = 'C' or r.omo_id='D', 1,r.gpo_id) gpo_id,
                if(r.omo_id = 'C' or r.omo_id='D', 18,r.ur_id) ur_id,
                r.omo_id origenModificacion, r.omo_id,mod_Vobo, tm.tmd_id, if(so.cff_id='A' and r.mod_Subsecretaria=2 and r.mod_Vobo=2 and tm.tmd_id='B' ,1,0) AS pne, IF(exc.ecf_id=r.ecf_id,1,2) as excenta
            FROM (SELECT
                    d.omo_id,
                    d.mod_gpo_id AS gpo_id,
                    d.mod_ur_id AS ur_id,
                    d.mod_id,
                    m.enc_oop,
                    min(c.toa_id)  toa_id,
                    m.emo_id,
                    m.ecf_id,m.mod_concepto,m.mod_fechaFolio,m.mod_Subsecretaria,m.mod_Vobo
                FROM
                    scp_DetalleModificacion d
                INNER JOIN enc_Programatico p USING (epg_id , gpo_id , ur_id)
                INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                #LEFT JOIN ref_ObraActividadRefrendables_14_15 USING (gpo_id , ur_id , epg_id , pg_id , pe_id , ecf_id , tcp_id)
                INNER JOIN scp_MaestroModificacion m USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                $where
                GROUP BY d.omo_id , d.mod_gpo_id , d.mod_ur_id , d.mod_id
            )r
            INNER JOIN cat_EstadoModificacion em using(emo_id)
            INNER JOIN enc_OrigenOperacion op using(omo_id,enc_oop)
            INNER JOIN cat_TipoModificacion tm using(tmd_id)
            INNER JOIN cat_OrigenModificacion om using(omo_id)
            INNER JOIN enc_ClaveFinanciamiento cvf using(ecf_id)
            INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . " so using(cff_id,fte_id,sft_id,ori_id,sor_id)
            LEFT JOIN cat_ExcepcionClaveFinanciamiento exc USING(ecf_id)
            $and
            ORDER BY mod_id)folio LEFT JOIN (
                SELECT
                    gpo_id,
                    ur_id,
                    omo_id,
                    mmd_id,
                    mod_Subsecretaria,
                    tfm_id,
                    cff_id,
                    tmd_id,
                    pum_fechaInicial,
                    pum_fechaFinal,
                    if(not isnull(pum_fechaInicial) and not isnull(pum_fechaFinal) and sts_id=1,1,2) as siFecha,
                    if(isnull(pum_fechaInicial) and isnull(pum_fechaFinal) and sts_id=1,1,2) as noFecha,
                    sts_id habilitado
                FROM cat_PermisosUrModificaciones
            )activate USING(gpo_id,ur_id,omo_id,mmd_id,mod_Subsecretaria,tfm_id,cff_id,tmd_id)
            order by folio.mod_id ASC;
        ";

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Revision';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".FolioRead").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addModFolio",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    enc_oop: $(this).attr("enc_oop"),
                    ecf_id: $(this).attr("ecf_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $(".tablaModificacionesRead").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    enc_oop: $(this).attr("enc_oop")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $(".validacionFolio").click(function() {
                ' . $script . '
                    ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision == 'autorizacionSEFIN' ? '$(this).attr("disabled","disabled")' : '') . '
                    comunica("' . $this->pantalla . '",{
                        metodo:"resumenAutorizacion",
                        omo_id:$(this).attr("omo_id"),
                        mod_gpo_id:$(this).attr("mod_gpo_id"),
                        mod_ur_id:$(this).attr("mod_ur_id"),
                        mod_id:$(this).attr("mod_id")
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    });
                ' . $endScript . '
            });

            $(".rechazarFolio").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addMensajeRechazo",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    pne:' . (isset($recursoPNE) && ($recursoPNE) ? '$(this).attr("pne")' : 0) . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
                return false;
            });
        ');
    } // Fin llenatablaFoliosRead

    public function resumenAutorizacion()
    {
        $parametros = [];
        $import = '';
        $amp = 0;
        $red = 0;
        $total = 0;

        if ((isset($this->prm->nivelRevision) && $this->prm->nivelRevision == 'autorizacionSEFIN')) {
            $sql = '
                SELECT
                    mod_id,CONCAT(mod_gpo_id,mod_ur_id) as ur,toa_id,tmd_nombre,
                    omo_id,  dmo_importe importe,cfi_clave,cfi_nombre,mov_id,tmd_id,tcp_id,pre_planeacion
                FROM scp_DetalleModificacion d
                INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente pv using(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
                LEFT JOIN scp_MaestroModificacion m using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                INNER JOIN cat_PartidaEspecifica using(pg_id,pe_id)
                INNER JOIN cat_TipoMovimiento using(mov_id)
                INNER JOIN enc_ClaveFinanciamiento cf USING( ecf_id )
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cv ON (cv.cff_id=p.cff_id and cv.fte_id=p.fte_id and cv.sft_id=p.sft_id and cv.ori_id=p.ori_id and cv.sor_id=p.sor_id and cv.cfi_id=p.cfi_id and cv.cfi_anio=p.cfi_anio and cv.cfi_ejercicio2 = ' . APP_EJERCICIO . ')
                INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                LEFT JOIN enc_OrigenOperacion using(enc_oop,omo_id)
                LEFT JOIN cat_TipoModificacion using(tmd_id)
                ' . (isset($this->prm->omo_id) ? ' WHERE omo_id=:omo_id' : '') . '
                ' . (isset($this->prm->mod_gpo_id) ? ' AND mod_gpo_id=:gpo_id ' : '') . '
                ' . (isset($this->prm->mod_ur_id) ? ' AND mod_ur_id=:ur_id' : '') . '
                ' . (isset($this->prm->mod_id) ? ' AND mod_id=:mod_id' : '') . '
                ' . (($this->perfil == 'S') ? ' AND tcp_id=:tcp_id ' : '') . '
                ' . (($this->perfil == 'U') ? ' AND tcp_id=:tcp_id2 ' : '') . '
                ' . (($this->nvj <= 2) ? ' AND tcp_id != :tcp_id3 ' : '') . '
                ORDER BY
                CASE WHEN tmd_nombre IS NULL THEN 1 ELSE 0 END,
                tmd_nombre;
            ';
            if (isset($this->prm->omo_id)) {
                $parametros[':omo_id'] = $this->prm->omo_id;
            }
            if (isset($this->prm->mod_gpo_id)) {
                $parametros[':gpo_id'] = $this->prm->mod_gpo_id;
            }
            if (isset($this->prm->mod_ur_id)) {
                $parametros[':ur_id'] = $this->prm->mod_ur_id;
            }
            if (isset($this->prm->mod_id)) {
                $parametros[':mod_id'] = $this->prm->mod_id;
            }
            if ($this->perfil == 'S') {
                $parametros[':tcp_id'] = 'D';
            }
            if ($this->perfil == 'U') {
                $parametros[':tcp_id2'] = 'C';
            }
            if ($this->nvj <= 2) {
                $parametros[':tcp_id3'] = 'P';
            }
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($parametros);
            $this->desconectar();

            $tipoFol_row = '';
            $cveFinan = '';

            // se agrega variables para ver mostrar datos de ampliación o reducción
            if ($this->getNumeroRegistros() > 0) {
                $count = 1;
                $countP = 0;
                $contPrevP = 0;
                while ($row = $this->extraer()) { //recorremos los datos de la consulta
                    if ($count == 1) {
                        $tipoFol_row = $row['tmd_nombre']; //tipo de folio
                        $cveFinan = $row['cfi_clave'] . '-' . $row['cfi_nombre'];
                        $cfi = $row['cfi_clave'];
                        $clave = $row['cfi_nombre'];
                    }
                    if ($row['mov_id'] == 'A') {
                        $amp += $row['importe']; //sumamos todas los registro de ampliaciones
                    } elseif ($row['mov_id'] == 'R') {
                        $red += $row['importe']; //sumamos todas los registro de reducciones
                    } else {
                        $total += $row['importe'];
                    }
                    if (($row['pre_planeacion'] == 1 && $row['toa_id'] == 2) || ($row['pre_planeacion'] == 1 || $row['tcp_id'] == 'P')) {
                        $countP++;
                    }
                    if (($row['pre_planeacion'] == 2 && $row['tcp_id'] == 'P')) {
                        $contPrevP++;
                    }
                    $tcp_id = $row['tcp_id'];
                    $count++;
                } // Fin while
                if ($amp > 0) {
                    $import .= ' Ampliación= $' . number_format($amp, 2);
                }
                if ($red > 0) {
                    $import .= ' Reducción= $' . number_format($red, 2);
                }
                if (!$amp && !$red) {
                    $import .= ' $' . number_format($total, 2);
                } //fin reducciones o ampliaciones
            } // Fin  getNumeroRegistros

            $objUr = new UnidadResponsable();
            $objUr->setGpo_id($this->prm->mod_gpo_id);
            $objUr->setUr_id($this->prm->mod_ur_id);
            $objUr->select();
            $unidadResponsable = $objUr->getClave() . '-' . $objUr->getNombre();
            $cveFinan = str_replace('"', '\'', $cveFinan);
            $cveFinan = str_replace("\n", ' ', $cveFinan);

            $texto = 'Folio: ' . $this->prm->mod_id . '<br>' . 'UR: ' . $unidadResponsable . '<br>'
                . 'Tipo de Folio: ' . $tipoFol_row . '<br>' . 'Cve Financ.:  ' . $cveFinan . '<br>'
                . 'Total: ' . $import . '<br>'
            ; // texto que se mostrara en el bootbox.dialog

            $this->mixScript('
                alertify.confirm("AUTORIZAR FOLIO","' . preg_replace("/[\r\n|\n|\r]+/", ' ', addslashes($texto)) . '<h5>¿Desea autorizar el folio ?</h5><h5 style=\"display:inline\"></h5> ",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"validacionFolio",
                            omo_id:"' . $this->prm->omo_id . '",
                            mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                            mod_ur_id:"' . $this->prm->mod_ur_id . '",
                            mod_id:' . $this->prm->mod_id . ',
                            cfi_clave:"' . $cfi . '",
                            cfi_nombre:"' . $clave . '",
                            cveFinan: "' . $cveFinan . '"
                            ' . (isset($countP) ? ',contadorPrev:"' . $countP . '"' : '') . '
                            ' . (isset($contPrevP) ? ',contadorPrevP:"' . $contPrevP . '"' : '') . '
                            ' . (isset($tcp_id) ? ',tcp_id:"' . $tcp_id . '"' : '') . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
            if (btnAceptar) {
                btnAceptar.classList.remove("btn-success");
                btnAceptar.classList.add("btn-outline-success");
            }
                const cancela = document.querySelector(".ajs-button.btn.btn-danger");
            if (cancela) {
                cancela.classList.remove("btn-danger");
                cancela.classList.add("btn-outline-danger");
            }
            '); //fin mixScript
        } else {
            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo:"validacionFolio",
                    omo_id:"' . $this->prm->omo_id . '",
                    mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:' . $this->prm->mod_id . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ');
        } // Fin if autorizacionSEFIN
    } // Fin resumenAutorizacion

    /*MÉTODO QUE REGISTRA EN LA BITACORA DEL MAESTRO O DETALLE ENVIANDO EL PARAMETRO DEL TIPO M = MAESTRO D = DETALLE*/
    private function bitacora($prm, $tipo = '', $cargaMasiva = false)
    {
        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $sql = '';

        if ($tipo != '') {
            if (isset($prm->mod_id) && $prm->mod_id != '') {
                $prm->abc = 'a';
                if ($tipo == 'M') {
                    BitacoraMaestro::abc($prm);
                } elseif ($tipo == 'D') {
                    if (isset($this->prm->meses)) {
                        for ($i = 0; $i <= 11; $i++) {
                            $prm->$meses[$i] = $prm->meses[$i];
                        }
                    }
                    BitacoraDetalle::abc($prm);
                }
                $mod_gpo_id = $prm->mod_gpo_id;
                $mod_ur_id = $prm->mod_ur_id;
                $rutalog = 'log/Adecuaciones-' . $this->usr_id . '-' . $mod_gpo_id . $mod_ur_id . '.log';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d H-i-s');

                foreach ($prm as $field => $value) {
                    if (is_string($value)) {
                        $tempdata .= '|' . $field . ' = ' . $value;
                    } // Fin if
                } // Fin foreach
                $tempdata .= '|IPADDRESS=' . $this->ip;
                $tempdata .= '|USRID=' . $this->usr_id;
                if ($sql != '') {
                    $tempdata .= '|CONSULTA=' . $sql . '';
                } // Fin $sql
                fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
                fclose($file);
                $peso = filesize($rutalog);
                $peso = ($peso / 1024) / 1024;
                if ($peso > 8) {
                    $rutamodificada = 'log/Adecuaciones-' . $this->usr_id . '-';
                    $rutamodificada .= $mod_gpo_id . $mod_ur_id . '-' . $hoy . '.log';
                    rename($rutalog, $rutamodificada);
                    $file = fopen($rutalog, 'a');
                    fwrite($file, 'Creado:' . $hoy . PHP_EOL);
                    fclose($file);
                } // Fin $peso
            }
        } else {
            $this->mixScript('
                notify("Hubo un problema al registrar en la bitacora","error");
            ');
            return false;
        } // Fin else validacion tipo
    } // Fin bitacora

    /*METODO QUE OBTIENE EL TOTAL DEL FOLIO: REVISAR*/
    private function muestraTotalFolio($mod, $enc_oop)
    {
        $parametros = [];
        $esUR = $this->ue || $this->ur;
        $detalleFolio = '';

        if ($mod->getOmo_id() != '') {
            $sqlDetalle = '
                SELECT
                    d.dmo_importe, tm.mov_id,tm. mov_nombre
                FROM scp_DetalleModificacion d
                JOIN cat_PartidaEspecifica pe using(pe_id, pg_id)
                JOIN cat_TipoMovimiento tm using(mov_id)
                WHERE d.omo_id = ?
                AND d.mod_gpo_id = ?
                AND d.mod_ur_id = ?
                AND d.mod_id = ?
            ';
            $parametros = [
                $mod->getOmo_id(),
                $mod->getMod_gpo_id(),
                $mod->getMod_ur_id(),
                $mod->getMod_id(),
            ];

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sqlDetalle);
            $conexion->consultar($parametros);
            $conexion->desconectar();

            $ampliaciones = $reducciones = $calendario = 0.00;

            while ($row = $conexion->extraer()) {
                switch ($row['mov_id']) {
                    case 'A':
                        $ampliaciones += $row['dmo_importe'];
                        break;
                    case 'R':
                        $reducciones += $row['dmo_importe'];
                        break;
                    case 'C':
                        $calendario += $row['dmo_importe'];
                        break;
                } // Fin switch
            } // Fin while

            switch ($enc_oop->getTmd_id()) {
                case 'A': //traspaso intrainstitucional
                case 'D': //Ampliacion liq
                case 'E': //Reduccion liq
                    $detalleFolio .= '
                        <div class="d-flex flex-row">
                            <fieldset class="col-md-4 col-xs-4"></fieldset>
                            <fieldset class="col-md-2 col-xs-2">AMPLIACIONES:<strong>' . number_format($ampliaciones, 2, '.', ',') . '</strong></fieldset>
                            <fieldset class="col-md-2 col-xs-2">REDUCCIONES:<strong>' . number_format($reducciones, 2, '.', ',') . '</strong></fieldset>
                            <fieldset class="col-md-4 col-xs-4">SALDO POR DISTRIBUIR:<strong>' . number_format(Math::redondear($ampliaciones - $reducciones, 2), 2, '.', ',') . '</strong></fieldset>
                        </div>';
                    break;
                case 'B': //Traspaso interinstitucional
                    if ($esUR) {
                        if ($mod->getTipoMovimiento() == 'A') {
                            $detalleFolio .= '
                                <div class="d-flex flex-row">
                                        <fieldset class="col-md-4 col-xs-4"></fieldset>
                                        <fieldset class="col-md-2 col-xs-2"><strong></strong></fieldset>
                                        <fieldset class="col-md-2 col-xs-2"><strong></strong></fieldset>
                                        <fieldset class="col-md-4 col-xs-4">TOTAL AMPLIACIONES:<strong>' . number_format($ampliaciones, 2, '.', ',') . '</strong></fieldset>
                                </div>';
                        }
                        if ($mod->getTipoMovimiento() == 'R') {
                            $detalleFolio .= '
                                <div class="d-flex flex-row">
                                        <fieldset class="col-md-4 col-xs-4"></fieldset>
                                        <fieldset class="col-md-4 col-xs-4">TOTAL REDUCCIONES:<strong>' . number_format($reducciones, 2, '.', ',') . '</strong></fieldset>
                                        <fieldset class="col-md-2 col-xs-2"><strong></strong></fieldset>
                                </div>';
                        }
                    } else {
                        $detalleFolio .= '
                            <div class="d-flex flex-row mb-4">
                                <fieldset class="col-md-4 col-xs-4">
                                    AMPLIACIONES:<strong>' . number_format($ampliaciones, 2, '.', ',') . '</strong>
                                </fieldset>
                                <fieldset class="col-md-4 col-xs-4">
                                    REDUCCIONES:<strong>' . number_format($reducciones, 2, '.', ',') . '</strong>
                                </fieldset>
                                <fieldset class="col-md-4 col-xs-4">
                                    SALDO POR DISTRIBUIR:<strong>' . number_format(Math::redondear($ampliaciones - $reducciones, 2), 2, '.', ',') . '</strong>
                                </fieldset>
                            </div>';
                    }
                    break;
                default: //calendario
                    $detalleFolio .= '
                        <div class="row">
                            <fieldset class="col-md-4 col-xs-4">
                            </fieldset>
                            <fieldset class="col-md-4 col-xs-4">
                            </fieldset>
                            <fieldset class="col-md-4 col-xs-4">
                                Importe Total: <strong>' . number_format(Math::redondear($calendario, 2), 2, '.', ',') . '</strong>
                            </fieldset>
                        </div>
                    ';
                    break;
            } // Fin switch
            $detalleFolio .= '';
        } //fin getOmo_id
        return $detalleFolio;
        //////////fin total del folio/////////
    } // Fin muestraTotalFolio

    /*METODO QUE AGREGA MENSAJES DE RECHAZO AL RECHAZAR EL FOLIO DE ADECUACION*/
    private function addMensajeRechazo()
    {
        $this->setContenedor('Mensaje');
        $mensajeRechazo = new stdClass();
        $mensajeRechazo->id = 'mensajeRechazo';
        $mensajeRechazo->required = true;
        $mensajeRechazo->enDiv = true;
        $mensajeRechazo->maxlength = 500;
        $mensajeRechazo->width = '300px';
        $mensajeRechazo->height = '100px';

        $modal = '
            <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; padding-top: 100px;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Motivo de rechazo</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">X</button>
                        </div>
                        <div class="modal-body">
                            <form id="Mensaje">
                            ' . $this->ponTextArea($mensajeRechazo) . '
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-outline-success" id="btnGuardarMensaje" form="">Aceptar</button>
                            <button type="button" class="btn btn-outline-secondary cerrar" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog modal-lg -->
            </div>';

        $this->mixHtml($modal);
        $this->mixScript('
            $(".cerrar").click(function(){ $("#myModal").remove(); });
            $("#btnGuardarMensaje").click(function(){
                valido = true;
                $("#Mensaje").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                    if(!this.checkValidity()){
                        $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 75%; width: 100%;  padding-top: 100px; margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
                    }
                });
                $( ".formError" ).show("pulsate");
                setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
                setTimeout(function() { $( ".formError" ).remove(); }, 4500 );
                if(valido){
                    comunica("' . $this->pantalla . '",{
                        metodo: "rechazarFolio",
                        omo_id:"' . $this->prm->omo_id . '",
                        mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                        mod_ur_id:"' . $this->prm->mod_ur_id . '",
                        mod_id:' . $this->prm->mod_id . '
                        ' . (isset($this->prm->pne) ?
                        ',pne:' . $this->prm->pne . '' : '') . '
                        ,observacion:$("#mensajeRechazo").val()
                        ' . ($this->nivelRevision != '' ?
                        ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    });
                    $("#myModal").remove();
                }
            });
        ');
    } // Fin addMensajeRechazo

    /*METODO QUE MUESTRA EL HISTORIAL DEL FOLIO : CAMBIARLA A VISTA*/
    public function tablaHistorialFolio()
    {
        $body = '';
        $this->setContenedor('divContenedorGeneralModal');
        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);
        $sql = "
            SELECT
                DATE_FORMAT(MAX(b.usr_fecha),'%d/%m/%Y') fecha, time(max(b.usr_fecha)) hora,b.mod_id, MAX(em.emo_nombre) emo_nombre,
                if(b.emo_id=3 or b.emo_id=5 or b.emo_id=7 or b.emo_id=9 or b.emo_id=11 or b.emo_id=13,  trim(replace(b.bit_observacion,'\"',' ')),'') as observacion, u.usr_id,u.usr_nombre,b.emo_id,u.usr_paterno,u.usr_materno,
                MAX(b.bit_mod) bit_mod
            FROM " . BD_BITACORA . '.bit_MaestroModificacion b
            INNER JOIN cat_EstadoModificacion em using(emo_id)
            INNER JOIN scp_MaestroModificacion m using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
            INNER JOIN ' . BD_USUARIO . ".cat_Usuario u on b.usr_id=u.usr_id
            WHERE m.omo_id= ?
            AND m.mod_gpo_id= ?
            AND m.mod_ur_id= ?
            AND m.mod_id= ?
            AND b.bit_observacion not in ('MOVIMIENTO AGREGADO','MOVIMIENTO ACTUALIZADO','MOVIMIENTO ELIMINADO')
            GROUP BY mod_id,emo_id,observacion, u.usr_id,u.usr_nombre,b.emo_id,u.usr_paterno,u.usr_materno
            ORDER BY MAX(b.usr_fecha) desc, bit_mod;
        ";
        $parametros = [
            $mod->getOmo_id(),
            $mod->getMod_gpo_id(),
            $mod->getMod_ur_id(),
            $mod->getMod_id(),
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();

        while ($row = $this->extraer()) {
            $body .= '
            <tr>
                <td>' . $row['fecha'] . '</td>
                <td>' . $row['hora'] . '</td>
                <td>' . $row['emo_id'] . ' - ' . $row['emo_nombre'] . '</td>
                <td>' . $row['observacion'] . '</td>
                <td>' . $row['usr_id'] . ' - ' . $row['usr_nombre'] . ' ' . $row['usr_paterno'] . ' ' . $row['usr_materno'] . '</td>
            </tr>';
        } // Fin while

        $ur = new UnidadResponsable();
        $ur->setGpo_id($mod->getMod_gpo_id());
        $ur->setUr_id($mod->getMod_ur_id());
        $ur->select();

        $modalHistorial = '
            <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; padding-top: 100px;">
            <div class="modal-dialog modal-lg" style="width: 100% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"></h4>
                            <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <table  class="table table table-bordered table-hover">
                                    <tbody>
                                            <tr>
                                                <th width="15%">Unidad Responsable </th>
                                                <td colspan="5">' . $this->adm_grupo . $this->adm_ur . ' - ' . $ur->getNombre() . '</td>
                                            </tr>
                                            <tr>
                                                <th>Folio</th>
                                                <td width="20%">' . $mod->getMod_id() . '</td>
                                                <th width="10%">Tipo de Adecuación</th>
                                                <td width="28%">' . $tipoMod->getNombre() . '</td>
                                                <th width="15%">Modalidad de adecuación</th>
                                                <td width="28%">' . (($enc_oop->getMmd_id() == 'E') ? 'EXTERNAS' : 'INTERNAS') . '</td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <table  class="table table table-bordered table-hover">
                                    <thead>
                                        <tr class="success"><th style="text-align:center;" colspan="5" style="">Historial del folio</th></tr>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Situación</th>
                                            <th>Comentario</th>
                                            <th>Clave única</th>
                                        </tr>
                                    </thead>
                                    <tbody>' . $body . '</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cerrar" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog modal-lg -->
            </div>
        ';

        $this->mixHtml($modalHistorial);
        $this->mixScript('
            $(".cerrar").click(function(){ $("#myModal").remove(); });
        ');
    } // Fin tablaHistorialFolio

    /*METODO QUE MUESTRA LA MODAL PARA CARGAR EL OFICIO DE AUTORIZACIÓN Y REPORTE INTERNO DE LAS ADECUACIONES INTERNAS*/
    private function modalOficioAut()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $scp_Maestro = new MaestroModificacion();
        $scp_Maestro->setOmo_id($this->prm->omo_id);
        $scp_Maestro->setMod_gpo_id($this->prm->mod_gpo_id);
        $scp_Maestro->setMod_ur_id($this->prm->mod_ur_id);
        $scp_Maestro->setMod_id($this->prm->mod_id);
        $scp_Maestro->select();

        $inicioIF = $finIF = '';
        if ($this->nivelRevision != '') {
            switch ($this->nivelRevision) {
                case 'validacionUR':
                    $inicioIF = '
                        if($("#inputValida").val().trim()!="" && $("#inputValidaRep").val().trim()!=""){
                        comunica("' . $this->pantalla . '",{
                            metodo:"validacionFolio"
                            ' . ($scp_Maestro->getOmo_id() != '' ?
                            ',omo_id:"' . $scp_Maestro->getOmo_id() . '"' : '') . '
                            ' . ($scp_Maestro->getMod_gpo_id() != '' ?
                            ',mod_gpo_id:"' . $scp_Maestro->getMod_gpo_id() . '"' : '') . '
                            ' . ($scp_Maestro->getMod_ur_id() != '' ?
                            ',mod_ur_id:"' . $scp_Maestro->getMod_ur_id() . '"' : '') . '
                            ' . ($scp_Maestro->getMod_id() != '' ?
                            ',mod_id:"' . $scp_Maestro->getMod_id() . '"' : '') . '
                            ' . ($this->nivelRevision != '' ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        });
                    ';

                    $finIF = '
                        } else {
                        notify("Favor de cargar el oficio de autorización correspondiente y reporte de adecuación presupuestal interna.","warning");
                        return false;
                        }
                    ';

                    $pdfOrganigrama = '
                        <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; padding-top:100px;">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-bs-dismiss="modal" aria-label="Close">X</button>
                                        <h4 class="modal-title" id="myModalLabel">Cargar Oficio de Autorización y reporte de la Adecuación presupuestal interna</h4>
                                    </div>
                                    <div class="modal-body">
                                        <fieldset class="col-md-12 col-xs-12">
                                            <div class="control-group">
                                                <label class="control-label"> Documento Digital Oficio de autorización</label></p>
                                                <b><a id="decretoArchivo" class="ver_pdf" tabindex=-1 clave="' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '" rep="1" file="I-' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '-' . $scp_Maestro->getMod_id() . '.pdf" mod_id="' . $scp_Maestro->getMod_id() . '" target="_blank">' . $scp_Maestro->getOficioArchivo() . '</a></b>
                                                <div class="controls">
                                                <input type="file" name ="oficioAutorizacionFile" id="nombreOficioAut" required="1"
                                                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"uploadOficioAut","mod_id":' . $this->prm->mod_id . '}\'>
                                                    <input type="hidden" id="inputValida" value=""/>
                                                </div>
                                            </div>
                                            <div id="progressOrg" class="progress progress-striped">
                                                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                                    <span class="sr-only">100% Complete</span>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <div class="control-group">
                                                <label class="control-label"> Documento Digital reporte de Adecuación presupuestal interna</label></p>
                                                <b>
                                                <a id="repArchivo" class="ver_pdf" tabindex=-1 clave="' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '" rep="2" file="reporte_I-' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '-' . $scp_Maestro->getMod_id() . '.pdf" mod_id="' . $scp_Maestro->getMod_id() . '" target="_blank" >' . $scp_Maestro->getReporteInterno() . '
                                                </a>
                                                </b>
                                                <div class="controls">
                                                    <input type="file" name ="reporteInternoFile" id="nombreRepInt" required="1"
                                                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"uploadRepInterno","mod_id":' . $this->prm->mod_id . '}\'>
                                                    <input type="hidden" id="inputValidaRep" value=""/>
                                                </div>
                                            </div>
                                            <div id="progressOrg2" class="progress progress-striped">
                                                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                                    <span class="sr-only">100% Complete</span>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-outline-primary" id="btnGuardarMensaje" form="">Aceptar</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog modal-lg -->
                        </div>
                    ';
                    $this->mixHtml($pdfOrganigrama);
                    break;
                case 'autorizacionUR':
                    $inicioIF = '
                        alertify.confirm("AUTORIZAR FOLIO","¿ Desea autorizar el folio <h5 style=\"display:inline\"></h5> ?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"validacionFolio"
                                    ' . ($scp_Maestro->getOmo_id() != '' ?
                                    ',omo_id:"' . $scp_Maestro->getOmo_id() . '"' : '') . '
                                    ' . ($scp_Maestro->getMod_gpo_id() != '' ?
                                    ',mod_gpo_id:"' . $scp_Maestro->getMod_gpo_id() . '"' : '') . '
                                    ' . ($scp_Maestro->getMod_ur_id() != '' ?
                                    ',mod_ur_id:"' . $scp_Maestro->getMod_ur_id() . '"' : '') . '
                                    ' . ($scp_Maestro->getMod_id() != '' ?
                                    ',mod_id:"' . $scp_Maestro->getMod_id() . '"' : '') . '
                                    ' . ($this->nivelRevision != '' ?
                                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                                });
                            },
                            function(){}
                        ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-success");
                            btnAceptar.classList.add("btn-outline-success");
                        }
                            const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                        if (cancela) {
                            cancela.classList.remove("btn-danger");
                            cancela.classList.add("btn-outline-danger");
                        }
                    ';
                    $finIF = '';
                    $verArchivo = '
                        <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-bs-dismiss="modal" aria-label="Close">X</button>
                                        <h4 class="modal-title" id="myModalLabel">Documentos digitales cargados</h4>
                                    </div>
                                    <div class="modal-body">
                                        <fieldset class="col-md-12 col-xs-12">
                                        <legend>Oficio de autorización</legend>
                                            <a id="decretoArchivo" class="ver_pdf" clave="' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '" rep="1" file="I-' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '-' . $scp_Maestro->getMod_id() . '.pdf" mod_id="' . $scp_Maestro->getMod_id() . '" target="_blank">' . $scp_Maestro->getOficioArchivo() . '</a>
                                        </fieldset>
                                        <fieldset class="col-md-12 col-xs-12">
                                        <legend>Reporte de Adecuación interna</legend>
                                            <a id="repArchivo" class="ver_pdf" clave="' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '" rep="2" file="reporte_I-' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '-' . $scp_Maestro->getMod_id() . '.pdf" mod_id="' . $scp_Maestro->getMod_id() . '" target="_blank">' . $scp_Maestro->getReporteInterno() . '</a>
                                        </fieldset>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-outline-primary" id="btnGuardarMensaje" form="">Autorizar</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog modal-lg -->
                        </div>
                    ';
                    $this->mixHtml($verArchivo);
                    break;
            } // Fin switch
        } // Fin if nivelRevision

        $script = '
            $("#btnGuardarMensaje").click(function(){
                $(".modal-backdrop").remove();
                ' . $inicioIF . '
            $("#myModal").remove(); ' . $finIF . ' });
            $("#myModal").modal();
            $(".cerrar").click(function () {
                $("#myModal").remove();
            });

            //Se asigna el valor del nombre del archivo para que sepan que ya se encuentra guardado
            $("#inputValida").val("' . $scp_Maestro->getOficioArchivo() . '");
            $("#inputValidaRep").val("' . $scp_Maestro->getReporteInterno() . '");
            $("#nombreOficioAut").click(function(){
                $("#progressOrg .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });

            $("#nombreRepInt").click(function(){
                $("#progressOrg .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });

            $("#nombreOficioAut").fileupload({
                add: function(e, data) {
                    if(data.originalFiles[0][\'size\'] > 8388608) {
                        notify(\'El archivo \'+ data.originalFiles[0][\'name\']+\' supera el tamaño máximo permitido de 8M\',"warning");
                        return false;
                    }
                    data.submit();
                },
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#nombreOficioAut").text(validacion.nombre);
                    $("#nombreOficioAut").hide();
                    $("#decretoArchivo").text(validacion.nombre);
                    $("#inputValida").val(validacion.nombre);
                    notify(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressOrg .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                },
                success:function(data){
                    $("#progressOrg .progress-bar").css("width","0%" );
                    comunicaCallback(data.comunica);
                }
            });

            $("#nombreRepInt").fileupload({
                add: function(e, data) {
                    if(data.originalFiles[0][\'size\'] > 8388608) {
                        notify(\'El archivo \'+ data.originalFiles[0][\'name\']+\' supera el tamaño máximo permitido de 8M\',"warning");
                        return false;
                    }
                    data.submit();
                },
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#nombreRepInt").text(validacion.nombre);
                    $("#nombreRepInt").hide();
                    $("#repArchivo").text(validacion.nombre);
                    $("#inputValidaRep").val(validacion.nombre);
                    notify(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressOrg2 .progress-bar").css("width", progress + "%" ).attr("aria-valuenow",progress);
                },
                success:function(data){
                    $("#progressOrg2 .progress-bar").css("width","0%" );
                    comunicaCallback(data.comunica);
                }
            });

            $(".ver_pdf").click(function(){
                var clave = $(this).attr("clave");
                var rep   = $(this).attr("rep");
                var file  = $(this).attr("file");
                var mod_id  = $(this).attr("mod_id");
                // window.open("cc", "_blank");
                comunica("' . $this->pantalla . '",{
                    metodo:"downloadFile",
                    clave:clave,
                    rep:rep,
                    mod_id:mod_id,
                    omo_id: "' . $scp_Maestro->getOmo_id() . '",
                    file:file
                });
                return false;
            });
        ';
        $this->mixScript($script);
    } // Fin modalOficioAut

    /*METODO QUE CARGA EL OFICIO DE AUTORIZACIÓN DE LA ADECUACION INTERNA EN LA FASE DE VALIDACION*/
    public function uploadOficioAut()
    {
        $parametros = [];
        $parametrosb = [];
        $maestro = new MaestroModificacion();
        $maestro->setOmo_id('A');
        $maestro->setMod_gpo_id($this->gpo);
        $maestro->setMod_ur_id($this->ur_id);
        $maestro->setMod_id($this->prm->mod_id);
        $ur_clave = $this->gpo . $this->ur_id;
        $name = 'oficioAutorizacionFile';
        $respuesta = new stdClass();

        $allowed = ['pdf'];

        if (isset($_FILES[$name]) && $_FILES[$name]['error'] == 0) {
            $extension = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);

            if (!in_array(strtolower($extension), $allowed)) {
                $this->setStatus('error');
                $respuesta->mensaje = 'La extensión del archivo no esta permitida';
                $respuesta->valido = false;
            } else {
                $mod_id = (isset($this->prm->mod_id) && $this->prm->mod_id != '' ? $this->prm->mod_id : '');
                $nomFile = 'I-' . $ur_clave . '-' . $mod_id;
                $nomFileDb = $_FILES[$name]['name'];

                $upload = new FicheroSefip();
                $upload->setTipos(['pdf']);
                $upload->setFichero($_FILES[$name]);
                $upload->setModulo('scp');
                $upload->setfile($nomFile . '.pdf');
                $upload->setUrFichero($ur_clave);
                $upload->setAnioFichero(APP_EJERCICIO);
                $upload->setRuta('adecuaciones/' . date('m') . '/oficiosAutorizacion');
                $nomDir = $upload->getRutaMod();

                $countArchivos = 0;

                foreach (glob($nomDir . $nomFile . '*.pdf') as $nombre_fichero) {
                    $countArchivos++;
                } // Fin foreach

                $bolrename = false;
                if ($countArchivos > 0) {
                    $bolrename = rename($nomDir . $nomFile . '.pdf', $nomDir . $nomFile . '-' . $countArchivos . '.pdf');
                } // Fin countArchivos

                // Marcos: 09/marzo/2016
                // Reportan que NO se actualiza el nuevo archivo, así que después de renombrar el actual
                // intentaré borrarlo... de ésta manera la función move_upload_file llegará a donde no
                // existe el archivo

                $bolUpdate = $upload->upload();

                // agregan triggers EN EL EJERCICIO 2020
                $observacion = 'SE AGREGA OFICIO DE AUTORIZACION DESDE PANTALLA S_1_1';
                $this->conectar();
                $this->setSql(
                    '
                    SET @bit_observacion = ?,
                    @usr_ip =INET_ATON(?),
                    @usr_id = ?;'
                );
                $parametrosb = [
                    $observacion,
                    $this->ip,
                    $this->usr_id,
                ];
                $this->consultar($parametrosb);
                $this->setSql(
                    '
                    UPDATE scp_MaestroModificacion 
                    SET mod_oficioArchivo = "' . $nomFileDb . '" 
                    WHERE omo_id=? AND mod_gpo_id = ? AND mod_ur_id = ? AND mod_id = ?;'
                );
                $parametros = [
                    'A',
                    $this->adm_grupo,
                    $this->adm_ur,
                    $this->prm->mod_id,
                ];
                $this->consultar($parametros);
                $this->desconectar();

                $borrado = '';
                if ($bolrename || $bolUpdate) {
                    $this->setStatus('success');
                    $respuesta->mensaje = $borrado . ' Archivo cargado con éxito';
                    $respuesta->valido = true;
                } else {
                    $this->setStatus('warning');
                    $respuesta->mensaje = $borrado . ' Houston, tenemos un problema...';
                    $respuesta->valido = false;
                }
                $respuesta->nombre = $nomFileDb;
            } // Fin else in_array
        } else {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrio un error al cargar el archivo';
        } //Fin validacion _FILES

        $this->setValidacion($respuesta);
    } // Fin uploadOficioAut

    /*METODO QUE CARGA EL REPORTE INTERNO DE LA ADECUACIÓN INTERNA EN LA FASE DE VALIDACION*/
    public function uploadRepInterno()
    {
        $parametros = [];
        $parametrosb = [];
        $borrado = '';
        $maestro = new MaestroModificacion();
        $maestro->setOmo_id('A');
        $maestro->setMod_gpo_id($this->gpo);
        $maestro->setMod_ur_id($this->ur_id);
        $maestro->setMod_id($this->prm->mod_id);
        $ur_clave = $this->gpo . $this->ur_id;
        $name = 'reporteInternoFile';
        $respuesta = new stdClass();
        $allowed = ['pdf'];

        if (isset($_FILES[$name]) && $_FILES[$name]['error'] == 0) {
            $extension = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);

            if (!in_array(strtolower($extension), $allowed)) {
                $this->setStatus('error');
                $respuesta->mensaje = 'La extensión del archivo no esta permitida';
                $respuesta->valido = false;
            } else {
                $mod_id = (isset($this->prm->mod_id) && $this->prm->mod_id != '' ? $this->prm->mod_id : '');
                $nomFile = 'reporte_I-' . $ur_clave . '-' . $mod_id;
                $nomFileDb = $_FILES[$name]['name'];

                $upload = new FicheroSefip();
                $upload->setTipos(['pdf']);
                $upload->setFichero($_FILES[$name]);
                $upload->setModulo('scp');
                $upload->setfile('reporte_I-' . $ur_clave . '-' . $mod_id . '.pdf');
                $upload->setUrFichero($ur_clave);
                $upload->setAnioFichero(APP_EJERCICIO);
                $upload->setRuta('adecuaciones/' . date('m') . '/rep_ModificacionesInternas');
                $nomDir = $upload->getRutaMod();

                if ($nomDir === false && $upload->getError() === true) {
                    $this->setStatus('error');
                    $respuesta->mensaje = $upload->getMsg();
                }

                $countArchivos = 0;
                foreach (glob($nomDir . $nomFile . '*.pdf') as $nombre_fichero) {
                    $countArchivos++;
                } // Fin foreach

                $bolrename = false;
                if ($countArchivos > 0) {
                    $bolrename = rename($nomDir . $nomFile . '.pdf', $nomDir . $nomFile . '-' . $countArchivos . '.pdf');
                } // Fin $countArchivos

                // Marcos: 09/marzo/2016
                // Reportan que NO se actualiza el nuevo archivo, así que después de renombrar el actual
                // intentaré borrarlo... de ésta manera la función move_upload_file llegará a donde no
                // existe el archivo

                $bolUpdate = $upload->upload();

                // agregan triggers EN EL EJERCICIO 2020
                $observacion = 'SE AGREGA REPORTE INTERNO DESDE PANTALLA S_1_1';
                $this->conectar();
                $this->setSql('
                    SET @bit_observacion = ?,
                    @usr_ip =INET_ATON(?),
                    @usr_id = ?;
                ');
                $parametrosb = [
                    $observacion,
                    $this->ip,
                    $this->usr_id,
                ];
                $this->consultar($parametrosb);
                $this->setSql(
                    '
                    UPDATE scp_MaestroModificacion
                    SET mod_reporteInterno = "' . $nomFileDb . '"
                    WHERE omo_id= ? AND mod_gpo_id = ? AND mod_ur_id = ? AND mod_id = ?;'
                );
                $parametros = [
                    'A',
                    $this->adm_grupo,
                    $this->adm_ur,
                    $this->prm->mod_id,
                ];
                $this->consultar($parametros);
                $this->desconectar();

                if ($bolrename || $bolUpdate) {
                    $this->setStatus('success');
                    $respuesta->mensaje = $borrado . ' Archivo cargado con éxito';
                    $respuesta->valido = true;
                } else {
                    $this->setStatus('warning');
                    $respuesta->mensaje = $borrado . ' Houston, tenemos un problema...';
                    $respuesta->valido = false;
                } // Fin if
                $respuesta->nombre = $nomFileDb;
            } // Fin in_array
        } else {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrio un error al cargar el archivo';
        } // Fin $_FILES[$name]
        $this->setValidacion($respuesta);
    } // Fin uploadRepInterno

    public function downloadFile() /* Metodo perdido*/
    {
        $parametros = [];
        if ($this->prm->rep == 1) {
            $and = 'AND bit_observacion = "SE AGREGA OFICIO DE AUTORIZACION DESDE PANTALLA S_1_1"';
            $carpeta = 'oficiosAutorizacion';
        } else {
            $and = 'AND bit_observacion = "SE AGREGA REPORTE INTERNO DESDE PANTALLA S_1_1"';
            $carpeta = 'rep_ModificacionesInternas';
        }

        $this->conectar();
        $this->setSql(
            'Select usr_fecha
            FROM ' . BD_BITACORA . '.bit_MaestroModificacion
            WHERE omo_id= ?
            AND CONCAT(mod_gpo_id,mod_ur_id) = ?
            AND mod_id = ?
            ' . $and . '
            ORDER BY usr_fecha DESC
            LIMIT 1;'
        );
        $parametros = [
            $this->prm->omo_id,
            $this->prm->clave,
            $this->prm->mod_id,
        ];

        $this->consultar($parametros);
        $this->desconectar();
        $bit = $this->extraer();
        $mes = substr($bit['usr_fecha'], 5, 2);
        $fichero = $this->prm->file;
        $rutatemp = __DIR__ . '/../../tmps/';
        $exists = is_dir($rutatemp);
        if (!$exists) {
            mkdir($rutatemp, 0777, true);
        }

        $rutatemp .= $fichero;
        $r = new RutaArchivoSefip();
        $r->setBase('SCP');
        $r->setPath('adecuaciones/' . $mes . '/' . $carpeta);
        $r->setAnioFichero(APP_EJERCICIO);
        $r->setUrFichero($this->prm->clave);
        $ruta = $r->getRutaMod();
        $ruta .= $fichero;

        if (is_file($ruta)) {
            copy($ruta, $rutatemp);
        } else {
            echo 'no existe';
            die();
        }

        $this->mixScript('
            function ver_archivo(){
                var form = $("<form >")
                .attr("method", "get")
                .attr("action","lib/file_download.php")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "r").attr("value", "' . $fichero . '" ))
                .append($("<input>").attr("type", "hidden").attr("name", "npi").attr("value", "' . rand(1, 99999) . '" ));
                $("body").append(form);
                form.submit();
                form.remove();
            }
            $(function(){ver_archivo();});
        ');
    } // Fin dowloadArchivo

    //METODO QUE ELIMINA MASIVAMENTE LOS MOVIMIENTOS DE UN FOLIO
    private function eliminarMovimientos()
    {
        $parametros = [];
        $parametrosb = [];
        $parametrosc = [];
        $parametros2 = [];
        $parametros3 = [];
        $bit_observacion = 'ELIMINACIÓN DESDE PANTALLA S_1_1'; // ACTUALIZADO DESDE PANTALLA S_1_1
        $usr_ip = ($this->ip != false && $this->ip != '::1') ? $this->ip : '127.0.0.1';
        $usr_id = $this->usr_id;

        if (isset($this->prm->omo_id) && isset($this->prm->mod_gpo_id) && isset($this->prm->mod_ur_id) && isset($this->prm->mod_id)) {
            $mod = new MaestroModificacion($this->prm);
            $sql = '
                SELECT count(*) as numero FROM scp_DetalleModificacion
                WHERE omo_id= ?
                AND mod_gpo_id= ?
                AND mod_ur_id= ?
                AND mod_id= ?;
            ';
            $parametros = [
                $mod->getOmo_id(),
                $mod->getMod_gpo_id(),
                $mod->getMod_ur_id(),
                $mod->getMod_id(),
            ];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($parametros);
            $numeroMov = $this->extraer();
            $sql = 'SET @bit_observacion = ?,@usr_ip = INET_ATON(?),@usr_id = ?';
            $parametrosb = [
                $bit_observacion,
                $usr_ip,
                $usr_id,
            ];
            $this->setSql($sql);
            $this->consultar($parametrosb);

            $sql3 = 'UPDATE
                presupuestoVigente e
                JOIN (
                    SELECT
                        mov_id, gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id
                    FROM scp_DetalleModificacion
                    WHERE omo_id = ?
                    AND mod_gpo_id = ?
                    AND mod_ur_id = ?
                    AND mod_id = ?
                    AND tcp_id != ?
                )modificaciones using(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
                SET e.pre_mov = "" ;
            ';
            $parametros3 = [
                $mod->getOmo_id(),
                $mod->getMod_gpo_id(),
                $mod->getMod_ur_id(),
                $mod->getMod_id(),
                'P',
            ];
            $this->setSql($sql3);
            $this->consultar($parametros3);

            $sqlClaves = '
                SELECT
                    omo_id,mod_gpo_id,mod_ur_id,mod_id,gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,
                    mov_id,dmo_importe importe,dmo_ene ene,dmo_feb feb,dmo_mar mar,dmo_abr abr,dmo_may may,dmo_jun jun,dmo_jul jul,dmo_ago ago,dmo_sep sep,dmo_oct oct,dmo_nov nov,dmo_dic dic
                FROM scp_DetalleModificacion
                WHERE omo_id = ?
                AND mod_gpo_id = ?
                AND mod_ur_id = ?
                AND mod_id = ?;
            ';
            $parametrosc = [
                $mod->getOmo_id(),
                $mod->getMod_gpo_id(),
                $mod->getMod_ur_id(),
                $mod->getMod_id(),
            ];
            $this->setSql($sqlClaves);
            $this->consultar($parametrosc);

            while ($row = $this->extraer()) {
                $parametrosBitacora = (object) $row;
                $parametrosBitacora->observacion = 'MOVIMIENTO ELIMINADO DE MANERA MASIVA';
                $parametrosBitacora->ip = $usr_ip;
                $this->bitacora('F', $parametrosBitacora);
            } //FIN while

            $sql2 = '
                DELETE FROM scp_DetalleModificacion
                WHERE omo_id= ?
                AND mod_gpo_id= ?
                AND mod_ur_id= ?
                AND mod_id=?;
            ';
            $parametros2 = [
                $mod->getOmo_id(),
                $mod->getMod_gpo_id(),
                $mod->getMod_ur_id(),
                $mod->getMod_id(),
            ];
            $this->setSql($sql2);
            $this->consultar($parametros2);
            $this->desconectar();

            $this->prm->enc_oop = $mod->getOop();
            $this->mixScript(' notify("' . $numeroMov['numero'] . ' movimientos eliminados");');
            $this->tablaModificaciones();
        } //FIN IF
    } // FIN eliminarMovimientos

    //METODO QUE CARGA LA MODAL DE VERIFICAR LA PARTIDA GENERICA Y ESPECIFICA PARA VER EL TIPO DE FOLIO A ELEGIR
    private function elegirTipoFolio()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //partida generica
        $pg = new stdClass();
        $pg->id = 'pg_id';
        $pg->label = 'Partida Genérica';
        $pg->maxlenght = 3;
        //partida especifica
        $pe = new stdClass();
        $pe->id = 'pe_id';
        $pe->label = 'Partida Específica';
        $pe->maxlenght = 3;

        $buscar = new stdClass();
        $buscar->id = 'buscar';
        $buscar->icon = 'search';
        $buscar->value = ' Buscar';
        $buscar->class = 'btn btn-outline-secondary';

        $modalTipoFolio = '
            <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; padding-top: 100px;">
                <div class="modal-dialog modal-lg" style="width: 80% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Que tipo de folio elegir de acuerdo a la Partida Genérica y Específica</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-bs-dismiss="modal" aria-label="Close">X</button>
                        </div>
                        <div class="modal-body">
                            <h5 style="color:red">Introduce la partida(s) generica(s) y especifica(s) que utilizarás en el folio, de este modo podrás saber con exactitud que tipo de folio elegir.</h5>
                            <div class="row">
                                <fieldset class="col-md-4 col-xs-4">
                                    ' . $this->ponInput($pg) . '
                                </fieldset>
                                <fieldset class="col-md-4 col-xs-4">
                                    ' . $this->ponInput($pe) . '
                                </fieldset >
                                <fieldset class="col-md-4 col-xs-4"><br/>
                                    ' . $this->ponBoton($buscar) . '
                                </fieldset>
                            </div>
                            <div id="mostrarResultado" class="row">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cerrar" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog modal-lg -->
            </div>
        ';
        $this->mixHtml($modalTipoFolio);

        $this->mixScript('
            $("#myModal").modal();
            $(".cerrar").click(function () {
                $("#myModal").remove();
            });

            $("#buscar").click(function(){
                if($("#pg_id").val()!="" && $("#pe_id").val()!=""){
                    comunica("' . $this->pantalla . '",{
                        metodo:"mostrarResultadoPartida",
                        pg_id:$("#pg_id").val(),
                        pe_id:$("#pe_id").val()
                    });
                }else{
                    notify("Favor de especificar partida genérica y específica a buscar","error");
                }
            });
        ');
    } // FIN elegirTipoFolio

    //METODO PARA MOSTRAR EL TIPO DE FOLIO A ELEGIR DE ACUERDO A LA PARTIDA GENERICA Y ESPECIFICA QUE SE CONSULTO.
    private function mostrarResultadoPartida()
    {
        $parametros = [];
        $this->setContenedor('mostrarResultado');
        $texto = '';

        if (!is_null($this->prm->pg_id) && !is_null($this->prm->pe_id)) {
            $sql = '
                SELECT IF(pe_id in(394,395,396,397,398,399,411),"A",idp_id) idp_id, clg_id
                FROM cat_PartidaEspecifica
                WHERE pg_id = ?
                AND pe_id = ?;
            ';
            $parametros = [
                $this->prm->pg_id,
                $this->prm->pe_id,
            ];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($parametros);
            $this->desconectar();
            $registros = $this->getNumeroRegistros();
            $idp = $this->extraer();

            if ($registros > 0) {
                //Funcionamiento de acuerdo al ticket 379044
                switch ($idp['clg_id']) {
                    case 1: // Servicios Personales
                        $texto = ' Servicios Personales';
                        break;
                        // Gastos de Operación
                    case 2:case 3:case 4:case 5:case 6:case 9:case 10:case 11:
                        $texto = ' Gastos de Operación';
                        break;
                    case 7:case 8:
                        $texto = ' Inversión';
                        break;
                    default:
                        $texto = '';
                        break;
                } //Fin switch
            } else {
                $this->mixScript('notify("La partida(s) generica(s) y especifica(s) que deseas utilizar en el folio no existen, por favor escribe la partida(s) generica(s) y especifica(s) existente","error")');
            }


            $this->mixHtml('
                <br/>
                <fieldset class="col-md-6 col-xs-6">
                    <label style="color:black;">' . (($texto != '') ?
                'Para esta partida, deberás elegir un folio de ' : ' No existe esta partida') . $texto . '</label>
                <fieldset/>
            ');
        } // Fin if
    } // FIN mostrarResultadoPartida

    // METODO QUE AGREGA LAS FIRMAS AL REPORTE DE ADECUACION PRESUPUESTAL PERFIL SEFIN
    private function addFirma()
    {
        $parametrosj = [];
        $abc = 'a';
        $departamentos = ($this->subSecretaria == 3) ? ' AND dep_id IN(41,48,49,50) ' : ' AND dep_id IN(30,31,32,33,34,35)';
        $departamento = ($this->departamento != false) ? $this->departamento : 0;
        $subSecretaria = ($this->subSecretaria != false) ? $this->subSecretaria : 0;
        $unidad = ($this->unidad != false) ? $this->unidad : 0;
        $direccion = ($this->direccion != false) ? $this->direccion : 0;

        $btn_regresa = $this->vista->vwBotones('regresar');
        $btn_regresa->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"tablaFolios"
                ' . (isset($this->prm->ecf_id) ? ',ecf_id:"' . $this->prm->ecf_id . '"' : '') . '
                ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                ' . (isset($this->prm->mod_gpo_id) ? ',mod_gpo_id:' . $this->prm->mod_gpo_id . '' : '') . '
                ' . (isset($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                ,nivelRevision:"revisionSEFIN"
                });
            return false;
        ';

        $btn_firmar = new stdClass();
        $btn_firmar->value = 'Firmar';
        $btn_firmar->color = 'verde';
        $btn_firmar->icon = 'download me-2';
        $btn_firmar->class = 'me-1';
        $btn_firmar->id = 'btn_firmar';
        $btn_firmar->attr = new stdClass();
        $btn_firmar->attr->mmd_id = $this->prm->mmd_id;
        $btn_firmar->attr->omo_id = $this->prm->omo_id;
        $btn_firmar->attr->mod_gpo_id = $this->prm->mod_gpo_id;
        $btn_firmar->attr->mod_ur_id = $this->prm->mod_ur_id;
        $btn_firmar->attr->mod_id = $this->prm->mod_id;

        $btn_imprimir = new stdClass();
        $btn_imprimir->value = 'Generar';
        $btn_imprimir->color = 'blanco';
        $btn_imprimir->icon = 'file-pdf me-2';
        $btn_imprimir->class = 'btn-outline-danger me-1';
        $btn_imprimir->id = 'btn_reporte';
        $btn_imprimir->attr = new stdClass();
        $btn_imprimir->attr->mmd_id = $this->prm->mmd_id;
        $btn_imprimir->attr->omo_id = $this->prm->omo_id;
        $btn_imprimir->attr->mod_gpo_id = $this->prm->mod_gpo_id;
        $btn_imprimir->attr->mod_ur_id = $this->prm->mod_ur_id;
        $btn_imprimir->attr->mod_id = $this->prm->mod_id;

        $cbo_firma = new stdClass();
        $cbo_firma->id = 'usr_idfirma';
        $cbo_firma->name = 'control';
        $cbo_firma->class = 'cbofirmas';
        $cbo_firma->required = true;
        $cbo_firma->enDiv = true;
        $cbo_firma->camposVal = 'usr_id';
        $cbo_firma->agrupaPor = 'usr_id';
        $cbo_firma->campoLabelAgrupacion = 'usr_nombre';
        $cbo_firma->mensaje = 'Selecciona';
        $cbo_firma->label = 'Firma 1';
        $cbo_firma->comoAtr = 'usr_id,level';

        if ($this->subSecretaria == 3) {
            $cbo_firma->sql = '
            SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
            FROM ' . BD_USUARIO . '.cat_Usuario
            INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
            INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
            WHERE ssc_id = ' . $this->subSecretaria . '
            AND gpo_id = 1 AND ur_id=15 AND usr_id = ' . $this->usr_id . '
            AND sts_id=1;';
        } elseif ($this->subSecretaria == 2) {
            $cbo_firma->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
                FROM ' . BD_USUARIO . '.cat_Usuario
                INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
                WHERE ssc_id = ' . $this->subSecretaria . '
                AND nvj_id=3
                AND gpo_id = 1 AND ur_id=15 AND dir_id=5
                AND sts_id=1;';
        } // Fin subSecretaria
        $cbo_firma = $this->ponCombo($cbo_firma);

        $firma2 = new stdClass();
        $firma2->id = 'cbofirma2';
        $firma2->name = 'control';
        $firma2->class = 'cbofirmas';
        $firma2->required = true;
        $firma2->enDiv = true;
        $firma2->camposVal = 'usr_id';
        $firma2->agrupaPor = 'usr_id';
        $firma2->campoLabelAgrupacion = 'usr_nombre';
        $firma2->mensaje = 'Selecciona';
        $firma2->label = 'Firma 2';
        $firma2->comoAtr = 'usr_id,level';

        if ($this->subSecretaria == 3) {
            $unidadSel = ($unidad == 0) ? ' uni_id in(16,18) ' : ' uni_id = ' . $unidad;
            $departamento = ($departamento == 0) ? $departamentos : ' AND dep_id = ' . $departamento;
            $firma2->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                    uni_id,"2" AS level
                FROM ' . BD_USUARIO . '.cat_Usuario
                INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=4
                AND ssc_id=' . $this->subSecretaria . ' AND dir_id=7
                AND uni_id in(16,18)
                AND sts_id=1
            ';
            $jefeDpto = '
                SELECT usr_id
                FROM ' . BD_USUARIO . '.cat_Usuario
                INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
                WHERE gpo_id = ? AND ur_id = ? AND nvj_id= ?
                AND ssc_id= ? AND dir_id= ?
                AND ' . $unidadSel . ' ' . $departamento . '
                AND sts_id=? LIMIT 1
            ';
            $parametrosj = [
                1,
                15,
                4,
                $this->subSecretaria,
                $direccion,
                1,
            ];
            $c = new AccesoDatos();
            $c->conectar();
            $c->setSql($jefeDpto);
            $c->consultar($parametrosj);
            $d = $c->extraer();
            $c->desconectar();

            if ($c->getNumeroRegistros() > 0) {
                $firma2->selecciona = $d['usr_id'];
            } // Fin getNumeroRegistros
        } elseif ($this->subSecretaria == 2) {
            $firma2->sql = '
                SELECT
                    usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"2" AS level
                FROM ' . BD_USUARIO . '.cat_Usuario
                INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=4
                AND ssc_id=' . $this->subSecretaria . ' AND dir_id=5
                AND sts_id=1';
        } // Fin subSecretaria

        $scri = '';
        $firma2 = $this->ponCombo($firma2);

        $firma3 = new stdClass();
        $firma3->id = 'cbofirma3';
        $firma3->name = 'control';
        $firma3->class = 'cbofirmas';
        $firma3->required = true;
        $firma3->enDiv = true;
        $firma3->camposVal = 'usr_id';
        $firma3->agrupaPor = 'usr_id';
        $firma3->campoLabelAgrupacion = 'usr_nombre';
        $firma3->mensaje = 'Selecciona';
        $firma3->label = 'Firma 3';
        $firma3->comoAtr = 'usr_id,level';

        if ($this->subSecretaria == 3) {
            $firma3->sql = '
                SELECT
                    usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,"3" AS level
                FROM ' . BD_USUARIO . '.cat_Usuario
                INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 5 AND ssc_id = ' . $subSecretaria . '
                AND dir_id = ' . $direccion . '
                AND uni_id=' . $unidad . '
                AND sts_id = 1
            ';
        } elseif ($this->subSecretaria == 2) {
            $firma3->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"3" AS level
                FROM ' . BD_USUARIO . '.cat_Usuario
                INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 6
                AND ssc_id=' . $this->subSecretaria . '
                AND dir_id = 5
                AND sts_id = 1
            ';
        } // Fin subSecretaria
        $firma3 = $this->ponCombo($firma3);

        $firma4 = new stdClass();
        $firma4->id = 'cbofirma4';
        $firma4->name = 'control';
        $firma4->class = 'cbofirmas';
        $firma4->required = true;
        $firma4->enDiv = true;
        $firma4->camposVal = 'usr_id';
        $firma4->agrupaPor = 'usr_id';
        $firma4->campoLabelAgrupacion = 'usr_nombre';
        $firma4->mensaje = 'Selecciona';
        $firma4->label = 'Firma 4';
        $firma4->comoAtr = 'usr_id,level';
        $firma4->sql = '
            SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"4" AS level
            FROM ' . BD_USUARIO . '.cat_Usuario
            INNER JOIN ' . BD_CTRL . '.cat_Usuario using(usr_id)
            INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin using(ejs_id)
            WHERE gpo_id= 1 AND ur_id=15 AND ' . $this->subSecretaria . '
            AND dir_id=7 AND nvj_id=6
            AND sts_id=1;
        ';
        $scri = '';
        $firma4 = ($this->subSecretaria == 3 && $this->prm->emo_id) ? $this->ponCombo($firma4) : '';

        $scri = '$(".cbofirmas").change();';
        $html = '';
        $html .= '
            <fieldset class="col-md-12 col-xs-12 mb-4">
                <legend>ADECUACIONES PRESUPUESTALES</legend>
            </fieldset>
            <fieldset style="align:center" class="col-md-12 col-xs-12" >
                <legend>Firmas del Reporte</legend>
                <div style=" border:1px solid RGBA(99,0,0,.6); padding:4px; font-size:13px; color :#666">
                    Si no selecciona alguna de las firmas,  en la impresión no se mostrarán las firmas<br/>
                    Si se muestra la leyenda [ sin puesto ] o [ sin titulo ], de click sobre la  leyenda para editarlo ya que con esos datos aparecerá al momento de la impresión, la información que se muestra esta guardad en la base de datos.
                </div>
            </fieldset>
                <hr/>
                <fieldset class="row col-md-12 col-xs-12 mb-4">
                    <fieldset style="align:center" class="col-md-4 col-xs-4" >
                        ' . $cbo_firma . '
                    </fieldset>
                    <fieldset style="align:center" class="col-md-4 col-xs-4" >
                        ' . $firma2 . '
                    </fieldset>
                    <fieldset style="align:center" class="col-md-4 col-xs-4" >
                        ' . $firma3 . '
                    </fieldset>
                </fieldset>
                <fieldset class="col-md-12 col-xs-12 mb-4">
                    <fieldset style="align:center" class="col-md-6 col-xs-6" >
                        ' . $firma4 . '
                    </fieldset>
                </fieldset>';
        $html .= '</div>';
        $html .= '
            <div id="infofirmas"  style="align:center">
                <center><legend>Firmas del Reporte</legend> ( Seleccione un usuario de firma)
                <fieldset class="col-md-12 col-xs-12">
                    <fieldset style="align:center" class="6 col-xs-6" id="firma1">
                        ...
                    </fieldset>
                    <fieldset style="align:center" class="6 col-xs-6" id="firma2">
                        ...
                    </fieldset>
                    <fieldset style="align:center" class="6 col-xs-6" id="firma3">
                        ...
                    </fieldset>
                    <fieldset style="align:center" class="col-md-6 col-xs-6" id="firma4">
                        ...
                    </fieldset>
                </fieldset>
            </div>
        ';
        $btnRegresa = $this->ponBoton($btn_regresa);
        $btnFirma = $this->ponBoton($btn_firmar);
        $btnImprimir = $this->ponBoton($btn_imprimir);
        $this->mixHtml($html);
        $this->mixHtml('
            <form id="impresion" method="post" action="" target="_blank">
                <input type="hidden" id="omo_id" name="omo_id" value=""/>
                <input type="hidden" id="mod_gpo_id" name="mod_gpo_id" value=""/>
                <input type="hidden" id="mod_ur_id" name="mod_ur_id" value=""/>
                <input type="hidden" id="mod_id" name="mod_id" value=""/>
                <input type="hidden" id="mod_tipoMovimiento" name="mod_tipoMovimiento" value=""/>
                <input type="hidden" id="bandera" name="bandera" value=""/>
            </form>
        ');
        $this->agregaTools($btnRegresa . ' ' . $btnFirma . ' ' . $btnImprimir);
        $this->mixScript('
            $("#btn_firmar").hide();
            $(".cbofirmas").change(function(){
                if($(this).val() !="")
                    comunica("' . $this->pantalla . '",{
                        "metodo":"firmas",
                        "level":$(this).find("option:selected").attr("level"),
                        usr:$(this).val()
                    })
                    $("#btn_firmar").show();
            });
            ' . $scri . '
            $("#btn_regresa").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"main"});
            });

            $("#btn_firmar").click(function(){
                fm1 ={t:$("#t1").val(),n:$("#n1").val(),c:$("#c1").val()};
                fm2 ={t:$("#t2").val(),n:$("#n2").val(),c:$("#c2").val()};
                fm3 ={t:$("#t3").val(),n:$("#n3").val(),c:$("#c3").val()};
                fm4 ={t:$("#t4").val(),n:$("#n4").val(),c:$("#c4").val()};
                comunica("' . $this->pantalla . '",{
                    metodo:"abcFirmas",
                    abc:"' . $abc . '",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    emo_id:$(this).attr("emo_id"),
                    mmd_id:$(this).attr("mmd_id"),
                    f1:fm1,
                    f2:fm2,
                    f3:fm3,
                    f4:fm4,
                    dep:$("#usr_idfirma").find("option:selected").val(),
                },true);
            });

            $("#btn_reporte").click(function(){
                if($(this).attr("mmd_id")=="E"){
                    $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesPresupuestalesExternas.php");
                        $("#bandera").val("autorizar");
                }else{
                    $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesPresupuestalesInternas.php");
                }
                $("#omo_id").val($(this).attr("omo_id"));
                $("#mod_gpo_id").val($(this).attr("mod_gpo_id"));
                $("#mod_ur_id").val($(this).attr("mod_ur_id"));
                $("#mod_id").val($(this).attr("mod_id"));
               // $("#mod_tipoMovimiento").val($(this).attr("mod_tipoMovimiento"));
                // $("#bandera").val();
                $("#impresion").submit();
            });
        ');
    } // Fin addFirma

    /*METODO QUE MUESTRA LAS FIRMAS ACTUALES DEL REPORTE PARA PERFIL SEFIN*/
    private function firmas()
    {
        $l = $this->prm->level;
        $this->setContenedor('firma' . $this->prm->level);
        $sql = '
            SELECT usr_nombre, usr_paterno,usr_materno,usr_titulo, usr_puesto
            FROM ' . BD_USUARIO . '.cat_Usuario WHERE usr_id = ?';
        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar([$this->prm->usr]);
        $d = $c->extraer();
        $c->desconectar();
        unset($c);
        $tit = trim($d['usr_titulo']) == '' ? '[ sin titulo ]' : $d['usr_titulo'];
        $puesto = trim($d['usr_puesto']) == '' ? '[ sin puesto ]' : $d['usr_puesto'];
        $html = '<br><div class="firmaslotes" style="width:100%; "> ';
        $html .= '<input id="t' . $l . '"  type="text" value="' . $tit .
            '" style="width:19%;text-align:right; display:inline-block"/>';
        $html .= '<input id="n' . $l . '"  type="text" value="' . $d['usr_nombre'] .
            ' ' . $d['usr_paterno'] . ' ' . $d['usr_materno'] .
            '" style="width:80%;text-align:left; display:inline-block"/><br/>';
        $html .= '<input id="c' . $l . '" type="text" value="' . htmlspecialchars($puesto) .
            '" style="width:100%;"/></div>';
        $this->mixHtml($html);
    } // FIN firmas

    /*METODO QUE INSERTA LAS FIRMAS DEL REPORTE DE ADECUACION PRESUPUESTAL PERFIL SEFIN*/
    private function abcFirmas()
    {
        $parametros = [];
        $valido = true;
        if ($valido) {
            switch ($this->prm->abc) {
                case 'a':
                    //Firmantes del reporte SEFIN
                    $this->prm->firma1 = $this->strtMayus($this->prm->f1->t == '[ sin titulo ]' ?
                        $this->prm->f1->n : $this->prm->f1->t . ' ' . $this->prm->f1->n);
                    $this->prm->cargo1 = $this->strtMayus($this->prm->f1->c == '[ sin puesto ]' ?
                        ' ' : $this->prm->f1->c);

                    $this->prm->firma2 = $this->strtMayus($this->prm->f2->t == '[ sin titulo ]' ?
                        $this->prm->f2->n : $this->prm->f2->t . ' ' . $this->prm->f2->n);
                    $this->prm->cargo2 = $this->strtMayus($this->prm->f2->c == '[ sin puesto ]' ?
                        ' ' : $this->prm->f2->c);

                    $this->prm->firma3 = $this->strtMayus($this->prm->f3->t == '[ sin titulo ]' ?
                        $this->prm->f3->n : $this->prm->f3->t . ' ' . $this->prm->f3->n);
                    $this->prm->cargo3 = $this->strtMayus($this->prm->f3->c == '[ sin puesto ]' ?
                        ' ' : $this->prm->f3->c);

                    if (isset($this->prm->f4)) {
                        $this->prm->firma4 = $this->strtMayus($this->prm->f4->t == '[ sin titulo ]' ?
                        $this->prm->f4->n : $this->prm->f4->t . ' ' . $this->prm->f4->n);
                        $this->prm->cargo4 = $this->strtMayus($this->prm->f4->c == '[ sin puesto ]' ?
                        ' ' : $this->prm->f4->c);
                    } else {
                        $this->prm->firma4 = '[ sin titulo ]';
                        $this->prm->cargo4 = '[ sin puesto ]';
                    }

                    $this->prm->ip = ($this->ip != false && $this->ip != '::1') ? $this->ip : '127.0.0.1';
                    $this->prm->usr_id = $this->usr_id;

                    //Registra o actualiza las firmas
                    $sql = "
                        INSERT INTO scp_ModificacionFirma (
                            omo_id,
                            mod_gpo_id,
                            mod_ur_id,
                            mod_id,
                            mod_cargo1,
                            mod_firma1,
                            mod_cargo2,
                            mod_firma2,
                            mod_cargo3,
                            mod_firma3,
                            mod_cargo4,
                            mod_firma4,
                            mod_cargo5,
                            mod_firma5,
                            mod_cargo6,
                            mod_firma6,
                            mod_cargo7,
                            mod_firma7,
                            usr_id
                        )
                        VALUES (
                            :omo_id,
                            :gpo_id,
                            :ur_id,
                            :mod_id,
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            :cargo1,
                            :firma1,
                            :cargo2,
                            :firma2,
                            :cargo3,
                            :firma3,
                            :cargo4,
                            :firma4,
                            :usr_id
                        )
                        ON DUPLICATE KEY UPDATE
                        mod_cargo4='" . $this->prm->cargo1 . "',
                        mod_firma4='" . $this->prm->firma1 . "',
                        mod_cargo5='" . $this->prm->cargo2 . "',
                        mod_firma5='" . $this->prm->firma2 . "',
                        mod_cargo6='" . $this->prm->cargo3 . "',
                        mod_firma6='" . $this->prm->firma3 . "',
                        mod_cargo7='" . $this->prm->cargo4 . "',
                        mod_firma7='" . $this->prm->firma4 . "';
                    ";
                    $parametros = [
                        ':omo_id' => $this->prm->omo_id,
                        ':gpo_id' => $this->prm->mod_gpo_id,
                        ':ur_id'  => $this->prm->mod_ur_id,
                        ':mod_id' => $this->prm->mod_id,
                        ':cargo1' => $this->prm->cargo1,
                        ':firma1' => $this->prm->firma1,
                        ':cargo2' => $this->prm->cargo2,
                        ':firma2' => $this->prm->firma2,
                        ':cargo3' => $this->prm->cargo3,
                        ':firma3' => $this->prm->firma3,
                        ':cargo4' => $this->prm->cargo4,
                        ':firma4' => $this->prm->firma4,
                        ':usr_id' => $this->usr_id,
                    ];
                    $firmasReportes = new AccesoDatos();
                    $firmasReportes->conectar();
                    $firmasReportes->setSql($sql);
                    $firmasReportes->consultar($parametros);
                    if ($firmasReportes) {
                        $this->mixScript('notify("Se registrarón correctamente las firmas","success")');
                    } else {
                        $this->mixScript('notify("Ocurrio un error al registras las firmas, intente de nuevo","error")');
                    }
                    break;
            } //Fin switch
        } //fin $valido
    } // FIN abcFirmas

    // METODO QUE CONVIERTE EL TEXTO A MAYUSCULAS
    private function strtMayus($variable)
    {
        $variable = strtr(strtoupper($variable), 'àèìòùáéíóúçñäëïöü', 'ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ');
        return $variable;
    } // Fin strtMayus

    // METODO QUE VERIFICA EL FOLIO
    private function verificarFolio()
    {
        $parametros = [];
        $omo_id = $this->prm->omo_id;
        $mod_gpo_id = $this->prm->mod_gpo_id;
        $mod_ur_id = $this->prm->mod_ur_id;
        $mod_id = $this->prm->mod_id;

        if (!isset($this->prm->pregunta)) {
            $maestro = new MaestroModificacion($this->prm);
            $operacion = new OrigenOperacion(['enc_oop' => $maestro->getOop()]);
            if ($operacion->getTmd_id() != 'C') {
                //se consulta si el folio tiene movimientos
                $this->conectar();
                $this->setSql('
                    SELECT mod_id FROM scp_DetalleModificacion WHERE omo_id = ?
                    AND mod_gpo_id = ? AND mod_ur_id = ?
                    AND mod_id = ?;
                ');
                $parametros = [
                    $omo_id,
                    $mod_gpo_id,
                    $mod_ur_id,
                    $mod_id,
                ];
                $this->consultar($parametros);
                $this->desconectar();
                //Si el folio tiene movimientos entonces se procede a preguntar si modifica metas
                if ($this->getNumeroRegistros() > 0) {
                    $this->mixScript('
                        $("#modalPregunta").html(
                            $("<div/>").css({"display":"block","padding-top":"100px"}).attr("tabindex","-1").addClass("modal").attr("id","myModal1")
                                .html(
                                    $("<div/>").addClass("modal-dialog")
                                        .html(
                                            $("<div/>").addClass("modal-content")
                                                .html(
                                                    $("<div/>").addClass("modal-header")
                                                        .html( $("<button/>").addClass("btn-close cerrar btn btn-xs btn-outline").attr("type","button").text("").click(function(){ $("#myModal1").remove(); return false;}) )
                                                        .append( $("<h4/>").addClass("modal-title").text("") )
                                                ).append(
                                                    $("<div/>").addClass("modal-body")
                                                        .html( $("<div/>").addClass("from-group")
                                                            .html( $("<h4/>").addClass("modal-title").text(String.fromCharCode(191)+" El folio capturado modifica metas ?") )
                                                        )
                                                ).append( $("<div/>").addClass("modal-footer")
                                                    .html( $("<button/>").attr("id","si").addClass("btn btn-outline-success").text("Si")
                                                        .click(function(){
                                                            $("#myModal1").remove();
                                                            comunica("' . $this->pantalla . '",{
                                                                metodo:"modificarMetas"
                                                                ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                                                                ' . (isset($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                                                                ' . (isset($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                                                                ' . (isset($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                                                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                                                            });
                                                            return false;
                                                        })
                                                    )
                                                    .append( $("<button/>").addClass("btn btn-outline-secondary").text("No").click(function(){
                                                        $("#myModal1").remove();
                                                        $("#justificacionMetas").html(
                                                            $("<div/>").css({"display":"block","padding-top":"100px"}).attr("tabindex","-1").addClass("modal").attr("id","myModal2")
                                                                .html(
                                                                    $("<div/>").addClass("modal-dialog")
                                                                        .html(
                                                                            $("<div/>").addClass("modal-content")
                                                                                .html(
                                                                                    $("<div/>").addClass("modal-header")
                                                                                    .append(
                                                                                        $("<h4/>").addClass("modal-title").text("Agregar justificación")
                                                                                    )
                                                                                    .append(
                                                                                        $("<button/>")
                                                                                            .addClass("close cerrar btn btn-xs btn-outline-secondary")
                                                                                            .attr("type", "button")
                                                                                            .text("X")
                                                                                            .click(function() {
                                                                                                $("#myModal2").remove();
                                                                                                return false;
                                                                                            })
                                                                                        )
                                                                                    ).append(
                                                                                        $("<div/>").addClass("modal-body")
                                                                                        .html( $("<div/>").addClass("from-group")
                                                                                            .html( $("<textarea/>").attr("name","justificacion").attr("id","justificacion").attr("maxlength","400").attr("required","1").addClass("form-control") )
                                                                                        )
                                                                                ).append( $("<div/>").addClass("modal-footer")
                                                                                    .html( $("<button/>").attr("id","btnGuardarObs").addClass("btn btn-outline-success").text("Aceptar")
                                                                                        .click(function(){
                                                                                            if($("#justificacion").val()!=""){
                                                                                                comunica("' . $this->pantalla . '",{
                                                                                                    metodo:"revisarFolio",
                                                                                                    justificacion:$("#justificacion").val(),
                                                                                                    borrar:true
                                                                                                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                                                                                                    ' . (isset($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                                                                                                    ' . (isset($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                                                                                                    ' . (isset($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                                                                                                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                                                                                                });
                                                                                                $("#myModal2").remove();
                                                                                            }else{
                                                                                                alert("Agregar justificación");
                                                                                            }
                                                                                            return false;
                                                                                        })
                                                                                    )
                                                                                    .append( $("<button/>").addClass("btn btn-outline-secondary").text("Cancelar").click(function(){ $("#myModal2").remove(); return false;}) )
                                                                                )
                                                                        )
                                                                )
                                                        );
                                                    })
                                                )
                                            )
                                        )
                                )
                        );
                    ');
                } else {
                    $this->mixScript('notify("El folio no tiene movimientos","warning");');
                } //Fin getNumeroRegistros
            } else {
                $this->revisarFolio();
            } //Fin getTmd_id
        } else {
            $this->conectar();
            $this->setSql('
                SELECT * FROM ' . BD_TEMPORAL . '.tem_ModificacionMetas
                WHERE omo_id = ?
                AND mod_gpo_id = ?
                AND mod_ur_id = ?
                AND mod_id = ?
            ');
            $parametros = [
                $omo_id,
                $mod_gpo_id,
                $mod_ur_id,
                $mod_id,
            ];
            $this->consultar($parametros);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                return true;
            } else {
                return false;
            } // FIN getNumeroRegistros
        } // Fin pregunta
    } // Fin verificarFolio

    private function modificarMetas()
    {
        $parametros = [];
        $lectura = (!isset($this->prm->lectura) ? false : true);
        $omo_id = $this->prm->omo_id;
        $mod_gpo_id = $this->prm->mod_gpo_id;
        $mod_ur_id = $this->prm->mod_ur_id;
        $mod_id = $this->prm->mod_id;

        $maestro = new MaestroModificacion($this->prm);

        if (!$lectura) {
            $regresar = new stdClass();
            $regresar->id = 'regresar';
            $regresar->class = 'btn btn-outline-secondary';
            $regresar->value = 'Cancelar';
            $regresar->icon = 'circle-left me-2';
            $this->agregaTools($this->ponBoton($regresar));
        } else {
            $this->setContenedor('metas');
        } // Fin lectura
        $this->conectar();
        $this->setSql('
            SELECT p.epg_id,o.prg_id,o.spr_id,o.py_id,o.oa_id,o.oa_nombre
            FROM scp_MaestroModificacion m
            INNER JOIN scp_DetalleModificacion d USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
            INNER JOIN enc_Programatico p USING(gpo_id,ur_id,epg_id)
            INNER JOIN cat_ObraActividad o USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
            ' . (($lectura) ? 'INNER JOIN ' . BD_TEMPORAL . '.tem_ModificacionMetas USING(omo_id,mod_gpo_id,mod_ur_id,mod_id,epg_id)' : '') . '
            WHERE m.omo_id = ? AND m.mod_gpo_id = ? AND m.mod_ur_id = ? AND m.mod_id = ?
            GROUP BY p.epg_id;
        ');
        $parametros = [
            $omo_id,
            $mod_gpo_id,
            $mod_ur_id,
            $mod_id,
        ];
        $this->consultar($parametros);
        $this->desconectar();

        $htm = '';
        $htm .= '<table  class="table table table-bordered table-hover">';
        $htm .= '
            <tr>
                <th class="warning" style="text-align:center" colspan="1">Programa</th>
                <th class="warning" style="text-align:center" colspan="1">Subprograma</th>
                <th class="warning" style="text-align:center" colspan="1">Proyecto</th>
                <th class="warning" style="text-align:center" colspan="1">Obra/Actividad</th>
                <th class="warning" style="text-align:center" colspan="8">Nombre</th>
                <th class="warning" style="text-align:center" colspan="1">Ver metas</th>
            </tr>
        ';

        $cambioClase = true;
        while ($row = $this->extraer()) {
            $clase = ($cambioClase) ? 'class="warning"' : '';
            $htm .= '
                <tr>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="8">' . $row['oa_nombre'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button" class="verMetas btn btn-outline-secondary" id="verMetas" icon="eye" title="Opción para ver metas de la actividad" epg_id=' . $row['epg_id'] . '>
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr class="warning">
                        <th></th>
                    <th colspan = "12" class ="divMetas" id="oa' . $row['epg_id'] . '"></th>
                </tr>
            ';
            $cambioClase = ($cambioClase) ? false : true;
        }
        $htm .= '</table>';
        $this->mixHtml($htm);

        $this->mixScript('
            $(".verMetas").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"listaMetas",
                    omo_id:"' . $omo_id . '",
                    mod_gpo_id:"' . $mod_gpo_id . '",
                    mod_ur_id:"' . $mod_ur_id . '",
                    mod_id:' . $mod_id . ',
                    epg_id:$(this).attr("epg_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    ' . ((!$lectura) ? ',lectura:false' : ',lectura:true') . '
                });
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    ecf_id:"' . $maestro->getEcf_id() . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            });
        ');
    } // FIN modificarMetas

    // METODO QUE ENLISTARA LAS METAS POR ACTIVIDAD PROGRAMÁTICA
    private function listaMetas()
    {
        $parametros = [];
        $this->setContenedor('oa' . $this->prm->epg_id);
        $m = new MaestroModificacion($this->prm);
        $lectura = $this->prm->lectura;
        $consulta = ($lectura) ? ' INNER JOIN ' : ' LEFT JOIN ';
        $cambioClase = '';
        //VALIDA SI ES LECTURA
        if (!$lectura) {
            //BOTON CANCELAR, CANCELA LA MODIFICACION DE METAS
            $regresar = new stdClass();
            $regresar->id = 'regresar';
            $regresar->value = 'Cancelar';
            $regresar->class = 'btn btn-outline-secondary me-1';
            $regresar->icon = 'circle-left me-2';
            //BOTON FINALIZA LA MODIFICACION DE METAS
            $endMetas = new stdClass();
            $endMetas->id = 'endMetas';
            $endMetas->value = 'Finalizar modificación';
            $endMetas->class = 'btn btn-outline-success';
            $endMetas->icon = 'check me-2';
            //AGREGA LOS BOTONES A LA BARRA DE HERRAMIENTAS
            $this->agregaTools($this->ponBoton($regresar) . ' ' . $this->ponBoton($endMetas));
        } // Fin lectura

        //CONSTRUYE LA CONSULTA PARA LAS METAS DE LA ACTIVIDAD
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre, pdi_mir,
            pdi_nombre,ctInd.umv_id,ctInd.umv_nombre, umi_nombre, tiv_nombre,
            cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
            mac_descripcion,epg_id,mac_original,mac_modificado vigente, macm_id
            FROM enc_Programatico enc
            JOIN cat_MetaActividad ma USING(epg_id)
            ' . $consulta . '  (
                SELECT
                    omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id,
                    CASE WHEN tri_id =1 THEN
                    mact_modificado
                    ELSE 0.00 END AS mod_trimestre1,
                    CASE WHEN tri_id =2 THEN
                    mact_modificado
                    ELSE 0.00 END AS mod_trimestre2,
                    CASE WHEN tri_id =3 THEN
                    mact_modificado
                    ELSE 0.00 END AS mod_trimestre3,
                    CASE WHEN tri_id =4 THEN
                    mact_modificado
                    ELSE 0.00 END AS mod_trimestre4
                FROM ' . BD_TEMPORAL . '.tem_ModificacionMetas
                WHERE  epg_id = :epg_id AND omo_id= :omo_id
                and mod_gpo_id= :gpo_id and mod_ur_id= :ur_id
                and mod_id=:mod_id
                GROUP BY omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id,tri_id,mact_modificado
            ) b using (pii_id,epg_id)
            JOIN cat_Subprograma cs USING(gpo_id,ur_id,prg_id,spr_id)
            JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            JOIN cat_Indicador i USING(ind_id,nim_id)
            JOIN cat_ProductoIntermedio USING(pdi_id)
            JOIN cat_UnidadMedidaVariable ctInd USING(umv_id)
            JOIN cat_UnidadMedidaIndicador USING(umi_id)
            JOIN cat_TipoValor tv ON tv.tiv_id = i.tiv_id 
            WHERE epg_id = :epg_id
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
        $parametros = [
            ':epg_id' => $this->prm->epg_id,
            ':omo_id' => $this->prm->omo_id,
            ':gpo_id' => $this->prm->mod_gpo_id,
            ':ur_id'  => $this->prm->mod_ur_id,
            ':mod_id' => $this->prm->mod_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();

        //ENCABEZADO DE LA TABLA DE LAS METAS DE LA ACTIVIDAD
        $htm = '';
        $htm .= '<table  class="table table table-bordered table-hover">';
        $htm .= '
            <tr>
                <th class="warning" style="text-align:center" colspan="2">Actividad MIR</th>
                <th class="warning" style="text-align:center" colspan="2">Indicador</th>
                <th class="warning" style="text-align:center" colspan="1">Ver</th>
            </tr>
        ';
        while ($row = $this->extraer()) {
            $clase = ($cambioClase) ? 'class="warning"' : '';
            //INFORMACIÓN DE METAS
            $htm .= '
                <tr>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['pdi_id'] . ' - ' . $row['pdi_mir'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['ind_id'] . ' - ' . $row['ind_nombre'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="1" >
                        <button type="button" id="verMetasDet" icon="eye-open" title="Opción para ver metas de la actividad" class="verMetasDet btn btn-outline-secondary" epg_id=' . $row['epg_id'] . ' pii_id = ' . $row['pii_id'] . ' macm_id=' . $row['macm_id'] . '>
                            <i class="fa -regular fa-eye"></i>
                        </button>
                    </td>
                    </tr>
                    <tr>
                        <th class="warning" style="text-align:center" colspan="2">Unidad Medida Variable</th>
                        <th class="warning" style="text-align:center" colspan="2">Descripción</th>
                    </tr>
                    <tr>
                        <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['umv_nombre'] . '</td>
                        <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['ind_definicion'] . '</td>
                    </tr>
                    <tr>
                        <th class="warning" style="text-align:center" colspan="2">Unidad Medida Indicador</th>
                        <th class="warning" style="text-align:center" colspan="2">Tipo Valor</th>
                    </tr>
                    <tr>
                        <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['umi_nombre'] . '</td>
                        <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['tiv_nombre'] . '</td>
                    </tr>
                    <tr class="warning">
                        <th colspan = "12" class ="divMetasDet" id="oa' . $row['epg_id'] . $row['pii_id'] . '"></th>
                    </tr>

            ';
            $cambioClase = ($cambioClase) ? false : true;
        } // Fin while

        $htm .= '</table>';
        $this->mixHtml($htm);
        $this->mixScript('
            if($("#endMetas").is(":disabled"))
                $("#endMetas").attr("disabled","disabled");

            $("#endMetas").click(function(){
                alertify.confirm("FINALIZAR MODIFICACIÓN DE METAS","¿ Confirma que desea finalizar la modificación de metas ?. <p>Esta acción modificará las metas temporalmente, viendose los cambios reflejados cuando el folio se autorice</p>",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "revisarFolio"
                            ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                            ' . (isset($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                            ' . (isset($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                            ' . (isset($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-success");
                    btnAceptar.classList.add("btn-outline-success");
                }
                    const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                if (cancela) {
                    cancela.classList.remove("btn-danger");
                    cancela.classList.add("btn-outline-danger");
                }
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"borrarDatosModificados",
                    omo_id:"' . $this->prm->omo_id . '",
                    mod_gpo_id:"' . $this->prm->mod_gpo_id . '",
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:"' . $this->prm->mod_id . '",
                    ecf_id:"' . $m->getEcf_id() . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            });

            $(".verMetasDet").click(function(){
                $(".divMetasDet").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"metas",
                    omo_id:"' . $this->prm->omo_id . '",
                    mod_gpo_id:"' . $this->prm->mod_gpo_id . '",
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:"' . $this->prm->mod_id . '",
                    pii_id:$(this).attr("pii_id"),
                    epg_id:$(this).attr("epg_id"),
                    macm_id:$(this).attr("macm_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    ' . ((!$lectura) ? ',lectura:false' : ',lectura:true') . '
                });
            });
        ');
    } // FIN listaMetas

    //MUESTRA LA INFORMACION CALENDARIZADA DE LA META
    private function metas()
    {
        $parametros = [];
        $this->setContenedor('oa' . $this->prm->epg_id . $this->prm->pii_id);
        $m = new MaestroModificacion($this->prm);
        $enc = new OrigenOperacion(['enc_oop' => $m->getOop()]);
        $lectura = $this->prm->lectura;
        $consulta = ($lectura) ? ' INNER JOIN ' : ' LEFT JOIN ';
        $this->prm->gpo_id = $this->prm->mod_gpo_id;
        $this->prm->ur_id = $this->prm->mod_ur_id;
        $this->prm->eri_id = $this->prm->pii_id;
        $this->prm->tri_id = $this->tri_id;

        $abc = 'c';
        $this->prm->nim_id = 4;

        //CONSTRUYE LA CONSULTA PARA OBTENER LA MODIFICACION DE METAS
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre,
            pdi_nombre,ctInd.umv_id,umi_nombre, "S" AS tiv,
            cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
            mac_descripcion,epg_id,mac_original, macm_id, i.umi_id, macm_ValorDeseado, tri_id,
            mact_modificado,tmf_id,
                (SELECT mact_capturaAvance
                FROM ' . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=1) AS cap_trimestre1,
                (SELECT mact_capturaAvance FROM ' . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=2) AS cap_trimestre2,
                (SELECT mact_capturaAvance FROM ' . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=3) AS cap_trimestre3,
                (SELECT mact_capturaAvance FROM ' . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=4) AS cap_trimestre4,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND nim_id=4 AND tri_id=1) AS est_trimestre1,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND nim_id=4 AND tri_id=2) AS est_trimestre2,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND nim_id = 4 AND tri_id = 3) AS est_trimestre3,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND nim_id = 4 AND tri_id = 4) AS est_trimestre4,
                i.tiv_id,tv.tiv_nombre
                FROM ' . BD_CTRL . '.enc_Programatico enc
                JOIN ' . BD_CTRL . '.cat_MetaActividad ma USING(epg_id)
                ' . $consulta . '  (
                    SELECT omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id,mact_modificado, macm_ValorDeseado, tmf_id, tri_id
                    FROM ' . BD_TEMPORAL . '.tem_ModificacionMetas
                    WHERE  epg_id = :epg_id
                    AND pii_id = :pii_id
                    AND omo_id= :omo_id
                    and mod_gpo_id= :gpo_id and mod_ur_id=:ur_id
                    and mod_id= :mod_id
                    GROUP BY omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id
                ) b using (pii_id,epg_id)
                JOIN ' . BD_CTRL . '.cat_Subprograma cs USING(gpo_id,ur_id,prg_id,spr_id)
                JOIN ' . BD_CTRL . '.vin_ProductoIntermedioIndicador USING(pii_id)
                JOIN ' . BD_CTRL . '.cat_Indicador i USING(ind_id,nim_id)
                JOIN ' . BD_CTRL . '.cat_ProductoIntermedio USING(pdi_id)
                JOIN ' . BD_CTRL . '.cat_UnidadMedidaVariable ctInd USING(umv_id)
                JOIN ' . BD_CTRL . '.cat_UnidadMedidaIndicador USING(umi_id)
                JOIN ' . BD_CTRL . '.cat_TipoValor tv ON tv.tiv_id = i.tiv_id 
            WHERE
            epg_id = :epg_id
            AND pii_id = :pii_id
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
        $parametros = [
            ':epg_id' => $this->prm->epg_id,
            ':pii_id' => $this->prm->pii_id,
            ':omo_id' => $this->prm->omo_id,
            ':gpo_id' => $this->prm->mod_gpo_id,
            ':ur_id'  => $this->prm->mod_ur_id,
            ':mod_id' => $this->prm->mod_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();
        $datos = $this->extraer();

        $sql = '
            SELECT pii_id as pii, gpo_id,ur_id,epg_id as epg, mact_alcanzado as alcanzado,
                    SUM(IF(mat.tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1,
                    SUM(IF(mat.tri_id=2,mact_capturaAvance,NULL)) cap_trimestre2,
                    SUM(IF(mat.tri_id=3,mact_capturaAvance,NULL)) cap_trimestre3,
                    SUM(IF(mat.tri_id=4,mact_capturaAvance,NULL)) cap_trimestre4
                FROM cat_MetaActividad mac
                INNER JOIN ( SELECT pii_id, epg_id, tri_id, mact_original, mact_modificado, mact_alcanzado,
                    mact_capturaAvance, 4 AS nim_id
                    FROM cat_MetaActividadTrimestre
                ) mat USING (pii_id,epg_id)
                INNER JOIN enc_Programatico USING(epg_id)
                INNER JOIN mir_TrimestreUr USING (gpo_id, ur_id,tri_id,nim_id)
            WHERE epg_id = :epg
            AND pii_id = :pii;
        ';
        $params = [':epg' => $this->prm->epg_id, ':pii' => $this->prm->pii_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $capturaAv = $this->extraer();

        $metaActividad = new MetaActividadMir($this->prm);
        $historicoMeta = new HistoricoMetaActividadMir($this->prm);

        $trimestres = '';
        $div = '<div class="row row-cols-md-4">';
        $datosMeta = '
            <form id="calendario">
                <table class="table table-hover table-bordered" >
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        ' . ((!$lectura) ? '<td align="right">
                            <button type="button" id="guardar" icon="ok"
                                title="Guardar modificación de metas de la actividad"
                                class="btn btn-outline-success"
                                pii_id=' . $datos['pii_id'] . '
                                epg_id=' . $datos['epg_id'] . '>
                                <i class="fa-solid fa-plus me-1"></i> Guardar
                            </button>
                        </td>' : '') . '
                    </tr>
                    <tr>
                        <td colspan=12 >
                        <input type="hidden" id="tiv_id" value="' . $datos['tiv_id'] . '" />
                        <input type="hidden" id="vigente" value="' . $datos['tiv_nombre'] . '"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=12 >
        ';

        $umi = $datos['umi_id'];
        $prgs_id = $datos['prg_id'];
        $sprs_id = $datos['spr_id'];

        //***********************LINEA BASE***************************************//
        $macLb = new stdClass();
        $macLb->id = 'lb';
        $macLb->readonly = true;
        $macLb->enDiv = true;
        $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;
        $macLb->maxlength = 4;
        $macLb->formato = 'entero';

        $macLbValor = new stdClass();
        $macLbValor->readonly = true;
        $macLbValor->id = 'lbValor';
        $macLbValor->enDiv = true;
        $macLbValor->value = $metaActividad->getLbValor();
        $macLbValor->formato = 'numero';

        $macLbIndicador = new stdClass();
        $macLbIndicador->readonly = true;
        $macLbIndicador->id = 'lbIndicador';
        $macLbIndicador->enDiv = true;
        $macLbIndicador->value = $metaActividad->getLbIndicador();
        $macLbIndicador->formato = 'numero';

        //***********************AÑO ANTERIOR***************************************//
        $macAnterior = new stdClass();
        $macAnterior->id = 'anterior';
        $macAnterior->enDiv = true;
        $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;
        $macAnterior->maxlength = 4;
        $macAnterior->formato = 'entero';

        $macAnteriorValor = new stdClass();
        $macAnteriorValor->id = 'anteriorValor';
        $macAnteriorValor->enDiv = true;
        $macAnteriorValor->value = $historicoMeta->getAnteriorValor();
        $macAnteriorValor->formato = 'numero';

        $macOriginal = new stdClass();
        $macOriginal->required = true;
        $macOriginal->id = 'macm_original';
        $macOriginal->enDiv = true;
        $macOriginal->decimales = 4;
        $macOriginal->value = $datos['macm_ValorDeseado'] != 0 ? $datos['macm_ValorDeseado'] : $historicoMeta->getOriginal();
        $macOriginal->formato = 'numero';

        $macAnteriorIndicador = new stdClass();
        $macAnteriorIndicador->id = 'anteriorIndicador';
        $macAnteriorIndicador->enDiv = true;
        $macAnteriorIndicador->value = $historicoMeta->getAnteriorIndicador();
        $macAnteriorIndicador->formato = 'numero';

        //****************************CALENDARIO 1 GENERAL*************************************//
        $trimestre1 = new stdClass();
        $trimestre1->id = 'ori_trimestre1';
        $trimestre1->enDiv = true;
        $trimestre1->label = '1° Trimestre';
        $trimestre1->formato = 'numero';

        $trimestre2 = new stdClass();
        $trimestre2->id = 'ori_trimestre2';
        $trimestre2->enDiv = true;
        $trimestre2->label = '2° Trimestre';
        $trimestre2->formato = 'numero';

        $trimestre3 = new stdClass();
        $trimestre3->id = 'ori_trimestre3';
        $trimestre3->enDiv = true;
        $trimestre3->label = '3° Trimestre';
        $trimestre3->formato = 'numero';

        $trimestre4 = new stdClass();
        $trimestre4->id = 'ori_trimestre4';
        $trimestre4->enDiv = true;
        $trimestre4->label = '4° Trimestre';
        $trimestre4->formato = 'numero';

        $total = new stdClass();
        $total->disabled = true;
        $total->id = 'ori_total';
        $total->enDiv = true;
        $total->label = 'Total';

        //****************************CALENDARIO 1*************************************//
        $trimestre1Cal1 = new stdClass();
        $trimestre1Cal1->id = 'numA_trimestre1';
        $trimestre1Cal1->enDiv = true;
        $trimestre1Cal1->label = '1° Trimestre';
        $trimestre1Cal1->formato = 'numero';
        $trimestre1Cal1->readonly = true;

        $trimestre2Cal1 = new stdClass();
        $trimestre2Cal1->id = 'numA_trimestre2';
        $trimestre2Cal1->enDiv = true;
        $trimestre2Cal1->label = '2° Trimestre';
        $trimestre2Cal1->formato = 'numero';
        $trimestre2Cal1->readonly = true;

        $trimestre3Cal1 = new stdClass();
        $trimestre3Cal1->id = 'numA_trimestre3';
        $trimestre3Cal1->enDiv = true;
        $trimestre3Cal1->label = '3° Trimestre';
        $trimestre3Cal1->formato = 'numero';
        $trimestre3Cal1->readonly = true;

        $trimestre4Cal1 = new stdClass();
        $trimestre4Cal1->id = 'numA_trimestre4';
        $trimestre4Cal1->enDiv = true;
        $trimestre4Cal1->label = '4° Trimestre';
        $trimestre4Cal1->formato = 'numero';
        $trimestre4Cal1->readonly = true;

        $totalCal1 = new stdClass();
        $totalCal1->disabled = true;
        $totalCal1->id = 'num_total';
        $totalCal1->enDiv = true;
        $totalCal1->label = 'Total';

        //****************************CALENDARIO 2*************************************//
        $trimestre1Cal2 = new stdClass();
        $trimestre1Cal2->id = 'denA_trimestre1';
        $trimestre1Cal2->enDiv = true;
        $trimestre1Cal2->label = '1° Trimestre';
        $trimestre1Cal2->formato = 'numero';

        $trimestre2Cal2 = new stdClass();
        $trimestre2Cal2->id = 'denA_trimestre2';
        $trimestre2Cal2->enDiv = true;
        $trimestre2Cal2->label = '2° Trimestre';
        $trimestre2Cal2->formato = 'numero';

        $trimestre3Cal2 = new stdClass();
        $trimestre3Cal2->id = 'denA_trimestre3';
        $trimestre3Cal2->enDiv = true;
        $trimestre3Cal2->label = '3° Trimestre';
        $trimestre3Cal2->formato = 'numero';

        $trimestre4Cal2 = new stdClass();
        $trimestre4Cal2->id = 'denA_trimestre4';
        $trimestre4Cal2->enDiv = true;
        $trimestre4Cal2->label = '4° Trimestre';
        $trimestre4Cal2->formato = 'numero';

        $totalCal2 = new stdClass();
        $totalCal2->disabled = true;
        $totalCal2->id = 'den_total';
        $totalCal2->enDiv = true;
        $totalCal2->label = 'Total';
        $totalCal2->formato = 'numero';

        $denominadorT1 = new stdClass();
        $denominadorT1->id = 'denominador1';
        $denominadorT1->enDiv = true;
        $denominadorT1->label = 'denoT1';
        $denominadorT1->formato = 'numero';

        $denominadorT2 = new stdClass();
        $denominadorT2->id = 'denominador2';
        $denominadorT2->enDiv = true;
        $denominadorT2->label = 'denoT2';
        $denominadorT2->formato = 'numero';

        $denominadorT3 = new stdClass();
        $denominadorT3->id = 'denominador3';
        $denominadorT3->enDiv = true;
        $denominadorT3->label = 'denoT3';
        $denominadorT3->formato = 'numero';

        $denominadorT4 = new stdClass();
        $denominadorT4->id = 'denominador4';
        $denominadorT4->enDiv = true;
        $denominadorT4->label = 'denoT4';
        $denominadorT4->formato = 'numero';

        //********************************LINEA BASE Numerador Denominador****************************************************//
        $macNumerador = new stdClass();
        $macNumerador->readonly = true;
        $macNumerador->id = 'numerador';
        $macNumerador->enDiv = true;
        $macNumerador->label = 'Numerador';
        $macNumerador->formato = 'numero';

        $macDenominador = new stdClass();
        $macDenominador->readonly = true;
        $macDenominador->id = 'denominador';
        $macDenominador->enDiv = true;
        $macDenominador->label = 'Denominador';
        $macDenominador->formato = 'numero';

        //********************************VALOR DESEADO Y PROGRAMADO (RAZON Y TASA) *********************//
        $macDeseadoNumerador = new stdClass();
        $macDeseadoNumerador->id = 'deseadoNumerador';
        $macDeseadoNumerador->enDiv = true;
        $macDeseadoNumerador->label = 'Numerador';
        $macDeseadoNumerador->formato = 'numero';
        $macDeseadoNumerador->disabled = true;

        $macDeseadoDenominador = new stdClass();
        $macDeseadoDenominador->id = 'deseadoDenominador';
        $macDeseadoDenominador->enDiv = true;
        $macDeseadoDenominador->label = 'Denominador';
        $macDeseadoDenominador->formato = 'numero';
        $macDeseadoDenominador->disabled = true;

        //********************************RESULTADO PARCIAL *********************//
        $resParcialT1 = new stdClass();
        $resParcialT1->id = 'rpar_trimestre1';
        $resParcialT1->enDiv = true;
        $resParcialT1->label = '1° Trimestre';
        $resParcialT1->disabled = true;

        $resParcialT2 = new stdClass();
        $resParcialT2->id = 'rpar_trimestre2';
        $resParcialT2->enDiv = true;
        $resParcialT2->label = '2° Trimestre';
        $resParcialT2->disabled = true;

        $resParcialT3 = new stdClass();
        $resParcialT3->id = 'rpar_trimestre3';
        $resParcialT3->enDiv = true;
        $resParcialT3->label = '3° Trimestre';
        $resParcialT3->disabled = true;

        $resParcialT4 = new stdClass();
        $resParcialT4->id = 'rpar_trimestre4';
        $resParcialT4->enDiv = true;
        $resParcialT4->label = '4° Trimestre';
        $resParcialT4->disabled = true;

        $resParcialTo = new stdClass();
        $resParcialTo->id = 'totalParcialA';
        $resParcialTo->enDiv = true;
        $resParcialTo->label = 'Total';
        $resParcialTo->disabled = true;
        //********************************FIN RESULTADO PARCIAL *********************//

        $macT0 = new stdClass();
        $macT0->id = 't0';
        $macT0->enDiv = true;
        $macT0->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;
        $macT0->maxlength = 4;
        $macT0->formato = 'entero';
        $macT0->label = 'AÑO T0';
        $macT0->disabled = true;

        $macT0Valor = new stdClass();
        $macT0Valor->required = true;
        $macT0Valor->id = 't0Valor';
        $macT0Valor->enDiv = true;
        $macT0Valor->value = $metaActividad->getAnteriorValor();
        $macT0Valor->formato = 'numero';
        $macT0Valor->label = 'VALOR AÑO T0';
        $macT0Valor->disabled = true;

        $macT1 = new stdClass();
        $macT1->id = 't1';
        $macT1->enDiv = true;
        $macT1->value = !empty($metaActividad->getAnio()) ? $metaActividad->getAnio() : APP_EJERCICIO;
        $macT1->maxlength = 4;
        $macT1->formato = 'entero';
        $macT1->label = 'AÑO T1';
        $macT1->disabled = true;

        $macT1Valor = new stdClass();
        $macT1Valor->id = 't1Valor';
        $macT1Valor->enDiv = true;
        $macT1Valor->formato = 'numero';
        $macT1Valor->label = 'VALOR AÑO T1';
        $macT1Valor->disabled = true;

        //*********************************************************************************************//
        //semestral
        $trimestre1->value = $historicoMeta->getModificadoT1();
        $trimestre2->value = $historicoMeta->getModificadoT2();
        $trimestre3->value = $historicoMeta->getModificadoT3();
        $trimestre4->value = $historicoMeta->getModificadoT4();

        $denominadorT1->value = $historicoMeta->getDenominadorT1();
        $denominadorT2->value = $historicoMeta->getDenominadorT2();
        $denominadorT3->value = $historicoMeta->getDenominadorT3();
        $denominadorT4->value = $historicoMeta->getDenominadorT4();

        if ($this->tri_id == 1) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1() != 0 ? $historicoMeta->getModificadoT1() : $historicoMeta->getNumeradorT1();
            $trimestre2Cal1->value = $historicoMeta->getNumeradorT2();
            $trimestre3Cal1->value = $historicoMeta->getNumeradorT3();
            $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($this->tri_id == 2) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2() != 0 ? $historicoMeta->getModificadoT2() : $historicoMeta->getNumeradorT2();
            $trimestre3Cal1->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal1->value = $historicoMeta->getDenominadorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($this->tri_id == 3) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2();
            $trimestre3Cal1->value = $historicoMeta->getModificadoT3() != 0 ? $historicoMeta->getModificadoT3() : $historicoMeta->getNumeradorT3();
            $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($this->tri_id == 4) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2();
            $trimestre3Cal1->value = $historicoMeta->getModificadoT3();
            $trimestre4Cal1->value = $historicoMeta->getModificadoT4() != 0 ? $historicoMeta->getModificadoT4() : $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT4();
        }

        $DenTrim1 = '<td>' . $this->ponInput($denominadorT1) . '</td>';
        $DenTrim2 = '<td>' . $this->ponInput($denominadorT2) . '</td>';
        $DenTrim3 = '<td>' . $this->ponInput($denominadorT3) . '</td>';
        $DenTrim4 = '<td>' . $this->ponInput($denominadorT4) . '</td>';
        $denominadoresOcultos = $DenTrim1 . $DenTrim2 . $DenTrim3 . $DenTrim4;

        if ($datos['tiv_id'] == 1) {
            $macDeseadoNumerador->value = number_format($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value, 4);
            $macDeseadoDenominador->value = number_format($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value, 4);
        } elseif ($datos['tiv_id'] == 2) {
            $macDeseadoNumerador->value = number_format($trimestre4Cal1->value, 4);
            $macDeseadoDenominador->value = number_format($trimestre4Cal2->value, 4);
        }

        $anteriorIndicador = '';
        $original = '';
        $deseadoNumerador = '';
        $deseadoDenominador = '';
        $t0 = '';
        $t0Valor = '';
        $t1 = '';

        $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
        $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = $tCal2 = '';

        if ($this->tri_id == 1) {
            $trimestre2Cal1->readonly = true;
            $trimestre2Cal2->readonly = true;
            $trimestre3Cal1->readonly = true;
            $trimestre3Cal2->readonly = true;
            $trimestre4Cal1->readonly = true;
            $trimestre4Cal2->readonly = true;
        } elseif ($this->tri_id == 2) {
            $trimestre1Cal1->readonly = true;
            $trimestre1Cal2->readonly = true;
            $trimestre3Cal1->readonly = true;
            $trimestre3Cal2->readonly = true;
            $trimestre4Cal1->readonly = true;
            $trimestre4Cal2->readonly = true;
        } elseif ($this->tri_id == 3) {
            $trimestre1Cal1->readonly = true;
            $trimestre1Cal2->readonly = true;
            $trimestre2Cal1->readonly = true;
            $trimestre2Cal2->readonly = true;
            $trimestre4Cal1->readonly = true;
            $trimestre4Cal2->readonly = true;
        } elseif ($this->tri_id == 4) {
            $trimestre1Cal1->readonly = true;
            $trimestre1Cal2->readonly = true;
            $trimestre2Cal1->readonly = true;
            $trimestre2Cal2->readonly = true;
            $trimestre3Cal1->readonly = true;
            $trimestre3Cal2->readonly = true;
        }

        $tipoM = new stdClass();
        $tipoM->id = 'mov1';
        $tipoM->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 1) {
            // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
            if ($capturaAv['cap_trimestre1'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM->disabled = true;
                $trimestre1Cal2->readonly = true;
            } else {
                $tipoM->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
                    ];
                $tipoM->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 1) {
            // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
            if ($capturaAv['cap_trimestre1'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM->disabled = true;
                $trimestre1Cal2->readonly = true;
            } else {
                $tipoM->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } else {
            if ($capturaAv['cap_trimestre1'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM->disabled = true;
                $trimestre1Cal2->readonly = true;
            } else {
                $tipoM->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
                    ];
                $tipoM->selecciona = '';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        }
        $comboMovimiento = $this->ponCombo($tipoM);

        $tipoM1 = new stdClass();
        $tipoM1->id = 'mov2';
        $tipoM1->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 2) {
            if ($capturaAv['cap_trimestre2'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM1->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM1->disabled = true;
                $trimestre2Cal2->readonly = true;
            } else {
                $tipoM1->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM1->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 2) {
            // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
            if ($capturaAv['cap_trimestre2'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM1->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM1->disabled = true;
                $trimestre2Cal2->readonly = true;
            } else {
                $tipoM1->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM1->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } else {
            // Si no es "A", puedes dejarlo en blanco o definir otro comportamiento
            if ($capturaAv['cap_trimestre2'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM1->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM1->disabled = true;
                $trimestre2Cal2->readonly = true;
            } else {
                $tipoM1->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM1->selecciona = '';  // Ninguna seleccionada
            }
        }
        $comboMovimiento1 = $this->ponCombo($tipoM1);

        $tipoM2 = new stdClass();
        $tipoM2->id = 'mov3';
        $tipoM2->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 3) {
            // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
            if ($capturaAv['cap_trimestre3'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM2->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM2->disabled = true;
                $trimestre3Cal2->readonly = true;
            } else {
                $tipoM2->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM2->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 3) {
            // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
            if ($capturaAv['cap_trimestre3'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM2->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM2->disabled = true;
                $trimestre3Cal2->readonly = true;
            } else {
                $tipoM2->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM2->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } else {
            // Si no es "A", puedes dejarlo en blanco o definir otro comportamiento
            if ($capturaAv['cap_trimestre3'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM2->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM2->disabled = true;
                $trimestre3Cal2->readonly = true;
            } else {
                $tipoM2->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM2->selecciona = '';  // Ninguna seleccionada
            }
        }
        $comboMovimiento2 = $this->ponCombo($tipoM2);

        $tipoM3 = new stdClass();
        $tipoM3->id = 'mov4';
        $tipoM3->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 4) {
            // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
            if ($capturaAv['cap_trimestre4'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM3->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM3->disabled = true;
                $trimestre4Cal2->readonly = true;
            } else {
                $tipoM3->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM3->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 4) {
            // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
            if ($capturaAv['cap_trimestre4'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM3->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM3->disabled = true;
                $trimestre4Cal2->readonly = true;
            } else {
                $tipoM3->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM3->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
            }
        } else {
            // Si no es "A", puedes dejarlo en blanco o definir otro comportamiento
            if ($capturaAv['cap_trimestre4'] == 1 && $capturaAv['alcanzado'] != 0) {
                # code...
                $tipoM3->opciones = [
                    '' => 'TIENE CAPTURA EN AVANCE',
                ];
                $tipoM3->disabled = true;
                $trimestre4Cal2->readonly = true;
            } else {
                $tipoM3->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM3->selecciona = '';  // Ninguna seleccionada
            }
        }
        $comboMovimiento3 = $this->ponCombo($tipoM3);

        switch ($umi) {
            case 2:
            case 3:
            case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                if ($trimestre1Cal1->value != null && $trimestre2Cal1->value != null && $trimestre3Cal1->value != null && $trimestre4Cal1->value != null && $trimestre1Cal2->value != null &&
                    $trimestre2Cal2->value != null && $trimestre3Cal2->value != null && $trimestre4Cal2->value != null) {

                    if ($umi == 2) {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format((($trimestre1Cal1->value / $trimestre1Cal2->value) * 100), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format((($trimestre2Cal1->value / $trimestre2Cal2->value) * 100), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format((($trimestre3Cal1->value / $trimestre3Cal2->value) * 100), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format((($trimestre4Cal1->value / $trimestre4Cal2->value) * 100), 4) : '0');
                    } else {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format(($trimestre1Cal1->value / $trimestre1Cal2->value), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format(($trimestre2Cal1->value / $trimestre2Cal2->value), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format(($trimestre3Cal1->value / $trimestre3Cal2->value), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format(($trimestre4Cal1->value / $trimestre4Cal2->value), 4) : '0');
                    }

                    if ($datos['tiv_id'] == 1) {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($macOriginal->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($macOriginal->value), 4);
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        }
                    } else {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4);
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        }

                    }
                } else {
                    if ($umi == 2) {
                        $resParcialT1->value = '0%';
                        $resParcialT2->value = '0%';
                        $resParcialT3->value = '0%';
                        $resParcialT4->value = '0%';
                        $resParcialTo->value = '0%';
                        $totalCal1->value = '0';
                        $totalCal2->value = '0';
                    } else {
                        $resParcialT1->value = '0';
                        $resParcialT2->value = '0';
                        $resParcialT3->value = '0';
                        $resParcialT4->value = '0';
                        $resParcialTo->value = '0';
                        $totalCal1->value = '0';
                        $totalCal2->value = '0';
                    }
                }
                $legend = 'VALOR DESEADO';
                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;

                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor LB';
                $macLbIndicador->disabled = true;
                $macOriginal->label = APP_EJERCICIO;
                $macOriginal->disabled = true;

                $macNumerador->value = $historicoMeta->getNumerador();
                $macDenominador->value = $historicoMeta->getDenominador();

                $deseadoNumerador = '<td>' . $this->ponInput($macDeseadoNumerador) . '</td>';
                $deseadoDenominador = '<td>' . $this->ponInput($macDeseadoDenominador) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';

                break;
            case 5: // TASA

                if ($trimestre1Cal1->value != null && $trimestre2Cal1->value != null && $trimestre3Cal1->value != null && $trimestre4Cal1->value != null && $trimestre1Cal2->value != null &&
                    $trimestre2Cal2->value != null && $trimestre3Cal2->value != null && $trimestre4Cal2->value != null) {

                    $resParcialT1->value = (($trimestre1Cal1->value != 0 || $trimestre1Cal2->value != 0) ? number_format(((($trimestre1Cal1->value / $trimestre1Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format(((($trimestre2Cal1->value / $trimestre2Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT3->value = (($trimestre3Cal1->value != 0 || $trimestre3Cal2->value != 0) ? number_format(((($trimestre3Cal1->value / $trimestre3Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format(((($trimestre4Cal1->value / $trimestre4Cal2->value) - 1) * 100), 4) : '0');

                    if ($datos['tiv_id'] == 1) {
                        $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                        $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        $resParcialTo->value = (($totalCal1->value != 0 || $totalCal2->value != 0) ? number_format(((($totalCal1->value / $totalCal2->value) - 1) * 100), 4) . '%' : '0%');
                        $macT0Valor->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                    } else {
                        $resParcialTo->value = $resParcialT4->value . '%';
                        $totalCal1->value = (number_format(($trimestre4Cal1->value), 4));
                        $totalCal2->value = (number_format(($trimestre4Cal2->value), 4));
                        $macT0Valor->value = (number_format(($trimestre4Cal2->value), 4));
                    }
                    $resParcialT1->value .= '%';
                    $resParcialT2->value .= '%';
                    $resParcialT3->value .= '%';
                    $resParcialT4->value .= '%';
                } else {
                    $resParcialT1->value = '0%';
                    $resParcialT2->value = '0%';
                    $resParcialT3->value = '0%';
                    $resParcialT4->value = '0%';
                    $resParcialTo->value = '0%';
                    $totalCal1->value = '0';
                    $totalCal2->value = '0';
                }
                $legend = 'VALORES PROGRAMADOS';

                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;
                $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;

                $macLb->label = 'AÑO T-1';
                $macLbValor->label = 'VALOR AÑO T-1';
                $macAnterior->label = 'AÑO T0';
                $macAnteriorValor->label = 'VALOR AÑO T0';
                $macLbIndicador->label = 'VALOR INDICADOR';
                $macAnteriorIndicador->label = 'VALOR INDICADOR';
                $macOriginal->label = 'VALOR AÑO T1';

                $macLbIndicador->disabled = true;
                $macOriginal->disabled = true;
                $macAnteriorIndicador->disabled = true;

                $anteriorIndicador = '<td>' . $this->ponInput($macAnteriorIndicador) . '</td>';

                $t0 = '<td>' . $this->ponInput($macT0) . '</td>';
                $t0Valor = '<td>' . $this->ponInput($macT0Valor) . '</td>';
                $t1 = '<td>' . $this->ponInput($macT1) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';
                break;
            default:
                if ($trimestre1->value != null && $trimestre2->value != null && $trimestre3->value != null && $trimestre4->value != null) {
                    $resParcialT1->value = number_format(($trimestre1->value), 4);
                    $resParcialT2->value = number_format(($trimestre2->value), 4);
                    $resParcialT3->value = number_format(($trimestre3->value), 4);
                    $resParcialT4->value = number_format(($trimestre4->value), 4);

                    if ($datos['tiv_id'] == 1) {
                        $totalCal1->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                    } else {
                        $totalCal1->value = number_format(($resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT4->value), 4);
                    }
                    $resParcialT1->value .= '%';
                    $resParcialT2->value .= '%';
                    $resParcialT3->value .= '%';
                    $resParcialT4->value .= '%';
                } else {
                    $resParcialT1->value = '0%';
                    $resParcialT2->value = '0%';
                    $resParcialT3->value = '0%';
                    $resParcialT4->value = '0%';
                    $resParcialTo->value = '0%';
                    $total->value = '0%';
                }
                $legend = 'Valor Programado Anual';

                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor';

                $macOriginal->disabled = true;

                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($total) . '</td>';

                break;
        }

        $trimestres .= '
            <fieldset class="col-md-8 col-xs-8">
                <legend><small>' . $legend . '</small></legend>
                <table class="table table-bordered table-striped ">
                    <tbody>
                        <tr>
                            ' . $deseadoNumerador . $deseadoDenominador . $t0 . $t0Valor . $t1 . $original . $anteriorIndicador . '
                        </tr>
                        <tr style="display:none;">
                            ' . $denominadoresOcultos . '
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <td colspan="12">
                            <div class="row row-cols-md-5">
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">1° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim1Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim1Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial1 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">2° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim2Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim2Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial2 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">3° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim3Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim3Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial3 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">4° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento3 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim4Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim4Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial4 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">TOTALES PARCIALES</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10"><br><br><br></td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $tCal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $tCal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $totalParcial . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
        ';

        $div .= $trimestres . '</div>';
        $datosMeta .= $div;
        $datosMeta .= '</td>
            </tr> </table>
        ';

        $this->mixHtml($datosMeta);

        $this->mixScript('
            var calculoLBIndicador=0;
            var calculoVPIndicador=0;

            $("#denA_trimestre1,#denA_trimestre2,#denA_trimestre3,#denA_trimestre4").blur(function() {

                var denT1 = unformatNumber($("#denominador1").val());
                var denT2 = unformatNumber($("#denominador2").val());
                var denT3 = unformatNumber($("#denominador3").val());
                var denT4 = unformatNumber($("#denominador4").val());

                var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                if(' . $this->tri_id . ' == 1 && $("#mov1").val() == "" || ' . $this->tri_id . ' == 2 && $("#mov2").val() == "" 
                    || ' . $this->tri_id . ' == 3 && $("#mov3").val() == "" || ' . $this->tri_id . ' == 4 && $("#mov4").val() == "") {
                    alertify.alert("IMPORTANTE", "Debe seleccionar el tipo de movimiento que va a realizar.",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 1 && $("#mov1").val() == "A" && denATrimestre1 < denT1) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 1 && $("#mov1").val() == "R" && denATrimestre1 > denT1) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 2 && $("#mov2").val() == "A" && denATrimestre2 < denT2) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 2 && $("#mov2").val() == "R" && denATrimestre2 > denT2) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 3 && $("#mov3").val() == "A" && denATrimestre3 < denT3) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 3 && $("#mov3").val() == "R" && denATrimestre3 > denT3) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 4 && $("#mov4").val() == "A" && denATrimestre4 < denT4) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 4 && $("#mov4").val() == "R" && denATrimestre4 > denT4) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
            });

            $("#mov1, #mov2, #mov3, #mov4").change(function(){
                var denT1 = unformatNumber($("#denominador1").val());
                var denT2 = unformatNumber($("#denominador2").val());
                var denT3 = unformatNumber($("#denominador3").val());
                var denT4 = unformatNumber($("#denominador4").val());

                var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                if('. $this->tri_id .' == 1 && $("#mov1").val() == "" || '. $this->tri_id .' == 2 && $("#mov2").val() == "" 
                    || '. $this->tri_id .' == 3 && $("#mov3").val() == "" || '. $this->tri_id .' == 4 && $("#mov4").val() == "") {
                    alertify.alert("IMPORTANTE", "Debe seleccionar el tipo de movimiento que va a realizar.",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 1 && $("#mov1").val() == "A" && denATrimestre1 < denT1) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 1 && $("#mov1").val() == "R" && denATrimestre1 > denT1) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 2 && $("#mov2").val() == "A" && denATrimestre2 < denT2) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 2 && $("#mov2").val() == "R" && denATrimestre2 > denT2) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 3 && $("#mov3").val() == "A" && denATrimestre3 < denT3) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 3 && $("#mov3").val() == "R" && denATrimestre3 > denT3) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 4 && $("#mov4").val() == "A" && denATrimestre4 < denT4) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 4 && $("#mov4").val() == "R" && denATrimestre4 > denT4) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
            });

            switch(' . $umi . '){
                case 2:case 3:case 4:    // RAZÓN

                    //calendario 2
                    $("#denA_trimestre1,#denA_trimestre2,#denA_trimestre3,#denA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $umi . ' == 2){
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = (numATrimestre1 / denATrimestre1)*100;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = (numATrimestre2 / denATrimestre2)*100;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = (numATrimestre3 / denATrimestre3)*100;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = (numATrimestre4 / denATrimestre4)*100;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        } else {
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = numATrimestre1 / denATrimestre1;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = numATrimestre2 / denATrimestre2;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = numATrimestre3 / denATrimestre3;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = numATrimestre4 / denATrimestre4;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        }

                        if(' . $datos['tiv_id'] . ' == 1){

                            $("#den_total").val(denATotal.toFixed(4));
                            $("#deseadoDenominador").val(denATotal.toFixed(4));
                            if(' . $umi . ' == 2){
                                $("#macm_original").val(((numATotal / denATotal)*100).toFixed(4));
                                $("#totalParcialA").val(((numATotal / denATotal)*100).toFixed(4) + "%");
                            } else {
                                $("#macm_original").val((numATotal / denATotal).toFixed(4));
                                $("#totalParcialA").val((numATotal / denATotal).toFixed(4));
                            }
                        } else {
                            if(' . $umi . ' == 2){
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            } else {
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4));
                            }
                            $("#den_total").val(denATrimestre4.toFixed(4));
                            $("#deseadoDenominador").val(denATotal.toFixed(4));
                            $("#macm_original").val(porc_trimestre4.toFixed(4));
                        }

                        if(' . $umi . ' == 2){
                            $("#rpar_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                            $("#rpar_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                            $("#rpar_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                            $("#rpar_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                        } else {
                            $("#rpar_trimestre1").val(porc_trimestre1.toFixed(4));
                            $("#rpar_trimestre2").val(porc_trimestre2.toFixed(4));
                            $("#rpar_trimestre3").val(porc_trimestre3.toFixed(4));
                            $("#rpar_trimestre4").val(porc_trimestre4.toFixed(4));
                        }
                    });

                break;
                case 5: // TASA
                    $("#anteriorValor,#lbValor").blur(function(){
                        calculoLBIndicador=((unformatNumber($("#anteriorValor").val()) / unformatNumber($("#lbValor").val()))-1) * 100;
                        $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                    });

                    //calendario 1
                    $("#numA_trimestre1,#numA_trimestre2,#numA_trimestre3,#numA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        if($("#denA_trimestre1").val() != 0 ) {
                            porc_trimestre1 = (((numATrimestre1 / denATrimestre1)-1)*100);
                        } else {
                            porc_trimestre1 = 0;
                        }
                        if($("#denA_trimestre2").val() != 0 ) {
                            porc_trimestre2 = (((numATrimestre2 / denATrimestre2)-1)*100);
                        } else {
                            porc_trimestre2 = 0;
                        }
                        if($("#denA_trimestre3").val() != 0 ) {
                            porc_trimestre3 = (((numATrimestre3 / denATrimestre3)-1)*100);
                        } else {
                            porc_trimestre3 = 0;
                        }
                        if($("#denA_trimestre4").val() != 0 ) {
                            porc_trimestre4 = (((numATrimestre4 / denATrimestre4)-1)*100);
                        } else {
                            porc_trimestre4 = 0;
                        }

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $datos['tiv_id'] . ' == 1){
                            $("#num_total").val(numATotal.toFixed(4));
                            $("#den_total").val(denATotal.toFixed(4));
                            $("#totalParcialA").val((((numATotal / denATotal)-1)*100).toFixed(4) + "%");
                            $("#macm_original").val(numATotal.toFixed(4));
                            $("#anteriorIndicador").val((((numATotal / denATotal)-1)*100).toFixed(4));
                            $("#t0Valor").val(denATotal.toFixed(4));
                        } else {
                            $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            $("#num_total").val(numATrimestre4.toFixed(4));
                            $("#den_total").val(denATrimestre4.toFixed(4));
                            $("#macm_original").val(numATrimestre4.toFixed(4));
                            $("#anteriorIndicador").val(porc_trimestre4.toFixed(4));
                            $("#t0Valor").val(denATrimestre4.toFixed(4));
                        }

                        $("#rpar_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                        $("#rpar_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                        $("#rpar_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                        $("#rpar_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                    });


                    //calendario 2
                    $("#denA_trimestre1,#denA_trimestre2,#denA_trimestre3,#denA_trimestre4").blur(function(){

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        if($("#denA_trimestre1").val() != 0 ) {
                            porc_trimestre1 = (((numATrimestre1 / denATrimestre1)-1)*100);
                        } else {
                            porc_trimestre1 = 0;
                        }
                        if($("#denA_trimestre2").val() != 0 ) {
                            porc_trimestre2 = (((numATrimestre2 / denATrimestre2)-1)*100);
                        } else {
                            porc_trimestre2 = 0;
                        }
                        if($("#denA_trimestre3").val() != 0 ) {
                            porc_trimestre3 = (((numATrimestre3 / denATrimestre3)-1)*100);
                        } else {
                            porc_trimestre3 = 0;
                        }
                        if($("#denA_trimestre4").val() != 0 ) {
                            porc_trimestre4 = (((numATrimestre4 / denATrimestre4)-1)*100);
                        } else {
                            porc_trimestre4 = 0;
                        }

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $datos['tiv_id'] . ' == 1){
                            $("#num_total").val(numATotal.toFixed(4));
                            $("#den_total").val(denATotal.toFixed(4));
                            $("#totalParcialA").val((((numATotal / denATotal)-1)*100).toFixed(4) + "%");
                            $("#macm_original").val(numATotal.toFixed(4));
                            $("#anteriorIndicador").val((((numATotal / denATotal)-1)*100).toFixed(4));
                            $("#t0Valor").val(denATotal.toFixed(4));
                        } else {
                            $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            $("#num_total").val(numATrimestre4.toFixed(4));
                            $("#den_total").val(denATrimestre4.toFixed(4));
                            $("#macm_original").val(numATrimestre4.toFixed(4));
                            $("#anteriorIndicador").val(porc_trimestre4.toFixed(4));
                            $("#t0Valor").val(denATrimestre4.toFixed(4));
                        }
                        $("#rpar_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                        $("#rpar_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                        $("#rpar_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                        $("#rpar_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                    });

                break;
                default: // todas las demás unidades de medida del indicador

                    $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur(function() {

                        $("#rpar_trimestre1").val($("#ori_trimestre1").val());
                        $("#rpar_trimestre2").val($("#ori_trimestre2").val());
                        $("#rpar_trimestre3").val($("#ori_trimestre3").val());
                        $("#rpar_trimestre4").val($("#ori_trimestre4").val());

                        if(' . $datos['tiv_id'] . ' == 1){
                            $("#num_total").val(formatNumber(unformatNumber($("#ori_trimestre1").val()) + unformatNumber($("#ori_trimestre2").val())+ unformatNumber($("#ori_trimestre3").val()) + unformatNumber($("#ori_trimestre4").val())));
                            $("#ori_total").val(formatNumber(unformatNumber($("#ori_trimestre1").val()) + unformatNumber($("#ori_trimestre2").val()) + unformatNumber($("#ori_trimestre3").val()) + unformatNumber($("#ori_trimestre4").val())));
                            $("#macm_original").val( formatNumber( unformatNumber( $("#ori_trimestre1").val() ) + unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre3").val() ) + unformatNumber( $("#ori_trimestre4").val() ) ) );
                        } else {
                            $("#ori_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#num_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#macm_original").val(unformatNumber($("#ori_trimestre4").val()));
                        }
                    });

                    if("' . $abc . '" == "c"){
                        $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur();
                    }
                    break;
            }

            $("#guardar").click(function(){
                var denT1 = unformatNumber($("#denominador1").val());
                var denT2 = unformatNumber($("#denominador2").val());
                var denT3 = unformatNumber($("#denominador3").val());
                var denT4 = unformatNumber($("#denominador4").val());

                var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                if(' . $this->tri_id . ' == 1 && $("#mov1").val() == "" || ' . $this->tri_id . ' == 2 && $("#mov2").val() == "" 
                    || ' . $this->tri_id . ' == 3 && $("#mov3").val() == "" || ' . $this->tri_id . ' == 4 && $("#mov4").val() == "") {

                    alertify.alert("IMPORTANTE", "Debe seleccionar el tipo de movimiento que va a realizar.",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                    
                }
                if( ' . $this->tri_id . ' == 1 && $("#mov1").val() == "A" && denATrimestre1 < denT1) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                    
                } else if( ' . $this->tri_id . ' == 1 && $("#mov1").val() == "R" && denATrimestre1 > denT1) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 2 && $("#mov2").val() == "A" && denATrimestre2 < denT2) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 2 && $("#mov2").val() == "R" && denATrimestre2 > denT2) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 3 && $("#mov3").val() == "A" && denATrimestre3 < denT3) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 3 && $("#mov3").val() == "R" && denATrimestre3 > denT3) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
                if( ' . $this->tri_id . ' == 4 && $("#mov4").val() == "A" && denATrimestre4 < denT4) {
                    alertify.alert("IMPORTANTE", "El valor a ampliar debe ser mayor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else if( ' . $this->tri_id . ' == 4 && $("#mov4").val() == "R" && denATrimestre4 > denT4) {
                    alertify.alert("IMPORTANTE", "El valor a reducir debe ser menor a la meta existente",
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else {
                    comunica("' . $this->pantalla . '",{
                        metodo:"actualizarMetas",
                        omo_id:"' . $this->prm->omo_id . '",
                        gpo_id:"' . $this->prm->mod_gpo_id . '",
                        ur_id:"' . $this->prm->mod_ur_id . '",
                        mod_id:"' . $this->prm->mod_id . '",
                        pii_id:$(this).attr("pii_id"),
                        epg_id:$(this).attr("epg_id"),
                        macm_id:' . $this->prm->macm_id . ',
                        prgs_id:"' . $prgs_id . '",
                        sprs_id:"' . $sprs_id . '",
                        tri_id:' . $this->tri_id . ',
                        umi_id:' . $umi . ',
                        abc:"' . $abc . '",
                        original:$("#macm_original").val(),
                        macm_denominadorT1:$("#denA_trimestre1").val(),
                        macm_denominadorT2:$("#denA_trimestre2").val(),
                        macm_denominadorT3:$("#denA_trimestre3").val(),
                        macm_denominadorT4:$("#denA_trimestre4").val(),
                        tipoMov1:$("#mov1").val(),
                        tipoMov2:$("#mov2").val(),
                        tipoMov3:$("#mov3").val(),
                        tipoMov4:$("#mov4").val()
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                : '') . '
                    });
                }
            });
        ');

    } // FIN metas

    private function actualizarMetas()
    {
        $mensaje = '';
        if ($this->tri_id == 1) {
            # code...
            $this->prm->modificado = $this->prm->macm_denominadorT1;
            $this->prm->tmf_id = $this->prm->tipoMov1;
        } elseif ($this->tri_id == 2) {
            # code...
            $this->prm->modificado = $this->prm->macm_denominadorT2;
            $this->prm->tmf_id = $this->prm->tipoMov2;
        } elseif ($this->tri_id == 3) {
            # code...
            $this->prm->modificado = $this->prm->macm_denominadorT3;
            $this->prm->tmf_id = $this->prm->tipoMov3;
        } elseif ($this->tri_id == 4) {
            # code...
            $this->prm->modificado = $this->prm->macm_denominadorT4;
            $this->prm->tmf_id = $this->prm->tipoMov4;
        }
        $this->prm->original = $this->prm->original;

        $this->prm->trimestreCierre = null;
        $ip = ($this->getIp() != '::1') ? $this->getIp() : '127.0.0.1';

        $this->prm->origen = 'Controlador Modificacion Metas S_1_1';
        $this->prm->tmf_id = '';
        $this->prm->auxoriginal = $this->prm->original;

        $modificado = str_replace(',', '', $this->prm->modificado);
        $original = str_replace(',', '', $this->prm->original);

        $this->conectar();
        switch ($this->prm->abc) {
            case 'c':
                $this->setSql('
                    SELECT *
                    FROM ' . BD_TEMPORAL . '.tem_ModificacionMetas
                    WHERE omo_id = ?
                    AND mod_gpo_id = ?
                    AND mod_ur_id = ?
                    AND mod_id = ?
                    AND epg_id = ?
                    AND pii_id = ?
                    AND tri_id = ?
                ');
                $parametros = [
                    $this->prm->omo_id,
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                    $this->prm->mod_id,
                    $this->prm->epg_id,
                    $this->prm->pii_id,
                    $this->tri_id,
                ];
                $this->consultar($parametros);

                if ($this->getNumeroRegistros() > 0) {
                    # code...
                    $movimiento = '';
                    if ($this->tri_id == 1) {
                        $movimiento = $this->prm->tipoMov1;
                    } elseif ($this->tri_id == 2) {
                        $movimiento = $this->prm->tipoMov2;
                    } elseif ($this->tri_id == 3) {
                        $movimiento = $this->prm->tipoMov3;
                    } elseif ($this->tri_id == 4) {
                        $movimiento = $this->prm->tipoMov4;
                    } else {
                        $movimiento = $this->prm->tipoMov1;
                    }
                    $this->setSql('
                        UPDATE ' . BD_TEMPORAL . '.tem_ModificacionMetas
                        SET mact_modificado = ' . $modificado . ', macm_ValorDeseado = ' . $original . ', tmf_id = "' . $movimiento . '"
                        WHERE omo_id = ?
                        AND mod_gpo_id = ?
                        AND mod_ur_id = ?
                        AND mod_id = ?
                        AND epg_id = ?
                        AND pii_id = ?
                        AND tri_id = ?
                    ');
                    $parametros = [
                        $this->prm->omo_id,
                        $this->prm->gpo_id,
                        $this->prm->ur_id,
                        $this->prm->mod_id,
                        $this->prm->epg_id,
                        $this->prm->pii_id,
                        $this->tri_id,
                    ];
                    $this->consultar($parametros);
                    $mensaje = 'Meta actualizada temporalmente';
                    $observacion = 'META ACTUALIZADA EN FOLIO DE ADECUACIONES S_1_1';
                    $this->setSql('
                        INSERT INTO ' . BD_BITACORA . '.bit_DetalleModificacionMeta
                        (bit_observacion,omo_id,mme_gpo_id,mme_ur_id,mme_id,epg_id,pii_id,tri_id,mact_modificado,usr_ip,usr_id)
                        VALUES ("' . $observacion . '",:omo_id, :mod_gpo_id, :mod_ur_id, :mod_id, :epg_id, :pii_id, :tri_id, :modificado, INET_ATON(:ip), :usr_id);
                    ');
                    $params = [
                        ':omo_id'     => $this->prm->omo_id,
                        ':mod_gpo_id' => $this->prm->gpo_id,
                        ':mod_ur_id'  => $this->prm->ur_id,
                        ':mod_id'     => $this->prm->mod_id,
                        ':epg_id'     => $this->prm->epg_id,
                        ':pii_id'     => $this->prm->pii_id,
                        ':tri_id'     => $this->tri_id,
                        ':modificado' => $modificado,
                        ':ip'         => $ip,
                        ':usr_id'     => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($params);
                } else {
                    $movimiento = '';
                    if ($this->tri_id == 1) {
                        $movimiento = $this->prm->tipoMov1;
                    } elseif ($this->tri_id == 2) {
                        $movimiento = $this->prm->tipoMov2;
                    } elseif ($this->tri_id == 3) {
                        $movimiento = $this->prm->tipoMov3;
                    } elseif ($this->tri_id == 4) {
                        $movimiento = $this->prm->tipoMov4;
                    } else {
                        $movimiento = $this->prm->tipoMov1;
                    }
                    $this->setSql('
                        INSERT INTO ' . BD_TEMPORAL . '.tem_ModificacionMetas
                        (omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id,tri_id,mact_modificado,macm_ValorDeseado,tmf_id,usr_id)
                        VALUES (:omo_id, :mod_gpo_id, :mod_ur_id, :mod_id, :pii_id, :epg_id, :tri_id, :modificado, :original, :tmf, :usr_id);
                    ');
                    $params = [
                        ':omo_id'     => $this->prm->omo_id,
                        ':mod_gpo_id' => $this->prm->gpo_id,
                        ':mod_ur_id'  => $this->prm->ur_id,
                        ':mod_id'     => $this->prm->mod_id,
                        ':pii_id'     => $this->prm->pii_id,
                        ':epg_id'     => $this->prm->epg_id,
                        ':tri_id'     => $this->tri_id,
                        ':modificado' => $modificado,
                        ':original'   => $original,
                        ':tmf'        => $movimiento,
                        ':usr_id'     => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($params);
                    $mensaje = 'Meta registrada temporalmente';
                    $observacion = 'META MODIFICADA EN FOLIO DE ADECUACIONES S_1_1';
                    $this->setSql('
                        INSERT INTO ' . BD_BITACORA . '.bit_DetalleModificacionMeta
                        (bit_observacion,omo_id,mme_gpo_id,mme_ur_id,mme_id,epg_id,pii_id,tri_id,mact_modificado,usr_ip,usr_id)
                        VALUES ("' . $observacion . '",:omo_id, :mod_gpo_id, :mod_ur_id, :mod_id, :epg_id, :pii_id, :tri_id, :modificado, INET_ATON(:ip), :usr_id);
                    ');
                    $params = [
                        ':omo_id'     => $this->prm->omo_id,
                        ':mod_gpo_id' => $this->prm->gpo_id,
                        ':mod_ur_id'  => $this->prm->ur_id,
                        ':mod_id'     => $this->prm->mod_id,
                        ':epg_id'     => $this->prm->epg_id,
                        ':pii_id'     => $this->prm->pii_id,
                        ':tri_id'     => $this->tri_id,
                        ':modificado' => $modificado,
                        ':ip'         => $ip,
                        ':usr_id'     => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($params);
                }

                break;
        } // Fin switch

        $this->desconectar();

        $this->mixScript('
            $("#endMetas").removeAttr("disabled");
                notify("La meta ha sido modificada temporalmente","success");
        ');

        $this->mixScript('
            notify("' . $mensaje . '","warning");
        ');

    } // Fin actualizarMetas

    // METODO QUE VERIFICA SI LA META YA FUE MODIFICADA
    private function consultaMetaModificada($omo_id, $mod_gpo_id, $mod_ur_id, $mod_id, $epg_id, $pii_id, $tri_id)
    {
        $parametros = [];
        $this->conectar();
        $this->setSql('
            SELECT *
            FROM ' . BD_TEMPORAL . '.tem_ModificacionMetas
            WHERE omo_id = ?
            AND mod_gpo_id = ?
            AND mod_ur_id = ?
            AND mod_id = ?
            AND epg_id = ?
            AND pii_id = ?
            AND tri_id = ?
        ');
        $parametros = [
            $omo_id,
            $mod_gpo_id,
            $mod_ur_id,
            $mod_id,
            $epg_id,
            $pii_id,
            $tri_id,
        ];
        $this->consultar($parametros);
        $this->desconectar();
        $numeroRegistros = $this->getNumeroRegistros();
        return $numeroRegistros;
    } // FIN consultaMetaModificada

    //REALIZA EL REGISTRO TRIMESTRE DE LOS TRIMESTRES MODIFICADOS
    private function abcTrimestres($abc, $omo_id, $mod_gpo_id, $mod_ur_id, $mod_id, $pii_id, $epg_id, $tri_id, $mact_modificado)
    {
        $parametros = [];
        $this->conectar();
        switch ($abc) {
            case 'I':
                $this->setSql('
                    INSERT INTO ' . BD_TEMPORAL . '.tem_ModificacionMetas
                    (omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id,tri_id,mact_modificado,usr_id)
                    VALUES (:omo_id, :gpo_id, :ur_id, :mod_id, :pii_id, :epg_id, :tri_id, :modificado, :usr_id);
                ');
                $parametros = [
                    ':omo_id'     => $omo_id,
                    ':gpo_id'     => $mod_gpo_id,
                    ':ur_id'      => $mod_ur_id,
                    ':mod_id'     => $mod_id,
                    ':pii_id'     => $pii_id,
                    ':epg_id'     => $epg_id,
                    ':tri_id'     => $tri_id,
                    ':modificado' => $mact_modificado,
                    ':usr_id'     => $this->usr_id,
                ];
                break;
            case 'U':
                $this->setSql(
                    '
                    UPDATE ' . BD_TEMPORAL . '.tem_ModificacionMetas
                    SET mact_modificado = ' . $mact_modificado . '
                    WHERE omo_id = :omo_id AND
                    mod_gpo_id = :gpo_id AND
                    mod_ur_id = :ur_id AND
                    mod_id = :mod_id AND
                    epg_id = :epg_id AND
                    pii_id = :pii_id AND
                    tri_id = :tri_id;'
                );
                $parametros = [
                    ':omo_id' => $omo_id,
                    ':gpo_id' => $mod_gpo_id,
                    ':ur_id'  => $mod_ur_id,
                    ':mod_id' => $mod_id,
                    ':epg_id' => $epg_id,
                    ':pii_id' => $pii_id,
                    ':tri_id' => $tri_id,
                ];
                break;
        } // Fin switch

        $this->consultar($parametros);
        $this->desconectar();
    } // Fin abcTrimestres

    // METODO QUE BORRA LAS METAS MODIFICADAS
    private function borrarDatosModificados()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
        ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $parametros = [];
        $this->conectar();
        $this->setSql('
            SELECT * FROM ' . BD_TEMPORAL . '.tem_ModificacionMetas WHERE
            omo_id = ? AND
            mod_gpo_id = ? AND
            mod_ur_id = ? AND
            mod_id = ?;
        ');
        $parametros = [
            $this->prm->omo_id,
            $this->prm->mod_gpo_id,
            $this->prm->mod_ur_id,
            $this->prm->mod_id,
        ];
        $this->consultar($parametros);
        $this->desconectar();

        // Si hay registros de metas de este folio se borran , sino unicamente se dirige a la funcion tablaFolios
        if ($this->getNumeroRegistros() > 0) {
            $this->conectar();
            $this->setSql('
                DELETE FROM ' . BD_TEMPORAL . '.tem_ModificacionMetas WHERE
                omo_id = ? AND
                mod_gpo_id = ? AND
                mod_ur_id = ? AND
                mod_id = ?;
            ');
            $parametros = [
                $this->prm->omo_id,
                $this->prm->mod_gpo_id,
                $this->prm->mod_ur_id,
                $this->prm->mod_id,
            ];
            $this->consultar($parametros);
            $this->desconectar();
        } // Fin getNumeroRegistros

        if (!isset($this->prm->borrar)) {
            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios"
                    ' . (isset($this->prm->ecf_id) ? ',ecf_id:"' . $this->prm->ecf_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ');
        } // Fin borrar
    } // Fin borrarDatosModificados

    // METODO EJECUTA PROCEDIMIENTO PARA ACTUALIZAR EL ESTADO DEL FOLIO EN EL SISTEMA DE INVERSION
    private function actualizaEstadoEnSI($mod_id, $emo_id)
    {
        $parametros = [$mod_id, $emo_id,$this->usr_id, 'REGISTRO ACTUALIZADO DESDE SEFIP/SCP/Adecuaciones Presupuestarias/validacionFolio - ControladorRegistroModificaciones->validacionFolio()'];
        $conexion = new AccesoDatos();
        $conexion->setServerDefault('siip_sefip');
        $conexion->conectar();
        $conexion->setSql('{call programacion.usp_ActualizarEstadoFolioAutorizacion (?,?,?,?)}');
        $conexion->consultar($parametros);
        $conexion->desconectar();
    }// Fin actualizaEstadoEnSI
} //Fin class ControladorRegistroModificaciones
/***** FIN FUNCIONES ***********************/
if (isset($_SESSION[_S]['prm']['S']['1']['1']['1'])) {
    new ControladorRegistroModificaciones();
} else {
    WebResponse::sinPermisos();
}
