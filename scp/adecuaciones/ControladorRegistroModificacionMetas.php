<?php

require_once dirname(__FILE__) . '/../../lib/scp/vistaAdecuacionMetas.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scp/sp_AutorizarModificacionMeta2027.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scp/ModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/DetalleModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraDetalleModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/EstadoModificacion.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuacionMetas.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../lib/scp/dibujaEncuadreProgramatico.php';
require_once dirname(__FILE__) . '/../../srv/CatalogoEncuadreProgramatico.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaActividadMir.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoMetaActividadMir.php';

class ControladorRegistroModificacionMetas extends WebResponse
{
    public $pantalla;
    private $vista;
    private $model;
    private $ur;
    private $ue;
    private $presupuesto;
    private $escritura;
    private $nivelRevision;
    private $omo_id;
    private $mme_gpo_id;
    private $mme_ur_id;
    private $mme_id;
    private $gpo_id;
    private $ur_id;
    public $perfil;
    public $nivel;
    private $subsec;
    private $ip;
    public $usr_id;
    public $tri_id;
    public function __construct()
    {
        //Manda a llamar constructor base
        parent::__construct();
        $this->pantalla = 'scp/adecuaciones/ControladorRegistroModificacionMetas.php';
        $this->vista = new vistaAdecuacionMetas();
        $this->model = new modelAdecuacionMetas();
        //Verifica el tipo de usuario
        $this->ur = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 2 && isset($_SESSION[_S]['prm']['S']['1']['7']['7']) ? true : false);
        $this->ue = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 1 ? true : false);
        $this->presupuesto = (isset($_SESSION[_S]['ssc_id']) && $_SESSION[_S]['ssc_id'] == 3 ? true : false);

        //Verificamos los permisos de escritura
        $this->escritura = (isset($_SESSION[_S]['prm']['S']['1']['7']['7']) || (isset($_SESSION[_S]['prm']['S']['1']['7']['14']) && isset($this->prm->nivelRevision) && $this->prm->nivelRevision == 'alta') ? true : false);

        $this->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '' ? $this->prm->nivelRevision : '');
        $this->omo_id = (isset($this->prm->omo_id) && $this->prm->omo_id != '' ? $this->prm->omo_id : '');
        $this->mme_gpo_id = (isset($this->prm->mme_gpo_id) && $this->prm->mme_gpo_id != '' ? $this->prm->mme_gpo_id : '');
        $this->mme_ur_id = (isset($this->prm->mme_ur_id) && $this->prm->mme_ur_id != '' ? $this->prm->mme_ur_id : '');
        $this->mme_id = (isset($this->prm->mme_id) && $this->prm->mme_id != '' ? $this->prm->mme_id : '');
        $this->gpo_id = !($this->ur || $this->ue) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->ur_id = !($this->ur || $this->ue) ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
        $this->perfil = $_SESSION[_S]['prf_id'] ?? '';
        $this->nivel = $_SESSION[_S]['nvj_id'] ?? '';
        $this->subsec = $_SESSION[_S]['ssc_id'] ?? '';
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->usr_id = $_SESSION[_S]['usr_id'];

        $hoy = date('Y-m-d');
        if ($hoy >= APP_EJERCICIO . '-01-01' && $hoy <= APP_EJERCICIO . '-03-31') {
            $this->tri_id = 1;
        } elseif ($hoy >= APP_EJERCICIO . '-04-01' && $hoy <= APP_EJERCICIO . '-06-30') {
            $this->tri_id = 2;
        } elseif ($hoy >= APP_EJERCICIO . '-07-01' && $hoy <= APP_EJERCICIO . '-09-30') {
            $this->tri_id = 3;
        } elseif ($hoy >= APP_EJERCICIO . '-10-01' && $hoy <= APP_EJERCICIO . '-12-31') {
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
                break; //Metodo que muestra la lista de folios creados por la UR Y SEFIN
            case 'llenaTablaFolios':
                $this->llenaTablaFolios();
                break;
            case 'tablaModificaciones':
                $this->tablaModificaciones();
                break;
            case 'addMmeModificacion':
                $this->addMmeModificacion();
                break; //Metodo para agregar modificaciones o movimientos al folio por la UR Y RH.
            case 'addMmeFolio':
                $this->addMmeFolio();
                break; //Metodo para agregar nuevo folio de la Dependencia, R.H Y SEFIN
            case 'abcModificacion':
                $this->abcModificacion();
                break;
            case 'abcFolios':
                $this->abcFolios();
                break; //Metodo que guarda la informacion del folio de origen A,B
            case 'tablaHistorialFolio':
                $this->tablaHistorialFolio();
                break; // Tabla de historial del folioss
            case 'eliminarMovimientos':
                $this->eliminarMovimientos();
                break;
            case 'modificarMetas':
                $this->modificarMetas();
                break;
            case 'listaMetas':
                $this->listaMetas();
                break;
            case 'metas':
                $this->metas();
                break;
            case 'actualizarMetas':
                $this->actualizarMetas();
                break;
            case 'borrarDatosModificados':
                $this->borrarDatosModificados();
                break;
            case 'verificarFolio':
                $this->verificarFolio();
                break; //Método que verifica si el folio tiene movimientos
            case 'revisarFolio':
                $this->revisarFolio();
                break; //Metodo para enviar a revision el folio por la ur, y asi mismo hacer la respectiva validación
            case 'tablaFoliosRead':
                $this->tablaFoliosRead();
                break; //Metodo para visualizar los folios en estado de validacion y autorizacion UR y validacion y autorizacion SEFIN
            case 'llenaTablaFoliosRead':
                $this->llenaTablaFoliosRead();
                break;
            case 'validacionFolio':
                $this->validacionFolio();
                break; //Metodo que sirve para revisar, validar y autorizar folios internos y enviar folios externos.
            case 'addMensajeRechazo':
                $this->addMensajeRechazo();
                break;
            case 'rechazarFolio':
                $this->rechazarFolio();
                break; //Metodo que sirve para rechazar folios en el modulo UR y sefin
            case 'addFirma':
                $this->addFirma(); //Metodo que muestra las firmas
                break;
            case 'firmas':
                $this->firmas(); //Muestra las firmas
                break;
            case 'abcFirmas':
                $this->abcFirmas(); //Registra las firmas
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }
    /* METODO QUE MUESTRA LAS OPCIONES SEGUN LOS PERMISOS, NIVEL Y PERFIL DEL USUARIO, UR Y SEFIN */

    private function main()
    {

        $botones = $this->vista->vwBotones('menuPrincipal');
        $this->mixHtml($botones);
        $this->agregaTools('');
        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            $(".altafoliour").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . (isset($this->perfil) && ($this->perfil == 'S' ||
            $this->perfil == 'U') ? 'tablaUrs' : 'tablaFolios') . '"
                    ' . (isset($this->perfil) && ($this->perfil == 'S' ||
            $this->perfil == 'U') || $this->nivel >= 3 ? ',nivelRevision:"alta"'
            : '') . '
                });
                return false;
            });

            $(".validafoliour").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . (isset($this->perfil) && ($this->perfil == 'S' ||
            $this->perfil == 'U') ? 'tablaUrs' : 'tablaFoliosRead') . '",
                    nivelRevision:"validacionUR"
                });
                return false;
            });

            $(".autorizafoliour").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . (isset($this->perfil) && ($this->perfil == 'S' ||
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
    }
    /* MÉTODO QUE CAMBIA EL VALOR DE LA VARIABLE DE SESIÓN DE LA CLASIFICACION ADMINISTRATIVA */

    private function cambiarUR($ur): void
    {
        if (isset($ur) && is_object($ur)) {
            $script = '$("#combour").val("' . $ur->gpo_id . $ur->ur_id . '").change();';
        } else {
            $script = 'ify("notNo se establecieron los valores correctamente","error");';
        }

        $this->mixScript($script);
    }
    /* MÉTODO QUE CONSULTA A LA ENTIDAD UR RESPOSABLE */

    private function getUR($gpo, $ur)
    {
        $objUr = new stdClass();
        if (isset($gpo) && isset($ur)) {
            $objUr = new UnidadResponsable();
            $objUr->setGpo_id($gpo);
            $objUr->setUr_id($ur);
            $objUr->select();
        }

        return $objUr;
    }
    /* METODO QUE DETERMINA SI ES MODO LECTURA */

    private function lectura($mme, $esUR): bool
    {
        $estadosUR = [1, 3];
        $estadosSEFIN = [6, 8, 11, 17];
        $lectura = false;
        if ($mme->getMme_id() != '') {
            if ($esUR && !in_array($mme->getEmo_id(), $estadosUR)) {
                $lectura = true;
            } elseif (!$esUR && !in_array($mme->getEmo_id(), $estadosSEFIN)) {
                $lectura = true;
            }
        } else {
            $lectura = false;
        }

        return $lectura;
    }
    /* MÉTODO QUE VA A HACER UN MIXSCRIPT SEGÚN LO ENVIADO */

    private function pintaScript($s): void
    {
        if (isset($s)) {
            $this->mixScript('' . $s . '');
        }
    }

    //METODO QUE MUESTRA UN LISTADO DE UR'S DE RECURSOS HUMANOS O DE SEFIN
    private function tablaUrs()
    {
        $script = 'desbloquearClasAdmin();';
        $this->pintaScript($script);

        if ($this->ur) {
            $this->omo_id = 'A';
        }
        if ($this->presupuesto) {
            $this->omo_id = 'B';
        }

        $html = '';
        $nivelRevision = isset($this->nivelRevision) && $this->nivelRevision != ''
        ?
        $this->nivelRevision : '';
        $btnNuevo = $this->vista->vwBotones('nuevoFolio', '', $nivelRevision);
        $btnNuevo->click = '
            $("#div_clave").show();
            comunica("' . $this->pantalla . '",{
                metodo:"addMmeFolio",
                ecf_id:$("#cveFin_id").val(),
                omo_id:"' . $this->omo_id . '",
                mme_gpo_id:$("#combour").find("option:selected").attr("gpo_id"),
                mme_ur_id:$("#combour").find("option:selected").attr("ur_id"),
                cambiaUR:true
                ' . (isset($nivelRevision) ? ',nivelRevision:"' . $nivelRevision . '"'
            : '') . '
            });
        ';
        $btnNuevo = is_object($btnNuevo) ? $this->ponBoton($btnNuevo) : '';
        $btnNuevo = $nivelRevision == 'alta' ? $btnNuevo : '';

        $btnRegresar = $this->vista->vwBotones('regresar', '', $nivelRevision);
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"main"
            });
        ';
        $btnRegresar = is_object($btnRegresar) ? $this->ponBoton($btnRegresar) : '';

        $esSefin = $this->presupuesto;

        //MOSTRAR TABLA DE LAS URS CON EL NUMERO DE FOLIOS PENDIENTE DE REVISIÓN
        $pantalla = 'scp/adecuaciones/ControladorRegistroModificacionMetas.php';
        $tabla = $this->vista->vwTablaUrs($nivelRevision, $esSefin, $pantalla);

        $this->mixHtml('<div id = "div_clave" class="col-md-12 col-xs-12">' . $html . '</div>
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

        $this->mixScript($script);
    }
    /* METODO QUE MUESTRA LAS URS CON FOLIOS PENDIENTES DE REVISION */

    private function llenaTablaUrs()
    {

        $origen = $sql = '';
        if ($this->presupuesto) {
            $origen = 'B';
            $sql = $this->model->getConsultaTablasUrs(
                'presupuesto',
                $this->nivelRevision
            );
        }
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Ur';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".verUr").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($this->nivelRevision == 'alta' || $this->nivelRevision
            == 'revisionSEFIN' ?
            'tablaFolios' : 'tablaFoliosRead') . '",
                    ' . (($origen != '') ?
            'omo_id:"' . $origen . '",' : 'omo_id:$(this).attr("omo_id"),') . '
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    cambiaUR:true
                    ' . ($this->nivelRevision != '' ? ' ,nivelRevision:"' . $this->nivelRevision . '"'
            : '') . '
                });
            });
        ');
    }
    /* METODO QUE MUESTRA LA LISTA DE FOLIOS CREADOS POR LA UR */

    private function tablaFolios()
    {
        $script = 'bloquearClasAdmin();';
        $this->pintaScript($script);

        $html = '';
        $mensaje = '';
        $esUR = $this->ur || $this->ue;
        $cambiaUR = isset($this->prm->cambiaUR);
        $nuevo = $this->vista->vwBotones('nuevoFolio');

        //SI ES LA DIRECCION DE PRESUPUESTO SE ENVIAR LOS PARAMETROS DE LA UR CON LA QUE SE VA A CREAR EL FOLIOS
        if ($this->presupuesto && $cambiaUR) {
            $this->gpo_id = $this->prm->mme_gpo_id;
            $this->ur_id = $this->prm->mme_ur_id;

            $cambiarUR = new stdClass();
            $cambiarUR->gpo_id = $this->gpo_id;
            $cambiarUR->ur_id = $this->ur_id;
            $this->cambiarUR($cambiarUR);
        }

        $objUR = $this->getUR($this->gpo_id, $this->ur_id);

        if ($esUR) {
            $nuevo->click = '
                comunica("' . $this->pantalla . '",{
                    metodo: "addMmeFolio"
                    ' . (($this->omo_id != '') ?
                ',omo_id:"' . $this->omo_id . '"' : ",omo_id:'A'") . '
                    ' . (($this->nivelRevision != '') ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    ' . (($this->gpo_id != '') ?
                ',mme_gpo_id:"' . $this->gpo_id . '"' : '') . '
                    ' . (($this->ur_id != '') ?
                ',mme_ur_id:"' . $this->ur_id . '"' : '') . '

                });
            ';
        } else {
            $nuevo->click = '
                comunica("' . $this->pantalla . '",{
                    metodo: "addMmeFolio"
                    ' . (($this->omo_id != '') ?
                ',omo_id:"' . $this->omo_id . '"' : ",omo_id:'B'") . '
                    ' . (($this->nivelRevision != '') ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    ' . (($this->gpo_id != '') ?
                ',mme_gpo_id:"' . $this->gpo_id . '"' : '') . '
                    ' . (($this->ur_id != '') ?
                ',mme_ur_id:"' . $this->ur_id . '"' : '') . '

                });
            ';
        }

        $nuevo = ($this->escritura) ? $this->ponBoton($nuevo) : '';
        $regresar = $this->vista->vwBotones('regresar');

        if ($this->ur || $this->ue) {
            $regresar->click = '
                comunica("' . $this->pantalla . '",{metodo: "main"});
            ';
        } else {
            // no se esta recibiendo el nivel de revision al regresar de la pantalla de modificacion de folio
            $regresar->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaUrs"
                    ' . (isset($this->nivelRevision) ? ',nivelRevision:"' . $this->nivelRevision . '"'
                : '') . '
                });
            ';
        }
        $regresar = $this->ponBoton($regresar);
        $botones = $regresar . ' ' . $nuevo;

        $tabla = new stdClass();
        $tabla->id = 'tbl-Folios';
        $tabla->titulo = 'Adecuacion de Metas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        unset($param);

        $param = new stdClass();
        $param->mostrar = ($this->ur || $this->ue) ? '' : 'ur';
        $param->parametrosExtra = new stdClass();
        $param->parametrosExtra->prm = $this->prm;
        $param->parametrosExtra->objUR = $objUR;
        $resTools = (isset($param->mostrar)) ? $this->vista->vwToolsTablaFolios($param)
        : '';
        if (isset($param->mostrar)) {
            $tabla->tools = $resTools;
        }

        $tabla->comunica->nivelRevision = (!empty($this->nivelRevision) ? $this->nivelRevision
            : '');
        $tabla->comunica->omo_id = (!empty($this->omo_id) ? $this->omo_id : '');
        $tabla->comunica->metodo = 'llenaTablaFolios';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        //Definicion de columnas
        $tr = [];
        //Ver/Editar
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
            array_push($tr, $th);
        }

        //Estado del folio
        $th = new stdClass();
        $th->texto = 'Estado';
        array_push($tr, $th);
        //Detalle del folio (obras/actividades)
        $th = new stdClass();
        $th->texto = 'Detalle';
        array_push($tr, $th);

        $trbody = [];
        //contenido ver
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';
        $td->casos->tipoCondicion = '||';
        //Sefin debe ver el boton de editar cuando el folio este en edo 11, la dependencia en edo 1 y 3
        if ($esUR) {
            $td->casos->valor = '1,3';
        } elseif ($this->presupuesto) {
            $td->casos->valor = '8,11,17';
        }
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'gris';
        $td->casos->verdadero->icono = 'pencil';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'origenModificacion';
        $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
        $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
        $td->casos->verdadero->attr->mme_id = 'mme_id';
        $td->casos->verdadero->class = 'addMmeFolio btn-xs';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->icono = 'eye';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->omo_id = 'origenModificacion';
        $td->casos->falso->attr->mme_gpo_id = 'mme_gpo_id';
        $td->casos->falso->attr->mme_ur_id = 'mme_ur_id';
        $td->casos->falso->attr->mme_id = 'mme_id';
        $td->casos->falso->class = 'addMmeFolio btn-xs';
        array_push($trbody, $td);

        //Para poder imprimir el folio en la dependencia, este debe estar en estado 1,3,6,8,11.
        //Si es sefin planeación 6,11 y egresos 8,11.
        if ($esUR) {
            //Impresión solo en estado 1,3,6,8,11
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'emo_id';
            $td->casos->tipoCondicion = '||';
            $td->casos->valor = '1,3';
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->color = 'rojo';
            $td->casos->verdadero->icono = 'file-pdf';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->omo_id = 'origenModificacion';
            $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
            $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
            $td->casos->verdadero->attr->mme_id = 'mme_id';
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
                if ($this->presupuesto) {
                    $td->casos->valor = '8,11,17';
                }
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->color = 'rojo';
                $td->casos->verdadero->icono = 'file-pdf';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->omo_id = 'origenModificacion';
                $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
                $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
                $td->casos->verdadero->attr->mme_id = 'mme_id';
                $td->casos->verdadero->attr->emo_id = 'emo_id';
                $td->casos->verdadero->attr->mmd_id = 'mmd_id';
                $td->casos->verdadero->class = 'addFirma btn-xs';
                array_push($trbody, $td);
            }

            $td = new stdClass();
            $td->campoTxt = 'omo_nombre';
            array_push($trbody, $td);
        }
        //Numero de folio
        $td = new stdClass();
        $td->campoTxt = 'mme_id';
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
        $td->campoTxt = 'mme_concepto';
        array_push($trbody, $td);
        //Fecha de creación del folio
        $td = new stdClass();
        $td->campoTxt = 'mme_fechaFolio';
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
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->icono = 'paper-plane';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->omo_id = 'origenModificacion';
            $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
            $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
            $td->casos->verdadero->attr->mme_id = 'mme_id';
            $td->casos->verdadero->class = 'btn btn-outline-info revisarFolio btn-xs';
            $td->casos->falso = new stdClass();
            $td->casos->falso->campoTxt = 'revisar';
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
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->attr->mme_id = 'mme_id';
        $td->class = 'detalleFolio btn-xs';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        if (($this->presupuesto) && $this->nivelRevision == 'alta') {
            $script = ' $("#div_clave").show(); ';
            $this->mixScript($script);
        }
        $this->mixHtml('
            <div id = "div_clave" class="col-md-12 col-xs-12" style = "display:none">' . $html . '</div>
            <div class="col-md-12 col-xs-12">' . $mensaje . '
                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');
        $this->mixHtml($this->agregaTools($botones) . '
            <form id="reporte" method="post" action="" target="_blank">
                <input type="hidden" id="omo_id" name="omo_id" value=""/>
                <input type="hidden" id="mme_gpo_id" name="mme_gpo_id" value=""/>
                <input type="hidden" id="mme_ur_id" name="mme_ur_id" value=""/>
                <input type="hidden" id="mme_id" name="mme_id" value=""/>
            </form>
        ');
        $this->llenaTablaFolios();

        $this->mixScript('
            $("' . (($this->nivel == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
        ');
    }
    /* MÉTODO QUE CONTIENE LA CONSULTA QUE LLENA EL LISTADO DE FOLIOS */

    private function llenaTablaFolios()
    {
        $esUR = $this->ur || $this->ue;
        $sql = '';
        if ($esUR) {
            $sql = $this->model->getConsultaTabla('ur', $this->prm);
        } elseif ($this->presupuesto) {
            $sql = $this->model->getConsultaTabla('presupuesto', $this->prm);
        }
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Folios';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $metodo = (($esUR) ? 'verificarFolio' : 'revisarFolio');

        $this->mixScript('
            $(".addMmeFolio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addMmeFolio",
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    omo_id:$(this).attr("omo_id"),
                    mme_id:$(this).attr("mme_id")
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
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id")
                    ' . (($this->nivelRevision != '') ?
            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $(".detalleFolio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id")
                    ' . (($this->nivelRevision != '') ?
            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . ',
                });
                return false;
            });
            $(".revisarFolio").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . $metodo . '",
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id")
                    ' . (($this->nivelRevision != '') ?
            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });
            $(".imprimir").click(function(){
                $("#reporte").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesMetas.php");
                $("#omo_id").val($(this).attr("omo_id"));
                $("#mme_gpo_id").val($(this).attr("mme_gpo_id"));
                $("#mme_ur_id").val($(this).attr("mme_ur_id"));
                $("#mme_id").val($(this).attr("mme_id"));
                $("#reporte").submit();
            });
        ');
    }
    /* METODO PARA MOSTRAR LAS OBRAS/ACTIVIDADES AGREGADOS AL FOLIO POR LA UR     */

    private function tablaModificaciones()
    {
        //INCIALIZACIÓN DE VARIABLES DE LOS BOTONES
        $btnNuevoMovimiento = $btnEliminarMovimientos = '';
        $btnRegresar = $btnValidar = $btnRechazar = '';
        $esUR = $this->ur || $this->ue;
        //BLOQUE CLASIFICACION ADMINISTRATIVA
        $script = 'bloquearClasAdmin();';
        $this->pintaScript($script);
        //INSTANCIA DE LAS ENTIDADES
        $mme = new ModificacionMeta($this->prm);
        $mme->select();
        //VERIFICAMOS SI ESTAMOS EN MODO LECTURA
        $lectura = $this->lectura($mme, $esUR);
        //CONSULTAMOS SI EL FOLIO TIENE OBRAS/ACTIVIDADES
        $tieneObrasActividades = $this->model->getEliminaFolio($mme);
        //BOTONES
        $btnNuevoMovimiento = $this->vista->vwBotones('nuevoMovimiento');
        $btnNuevoMovimiento->value = 'Nuevo';
        $btnNuevoMovimiento->click = '
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            comunica("' . $this->pantalla . '",{
                metodo:"addMmeModificacion",
                omo_id:"' . $mme->getOmo_id() . '",
                mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                mme_ur_id:"' . $mme->getMme_ur_id() . '",
                mme_id:' . $mme->getMme_id() . '
                ' . ($this->nivelRevision != '' ?
            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnNuevoMovimiento = $lectura ? '' : $btnNuevoMovimiento;
        $btnEliminarMovimientos = $this->vista->vwBotones('eliminarMovimientos');
        $btnEliminarMovimientos->click = '
            alertify.confirm("ELIMINAR MOVIMIENTOS", "¿ Realmente desea eliminar todos los movimientos del folio ? ¡¡¡Una vez realizada esta acción no se puede deshacer!!!<h5 style=\"display:inline\"></h5> ",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"eliminarMovimientos",
                        omo_id:"' . $mme->getOmo_id() . '",
                        mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                        mme_ur_id:"' . $mme->getMme_ur_id() . '",
                        mme_id:' . $mme->getMme_id() . '
                        ' . ($this->nivelRevision != '' ? ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        }
                    );
                },
                function(){},
            ).set("defaultFocus","cancel").set("labels", {ok:"Eliminar",cancel:"Cancelar"});
        ';
        $btnEliminarMovimientos = $tieneObrasActividades['registros'] ? $btnEliminarMovimientos
        : '';
        $btnEliminarMovimientos = is_object($btnEliminarMovimientos) ? $this->ponBoton($btnEliminarMovimientos)
        : '';
        if ($this->ur || $this->ue) {
            switch ($mme->getEmo_id()) {
                case 1:
                case 3: // en estado 1 y 3 capturado ó rechazado por ur
                    $$btnEliminarMovimientos = $btnEliminarMovimientos;
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
                omo_id:"' . $mme->getOmo_id() . '",
                mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                mme_ur_id:"' . $mme->getMme_ur_id() . '"
                ' . ($this->nivelRevision != '' ?
            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';

        $btnValidar = $this->vista->vwBotones('validar');
        $btnValidar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"validacionFolio",
                omo_id:"' . $mme->getOmo_id() . '",
                mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                mme_ur_id:"' . $mme->getMme_ur_id() . '",
                mme_id:' . $mme->getMme_id() . '
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
                omo_id:"' . $mme->getOmo_id() . '",
                mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                mme_ur_id:"' . $mme->getMme_ur_id() . '",
                mme_id:' . $mme->getMme_id() . '
                ' . ($this->nivelRevision != '' ?
            ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
            });
            return false;
        ';
        $btnRechazar = is_object($btnRechazar) ? $this->ponBoton($btnRechazar) : '';
        if (!$esUR) {
            //EVALUAMOS EL NIVEL DE REVISION DE SEFIN
            switch ($this->nivelRevision) {
                case 'alta':
                    $btnValidar = $btnRechazar = '';
                    //Verifica si el origen es de dependencia, sefin no puede eliminar o agregar movimientos
                    if ($mme->getOmo_id() == 'A') {
                        $btnEliminarMovimientos = '';
                        $btnNuevoMovimiento = '';
                    }
                    break;
                case 'revisionSEFIN':
                    $btnEliminarMovimientos = '';
                    if ($mme->getOmo_id() == 'B') {
                        $btnRechazar = ''; // no se puede rechazar el folio al estado 9 si el folio nace de SEFIN
                    } else {
                        $btnNuevoMovimiento = ''; // no se puede agregar nuevos movimientos al folio cuando viene de ur
                    }
                    break;
                default:
                    $btnValidar = $btnRechazar = $btnEliminarMovimientos = '';
                    $btnRegresar->click = '
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFoliosRead",
                            omo_id:"' . $mme->getOmo_id() . '",
                            mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                            mme_ur_id:"' . $mme->getMme_ur_id() . '"
                            ' . ($this->nivelRevision != '' ?
                        ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        });
                        return false;
                    ';
                    break;
            }
        } else {
            switch ($this->nivelRevision) {
                case 'validacionUR':
                case 'autorizacionUR':
                    $btnRegresar->click = '
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFoliosRead",
                            mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                            mme_ur_id:"' . $mme->getMme_ur_id() . '",
                            omo_id:"' . $mme->getOmo_id() . '"
                            ' . ($this->nivelRevision != '' ?
                        ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                        });
                        return false;
                    ';
                    $btnEliminarMovimientos = '';
                    break;
                default:
                    break;
            }
            //ES UNIDAD RESPONSABLE
            $btnValidar = $btnRechazar = '';
        }
        $btnRegresar = is_object($btnRegresar) ? $this->ponBoton($btnRegresar) : '';
        $btnNuevoMovimiento = is_object($btnNuevoMovimiento) ? $this->ponBoton($btnNuevoMovimiento) : '';
        $html = $this->vista->vwInformacionFolio($mme);
        //DEFINICIÓN DE LA TABLA DE OBRAS/ACTIVIDADES DEL FOLIO
        $pantalla = $this->pantalla;
        $tabla = $this->vista->vwTablaObrasActividades($mme, $esUR, $this->presupuesto, $pantalla, $this->nivelRevision);
        $tabla->tools = $html;

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');
        $this->llenaTablaModificaciones($mme);
        //SE AGREGAN LOS BOTONES
        $this->agregaTools($btnRegresar . ' ' . $btnNuevoMovimiento . ' ' . $btnValidar . ' ' . $btnRechazar . ' ' . $btnEliminarMovimientos);
        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaFolios").click(); e.stopPropagation(); });
        ');
    }
    /* METODO QUE LLENA LA TABLA DE LAS OBRAS/ACTIVIDADES DEL FOLIO */

    private function llenaTablaModificaciones($mme)
    {
        $sql = '';
        $esUR = $this->ur || $this->ue;
        if ($esUR) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('ur', $mme);
        }

        if ($this->presupuesto) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('sefin', $mme);
        }
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Modificaciones';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
        $this->prm->nivelRevision = $this->nivelRevision;

        $this->mixScript('
            $(".addMmeModificacion").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($esUR ?
            'addMmeModificacion' : 'addModPrevision') . '",
                    omo_id:$(this).attr("omo_id"),
                    mme_id:$(this).attr("mme_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
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
            $(".addModModificacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"modificarMetas",
                    lectura:1,
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id"),
                    epg_id:$(this).attr("epg_id")
                    ' . (($this->prm->nivelRevision != '') ?
            ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
                return false;
            });
        ');
    }
    /* METODO PARA AGREGAR NUEVO FOLIO DE LA DEPENDENCIA */

    private function addMmeFolio()
    {
        $metas = '';
        $objUr = $this->getUR($this->gpo_id, $this->ur_id);
        $esUR = ($this->ue || $this->ur) ? true : false;
        //Inicialización de variables
        $mensajeRechazo = $unidadResponsable = '';
        $mme = new ModificacionMeta($this->prm); //INSTANCIANDO LA ENTIDAD MODIFICACION META}
        if (isset($this->prm->mme_id)) {
            $mme->select();
        }

        $lectura = $this->lectura($mme, $esUR); //CONSULTAR SI ESTAMOS EN MODO LECTURA
        $abc = ($mme->getMme_id() != '') ? 'c' : 'a'; //VERIFICAMOS SI ES ALTA O CAMBIO (ACTUALIZACION)
        //BOTONES
        $btnGuardar = $this->vista->vwBotones('guardarFolio');
        $btnEliminar = $this->vista->vwBotones('eliminarFolio');
        $btnRegresar = $this->vista->vwBotones('regresar');
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"' . (isset($this->nivelRevision) &&
            ($this->nivelRevision == 'alta' || $this->nivelRevision == '' || $this->nivelRevision
                == 'revisionSEFIN') ? 'tablaFolios' : 'tablaFoliosRead') . '"
                ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"'
            : '') . '
                ' . ($this->gpo_id != '' ? ',mme_gpo_id:' . $this->gpo_id . '' : '') . '
                ' . ($this->ur_id != '' ? ',mme_ur_id:"' . $this->ur_id . '"' : '') . '
                ' . ($this->nivelRevision != '' ? ',nivelRevision:"' . $this->nivelRevision . '"'
            : '') . '
                });
            return false;
        ';
        $btnRegresar = $this->ponBoton($btnRegresar);
        $btnHistorial = $this->vista->vwBotones('historial');
        $btnHistorial = $this->ponBoton($btnHistorial);
        // FIN BOTONES
        //INPUT
        $numeroFolio = $this->vista->vwInput('numeroFolio', $mme, $esUR);
        $fecha = $this->vista->vwInput('fecha', $mme, $esUR);
        $fecha->readonly = true;
        $justificacion = $this->vista->vwInput('justificacion', $mme, $esUR);
        $oficioSolicitud = $this->vista->vwInput('oficioSolicitud', $mme, $esUR);
        $oficioAutorizacion = $this->vista->vwInput('oficioAutorizacion', $mme, $esUR);
        // FIN INPUT
        //COMBOS
        $unidadEjecutora = $this->vista->vwCombo('unidadEjecutora', $mme);
        if ($this->ue && is_object($unidadEjecutora)) {
            $unidadEjecutora->selecciona = $_SESSION[_S]['usr_id'];
            $unidadEjecutora->disabled = true;
        }
        $modalidadModificacion = $this->vista->vwCombo('modalidadAdecuacion', $mme);
        $tipoAdecuacion = $this->vista->vwCombo('tipoAdecuacion', $mme);
        $tipoFolio = $this->vista->vwCombo('tipoFolio', $mme);
        $subsecretaria = $this->vista->vwCombo('subsecretaria', $mme);
        // FIN COMBOS

        $elimina = $this->model->getEliminaFolio($mme); //CONSULTA PARA VERIFICAR SI SE PUEDE ELIMINAR EL FOLIO

        if ($esUR) { // ACCIONES PARA LAS UNIDADES RESPONSABLES
            // EVENTOS DE LOS BOTONES
            $btnGuardar->click = '
                var sub = (($("#Subsecretaria").length) ? $("#Subsecretaria").find("option:selected").val():1);
                comunica("' . $this->pantalla . '",{
                    metodo:"abcFolios",
                    abc:"' . $abc . '",
                    ' . ($mme->getMme_id() != '' ? 'mme_id:$("#folio").val(),' : '') .
                'subsecretaria:sub,
                    omo_id: "A",
                    tfm_id:$("#tfm_id").find("option:selected").val(),
                    tmd_id:$("#tmd_id").find("option:selected").val(),
                    mmd_id:$("#mmd_id").find("option:selected").val(),
                    concepto:$("#concepto").val(),
                    mme_ue_id:$("#mme_ue_id").val(),
                    vobo:0 // visto bueno
                    ' . (isset($this->nivelRevision) ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                },true);
                return false;
            ';

            //PARAMETROS BLOQUEADOS PARA LA UR
            $numeroFolio->readonly = true;
            $oficioSolicitud = $oficioAutorizacion = '';

            $consultaMotivoRechazo = $this->model->getMotivoRechazo($mme, 'ur'); //CONSULTA MOTIVO RECHAZO

            if ($lectura) { //CONSULTAR SI SE PUEDEN MOSTRAR LOS BOTONES
                $btnGuardar = '';
            }

            if ($elimina['registros']) { //CONSULTAR SI TIENE MOVIMIENTOS
                $btnEliminar = '';
            }

            if ($abc == 'c') {

                if ($consultaMotivoRechazo['bit_observacion'] != 0) { //MOTIVO DE RECHAZO
                    //$mensajeRechazo = $this->vista->vwMensajeRechazo($mod,$consultaMotivoRechazo['bit_observacion']);
                }
            } else {
                $btnHistorial = '';
                $numeroFolio = $fecha = '';
            }
        } // FIN ACCIONES PARA LAS UNIDADES RESPONSABLES
        else { // ACCIONES PARA SEFIN
            $numeroFolio->datosExtra->omo_id = $this->omo_id;
            //PARA MOSTRAR LA UNIDAD RESPONSABLE DE LA QUE SE ESTÁ CAPTURANDO EL FOLIO
            $prmUR = new stdClass();
            $prmUR->mostrar = 'ur';
            $prmUR->parametrosExtra = new stdClass();
            $prmUR->parametrosExtra->prm = $this->prm;
            $prmUR->parametrosExtra->objUR = $objUr;
            $unidadResponsable = $this->vista->vwToolsTablaFolios($prmUR);

            // EVENTOS
            $btnGuardar->click = '
                var sub = (($("#Subsecretaria").length) ? $("#Subsecretaria").find("option:selected").val():1);
                comunica("' . $this->pantalla . '",{
                    metodo:"abcFolios",
                    abc:"' . $abc . '",
                    ' . (isset($this->prm->omo_id) ? ' omo_id:"' . $this->prm->omo_id . '",'
                : ' omo_id: "B",') . '
                    mme_id:$("#folio").val(),
                    subsecretaria:sub,
                    tfm_id:$("#tfm_id").find("option:selected").val(),
                    tmd_id:$("#tmd_id").find("option:selected").val(),
                    mmd_id:$("#mmd_id").find("option:selected").val(),
                    concepto:$("#concepto").val(),
                    oficioSolicitudSefin:$("#oficioSolicitudSefin").val(),
                    oficioSefin:$("#OficioSefin").val(),
                    mme_ue_id:$("#mme_ue_id").val(),
                    vobo:0 // visto bueno
                    ' . (isset($this->nivelRevision) ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                },true);
                return false;
            ';

            $consultaMotivoRechazo = $this->model->getMotivoRechazo($mme, 'sefin'); //SI ES PRESUPUESTO O PLANEACIÓN

            if ($lectura) { //CONSULTAR SI SE PUEDEN MOSTRAR LOS BOTONES
                $btnGuardar = '';
            } elseif ($mme->getOmo_id() == 'A' && $this->nivelRevision == 'alta') {
                $btnGuardar = ''; // se oculta el boton agregar cuando el folio surge de una UR en la pantalla de alta de Sefin.
            }

            if ($elimina['registros']) {
                $btnEliminar = '';
            }

            if ($abc == 'c') {
                //ESPACIO PARA APARTADO DE METAS DESDE SEFIN

                if ($consultaMotivoRechazo['bit_observacion'] != 0) { //MOTIVO DE RECHAZO
                    //$mensajeRechazo = $this->vista->vwMensajeRechazo($mod,
                }
            } else {
                $btnHistorial = '';
            }
        } // FIN ACCIONES PARA SEFIN
        // FORMULARIO
        $numeroFolio = is_object($numeroFolio) ? $this->ponInput($numeroFolio) : '';
        $fecha = is_object($fecha) ? $this->ponInput($fecha) : '';
        $justificacion = is_object($justificacion) ? $this->ponTextArea($justificacion)
        : '';
        $oficioSolicitud = is_object($oficioSolicitud) ? $this->ponInput($oficioSolicitud)
        : '';
        $oficioAutorizacion = is_object($oficioAutorizacion) ? $this->ponInput($oficioAutorizacion)
        : '';
        $modalidadModificacion = is_object($modalidadModificacion) ? $this->ponCombo($modalidadModificacion)
        : '';
        $unidadEjecutora = is_object($unidadEjecutora) ? $this->ponCombo($unidadEjecutora)
        : '';
        $tipoAdecuacion = is_object($tipoAdecuacion) ? $this->ponCombo($tipoAdecuacion)
        : '';
        $tipoFolio = is_object($tipoFolio) ? $this->ponCombo($tipoFolio) : '';
        $subsecretaria = is_object($subsecretaria) ? $this->ponCombo($subsecretaria)
        : '';

        $formulario = $mensajeRechazo . '
            <div class = "row">
                ' . $unidadResponsable . '
            </div>
            <form id = "Folio">
                <legend><span style = "font-size:14px;" class="badge bg-secondary">Datos Generales</span></legend>
                <div class = "row">
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
                <div class = "row">
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $modalidadModificacion . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $tipoAdecuacion . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $tipoFolio . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $subsecretaria . '
                    </fieldset>
                </div>
                <div class = "row">
                    <fieldset class = "col-md-6 col-xs-6">
                        ' . $justificacion . '
                    </fieldset>
                    <fieldset class = "col-md-3 col-xs-3">
                        ' . $oficioAutorizacion . '
                    </fieldset>
                    <fieldset class = "col-md-3 col-xs-3">
                        ' . $oficioSolicitud . '
                    </fieldset>
                </div>
            </form>
            ' . (isset($this->prm->lectura) || $metas != '' ? '
                <legend><span style = "font-size:14px;" class="label label-info">Metas</span> <label style="font-size:12px; color:red;"> (Si el folio modificó metas aquí aparecerá el detalle de las mismas, en caso contrario, la justificación)</label></legend>
                <fieldset id ="metas" class="col-md-12 col-xs-12">
                    ' . $metas . '
                </fieldset>
            ' : '');

        $this->mixHtml($formulario);
        if (isset($this->prm->lectura)) {
            $this->modificarMetas();
        }
        // FIN FORMULARIO
        // BOTONES EN TOOLS
        $btnGuardar = is_object($btnGuardar) ? $this->ponBoton($btnGuardar) : '';
        $btnEliminar = is_object($btnEliminar) ? $this->ponBoton($btnEliminar) : '';
        $this->agregaTools($btnRegresar . ' ' . $btnGuardar . ' ' . $btnEliminar . ' ' . $btnHistorial);
        //FIN BOTONES TOOLS
        $this->mixScript('
            $("#btnEliminarFolio").click(function(){
                alertify.confirm("ELIMINAR FOLIO", "¿ Realmente desea eliminar el folio <h5 style=\"display:inline\"></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"abcFolios",
                            abc:"b",
                           ' . (isset($this->prm->omo_id) ? ' omo_id:"' . $this->prm->omo_id . '",'
            : ' omo_id:$("#tmd_id").find("option:selected").attr("omo_id"),') . '
                            mme_id:$("#folio").val()' . (isset($this->prm->mme_gpo_id) && !empty($this->prm->mme_gpo_id)
            ? ',mme_gpo_id:"' . $this->prm->mme_gpo_id . '"' : '') . '
                            ' . (isset($this->prm->mme_ur_id) && !empty($this->prm->mme_ur_id) ? ',mme_ur_id:"' . $this->prm->mme_ur_id . '"' : '') . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            }
                        );
                    },
                    function(){},).set("defaultFocus","cancel").set("labels", {ok:"Eliminar",cancel:"Cancelar"});
            });

            $("#btnHistorial").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaHistorialFolio"
                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"'
            : '') . '
                    ' . (isset($this->prm->mme_gpo_id) ? ',mme_gpo_id:' . $this->prm->mme_gpo_id . ''
            : '') . '
                    ' . (isset($this->prm->mme_ur_id) ? ',mme_ur_id:' . $this->prm->mme_ur_id . ''
            : '') . '
                    ' . (isset($this->prm->mme_id) ? ',mme_id:' . $this->prm->mme_id . ''
            : '') . '
                });
            });
        ');
    }

    //fin metodo addMmeFolio
    //METODO QUE GUARDA LA INFORMACION DEL FOLIO
    private function abcFolios()
    {
        $observacion = '';
        $abc = $this->prm->abc;

        $this->prm->mme_id = isset($this->prm->mme_id) && $this->prm->mme_id != ''
        ? $this->prm->mme_id : '0'; // el trigger pone el folio para las ur
        $this->prm->oficioSefin = isset($this->prm->oficioSefin) && $this->prm->oficioSefin
        != '' ? $this->prm->oficioSefin : '-';
        $this->prm->oficioSolicitudSefin = isset($this->prm->oficioSolicitudSefin)
        && $this->prm->oficioSolicitudSefin != '' ? $this->prm->oficioSolicitudSefin
        : '-';

        if ($this->ur || $this->ue) {
            $this->prm->mme_gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $this->prm->mme_ur_id = $_SESSION[_S]['clasAdm-ur'];
            $this->prm->emo_id = 1;
        } else {
            $this->prm->mme_gpo_id = (isset($this->prm->mme_gpo_id) ? $this->prm->mme_gpo_id : $_SESSION[_S]['clasAdm-gpo']);
            $this->prm->mme_ur_id = (isset($this->prm->mme_ur_id) ? $this->prm->mme_ur_id : $_SESSION[_S]['clasAdm-ur']);
            $this->prm->emo_id = 17;
            ($this->prm->nivelRevision != 'revisionSEFIN') ? $this->prm->omo_id = 'B' : $this->prm->omo_id;
        }
        //CONSULTA INFORMACIÓN DEL FOLIO
        $mme = new ModificacionMeta($this->prm);
        $mme->select();
        $elimina = $this->model->getEliminaFolio($mme);
        switch ($abc) {

            case 'a':
                $this->prm->fechaFolio = date('Y-m-d');
                $this->prm->concepto = str_replace(
                    ['+', '%2C', '%2F'],
                    [' ', ',', '/'],
                    $this->prm->concepto
                );
                $this->prm->origen = 'S';
                $this->prm->vobo = '0';
                $observacion = ' capturado';
                break;
            case 'b':
                $this->prm->mme_ue_id = $mme->getMme_ue_id();
                $this->prm->emo_id = $mme->getEmo_id();
                $this->prm->fechaFolio = $mme->getFechaFolio();
                $this->prm->concepto = $mme->getConcepto();
                $this->prm->clave = $mme->getClave();
                $this->prm->subsecretaria = $mme->getSubsecretaria();
                $this->prm->mmd_id = $mme->getMmd_id();
                $this->prm->tfm_id = $mme->getTfm_id();
                $this->prm->tmd_id = $mme->getTmd_id();
                $this->prm->origen = $mme->getorigen();
                $this->prm->clave = $mme->getClave();
                $this->prm->vobo = $mme->getVobo();
                if ($elimina['registros'] == 0) {
                    $observacion = ' eliminado';
                } else {
                    $this->mixScript('notify("El folio no puede ser eliminado ya que tiene movimientos");');
                }
                break;
            case 'c':
                $this->prm->fechaFolio = $mme->getFechaFolio();
                $this->prm->emo_id = $mme->getEmo_id();
                $this->prm->origen = $mme->getOrigen();
                $this->prm->clave = $mme->getClave();
                $this->prm->concepto = str_replace(
                    ['+', '%2C', '%2F'],
                    [' ', ',', '/'],
                    $this->prm->concepto
                );
                $observacion = ' actualizado';
                break;
        }

        if ($observacion != '') {
            if ($abc == 'b') {

                //Elimina las firmas
                $sql = 'DELETE FROM ' . BD_CTRL . '.scp_ModificacionMetaFirma WHERE omo_id= ? AND mme_gpo_id= ? AND mme_ur_id = ?
                    AND mme_id = ? ;';
                $params = [$mme->getOmo_id(), $mme->getMme_gpo_id(), $mme->getMme_ur_id(), $mme->getMme_id()];
                $elimina = new AccesoDatos();
                $elimina->conectar();
                $elimina->setSql($sql);
                $elimina->consultar($params);
                $elimina->desconectar();
            }
            $this->prm->mme_id = ModificacionMeta::abc($this->prm); // linea para insertar, modificar y consultar segun el paramentro de abc

            if ($mme->getMme_id() != '0' && $mme->getMme_id() != '') { // comparacion para setear el mme_id para la bitacora
                $this->prm->mme_id = $mme->getMme_id();
            }

            if ($abc == 'a') { // paramentros adiccionales a agregar en la bitacora
                $mme = new ModificacionMeta($this->prm);
                $mme->select();
                $this->prm->clave = $mme->getClave();
            }

            $observacion = 'Folio ' . $this->prm->mme_id . $observacion;
            $this->prm->observacion = $observacion;
            $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? AccesoDatos::getIP() : '127.0.0.1';
            $this->bitacora('M', $this->prm); // insertamos en la bitacora
            $this->mixScript('notify("' . $observacion . '","success");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios"
                    ' . (isset($this->prm->omo_id) ?
                ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->mme_gpo_id) ?
                ',mme_gpo_id:' . $this->prm->mme_gpo_id . '' : '') . '
                    ' . (isset($this->prm->mme_ur_id) ?
                ',mme_ur_id:"' . $this->prm->mme_ur_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision
                != '' ?
                ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ');
        }
    }

    // fin metodo abcFolios

    /* METODO QUE MUESTRA EL HISTORIAL DEL FOLIO */

    public function tablaHistorialFolio()
    {
        $body = '';
        $this->setContenedor('divContenedorGeneralModal');
        $mod = new ModificacionMeta($this->prm);

        $sql = "
            SELECT DATE_FORMAT(b.usr_fecha,'%d/%m/%Y') fecha, time(b.usr_fecha) hora,mme_id, emo_nombre,
            if(b.emo_id=3 or b.emo_id=5 or b.emo_id=7 or b.emo_id=9 or b.emo_id=11 or b.emo_id=13,  trim(replace(bit_observacion,'\"',' ')),'') as bit_observacion, b.usr_id,usr_nombre,b.emo_id,usr_paterno,usr_materno
            FROM
            " . BD_BITACORA . '.bit_ModificacionMeta b
            INNER JOIN ' . BD_CTRL . '.cat_EstadoModificacion using(emo_id)
            INNER JOIN ' . BD_CTRL . '.scp_ModificacionMeta m using(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN ' . BD_CTRL . '.cat_Usuario u on b.usr_id=u.usr_id
            INNER JOIN ' . BD_USUARIO . ".cat_Usuario us ON u.usr_id=us.usr_id
            WHERE m.omo_id= :omo_id
            AND m.mme_gpo_id= :mme_gpo_id
            AND m.mme_ur_id= :mme_ur_id
            AND m.mme_id= :mme_id
            AND bit_observacion not in ('MOVIMIENTO AGREGADO','MOVIMIENTO ACTUALIZADO','MOVIMIENTO ELIMINADO')
            ORDER BY b.usr_fecha desc, bit_mme
        ";
        $params = [':omo_id' => $mod->getOmo_id(), ':mme_gpo_id' => $mod->getMme_gpo_id(), ':mme_ur_id' => $mod->getMme_ur_id(), ':mme_id' => $mod->getMme_id()];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        while ($row = $this->extraer()) {
            $body .= '
            <tr>
                <td>' . $row['fecha'] . '</td>
                <td>' . $row['hora'] . '</td>
                <td>' . $row['emo_id'] . ' - ' . $row['emo_nombre'] . '</td>
                <td>' . $row['bit_observacion'] . '</td>
                <td>' . $row['usr_id'] . ' - ' . $row['usr_nombre'] . ' ' . $row['usr_paterno'] . ' ' . $row['usr_materno'] . '</td>
            </tr>';
        }

        $ur = new UnidadResponsable();
        $ur->setGpo_id($mod->getMme_gpo_id());
        $ur->setUr_id($mod->getMme_ur_id());
        $ur->select();

        $modalHistorial = '
        <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
        <div class="modal-dialog modal-xl" style="width: 100% !IMPORTANT;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel"></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table  class="table table table-bordered table-hover">
                                <tbody>
                                        <tr>
                                            <th width="15%">Unidad Responsable </th>
                                            <td colspan="5">' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . ' - ' . $ur->getNombre() . '</td>
                                        </tr>
                                        <tr>
                                            <th>Folio</th>
                                            <td width="20%">' . $mod->getMme_id() . '</td>
                                            <th width="10%">Tipo de Adecuación</th>
                                            <td width="28%">ADECUACION DE METAS</td>
                                            <th width="15%">Modalidad de adecuación</th>
                                            <td width="28%">EXTERNA</td>
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
                        <button type="button" class="btn btn-outline-default btn btn-outline-danger me-2" data-bs-dismiss="modal" aria-hidden="true">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modalHistorial);

        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });
            $("#myModal").modal().show();
            $("#cerrar").click(function(){ $("#myModal").remove(); });
            $(".close").click(function(){ $("#myModal").remove(); });
        ');
    }
    /* MÉTODO PARA AGREGAR LA OBRA/ACTIVIDAD AL FOLIO */

    private function addMmeModificacion()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //Definicion de variables
        $ejecutora = '';
        $ue_id = '';
        if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {
            $mme = new ModificacionMeta($this->prm);
            $ejecutora = 'AND ue_id = ' . $mme->getMme_ue_id();
            $ue_id = $mme->getMme_ue_id();
        }
        $sql = "
            SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id,'-',oa_nombre) enc_prog,
            GROUP_CONCAT(pii_id ORDER BY pii_id SEPARATOR ',') AS pii
            FROM " . BD_CTRL . '.cat_ObraActividad
            INNER JOIN ' . BD_CTRL . '.enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_MetaActividad USING(epg_id)
            WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . "
            $ejecutora
            AND eoa_id = 4 AND toa_id = 1 AND oa_id!=00
            GROUP BY epg_id;
        ";
        //Combo encuadre programatico
        $encProgramatico = new stdClass();
        $encProgramatico->id = 'epg_id';
        $encProgramatico->width = '1000px';
        $encProgramatico->comoAtr = 'epg_id,pii';
        $encProgramatico->requerid = true;
        $encProgramatico->sql = $sql;
        $this->recargaCombo($encProgramatico);

        $parametros = [
            'filtro'          => 'presupuestoValidada',
            'metodo'          => 'abcModificacion',
            'omo_id'          => $this->prm->omo_id,
            'pantalla'        => $this->pantalla,
            'mme_gpo_id'      => $this->prm->mme_gpo_id,
            'mme_ur_id'       => $this->prm->mme_ur_id,
            'mme_id'          => $this->prm->mme_id,
            'tri_id'          => $this->tri_id,
            'mact_modificado' => 0,
            'abc'             => 'a',
            'nivelRevision'   => $this->nivelRevision,
        ];

        $html = '';
        $html .= CatalogoEncuadreProgramatico::ponModalEncuadre($parametros);

        $this->mixHtml($html);
        $this->mixScript('
            $("#myModal").modal("show");
            $("#cerrar").click(function(){ $("#myModal").modal("hide") });

        ');
    }
    /* METODO QUE GUARDA LOS MOVIMIENTOS DEL FOLIO */

    private function abcModificacion()
    {
        $this->mixScript('$("#myModal").modal("hide");');
        $mensaje = '';
        $men = '';
        $mme = new ModificacionMeta($this->prm);

        $abc = $this->prm->abc;
        switch ($abc) {
            case 'a':
                //Verifica si ya seha registrado la obra/actividad
                $registroObraActividad = $this->buscaObraActividad();
                if ($registroObraActividad) {
                    $this->mixScript('
                        notify("La actividad ya se ha registrado","warning")
                    ');
                } else {
                    //Registra la obra/actividad
                    $piiArray = array_filter(
                        array_map('intval', explode(',', $this->prm->pii_id))
                    );
                    foreach ($piiArray as $pii) {
                        $this->prm->pii_id = $pii;
                        DetalleModificacionMeta::abc($this->prm);
                        $mensaje = 'MOVIMIENTO AGREGADO AL FOLIO, ENCUADRE PROGRAMATICO ' . $this->prm->epg_id . '';
                        $men = 'Actividad agregada';
                    }
                }
                break;
            case 'b':
                //Elimina la obra/actividad
                DetalleModificacionMeta::abc($this->prm);
                $mensaje = 'MOVIMIENTO ELIMINADO, ENCUADRE PROGRAMATICO ' . $this->prm->epg_id . '';
                $men = 'Actividad eliminada';
                break;
        }
        if ($mensaje != '') {
            $this->prm->observacion = $mensaje;
            $this->prm->ip = $this->ip;
            $this->prm->emo_id = $mme->getEmo_id();
            $this->bitacora('D', $this->prm);
            $this->mixScript('notify("' . $men . '");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:"' . $mme->getOmo_id() . '",
                    mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                    mme_ur_id:"' . $mme->getMme_ur_id() . '",
                    mme_id:' . $mme->getMme_id() . '
                    ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
            ');
        }
    }
    /* MÉTODO QUE BUSCA LA ACTIVIDAD EN FOLIO */

    private function buscaObraActividad()
    {
        $sql = 'SELECT * FROM ' . BD_CTRL . '.scp_DetalleModificacionMeta
            WHERE omo_id = ?
            AND mme_gpo_id = ?
            AND mme_ur_id = ?
            AND mme_id = ?
            AND epg_id = ? ;
            ';
        $params = [$this->prm->omo_id, $this->prm->mme_gpo_id, $this->prm->mme_ur_id, $this->prm->mme_id, $this->prm->epg_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            return true;
        } else {
            return false;
        }

    }
    /* MÉTODO QUE REGISTRA EN LA BITACORA DEL MAESTRO O DETALLE ENVIANDO EL PARAMETRO DEL TIPO M= MODIFICACION META  D = DETALLE MODIFICACION META */

    private function bitacora($tipo, $prm)
    {
        $sql = '';
        if ($tipo != '') {
            if (isset($prm->mme_id) && $prm->mme_id != '') {
                $prm->abc = 'a';
                if ($tipo == 'M') {
                    BitacoraModificacionMeta::abc($prm);
                } elseif ($tipo == 'D') {
                    BitacoraDetalleModificacionMeta::abc($prm);
                }
                $mme_gpo_id = $prm->mme_gpo_id;
                $mme_ur_id = $prm->mme_ur_id;

                $rutalog = 'log/AdecuacionesMetas-' . $_SESSION[_S]['usr_id'] . '-' . $mme_gpo_id . $mme_ur_id . '.txt';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d H-i-s');
                foreach ($prm as $field => $value) {
                    if (is_string($value)) {
                        $tempdata .= '|' . $field . ' = ' . $value;
                    }
                }
                $tempdata .= '|IPADDRESS=' . isset($_SESSION[_S]['ses_ip']);
                $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];
                if ($sql != '') {
                    $tempdata .= '|CONSULTA=' . $sql . '';
                }
                fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
                fclose($file);
                $peso = filesize($rutalog);
                $peso = ($peso / 1024) / 1024;

                if ($peso > 8) {
                    $rutamodificada = 'log/AdecuacionesMetas-' . $_SESSION[_S]['usr_id'] . '-';
                    $rutamodificada .= $mme_gpo_id . $mme_ur_id . '-' . $hoy . '.txt';
                    rename($rutalog, $rutamodificada);
                    $file = fopen($rutalog, 'a');
                    fwrite($file, 'Creado:' . $hoy . PHP_EOL);
                    fclose($file);
                }
            }
        } else {
            $this->mixScript('
                notify("Hubo un problema al registrar en la bitacora","error");
            ');
            return false;
        }
    }

    //METODO QUE ELIMINA MASIVAMENTE LOS MOVIMIENTOS DE UN FOLIO
    private function eliminarMovimientos()
    {
        $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_7';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
        ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $id = $_SESSION[_S]['usr_id'];

        if (isset($this->prm->omo_id) && isset($this->prm->mme_gpo_id) && isset($this->prm->mme_ur_id)
            && isset($this->prm->mme_id)) {
            //INSTANCIANDO LA ENTIDAD MODIFICACION META
            $mme = new ModificacionMeta($this->prm);
            $sql = 'select count(*) as numero from ' . BD_CTRL . '.scp_DetalleModificacionMeta
                WHERE omo_id= ?
                AND mme_gpo_id= ?
                AND mme_ur_id= ?
                AND mme_id= ? ;';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$mme->getOmo_id(), $mme->getMme_gpo_id(), $mme->getMme_ur_id(), $mme->getMme_id()]);
            $numeroMov = $this->extraer();
            $sqlObs = 'SET @bit_observacion = ? ,@usr_ip = INET_ATON( ? ),@usr_id = ?';
            $this->setSql($sqlObs);
            $this->consultar([$observacion, $ip, $id]);

            $sqlObras = '
                SELECT omo_id, mme_gpo_id, mme_ur_id, mme_id, epg_id, pii_id, tri_id, mact_modificado
                FROM ' . BD_CTRL . '.scp_DetalleModificacionMeta
                WHERE omo_id = ?
                AND mme_gpo_id = ?
                AND mme_ur_id = ?
                AND mme_id = ?
                ;
            ';
            $this->setSql($sqlObras);
            $this->consultar([$mme->getOmo_id(), $mme->getMme_gpo_id(), $mme->getMme_ur_id(), $mme->getMme_id()]);
            while ($row = $this->extraer()) {
                $parametrosBitacora = (object) $row;
                $parametrosBitacora->observacion = 'MOVIMIENTO ELIMINADO DE MANERA MASIVA';
                $parametrosBitacora->ip = $ip;
                $this->bitacora('D', $parametrosBitacora);
            }
            //ELIMINA LA MODIFICACION DE METAS TEMPORAL
            $sql = ' DELETE FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                WHERE omo_id = ?
                AND mme_gpo_id = ?
                AND mme_ur_id = ?
                AND mme_id =  ?;';
            $params = [$this->prm->omo_id, $this->prm->mme_gpo_id, $this->prm->mme_ur_id, $this->prm->mme_id];
            $this->setSql($sql);
            $this->consultar($params);
            //ELIMINA LOS MOVIMIENTOS DEL FOLIO
            $sql2 = 'DELETE FROM ' . BD_CTRL . '.scp_DetalleModificacionMeta
                WHERE omo_id= ?
                AND mme_gpo_id= ?
                AND mme_ur_id= ?
                AND mme_id= ? ;';
            $param2 = [$mme->getOmo_id(), $mme->getMme_gpo_id(), $mme->getMme_ur_id(), $mme->getMme_id()];
            $this->setSql($sql2);
            $this->consultar($param2);
            $this->desconectar();

            $this->mixScript('
                notify("' . $numeroMov['numero'] . ' movimientos eliminados");
            ');

            $this->tablaModificaciones();
        }
    }

    private function modificarMetas()
    {
        $lectura = (!isset($this->prm->lectura) ? false : true);
        $omo_id = $this->prm->omo_id;
        $mme_gpo_id = $this->prm->mme_gpo_id;
        $mme_ur_id = $this->prm->mme_ur_id;
        $mme_id = $this->prm->mme_id;
        $botonRegresar = '';
        $botonEliminar = '';
        $mme = new ModificacionMeta($this->prm);
        $mme->select();
        $emo_id = $mme->getEmo_id();
        $this->prm->nivelRevision = $this->nivelRevision;

        $lectura = false;
        switch ($emo_id) {
            case 1:
                $lectura = false;
                break;
            case 3:
                $lectura = false;
                break;
            case 17:
                $lectura = false;
                break;
            case 11:
                if ($omo_id == 'B') {
                    $lectura = false;
                } else {
                    $lectura = true;
                }

                break;
            default:
                $lectura = true;
                break;
        }
        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->value = 'Regresar';
        $regresar->class = 'me-2';
        $regresar->color = 'gris';
        $regresar->icon = 'fa-solid fa-circle-left me-1';
        $botonRegresar = $this->ponBoton($regresar);
        if (!$lectura) {
            //BOTON CANCELAR, CANCELA LA MODIFICACION DE METAS
            $eliminarMov = new stdClass();
            $eliminarMov->id = 'eliminar';
            $eliminarMov->value = 'Eliminar';
            $eliminarMov->class = 'me-2';
            $eliminarMov->color = 'rojo';
            $eliminarMov->icon = 'fa-solid fa-trash me-1';
            $botonEliminar = $this->ponBoton($eliminarMov);
        }
        $sql = 'SELECT epg_id,prg_id,spr_id,py_id,oa_id,oa_nombre
            FROM ' . BD_CTRL . '.scp_ModificacionMeta
            INNER JOIN ' . BD_CTRL . '.scp_DetalleModificacionMeta USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN ' . BD_CTRL . '.enc_Programatico  USING(epg_id)
            INNER JOIN ' . BD_CTRL . '.cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            ' . (($lectura) ? 'INNER JOIN ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral USING(omo_id,mme_gpo_id,mme_ur_id,mme_id,epg_id)'
            : '') . '
            WHERE omo_id = :omo_id AND mme_gpo_id = :mme_gpo_id  AND mme_ur_id = :mme_ur_id  AND mme_id = :mme_id
            AND epg_id = :epg_id
            GROUP BY epg_id;';
        $params = [':omo_id' => $omo_id, ':mme_gpo_id' => $mme_gpo_id, ':mme_ur_id' => $mme_ur_id, ':mme_id' => $mme_id, ':epg_id' => $this->prm->epg_id];
        $this->agregaTools($botonRegresar . ' ' . $botonEliminar);
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);

        $this->desconectar();

        $htm = '';
        $htm .= '<table  class="table table table-bordered table-hover">';
        $htm .= '
            <tr>
                <th class="" style="text-align:center; background: rgb(255, 234, 168)" colspan="1">Programa</th>
                <th class="" style="text-align:center; background: rgb(255, 234, 168)" colspan="1">Subprograma</th>
                <th class="" style="text-align:center; background: rgb(255, 234, 168)" colspan="1">Proyecto</th>
                <th class="" style="text-align:center; background: rgb(255, 234, 168)" colspan="1">Obra/Actividad</th>
                <th class="" style="text-align:center; background: rgb(255, 234, 168)" colspan="8">Nombre</th>
                <th class="" style="text-align:center; background: rgb(255, 234, 168)" colspan="1">Ver metas</th>
            </tr>
        ';

        $cambioClase = true;
        while ($row = $this->extraer()) {

            $clase = ($cambioClase) ? 'class="warning"' : '';
            $htm .= '<tr>
                <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="8">' . $row['oa_nombre'] . '</td>
                <td ' . $clase . ' align="center" colspan="1" >
                    <button type="button" id="verMetas" icon="eye-open" title="Opción para ver metas de la actividad" class="verMetas btn btn-outline-secondary" epg_id=' . $row['epg_id'] . '>
                        <i class="fa-regular fa-eye"></i>
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
                    mme_gpo_id:"' . $mme_gpo_id . '",
                    mme_ur_id:"' . $mme_ur_id . '",
                    mme_id:' . $mme_id . ',
                    epg_id:$(this).attr("epg_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    ' . ((!$lectura) ? ',lectura:false' : ',lectura:true') . '
                });
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones"
                    ' . (isset($omo_id) ? ',omo_id:"' . $omo_id . '"' : '') . '
                    ' . (isset($mme_gpo_id) ? ',mme_gpo_id:"' . $mme_gpo_id . '"' : '') . '
                    ' . (isset($mme_ur_id) ? ',mme_ur_id:"' . $mme_ur_id . '"' : '') . '
                    ' . (isset($mme_id) ? ',mme_id:"' . $mme_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                });
            });
        ');
        $strAlertHead = '$("#eliminar").click(function(){ ';
        $strAlertHeadMethod = 'alertify.confirm("ELIMINAR MOVIMIENTO", "¿ Realmente desea eliminar el movimiento <h5 style=\"display:inline\"></h5> ?",
                function(){
                comunica("' . $this->pantalla . '",{
                        metodo:"borrarDatosModificados",
                        omo_id:"' . $this->prm->omo_id . '",
                        mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                        mme_ur_id:"' . $this->prm->mme_ur_id . '",
                        mme_id:"' . $this->prm->mme_id . '",
                        epg_id:"' . $this->prm->epg_id . '"
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                        });
                    },function(){},).set("defaultFocus","cancel").set("labels", {ok:"Aceptar",cancel:"Cancelar"});';
        $strAlertMethodVar = '});';

        $this->mixScript($strAlertHead . $strAlertHeadMethod . $strAlertMethodVar);
    }

    //METODO QUE ENLISTARA LAS METAS POR ACTIVIDAD PROGRAMÁTICA
    private function listaMetas()
    {
        $this->setContenedor('oa' . $this->prm->epg_id);
        $m = new ModificacionMeta($this->prm);
        $lectura = $this->prm->lectura;
        $consulta = ($lectura) ? ' INNER JOIN ' : ' LEFT JOIN ';
        $botonRegresar = '';
        $botonEliminar = '';
        $botonEndMeta = '';
        //VALIDA SI ES LECTURA
        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->value = 'Regresar';
        $regresar->class = 'me-2';
        $regresar->color = 'gris';
        $regresar->icon = 'fa-solid fa-circle-left me-1';
        $botonRegresar = $this->ponBoton($regresar);
        if (!$lectura) {
            //BOTON CANCELAR, CANCELA LA MODIFICACION DE METAS
            $eliminarMov = new stdClass();
            $eliminarMov->id = 'eliminar';
            $eliminarMov->value = 'Eliminar';
            $eliminarMov->class = 'me-2';
            $eliminarMov->color = 'rojo';
            $eliminarMov->icon = 'fa-solid fa-trash me-1';
            $botonEliminar = $this->ponBoton($eliminarMov);

        }

        //AGREGA LOS BOTONES A LA BARRA DE HERRAMIENTAS
        $this->agregaTools($botonRegresar . ' ' . $botonEliminar . ' ' . $botonEndMeta);

        //CONSTRUYE LA CONSULTA PARA LAS METAS DE LA ACTIVIDAD
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre, pdi_mir,
            pdi_nombre,ctInd.umv_id,ctInd.umv_nombre, umi_nombre, tiv_nombre,
            cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
            mac_descripcion,epg_id,mac_original,mac_modificado vigente, macm_id
                FROM ' . BD_CTRL . '.enc_Programatico enc
                JOIN ' . BD_CTRL . '.cat_MetaActividad ma USING(epg_id)
                ' . $consulta . '  (
                    SELECT omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,
                    CASE WHEN MAX(tri_id) =1 THEN
                    SUM(mact_modificado)
                    ELSE 0.00 END AS mod_trimestre1,
                    CASE WHEN MAX(tri_id) =2 THEN
                    SUM(mact_modificado)
                    ELSE 0.00 END AS mod_trimestre2,
                    CASE WHEN MAX(tri_id) =3 THEN
                    SUM(mact_modificado)
                    ELSE 0.00 END AS mod_trimestre3,
                    CASE WHEN MAX(tri_id) =4 THEN
                    SUM(mact_modificado)
                    ELSE 0.00 END AS mod_trimestre4
                    FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                    WHERE  epg_id = :epg_id AND omo_id= :omo_id and mme_gpo_id= :mme_gpo_id and mme_ur_id= :mme_ur_id and mme_id= :mme_id
                    GROUP BY omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id
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
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
        $params = [':epg_id' => $this->prm->epg_id, ':omo_id' => $this->prm->omo_id, ':mme_gpo_id' => $this->prm->mme_gpo_id, ':mme_ur_id' => $this->prm->mme_ur_id, ':mme_id' => $this->prm->mme_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $cambioClase = '';

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
            $htm .= '<tr>
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
        }

        $htm .= '</table>';
        $this->mixHtml($htm);
        $this->mixScript('
            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones"
                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->mme_gpo_id) ? ',mme_gpo_id:"' . $this->prm->mme_gpo_id . '"' : '') . '
                    ' . (isset($this->prm->mme_ur_id) ? ',mme_ur_id:"' . $this->prm->mme_ur_id . '"' : '') . '
                    ' . (isset($this->prm->mme_id) ? ',mme_id:"' . $this->prm->mme_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            });

            $("#eliminar").click(function(){
                alertify.confirm("ELIMINAR MOVIMIENTO", "¿ Realmente desea eliminar el movimiento <h5 style=\"display:inline\"></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"borrarDatosModificados",
                            omo_id:"' . $this->prm->omo_id . '",
                            mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                            mme_ur_id:"' . $this->prm->mme_ur_id . '",
                            mme_id:"' . $this->prm->mme_id . '",
                            epg_id:"' . $this->prm->epg_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                            }
                        );
                    },
                    function(){},
                ).set("defaultFocus","cancel").set("labels", {ok:"Eliminar",cancel:"Cancelar"});
            });

            $(".verMetasDet").click(function(){
                $(".divMetasDet").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"metas",
                    omo_id:"' . $this->prm->omo_id . '",
                    mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                    mme_ur_id:"' . $this->prm->mme_ur_id . '",
                    mme_id:"' . $this->prm->mme_id . '",
                    pii_id:$(this).attr("pii_id"),
                    epg_id:$(this).attr("epg_id"),
                    macm_id:$(this).attr("macm_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    ' . ((!$lectura) ? ',lectura:false' : ',lectura:true') . '
                });
            });
        ');
    }

    //MUESTRA LA INFORMACION CALENDARIZADA DE LA META
    private function metas()
    {
        $this->setContenedor('oa' . $this->prm->epg_id . $this->prm->pii_id);
        $m = new ModificacionMeta($this->prm);
        $lectura = $this->prm->lectura;
        $consulta = ($lectura) ? ' INNER JOIN ' : ' LEFT JOIN ';
        $this->prm->gpo_id = $this->prm->mme_gpo_id;
        $this->prm->ur_id = $this->prm->mme_ur_id;
        $this->prm->eri_id = $this->prm->pii_id;
        $this->prm->tri_id = $this->tri_id;

        $abc = 'c';
        $this->prm->nim_id = 4;

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

        //CONSTRUYE LA CONSULTA PARA OBTENER LA MODIFICACION DE METAS
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre,
            pdi_nombre,ctInd.umv_id,umi_nombre, "S" AS tiv,
            cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
            mac_descripcion,epg_id,mac_original, macm_id, i.umi_id, macm_ValorDeseado, tri_id,
            mact_modificado,tmf_id,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id=4 AND tri_id=1) AS est_trimestre1,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id=4 AND tri_id=2) AS est_trimestre2,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id = 4 AND tri_id = 3) AS est_trimestre3,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id = 4 AND tri_id = 4) AS est_trimestre4,
                i.tiv_id,tv.tiv_nombre
                FROM ' . BD_CTRL . '.enc_Programatico enc
                JOIN ' . BD_CTRL . '.cat_MetaActividad ma USING(epg_id)
                ' . $consulta . '  (
                    SELECT omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,mact_modificado, macm_ValorDeseado, tmf_id, tri_id
                    FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                    WHERE  epg_id = :epg
                    AND pii_id = :pii
                    AND omo_id= :omo and mme_gpo_id= :gpo and mme_ur_id=:ur and mme_id= :mme
                    GROUP BY omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id
                ) b using (pii_id,epg_id)
                JOIN ' . BD_CTRL . '.cat_Subprograma cs USING(gpo_id,ur_id,prg_id,spr_id)
                JOIN ' . BD_CTRL . '.vin_ProductoIntermedioIndicador USING(pii_id)
                JOIN ' . BD_CTRL . '.cat_Indicador i USING(ind_id,nim_id)
                JOIN ' . BD_CTRL . '.cat_ProductoIntermedio USING(pdi_id)
                JOIN ' . BD_CTRL . '.cat_UnidadMedidaVariable ctInd USING(umv_id)
                JOIN ' . BD_CTRL . '.cat_UnidadMedidaIndicador USING(umi_id)
                JOIN ' . BD_CTRL . '.cat_TipoValor tv ON tv.tiv_id = i.tiv_id 
            WHERE
            epg_id = :epg
            AND pii_id = :pii
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
        $params = [':gpo' => $this->prm->mme_gpo_id, ':ur' => $this->prm->mme_ur_id, ':epg' => $this->prm->epg_id, ':pii' => $this->prm->pii_id, ':omo' => $this->prm->omo_id, ':mme' => $this->prm->mme_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $datos = $this->extraer();

        $metaActividad = new MetaActividadMir($this->prm);
        $historicoMeta = new HistoricoMetaActividadMir($this->prm);

        $trimestres = '';
        $div = '<div class="row row-cols-md-4">';
        $datosMeta = '
            <form id="calendario">
                <table class="table table-hover table-bordered" >
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        ' . ((!$lectura) && !(($capturaAv['cap_trimestre1'] == 1 && $capturaAv['alcanzado'] != 0 && $this->tri_id == 1) || ($capturaAv['cap_trimestre2'] == 1 && $capturaAv['alcanzado'] != 0 && $this->tri_id == 2)
                        || ($capturaAv['cap_trimestre3'] == 1 && $capturaAv['alcanzado'] != 0 && $this->tri_id == 3) || ($capturaAv['cap_trimestre4'] == 1 && $capturaAv['alcanzado'] != 0 && $this->tri_id == 4)) ? '<td align="right">
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
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1() != '' ? $historicoMeta->getModificadoT1() : $historicoMeta->getNumeradorT1();
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
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2() != '' ? $historicoMeta->getModificadoT2() : $historicoMeta->getNumeradorT2();
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
            $trimestre3Cal1->value = $historicoMeta->getModificadoT3() != '' ? $historicoMeta->getModificadoT3() : $historicoMeta->getNumeradorT3();
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
            $trimestre4Cal1->value = $historicoMeta->getModificadoT4() != '' ? $historicoMeta->getModificadoT4() : $historicoMeta->getNumeradorT4();

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
        $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';

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
                        gpo_id:"' . $this->prm->mme_gpo_id . '",
                        ur_id:"' . $this->prm->mme_ur_id . '",
                        mme_id:"' . $this->prm->mme_id . '",
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
    }

    private function actualizarMetas()
    {

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

        $this->prm->origen = 'Controlador Modificacion Metas';
        $this->prm->tmf_id = '';
        $this->prm->auxoriginal = $this->prm->original;

        $modificado = str_replace(',', '', $this->prm->modificado);
        $original = str_replace(',', '', $this->prm->original);

        $this->conectar();
        switch ($this->prm->abc) {
            case 'c':
                $this->setSql('
            SELECT *
            FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
            WHERE omo_id = ?
            AND mme_gpo_id = ?
            AND mme_ur_id = ?
            AND mme_id = ?
            AND epg_id = ?
            AND pii_id = ?
            AND tri_id = ?
        ');
                $parametros = [
                    $this->prm->omo_id,
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                    $this->prm->mme_id,
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
                        UPDATE ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                        SET mact_modificado = ' . $modificado . ', macm_ValorDeseado = ' . $original . ', tmf_id = "' . $movimiento . '"
                        WHERE omo_id = ?
                        AND mme_gpo_id = ?
                        AND mme_ur_id = ?
                        AND mme_id = ?
                        AND epg_id = ?
                        AND pii_id = ?
                        AND tri_id = ?
                    ');
                    $parametros = [
                        $this->prm->omo_id,
                        $this->prm->gpo_id,
                        $this->prm->ur_id,
                        $this->prm->mme_id,
                        $this->prm->epg_id,
                        $this->prm->pii_id,
                        $this->tri_id,
                    ];
                    $this->consultar($parametros);

                    $mensaje = 'Meta actualizada temporalmente';
                    $observacion = 'META ACTUALIZADA EN FOLIO DE ADECUACIONES S_1_7';
                    $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_DetalleModificacionMeta
                    (bit_observacion,omo_id,mme_gpo_id,mme_ur_id,mme_id,epg_id,pii_id,tri_id,mact_modificado,usr_ip,usr_id)
                    VALUES ("' . $observacion . '",:omo_id, :mme_gpo_id, :mme_ur_id, :mme_id, :epg_id, :pii_id, :tri_id, :modificado, INET_ATON(:ip), :usr_id);
                ');
                    $params = [
                        ':omo_id'     => $this->prm->omo_id,
                        ':mme_gpo_id' => $this->prm->gpo_id,
                        ':mme_ur_id'  => $this->prm->ur_id,
                        ':mme_id'     => $this->prm->mme_id,
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
                        INSERT INTO ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                        (omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,tri_id,mact_modificado,macm_ValorDeseado,tmf_id,usr_id)
                        VALUES (:omo_id, :mme_gpo_id, :mme_ur_id, :mme_id, :pii_id, :epg_id, :tri_id, :modificado, :original, :tmf, :usr_id);
                    ');
                    $params = [
                        ':omo_id'     => $this->prm->omo_id,
                        ':mme_gpo_id' => $this->prm->gpo_id,
                        ':mme_ur_id'  => $this->prm->ur_id,
                        ':mme_id'     => $this->prm->mme_id,
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
                    $observacion = 'META MODIFICADA EN FOLIO DE ADECUACIONES S_1_7';
                    $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_DetalleModificacionMeta
                    (bit_observacion,omo_id,mme_gpo_id,mme_ur_id,mme_id,epg_id,pii_id,tri_id,mact_modificado,usr_ip,usr_id)
                    VALUES ("' . $observacion . '",:omo_id, :mme_gpo_id, :mme_ur_id, :mme_id, :epg_id, :pii_id, :tri_id, :modificado, INET_ATON(:ip), :usr_id);
                ');
                    $params = [
                        ':omo_id'     => $this->prm->omo_id,
                        ':mme_gpo_id' => $this->prm->gpo_id,
                        ':mme_ur_id'  => $this->prm->ur_id,
                        ':mme_id'     => $this->prm->mme_id,
                        ':epg_id'     => $this->prm->epg_id,
                        ':pii_id'     => $this->prm->pii_id,
                        ':tri_id'     => $this->tri_id,
                        ':modificado' => $modificado,
                        ':ip'         => $ip,
                        ':usr_id'     => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($params);
                }

                $sql2 = '
                    UPDATE ' . BD_CTRL . '.scp_DetalleModificacionMeta SET tri_id = ' . $this->tri_id . ', mact_modificado = ' . $modificado . '
                    WHERE omo_id = :omo
                    AND mme_gpo_id = :gpo
                    AND mme_ur_id = :ur
                    AND mme_id = :mme
                    AND epg_id = :epg 
                    AND pii_id = :pii;
                ';
                $params2 = [':omo' => $this->prm->omo_id, ':gpo' => $this->prm->gpo_id, ':ur' => $this->prm->ur_id, ':mme' => $this->prm->mme_id, ':epg' => $this->prm->epg_id, ':pii' => $this->prm->pii_id];
                $this->setSql($sql2);
                $this->consultar($params2);

                break;
        } // Fin switch

        $this->desconectar();

        $this->mixScript('
            $("#myModal,#modalDeTablatbl-metasIndicador").modal("hide");
            comunica("' . $this->pantalla . '",{
                metodo:"tablaModificaciones",
                omo_id:"' . $this->prm->omo_id . '",
                mme_gpo_id:' . $this->prm->gpo_id . ',
                mme_ur_id:' . $this->prm->ur_id . ',
                prgs_id:' . $this->prm->prgs_id . ',
                sprs_id:' . $this->prm->sprs_id . ',
                mme_id:' . $this->prm->mme_id . '
                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
            });
        ');

        $this->mixScript('
                notify("' . $mensaje . '","warning");
            ');

    }

    //METODO QUE VERIFICA SI LA META YA FUE MODIFICADA
    private function consultaMetaModificada(
        $omo_id,
        $mme_gpo_id,
        $mme_ur_id,
        $mme_id,
        $epg_id,
        $pii_id,
        $tri_id
    ) {
        $this->conectar();
        $this->setSql('
            SELECT * FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral WHERE
            omo_id = ? AND
            mme_gpo_id = ? AND
            mme_ur_id = ? AND
            mme_id = ? AND
            epg_id = ? AND
            pii_id = ? AND
            tri_id = ?;
        ');
        $this->consultar([$omo_id, $mme_gpo_id, $mme_ur_id, $mme_id, $epg_id, $pii_id, $tri_id]);
        $this->desconectar();
        $numeroRegistros = $this->getNumeroRegistros();
        return $numeroRegistros;
    }

    //REALIZA EL REGISTRO TRIMESTRE DE LOS TRIMESTRES MODIFICADOS
    private function abcTrimestres(
        $abc,
        $omo_id,
        $mme_gpo_id,
        $mme_ur_id,
        $mme_id,
        $pii_id,
        $epg_id,
        $tri_id,
        $mact_modificado
    ) {
        $params = [];
        $this->conectar();
        switch ($abc) {
            case 'I':
                $params = null;
                $this->setSql('
                    INSERT INTO ' . BD_TEMPORAL . '. tem_ModificacionMetaTrimestral
                    (omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,tri_id,mact_modificado,usr_id)
                    VALUES (:omo_id, :mme_gpo_id, :mme_ur_id, :mme_id, :pii_id, :epg_id, :tri_id,
                    :mact_modificado, :usr_id);
                ');
                $params = [':omo_id'   => $omo_id,
                    ':mme_gpo_id'      => $mme_gpo_id,
                    ':mme_ur_id'       => $mme_ur_id,
                    ':mme_id'          => $mme_id,
                    ':pii_id'          => $pii_id,
                    ':epg_id'          => $epg_id,
                    ':tri_id'          => $tri_id,
                    ':mact_modificado' => $mact_modificado,
                    ':usr_id'          => $_SESSION[_S]['usr_id']];
                break;
            case 'U':
                $params = null;
                $this->setSql('
                    UPDATE ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                    SET mact_modificado = :mact_modificado
                    WHERE omo_id = :omo_id
                    AND mme_gpo_id = :mme_gpo_id
                    AND mme_ur_id = :mme_ur_id
                    AND mme_id = :mme_id
                    AND epg_id = :epg_id
                    AND pii_id = :pii_id
                    AND tri_id = :tri_id;
                ');
                $params = [':mact_modificado' => $mact_modificado,
                    ':omo_id'                 => $omo_id,
                    ':mme_gpo_id'             => $mme_gpo_id,
                    ':mme_ur_id'              => $mme_ur_id,
                    ':mme_id'                 => $mme_id,
                    ':epg_id'                 => $epg_id,
                    ':pii_id'                 => $pii_id,
                    ':tri_id'                 => $tri_id];
                break;
        }
        $this->consultar($params);
        $this->desconectar();
    }

    //METODO QUE BORRA LAS METAS MODIFICADAS
    private function borrarDatosModificados()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
        ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $id = $_SESSION[_S]['usr_id'];
        $sql = 'SELECT * FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
            WHERE omo_id = :omo
            AND mme_gpo_id = :gpo
            AND mme_ur_id = :ur
            AND mme_id = :mme
            AND epg_id = :epg;';
        $params = [':omo' => $this->prm->omo_id, ':gpo' => $this->prm->mme_gpo_id, ':ur' => $this->prm->mme_ur_id, ':mme' => $this->prm->mme_id, ':epg' => $this->prm->epg_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $this->prm->pii_id = 0;
        $this->prm->tri_id = 0;
        $this->prm->mact_modificado = 0;
        //Si hay registros de metas de este folio se borran , sino unicamente se dirige a la funcion tablaFolios
        if ($this->getNumeroRegistros() > 0) {
            $datos = $this->extraer();
            $this->prm->pii_id = $datos['pii_id'];
            $this->prm->tri_id = $datos['tri_id'];
            $this->prm->mact_modificado = $datos['mact_modificado'];
            $sql = 'DELETE FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                WHERE omo_id = :omo
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                AND mme_id = :mme
                AND epg_id = :epg ;';
            $params = [':omo' => $this->prm->omo_id, ':gpo' => $this->prm->mme_gpo_id, ':ur' => $this->prm->mme_ur_id, ':mme' => $this->prm->mme_id, ':epg' => $this->prm->epg_id];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $this->desconectar();
        }
        $this->conectar();
        //ELIMINA LA OBRA/ACTIVIDAD DEL FOLIO
        $sql2 = 'DELETE FROM ' . BD_CTRL . '.scp_DetalleModificacionMeta
                WHERE omo_id = :omo
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                AND mme_id = :mme
                AND epg_id = :epg ;';
        $params2 = [':omo' => $this->prm->omo_id, ':gpo' => $this->prm->mme_gpo_id, ':ur' => $this->prm->mme_ur_id, ':mme' => $this->prm->mme_id, ':epg' => $this->prm->epg_id];
        $this->setSql($sql2);
        $this->consultar($params2);
        $this->desconectar();

        $this->prm->observacion = 'MOVIMIENTO ELIMINADO, ENCUADRE PROGRAMATICO ' . $this->prm->epg_id . '';
        $this->prm->ip = $ip;
        $this->bitacora('D', $this->prm);

        $this->mixScript('
            notify("Movimiento eliminado");
        ');

        $this->mixScript('
            comunica("' . $this->pantalla . '",{
                metodo:"tablaModificaciones"
                ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                ' . (isset($this->prm->mme_gpo_id) ? ',mme_gpo_id:"' . $this->prm->mme_gpo_id . '"'
            : '') . '
                ' . (isset($this->prm->mme_ur_id) ? ',mme_ur_id:"' . $this->prm->mme_ur_id . '"' : '') . '
                ' . (isset($this->prm->mme_id) ? ',mme_id:"' . $this->prm->mme_id . '"' : '') . '
                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
            });
        ');
    }
    /* MÉTODO QUE VERIFICA EL FOLIO */

    private function verificarFolio()
    {
        $omo_id = $this->prm->omo_id;
        $mme_gpo_id = $this->prm->mme_gpo_id;
        $mme_ur_id = $this->prm->mme_ur_id;
        $mme_id = $this->prm->mme_id;
        $sql = '
            SELECT mme_id FROM ' . BD_CTRL . '.scp_DetalleModificacionMeta
            WHERE omo_id = ?
            AND mme_gpo_id = ?
            AND mme_ur_id = ?
            AND mme_id = ?;
        ';
        $params = [$omo_id, $mme_gpo_id, $mme_ur_id, $mme_id];
        //Verifica si el folio tiene movimientos
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            $this->revisarFolio();
        } else {
            $this->mixScript('notify("El folio no tiene movimientos","warning");');
        }
    }
    /* MÉTODO QUE ACTUALIZA EL ESTADO DEL FOLIO A 2:FOLIO REVISADO POR UR */

    private function revisarFolio()
    {
        //Definicion de variables
        $script = '';
        $html = '';

        $mme = new ModificacionMeta($this->prm);
        $mme->select();
        //INSTANCIAMOS EL ESTADO DE LA MODIFICACION
        $emod = new EstadoModificacion();
        $emod->setEmo_id(2); //Enviado a revision por UR
        $emod->select();

        //SE INICIA LA BITACORA DE LA MODIFICACION
        $prm = new stdClass();
        $prm->emo_id = $emod->getEmo_id();
        $prm->omo_id = $mme->getOmo_id();
        $prm->mme_gpo_id = $mme->getMme_gpo_id();
        $prm->mme_ur_id = $mme->getMme_ur_id();
        $prm->mme_id = $mme->getMme_id();
        $prm->clave = $mme->getClave();
        $prm->observacion = $emod->getNombre();
        $prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
        ?
        AccesoDatos::getIP() : '127.0.0.1';

        $datos = $this->model->getValidaFolio($mme);
        if (count($datos) == 0) {
            //INSERTAMOS A LA BITACORA EL CAMBIO REALIZADO
            $this->prm->emo_id = $emod->getEmo_id();
            $this->prm->observacion = $prm->observacion;
            $this->prm->clave = $mme->getClave();
            $this->prm->concepto = $mme->getConcepto();
            $this->prm->fechaFolio = $mme->getFechaFolio();
            $this->prm->oficioSefin = empty($mme->getOficioSefin()) ? ' ' : $mme->getOficioSefin();
            $this->prm->oficioSolicitudSefin = empty($mme->getOficioSolicitudSefin())
            ? ' ' : $mme->getOficioSolicitudSefin();
            $this->prm->origen = $mme->getOrigen();
            $this->prm->subsecretaria = $mme->getSubsecretaria();
            $this->prm->mmd_id = $mme->getMmd_id();
            $this->prm->tfm_id = $mme->getTfm_id();
            $this->prm->tmd_id = $mme->getTmd_id();
            $this->prm->origen = $mme->getorigen();
            $this->prm->vobo = $mme->getVobo();
            $this->prm->ip = $prm->ip;

            $this->bitacora('M', $this->prm);
            $this->prm->abc = 'c';
            //INSTANCIAMOS EL MAESTRO PARA ACTUALIZAR EL ESTADO DEL FOLIO
            ModificacionMeta::abc($this->prm);
            $script .= '
                notify("Folio ' . $mme->getMme_id() . ' revisado");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    omo_id:"' . $mme->getOmo_id() . '",
                    mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                    mme_ur_id:"' . $mme->getMme_ur_id() . '",
                    mme_id:' . $mme->getMme_id() . '
                    ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
            ';
            $this->mixScript($script);
        } else {
            if (count($datos) > 1) {
                //VISTA QUE MOSTRARÁ EN UNA TABLA LOS ERRORES OBTENIDOS
                $mod = '';
                $html .= $this->vista->vwMuestraTablaError($datos);
                $btnRegresar = $this->vista->vwBotones(
                    'regresar',
                    $mod,
                    $this->nivelRevision
                );
                $btnRegresar->click = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaFolios",
                        omo_id:"' . $mme->getOmo_id() . '",
                        mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                        mme_ur_id:"' . $mme->getMme_ur_id() . '",
                        mme_id:' . $mme->getMme_id() . '
                        ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    });
                ';
                $btnRegresar = $this->ponBoton($btnRegresar);
                $this->agregaTools($btnRegresar);
            } else {
                $mensaje = 'Error ' . $datos[1]['numero'] . ' ' . $datos[1]['mensaje'];
                //SI SOLO ES UN ERROR SE ENVIA UN NOTIFY
                $this->mixScript('notify("' . $mensaje . '", "error",false)');
            }
        }
        $this->mixHtml($html);
    }
    /* METODO QUE MUESTRA LISTADO DE FOLIOS PARA VALIDAR/AUTORIZAR, ES DE SOLO LECTURA */

    private function tablaFoliosRead()
    {
        switch ($this->nivel) { //Decide al metodo que irá al dar clic en regresar
            case 1:case 2: //UR y Ue
                $metodo = 'main';
                break;
            default: //Sefin
                $metodo = 'tablaUrs';
                $this->mixScript('$("#combour").val("' . $this->prm->mme_gpo_id . $this->prm->mme_ur_id . '").change();');
                break;
        }

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->class = 'me-2';
        $btnRegresar->color = 'gris';

        $tabla = new stdClass();
        $tabla->titulo = 'ADECUACION DE METAS';
        $tabla->id = 'tbl-Revision';
        //Verifica si es sefin
        if ($this->nivel >= 3) {
            $objUr = new UnidadResponsable();
            $objUr->setGpo_id($this->prm->mme_gpo_id);
            $objUr->setUr_id($this->prm->mme_ur_id);
            $objUr->select();

            $unidadResponsable = $objUr->getClave() . '-' . $objUr->getNombre();
            $tabla->tools = '
                <fieldset class="col-md-12 col-xs-12">
                    <legend>&nbsp;' . $unidadResponsable . '</legend>
                </fieldset>
            ';
        }

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaFoliosRead';
        $tabla->comunica->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision
            != '') ? $this->nivelRevision : '';
        //Verifica si es sefin
        if ($this->nivel >= 3) {
            $tabla->comunica->mme_gpo_id = (isset($this->mme_gpo_id) ? $this->prm->mme_gpo_id
                : '');
            $tabla->comunica->mme_ur_id = (isset($this->mme_ur_id) ? $this->prm->mme_ur_id
                : '');
        }

        $tr = [];
        //Ver folio
        $th = new stdClass();
        $th->texto = 'Ver Folio';
        $th->width = '5px';
        array_push($tr, $th);

        if ($this->nivel >= 3) {
            $th = new stdClass();
            $th->texto = 'Origen';
            $th->width = '30px';
            array_push($tr, $th);
        }

        //Número de Folio
        $th = new stdClass();
        $th->texto = 'Folio';
        $th->width = '10px';
        array_push($tr, $th);

        //Modalidad del folio
        $th = new stdClass();
        $th->texto = 'Modalidad';
        array_push($tr, $th);

        //Tipo de Adecuación
        $th = new stdClass();
        $th->texto = 'Tipo de Adecuación';
        array_push($tr, $th);

        //Justificación
        $th = new stdClass();
        $th->texto = 'Justificación';
        array_push($tr, $th);

        //Fecha
        $th = new stdClass();
        $th->texto = 'Fecha';
        $th->width = '60px';
        array_push($tr, $th);

        //Columna de Autorización/Validación
        $th = new stdClass();
        switch ($this->nivelRevision) {
            case 'validacionUR':
                $th->texto = 'Enviar a Validación';
                break;
            case 'autorizacionUR':
                $th->texto = 'Enviar a SEFIN Folio Externo';
                break;
            case 'validacionSEFIN':
                $th->texto = 'Solicitar Validación SEFIN';
                break;
            case 'autorizacionSEFIN':
                $th->texto = 'Autorizar SEFIN';
                break;
        }
        $th->width = '60px';
        array_push($tr, $th);

        //Columna de Rechazo
        $th = new stdClass();
        switch ($this->nivelRevision) {
            case 'validacionUR':
                $th->texto = 'Rechazar Validación';
                break;
            case 'autorizacionUR':
                $th->texto = 'Rechazar Envio';
                break;
            case 'validacionSEFIN':
                $th->texto = 'Rechazar Validación SEFIN';
                break;
            case 'autorizacionSEFIN':
                $th->texto = 'Rechazar Autorización SEFIN';
                break;
        }
        $th->width = '60px';
        array_push($tr, $th);

        //Estado del folio
        $th = new stdClass();
        $th->texto = 'Estado';
        $th->width = '120px';
        array_push($tr, $th);

        //Detalle del folio
        $th = new stdClass();
        $th->texto = 'Detalle Folio';
        $th->width = '60px';
        array_push($tr, $th);

        //Cuerpo de la tabla
        $trbody = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa-regular fa-eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->attr->mme_id = 'mme_id';
        $td->class = 'FolioRead btn-xs';
        array_push($trbody, $td);

        //Valida el nivel jerarquico
        if ($this->nivel >= 3) {
            $td = new stdClass();
            $td->campoTxt = 'omo_nombre';
            array_push($trbody, $td);
        }

        //Numero de Folio
        $td = new stdClass();
        $td->campoTxt = 'mme_id';
        array_push($trbody, $td);

        //Modalidad del folio
        $td = new stdClass();
        $td->campoTxt = 'mmd_id';
        array_push($trbody, $td);

        //Tipo de Adecuación
        $td = new stdClass();
        $td->campoTxt = 'tmd_nombre';
        array_push($trbody, $td);
        //Justificación
        $td = new stdClass();
        $td->campoTxt = 'mme_concepto';
        array_push($trbody, $td);
        //Fecha
        $td = new stdClass();
        $td->campoTxt = 'mme_fechaFolio';
        array_push($trbody, $td);

        //Columna de Autorización/Validación
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';
        $clase = 'validacionFolio';
        switch ($this->nivelRevision) {
            case 'validacionUR':
                $td->casos->valor = 2;
                $td->casos->ands = [];
                $and = new stdClass();
                $and->campo = 'habilitado';
                $and->operador = '==';
                $and->valor = 1;
                $and->valorTipo = 'numero';
                array_push($td->casos->ands, $and);
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
                $clase = 'validacionFolio';
                break;
            case 'validacionSEFIN': //validacion de jefe de departamento
                $td->casos->valor = 10;
                $clase = 'validacionFolio';
                switch ($this->subsec) {
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mme_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 1;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        break;
                }
                break;
            case 'autorizacionSEFIN': //autorizacion de jefe de unidad
                $td->casos->valor = 12;
                $clase = 'validacionFolio';

                switch ($this->subsec) {
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mme_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 1;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);
                        break;
                }
                break;
        }
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'fa fa-paper-plane';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'origenModificacion';
        $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
        $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
        $td->casos->verdadero->attr->mme_id = 'mme_id';
        $td->casos->verdadero->class = $clase . ' btn btn-outline-info btn-xs';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';
        //Columna de rechazo
        switch ($this->nivelRevision) {
            case 'validacionUR':
                $td->casos->valor = 5;
                $clase = 'rechazarFolio';

                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->icono = 'fa fa-xmark';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->omo_id = 'origenModificacion';
                $td->casos->falso->attr->mme_gpo_id = 'mme_gpo_id';
                $td->casos->falso->attr->mme_ur_id = 'mme_ur_id';
                $td->casos->falso->attr->mme_id = 'mme_id';
                $td->casos->falso->class = $clase . ' btn btn-outline-danger btn-xs';
                break;
            case 'autorizacionUR':
                $td->casos->valor = '9';
                $clase = 'rechazarFolio';

                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->icono = 'fa fa-xmark';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->omo_id = 'origenModificacion';
                $td->casos->falso->attr->mme_gpo_id = 'mme_gpo_id';
                $td->casos->falso->attr->mme_ur_id = 'mme_ur_id';
                $td->casos->falso->attr->mme_id = 'mme_id';
                $td->casos->falso->class = $clase . ' btn-outline-danger btn-xs';

                break;
            case 'validacionSEFIN': //rechazo jefe de departamento
                $td->casos->valor = 13;
                $clase = 'rechazarFolio';

                switch ($this->subsec) {
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mme_Subsecretaria';
                        $and->operador = '==';
                        $and->valor = 1;
                        $and->valorTipo = 'numero';
                        array_push($td->casos->ands, $and);

                        $td->casos->ors = [];
                        $ors = new stdClass();
                        $ors->campo = 'mme_Subsecretaria';
                        $ors->operador = '==';
                        $ors->valor = 1;
                        $ors->valorTipo = 'numero';
                        array_push($td->casos->ors, $ors);
                        break;
                }
                break;
            case 'autorizacionSEFIN': //rechazo jefe de unidad
                $td->casos->valor = 12;
                $clase = 'rechazarFolio';

                switch ($this->subsec) {
                    case 3: // egresos
                        $td->casos->ands = [];
                        $and = new stdClass();
                        $and->campo = 'mme_Subsecretaria';
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
        }
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'fa fa-xmark';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'origenModificacion';
        $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
        $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
        $td->casos->verdadero->attr->mme_id = 'mme_id';
        $td->casos->verdadero->class = $clase . ' btn btn-outline-danger btn-xs';
        array_push($trbody, $td);

        //Estado del folio
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'emo_color';
        $td->color = 'emo_color';
        $td->campoTxt = 'emo_id';
        $td->lbl = 'emo_nombre';
        array_push($trbody, $td);

        //Detalle del folio
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa- regular fa-eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mme_id = 'mme_id';
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->class = 'tablaModificacionesRead btn-xs';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->agregaTools($this->ponBoton($btnRegresar));

        //Espacio para el mensaje de rechazo
        $this->mixHtml('
            <div class="row">
                <fieldset id="Mensaje" class ="col-md-12 col-xs-12">
                </fieldset>
            </div>
        ');
        $this->mixScript('
            ' . $this->wrLlamaLlenaTabla('tbl-Revision') . '
            $("' . (($this->nivel == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresar").click(); e.stopPropagation(); });

            $("#btnRegresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"' . $metodo . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                });
                return false;
            });
        ');
    }

    private function llenaTablaFoliosRead()
    {
        //Defincion de varibles
        $script = '';
        $and = '';
        $endScript = '';

        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $and .= "
                        AND omo_id = 'A'
                        AND emo_id IN (2,5)
                        AND mme_subsecretaria=1
                    ";
                break;
            case 'autorizacionUR':
                $and .= "
                        AND omo_id = 'A'
                        AND emo_id IN (4,9)
                        AND mme_subsecretaria=1
                    ";
                break;
            case 'validacionSEFIN':
                $and .= ' AND emo_id IN (10,13) 
                AND mme_subsecretaria=1';
                break;
            case 'autorizacionSEFIN':
                $and .= ' AND emo_id IN (12) 
                AND mme_subsecretaria=1';

                $confirm = '
                    alertify.confirm("AUTORIZAR FOLIO", "¿ Desea autorizar el folio <h5 style=\"display:inline\"></h5> ?",
                        function(){
                ';

                $endConfirm = '
                    },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar",cancel:"Cancelar"});
                ';
                break;
        }

        $mme_gpo_id = isset($this->prm->mme_gpo_id) ? $this->prm->mme_gpo_id : $_SESSION[_S]['clasAdm-gpo'];
        $mme_ur_id = isset($this->prm->mme_ur_id) ? $this->prm->mme_ur_id : $_SESSION[_S]['clasAdm-ur'];
        $sql = "
            SELECT *,omo_id origenModificacion,'ADECUACION DE METAS' AS tmd_nombre, mme_Subsecretaria,
            1 habilitado
            FROM " . BD_CTRL . '.scp_ModificacionMeta
            INNER JOIN ' . BD_CTRL . '.cat_EstadoModificacion using(emo_id)
            INNER JOIN ' . BD_CTRL . '.cat_ModalidadModificacion using(mmd_id)
            INNER JOIN ' . BD_CTRL . ".cat_OrigenModificacion using(omo_id)
            WHERE
            mme_gpo_id = :mme_gpo_id
            AND mme_ur_id = :mme_ur_id
            AND prg_cero=0
                $and
                ORDER BY mme_id;
        ";
        $this->setSql($sql);
        $this->setParams([':mme_gpo_id' => $mme_gpo_id, ':mme_ur_id' => $mme_ur_id]);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Revision';
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".FolioRead").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addMmeFolio",
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                });
                return false;
            });
            $(".tablaModificacionesRead").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                });
                return false;
            });

            $(".validacionFolio").click(function(){
                var mme_id = $(this).attr("mme_id");
                var omo_id = $(this).attr("omo_id");
                var gpo_id = $(this).attr("mme_gpo_id");
                var ur_id = $(this).attr("mme_ur_id");

                ' . $script . '
                ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision == 'autorizacionSEFIN' ? $confirm : '') . '
                    comunica("' . $this->pantalla . '",{
                            metodo:"validacionFolio",
                            omo_id: omo_id,
                            mme_gpo_id:gpo_id,
                            mme_ur_id:ur_id,
                            mme_id:mme_id
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    });
                ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision == 'autorizacionSEFIN' ? $endConfirm : '') . '
                ' . $endScript . '

            });

            $(".rechazarFolio").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addMensajeRechazo",
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                });
                return false;
            });
        ');
    }
    /* METODO QUE CAMBIA DE ESTADO EL FOLIO A PARTIR DEL ESTADO 2 */

    private function validacionFolio()
    {
        $vobo = $notify = '';
        $ban = 1;
        $mme = new ModificacionMeta($this->prm);
        $emme = new EstadoModificacion();
        $datos = $this->model->getValidaFolio($mme, false, '');

        if (count($datos) == 0) {
            switch ($this->nivelRevision) {
                case 'validacionUR':
                    $emme->setEmo_id(4);
                    $mme->select();
                    $emme->select();
                    $mme->conectar();

                    $mme->setEmo_id($emme->getEmo_id());
                    $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="Envia  Folio a validación";');
                    $mme->consultar();
                    $mme->update(false);

                    $this->mixScript('
                        notify("Folio ' . $mme->getMme_id() . ' enviado a validación.");
                        comunica("' . $this->pantalla . '",{
                            metodo:"llenaTablaFoliosRead",
                            mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                            mme_ur_id:"' . $this->prm->mme_ur_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                        : '') . '
                        });
                    ');
                    break;
                case 'autorizacionUR':
                    $emme->setEmo_id(8);
                    $men = 'Presupuesto';
                    $emme->select();

                    $mme->conectar();
                    $mme->setEmo_id($emme->getEmo_id());

                    $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="Envia  Folio a Autorización UR";');
                    $mme->consultar();
                    $mme->update(false);
                    $mme->select();
                    $this->mixScript('
                        notify("Folio ' . $mme->getMme_id() . ' enviado a ' . $men . '.");
                        comunica("' . $this->pantalla . '",{
                            metodo:"llenaTablaFoliosRead",
                            mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                            mme_ur_id:"' . $this->prm->mme_ur_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                        : '') . '
                        });
                    '); //Enviado a revision por egresos
                    break;
                case 'revisionSEFIN':
                    $emme->setEmo_id(10); //Revision por jefe de departamento
                    $vobo = 3;
                    $notify = 'Folio ' . $mme->getMme_id() . ' revisado por SEFIN';
                    $emme->select();
                    $mme->conectar();
                    $mme->setEmo_id($emme->getEmo_id());
                    $mme->setVobo($vobo);
                    $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="Envia  Folio a revisión - SEFIN";');
                    $mme->consultar();
                    $mme->update(false);
                    $mme->select();

                    $this->mixScript('
                        notify("' . $notify . '");
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFolios",
                            mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                            mme_ur_id:"' . $this->prm->mme_ur_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                        : '') . '
                        });
                    ');
                    break;
                case 'validacionSEFIN':
                    $emme->setEmo_id(12); //Revision por jefe de unidad
                    $emme->select();
                    $mme->conectar();
                    $mme->setEmo_id($emme->getEmo_id());
                    $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="Envia  Folio a validación -SEFIN";');
                    $mme->consultar();
                    $mme->update(false);
                    $mme->select();

                    $this->mixScript('
                        notify("Folio ' . $mme->getMme_id() . ' validado por SEFIN");
                        comunica("' . $this->pantalla . '",{
                            metodo:"llenaTablaFoliosRead",
                            mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                            mme_ur_id:"' . $this->prm->mme_ur_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                        : '') . '
                        });
                    ');
                    break;
                case 'autorizacionSEFIN':

                    $emme->setEmo_id(14);
                    $ban = 0;
                    $emme->select();
                    $mme->select();
                    $storeProcedure = new sp_AutorizarModificacionMeta2027();
                    $storeProcedure->setOmo_id($mme->getOmo_id());
                    $storeProcedure->setMme_gpo_id($mme->getMme_gpo_id());
                    $storeProcedure->setMme_ur_id($mme->getMme_ur_id());
                    $storeProcedure->setMme_id($mme->getMme_id());
                    $storeProcedure->setMme_Subsecretaria($mme->getSubsecretaria());
                    $storeProcedure->setSis_id('SCP');
                    $storeProcedure->setEjercicio(APP_EJERCICIO);
                    $storeProcedure->setBit_observacion('SE AUTORIZA FOLIO DE ADECUACIÓN DE METAS EXTERNA DESDE PANTALLA S_1_7');
                    $storeProcedure->conectar();
                    $storeProcedure->setSql('SET @usr_ip =INET_ATON("' . $this->ip . '"), @usr_id ="' . $_SESSION[_S]['usr_id'] . '";');
                    $storeProcedure->consultar();
                    $r = $storeProcedure->execute();
                    if (!$r->bandera && !is_null($r->bandera)) {
                        $this->mixScript(' notify("ERROR ' . $r->mensaje . '","error",false);');
                    } else {
                        $mme->select();
                        $this->mixScript('
                            notify("Folio ' . $mme->getMme_id() . ' autorizado por SEFIN Operación número: ' . $mme->getOperacion() . ' \n Oficio de Sefin: ' . $mme->getOficioSefin() . ' ","success",false);
                            comunica("' . $this->pantalla . '",{
                                metodo:"tablaFoliosRead",
                                mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                                mme_ur_id:"' . $this->prm->mme_ur_id . '"
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                            : '') . '
                            });
                        ');
                    }

                    break;
            }

            $this->prm->omo_id = $mme->getOmo_id();
            $this->prm->mme_gpo_id = $mme->getMme_gpo_id();
            $this->prm->mme_ur_id = $mme->getMme_ur_id();
            $this->prm->mme_id = $mme->getMme_id();
            $this->prm->ue_id = $mme->getMme_ue_id();
            $this->prm->emo_id = $emme->getEmo_id();
            $this->prm->observacion = (isset($this->prm->observacion)) ? $this->prm->observacion
            : $emme->getNombre();
            $this->prm->clave = $mme->getClave();
            $this->prm->mmd_id = $mme->getMmd_id();
            $this->prm->tfm_id = $mme->getTfm_id();
            $this->prm->tmd_id = $mme->getTmd_id();
            $this->prm->concepto = $mme->getConcepto();
            $this->prm->fechaFolio = $mme->getFechaFolio();
            $this->prm->oficioSefin = $mme->getOficioSefin();
            $this->prm->oficioSolicitudSefin = $mme->getOficioSolicitudSefin();
            $this->prm->operacion = $mme->getOperacion();

            $this->prm->origen = $mme->getOrigen();
            $this->prm->subsecretaria = $mme->getSubsecretaria();
            $this->prm->clave = $mme->getClave();
            $this->prm->vobo = $mme->getVobo();
            $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
                != '::1') ? AccesoDatos::getIP() : '127.0.0.1';
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
                $btnregresa->icon = 'fa-solid fa-circle-left me-1';
                $btnregresa->text = 'Regresar';
                $btnregresa->value = 'Regresar';
                $btnregresa->class = 'me-2';
                $btnregresa->color = 'gris';
                $btnregresa->type = 'button';

                $this->agregaTools($this->ponBoton($btnregresa));
                $this->mixHtml($html);
                $this->mixScript('
                    $("#btnregresa").click(function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFoliosRead",
                            mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                            mme_ur_id:"' . $this->prm->mme_ur_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '
                        });
                    })
                ');
            } else {
                $this->mixScript('notify("' . utf8_encode('Error ' . $datos[1]['numero'] . '. ' . $datos[1]['mensaje']) . '","error",false);');
            }
        }
    }

    // fin validacionFolio

    /* METODO QUE AGREGA MENSAJES DE RECHAZO AL RECHAZAR EL FOLIO DE ADECUACION */

    private function addMensajeRechazo()
    {
        $this->setContenedor('Mensaje');
        $mensajeRechazo = new stdClass();
        $mensajeRechazo->id = 'mensajeRechazo';
        $mensajeRechazo->required = true;
        $mensajeRechazo->enDiv = true;
        $mensajeRechazo->maxlength = 100;
        $mensajeRechazo->width = '300px';
        $mensajeRechazo->height = '100px';

        $modal = '<div id="myModalRechazo" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; padding-top: 100px;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Motivo de rechazo</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <form id="Mensaje">
                        ' . $this->ponTextArea($mensajeRechazo) . '
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success" id="btnGuardarMensaje" form="">Aceptar</button>
                        <button type="button" class="btn btn-outline-secondary btn-outline-danger cerrar" id="btnCancelarMensaje" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog modal-lg -->
        </div>';

        $this->mixHtml($modal);

        $this->mixScript('
            $("#myModalRechazo").modal("show");

            $("#btnGuardarMensaje").click(function(){
                valido = true;
                $("#Mensaje").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                    if(!this.checkValidity()){
                        $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  padding-top: 100px; margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
                    }
                });
                $( ".formError" ).show("pulsate");
                setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
                setTimeout(function() { $( ".formError" ).remove(); }, 4500 );

                if(valido){
                    comunica("' . $this->pantalla . '",{
                        metodo: "rechazarFolio",
                        omo_id:"' . $this->prm->omo_id . '",
                        mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                        mme_ur_id:"' . $this->prm->mme_ur_id . '",
                        mme_id:' . $this->prm->mme_id . '
                        ,observacion:$("#mensajeRechazo").val()
                        ' . ($this->nivelRevision != '' ?
            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    });
                $("#myModalRechazo").modal("hide").on("hidden.bs.modal", function () {
                        $(this).remove();
                });
                }
            });

            $(".cerrar").click(function(){$("#myModalRechazo").remove();});
        ');
    }

    // fin metodo addMensajeRechazo

    /* METODO QUE CAMBIA DE ESTADO AL FOLIO EN EL RECHAZO */
    private function rechazarFolio()
    {
        $mme = new ModificacionMeta($this->prm);
        /* Instanciamos el estado de la modificacion */
        $emme = new EstadoModificacion();
        $mensaje = true;
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $emme->setEmo_id(3);
                $emme->select();
                $mme->select();
                $mme->conectar();
                $mme->setEmo_id($emme->getEmo_id());
                $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="' . $this->prm->observacion . '";');
                $mme->consultar();
                $mme->update(false);

                $this->mixScript('
                    notify("Folio ' . $mme->getMme_id() . ' rechazado");
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaFoliosRead",
                        mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                        mme_ur_id:"' . $mme->getMme_ur_id() . '",
                        omo_id:"' . $mme->getOmo_id() . '",
                        mme_id:' . $mme->getMme_id() . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '
                    });
                ');
                break;
            case 'autorizacionUR':
                $emme->setEmo_id(5);
                $emme->select();
                $mme->select();
                $mme->conectar();
                $mme->setEmo_id($emme->getEmo_id());
                $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="' . $this->prm->observacion . '";');
                $mme->consultar();
                $mme->update(false);
                $this->mixScript('
                    notify("Folio ' . $mme->getMme_id() . ' rechazado");
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaFoliosRead",
                        mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                        mme_ur_id:"' . $mme->getMme_ur_id() . '",
                        omo_id:"' . $mme->getOmo_id() . '",
                        mme_id:' . $mme->getMme_id() . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '
                    });
                ');
                break;
            case 'revisionSEFIN':
                if ($mme->getOmo_id() == 'A') {
                    $emme->setEmo_id(9);
                } else {
                    $emme->setEmo_id(11);
                }
                $emme->select();
                $mme->setEmo_id($emme->getEmo_id());
                $mme->setVobo(3);

                if ($mensaje) {
                    $mme->conectar();
                    $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="' . $this->prm->observacion . '";');
                    $mme->consultar();
                    $mme->update(false);
                    $mme->select();
                    $this->mixScript('
                        notify("Folio ' . $mme->getMme_id() . ' rechazado");
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaFolios",
                            mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                            mme_ur_id:"' . $this->prm->mme_ur_id . '",
                            omo_id:"' . $mme->getOmo_id() . '",
                            mme_id:' . $mme->getMme_id() . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                        : '') . '

                        });
                    ');
                }
                break;
            case 'validacionSEFIN':
                $emme->setEmo_id(11); //Revision por jefe de departamento
                $emme->select();
                $mme->conectar();
                $mme->setEmo_id($emme->getEmo_id());
                $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="' . $this->prm->observacion . '";');
                $mme->consultar();
                $mme->update(false);
                $mme->select();

                $this->mixScript('
                    notify("Folio ' . $mme->getMme_id() . ' rechazado");
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaFoliosRead",
                        mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                        mme_ur_id:"' . $this->prm->mme_ur_id . '",
                        omo_id:"' . $mme->getOmo_id() . '",
                        mme_id:' . $mme->getMme_id() . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '

                    });
                ');
                break;
            case 'autorizacionSEFIN':
                $emme->setEmo_id(13);
                $emme->select();
                $mme->conectar();
                $mme->setEmo_id($emme->getEmo_id());
                $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="' . $this->prm->observacion . '";');
                $mme->consultar();
                $mme->update(false);
                $mme->select();
                $this->mixScript('
                    notify("Folio ' . $mme->getMme_id() . ' rechazado");
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaFoliosRead",
                        mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                        mme_ur_id:"' . $this->prm->mme_ur_id . '",
                        omo_id:"' . $mme->getOmo_id() . '",
                        mme_id:' . $mme->getMme_id() . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '
                    });
                ');
                break;
        }

        $this->prm->omo_id = $mme->getOmo_id();
        $this->prm->mme_gpo_id = $mme->getMme_gpo_id();
        $this->prm->mme_ur_id = $mme->getMme_ur_id();
        $this->prm->mme_id = $mme->getMme_id();
        $this->prm->mme_ue_id = $mme->getMme_ue_id();
        $this->prm->emo_id = $emme->getEmo_id();
        $this->prm->observacion = (isset($this->prm->observacion)) ? $this->prm->observacion
        : $emme->getNombre();
        $this->prm->clave = $mme->getClave();
        $this->prm->mmd_id = $mme->getMmd_id();
        $this->prm->tfm_id = $mme->getTfm_id();
        $this->prm->tmd_id = $mme->getTmd_id();
        $this->prm->concepto = $mme->getConcepto();
        $this->prm->fechaFolio = $mme->getFechaFolio();
        $this->prm->oficioSefin = $mme->getOficioSefin();
        $this->prm->oficioSolicitudSefin = $mme->getOficioSolicitudSefin();
        $this->prm->operacion = $mme->getOperacion();
        $this->prm->origen = $mme->getOrigen();
        $this->prm->subsecretaria = $mme->getSubsecretaria();
        $this->prm->vobo = $mme->getVobo();
        $this->prm->clave = $mme->getClave();
        $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
            != '::1') ? AccesoDatos::getIP() : '127.0.0.1';
        if ($mensaje) {
            $this->bitacora('M', $this->prm);
        }
    }

    // fin metodo rechazarFolio
    //METODO QUE AGREGA LAS FIRMAS AL REPORTE DE ADECUACION PRESUPUESTAL PERFIL SEFIN
    private function addFirma()
    {
        $abc = 'a';
        $departamentos = ($this->subsec == 3) ? ' AND dep_id IN(41,48,49,50) ' : ' AND dep_id IN(30,31,32,33,34,35)';
        $departamento = (isset($_SESSION[_S]['dep_id'])) ? $_SESSION[_S]['dep_id']
        : 0;
        $nivelJerarquico = (isset($this->nivel)) ? $this->nivel : 0;
        $subSecretaria = (isset($this->subsec)) ? $this->subsec : 0;
        $unidad = (isset($_SESSION[_S]['uni_id'])) ? $_SESSION[_S]['uni_id'] : 0;
        $direccion = (isset($_SESSION[_S]['dir_id'])) ? $_SESSION[_S]['dir_id']
        : 0;

        $btn_regresa = $this->vista->vwBotones('regresar');
        $btn_regresa->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"tablaFolios"
                ' . (isset($this->prm->ecf_id) ? ',ecf_id:"' . $this->prm->ecf_id . '"'
            : '') . '
                ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"'
            : '') . '
                ' . (isset($this->prm->mme_gpo_id) ? ',mme_gpo_id:' . $this->prm->mme_gpo_id . ''
            : '') . '
                ' . (isset($this->prm->mme_ur_id) ? ',mme_ur_id:"' . $this->prm->mme_ur_id . '"'
            : '') . '
                    ,nivelRevision:"revisionSEFIN"
                });
            return false;
        ';

        $btn_firmar = new stdClass();
        $btn_firmar->value = 'Firmar';
        $btn_firmar->color = 'verde';
        $btn_firmar->class = 'me-2';
        $btn_firmar->icon = 'fa-solid fa-floppy-disk me-1';
        $btn_firmar->id = 'btn_firmar';
        $btn_firmar->attr = new stdClass();
        $btn_firmar->attr->mmd_id = $this->prm->mmd_id;
        $btn_firmar->attr->omo_id = $this->prm->omo_id;
        $btn_firmar->attr->mme_gpo_id = $this->prm->mme_gpo_id;
        $btn_firmar->attr->mme_ur_id = $this->prm->mme_ur_id;
        $btn_firmar->attr->mme_id = $this->prm->mme_id;

        $btn_imprimir = new stdClass();
        $btn_imprimir->value = 'Generar';
        $btn_imprimir->color = 'gris';
        $btn_imprimir->class = 'me-2';
        $btn_imprimir->icon = 'fa fa-print me-1';
        $btn_imprimir->id = 'btn_reporte';
        $btn_imprimir->attr = new stdClass();
        $btn_imprimir->attr->mmd_id = $this->prm->mmd_id;
        $btn_imprimir->attr->omo_id = $this->prm->omo_id;
        $btn_imprimir->attr->mme_gpo_id = $this->prm->mme_gpo_id;
        $btn_imprimir->attr->mme_ur_id = $this->prm->mme_ur_id;
        $btn_imprimir->attr->mme_id = $this->prm->mme_id;

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

        if ($this->subsec == 3) {
            $cbo_firma->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE ssc_id = ' . $this->subsec . '
                AND gpo_id = 1 AND ur_id=15 AND usr_id = ' . $_SESSION[_S]['usr_id'] . '
                AND sts_id=1;';
        } elseif ($this->subsec == 2) {
            $cbo_firma->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE ssc_id = ' . $this->subsec . '
                AND nvj_id=3
                AND gpo_id = 1 AND ur_id=15 AND dir_id=5
                AND sts_id=1;';
        }

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

        if ($this->subsec == 3) {
            $unidadSel = ($unidad == 0) ? ' uni_id in(16,18) ' : ' uni_id = ' . $unidad;
            $departamento = ($departamento == 0) ? $departamentos : ' AND dep_id = ' . $departamento;
            $firma2->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=4
                AND ssc_id=' . $this->subsec . ' AND dir_id=7
                AND uni_id in(16,18)
                AND sts_id=1';

            $jefeDpto = ' SELECT usr_id
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=4
                AND ssc_id=' . $this->subsec . ' AND dir_id=' . $direccion . '
                AND ' . $unidadSel . ' ' . $departamento . '
                AND sts_id=1 LIMIT 1';

            $c = new AccesoDatos();
            $c->conectar();
            $c->setSql($jefeDpto);
            $c->consultar();
            $d = $c->extraer();
            $c->desconectar();
            if ($c->getNumeroRegistros() > 0) {
                $firma2->selecciona = $d['usr_id'];
            }
        } elseif ($this->subsec == 2) {
            $firma2->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=4
                AND ssc_id=' . $this->subsec . ' AND dir_id=5
                AND sts_id=1';
        }

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
        if ($this->subsec == 3) {
            $firma3->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,"3" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 5 AND ssc_id = ' . $subSecretaria . '
                AND dir_id = ' . $direccion . '
                AND uni_id=' . $unidad . '
                AND sts_id = 1 ';
        } elseif ($this->subsec == 2) {
            $firma3->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"3" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 6
                AND ssc_id=' . $this->subsec . '
                AND dir_id = 5
                AND sts_id = 1 ';
        }

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
            FROM cat_Usuario
            INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
            INNER JOIN enc_JerarquiaSefin USING (ejs_id)
            WHERE gpo_id= 1 AND ur_id=15 AND ' . $this->subsec . '
            AND dir_id=7 AND nvj_id=6
            AND sts_id=1;
        ';
        $scri = '';
        $firma4 = ($this->subsec == 3 && $this->prm->emo_id) ? $this->ponCombo($firma4)
        : '';

        $scri = '$(".cbofirmas").change();';

        $html = '';
        $html .= '
                <fieldset class="col-md-12 col-xs-12">
                    <legend>ADECUACIONES DE METAS</legend>
                </fieldset>

                <fieldset style="align:center" class="col-md-12 col-xs-12 mb-4" >
                    <legend>Firmas del Reporte</legend>
                    <div style=" border:1px solid RGBA(99,0,0,.6); padding:4px; font-size:13px; color :#666">
                            Si no seleccciona alguna de las firmas, esa firma ira vacia en la impresi&oacute;n.<br/>
                            Si ve la leyenda [ sin puesto ] o [ sin titulo ], de click sobre esa leyenda para
                            editar ese texto ya que es así como aparecerá en la impresión,
                            esta información viene directamente de la base de datos.
                    </div>
                </fieldset>
                <div class="container-fluid mb-3">
                    <div class="row mb-3">
                        <fieldset style="align:center" class="col-md-4" >
                            ' . $cbo_firma . '
                        </fieldset>
                        <fieldset style="align:center" class="col-md-4" >
                            ' . $firma2 . '
                        </fieldset>
                        <fieldset style="align:center" class="col-md-4" >
                            ' . $firma3 . '
                        </fieldset>
                    </div>
                    <div class="row mb-4">
                        <fieldset style="align:center" class="col-md-4 col-xs-4" >
                        ' . $firma4 . '
                        </fieldset>
                    </div>
                </div>';

        $html .= '</div>';
        $html .= '<div id="infofirmas"  style="align:center">
                    <div class="mb-4"><center><legend>Firmas del Reporte</legend> ( Seleccione un usuario de firma)</div>
                    <div class="container-fluid mb-3">
                        <div class="row mb-3">
                            <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma1">
                                ...
                            </fieldset>
                            <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma2">
                                ...
                            </fieldset>
                            <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma3">
                                ...
                            </fieldset>
                        </div>
                        <div class="row">
                            <fieldset class="col-md-4 col-xs-4 offset-md-4" id="firma4">
                            ...
                            </fieldset>
                        </div>
                    </div>
                </div>';

        $btnRegresa = $this->ponBoton($btn_regresa);
        $btnFirma = $this->ponBoton($btn_firmar);
        $btnImprimir = $this->ponBoton($btn_imprimir);
        $this->mixHtml($html);
        $this->mixHtml('
            <form id="impresion" method="post" action="" target="_blank">
                <input type="hidden" id="omo_id" name="omo_id" value=""/>
                <input type="hidden" id="mme_gpo_id" name="mme_gpo_id" value=""/>
                <input type="hidden" id="mme_ur_id" name="mme_ur_id" value=""/>
                <input type="hidden" id="mme_id" name="mme_id" value=""/>
                <input type="hidden" id="mme_tipoMovimiento" name="mme_tipoMovimiento" value=""/>
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
                            usr:$(this).val()})
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
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id"),
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
                $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesMetas.php");
                $("#omo_id").val($(this).attr("omo_id"));
                $("#mme_gpo_id").val($(this).attr("mme_gpo_id"));
                $("#mme_ur_id").val($(this).attr("mme_ur_id"));
                $("#mme_id").val($(this).attr("mme_id"));
                $("#mme_tipoMovimiento").val($(this).attr("mme_tipoMovimiento"));
                $("#tri_id").val($(this).attr("' . $this->tri_id . '"));
                $("#impresion").submit();
            });
        ');
    }

    //fin addFirma

    /* METODO QUE MUESTRA LAS FIRMAS ACTUALES DEL REPORTE PARA PERFIL SEFIN */

    private function firmas()
    {
        $l = $this->prm->level;
        $this->setContenedor('firma' . $this->prm->level);
        $sql = 'SELECT usr_titulo, usr_puesto, usr_nombre,  usr_paterno, usr_materno
                    FROM ' . BD_CTRL . '.cat_Usuario
                    INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                    WHERE usr_id = ?';
        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar([$this->prm->usr]);
        $d = $c->extraer();
        $c->desconectar();
        unset($c);
        $tit = trim($d['usr_titulo']) == '' ? '[ sin titulo ]' : $d['usr_titulo'];
        $puesto = trim($d['usr_puesto']) == '' ? '[ sin puesto ]' : $d['usr_puesto'];
        $html = '<div class="firmaslotes" style="width:100%; "> ';
        $html .= '<input id="t' . $l . '"  type="text" value="' . $tit .
            '" style="width:19%;text-align:right; display:inline-block"/>';
        $html .= '<input id="n' . $l . '"  type="text" value="' . $d['usr_nombre'] .
            ' ' . $d['usr_paterno'] . ' ' . $d['usr_materno'] .
            '" style="width:80%;text-align:left; display:inline-block"/><br/>';
        $html .= '<input id="c' . $l . '" type="text" value="' . htmlspecialchars($puesto) .
            '" style="width:100%;"/></div>';
        $this->mixHtml($html);
    }

    // fin firmas

    /* METODO QUE INSERTA LAS FIRMAS DEL REPORTE DE ADECUACION PRESUPUESTAL PERFIL SEFIN */

    private function abcFirmas()
    {
        $valido = true;
        if ($valido) {
            switch ($this->prm->abc) {
                case 'a':
                    //Firmantes del reporte SEFIN
                    $this->prm->firma1 = $this->strtMayus($this->prm->f1->t == '[ sin titulo ]'
                        ?
                        $this->prm->f1->n : $this->prm->f1->t . ' ' . $this->prm->f1->n);
                    $this->prm->cargo1 = $this->strtMayus($this->prm->f1->c == '[ sin puesto ]'
                        ?
                        ' ' : $this->prm->f1->c);

                    $this->prm->firma2 = $this->strtMayus($this->prm->f2->t == '[ sin titulo ]'
                        ?
                        $this->prm->f2->n : $this->prm->f2->t . ' ' . $this->prm->f2->n);

                    $this->prm->cargo2 = $this->strtMayus($this->prm->f2->c == '[ sin puesto ]'
                        ?
                        ' ' : $this->prm->f2->c);

                    $this->prm->firma3 = $this->strtMayus($this->prm->f3->t == '[ sin titulo ]'
                        ?
                        $this->prm->f3->n : $this->prm->f3->t . ' ' . $this->prm->f3->n);

                    $this->prm->cargo3 = $this->strtMayus($this->prm->f3->c == '[ sin puesto ]'
                        ?
                        ' ' : $this->prm->f3->c);

                    $this->prm->firma4 = $this->strtMayus($this->prm->f4->t == '[ sin titulo ]'
                        ?
                        $this->prm->f4->n : $this->prm->f4->t . ' ' . $this->prm->f4->n);

                    $this->prm->cargo4 = $this->strtMayus($this->prm->f4->c == '[ sin puesto ]'
                        ?
                        ' ' : $this->prm->f4->c);

                    $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
                        != '::1') ?
                    $_SESSION[_S]['ses_ip'] : '127.0.0.1';
                    $this->prm->usr_id = $_SESSION[_S]['usr_id'];

                    //Registra o actualiza las firmas
                    $sql = "INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id, mme_id, mme_cargo1,
                        mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3, mme_cargo4, mme_firma4, mme_cargo5,
                        mme_firma5, mme_cargo6, mme_firma6, mme_cargo7, mme_firma7, usr_id)
                        VALUES (:omo_id, :mme_gpo_id, :mme_ur_id, :mme_id, '', '', '', '', '', '', :cargo1, :firma1,
                                :cargo2, :firma2, :cargo3, :firma3, :cargo4, :firma4, :usr_id)
                        ON DUPLICATE KEY UPDATE mme_cargo4 = :cargo1, mme_firma4 = :firma1, mme_cargo5 = :cargo2,
                            mme_firma5 = :firma2, mme_cargo6 = :cargo3, mme_firma6 = :firma3, mme_cargo7 = :cargo4,
                            mme_firma7 = :firma4";

                    $firmasReportes = new AccesoDatos();
                    $firmasReportes->conectar();
                    $firmasReportes->setSql($sql);
                    $firmasReportes->consultar([':omo_id' => $this->prm->omo_id,
                        ':mme_gpo_id'                     => $this->prm->mme_gpo_id,
                        ':mme_ur_id'                      => $this->prm->mme_ur_id,
                        ':mme_id'                         => $this->prm->mme_id,
                        ':cargo1'                         => $this->prm->cargo1,
                        ':firma1'                         => $this->prm->firma1,
                        ':cargo2'                         => $this->prm->cargo2,
                        ':firma2'                         => $this->prm->firma2,
                        ':cargo3'                         => $this->prm->cargo3,
                        ':firma3'                         => $this->prm->firma3,
                        ':cargo4'                         => $this->prm->cargo4,
                        ':firma4'                         => $this->prm->firma4,
                        ':usr_id'                         => $_SESSION[_S]['usr_id']]);
                    if ($firmasReportes) {
                        $this->mixScript('notify("Se registrarón correctamente las firmas","success")');
                    } else {
                        $this->mixScript('notify("Ocurrio un error al registras las firmas, intente de nuevo","error")');
                    }
                    break;
            }
        }
    }

    //fin abcFirmas

    public function strtMayus($variable)
    {
        $variable = strtr(
            strtoupper($variable),
            'àèìòùáéíóúçñäëïöü',
            'ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ'
        );
        return $variable;
    }
}
// FIN DE LA CLASE
/* * *** FIN FUNCIONES ********************** */
$presupuesto = isset($_SESSION[_S]['ssc_id']) && $_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['nvj_id']
    >= 3;
$ur = isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] <= 2;

if (isset($_SESSION[_S]['prm']['S']['1']['7']) && ($ur || $presupuesto)) {
    new ControladorRegistroModificacionMetas();
} else {
    WebResponse::sinPermisos();
}
