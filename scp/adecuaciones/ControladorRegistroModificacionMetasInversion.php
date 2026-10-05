<?php

require_once dirname(__FILE__) . '/../../lib/scp/vistaAdecuacionMetasInversion.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scp/sp_AutorizarModificacionMetaInversion2025.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scp/ModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/DetalleModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraDetalleModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/EstadoModificacion.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuacionMetasInversion.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/CatalogoEncuadreProgramatico.php';

class ControladorRegistroModificacionMetasInversion extends WebResponse
{
    public $vista;
    public $model;
    public $ur;
    public $ue;
    public $planeacion;
    public $escritura;
    public $nivelRevision;
    public $omo_id;
    public $mme_gpo_id;
    public $mme_ur_id;
    public $mme_id;
    public $gpo_id;
    public $usr_id;
    public $perfil;
    public $nivel;
    public $subsec;
    public $ip;
    public $ur_id;
    public $tri_id;

    public function __construct()
    {
        //Manda a llamar constructor base
        parent::__construct();
        $this->pantalla = 'scp/adecuaciones/ControladorRegistroModificacionMetasInversion.php';
        $this->vista = new vistaAdecuacionMetasInversion();
        $this->model = new modelAdecuacionMetasInversion();
        //Verifica el tipo de usuario
        $this->ur = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id']
            == 2 &&
            isset($_SESSION[_S]['prm']['S']['1']['8']['112']) ? true : false);
        $this->ue = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id']
            == 1 ? true : false);
        $this->planeacion = (isset($_SESSION[_S]['ssc_id']) && $_SESSION[_S]['ssc_id']
            == 2 ? true : false);

        //Verificamos los permisos de escritura
        $this->escritura = (isset($_SESSION[_S]['prm']['S']['1']['8']['112']) ||
            (isset($_SESSION[_S]['prm']['S']['1']['8']['108']) && isset($this->prm->nivelRevision)
                && $this->prm->nivelRevision == 'alta') ? true : false);

        $this->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision
            != '' ? $this->prm->nivelRevision : '');
        $this->omo_id = (isset($this->prm->omo_id) && $this->prm->omo_id != '' ? $this->prm->omo_id
            : '');
        $this->mme_gpo_id = (isset($this->prm->mme_gpo_id) && $this->prm->mme_gpo_id
            != '' ? $this->prm->mme_gpo_id : '');
        $this->mme_ur_id = (isset($this->prm->mme_ur_id) && $this->prm->mme_ur_id
            != '' ? $this->prm->mme_ur_id : '');
        $this->mme_id = (isset($this->prm->mme_id) && $this->prm->mme_id != '' ? $this->prm->mme_id
            : '');

        $this->gpo_id = !($this->ur || $this->ue) ? $_SESSION[_S]['clasAdm-gpo']
            : $_SESSION[_S]['gpo_id'];
        $this->ur_id = !($this->ur || $this->ue) ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

        $this->perfil = isset($_SESSION[_S]['prf_id']) ? $_SESSION[_S]['prf_id']
            : '';
        $this->nivel = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id']
            : '';
        $this->subsec = isset($_SESSION[_S]['ssc_id']) ? $_SESSION[_S]['ssc_id']
            : '';
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
            ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->usr_id = $_SESSION[_S]['usr_id'];

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
                    $this->perfil == 'U') || $this->nivel >= 2 ? ',nivelRevision:"alta"'
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
    private function cambiarUR($ur)
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
    private function lectura($mme, $esUR)
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
    private function pintaScript($s)
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
        if ($this->planeacion) {
            $this->omo_id = 'E';
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

        $esSefin = $this->planeacion;

        //MOSTRAR TABLA DE LAS URS CON EL NUMERO DE FOLIOS PENDIENTE DE REVISIÓN
        $pantalla = 'scp/adecuaciones/ControladorRegistroModificacionMetasInversion.php';
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
        if ($this->planeacion) {
            $origen = 'E';
            $sql = $this->model->getConsultaTablasUrs(
                'planeacion',
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
        if ($this->planeacion && $cambiaUR) {
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
                    ',omo_id:"' . $this->omo_id . '"' : ",omo_id:'E'") . '
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
        } elseif ($this->planeacion) {
            $td->casos->valor = '6,11,17';
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
            $td->casos->verdadero->icono = 'file-pdf';
            $td->casos->verdadero->color = 'rojo';
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
                if ($this->planeacion) {
                    $td->casos->valor = '6,11,17';
                }
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->icono = 'file-pdf';
                $td->casos->verdadero->color = 'rojo';
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
            $td->casos->verdadero->icono = 'fa fa-paper-plane';
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

        if (($this->planeacion) && $this->nivelRevision == 'alta') {
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
        } elseif ($this->planeacion) {
            $sql = $this->model->getConsultaTabla('planeacion', $this->prm);
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
                $("#reporte").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesMetasInversion.php");
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
            alertify.confirm(
            "<h5> ELIMINAR MOVIMIENTOS </h5>",
            "¿ Realmente desea eliminar todos los movimientos del folio ? ¡¡¡Una vez realizada esta acción no se puede deshacer!!!",
            function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"eliminarMovimientos",
                    omo_id:"' . $mme->getOmo_id() . '",
                    mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                    mme_ur_id:"' . $mme->getMme_ur_id() . '",
                    mme_id:' . $mme->getMme_id() . '
                    ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
            },
            function(){},
            ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
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
        $btnEliminarMovimientos = $tieneObrasActividades['registros'] ? $btnEliminarMovimientos
            : '';
        $btnEliminarMovimientos = is_object($btnEliminarMovimientos) ? $this->ponBoton($btnEliminarMovimientos)
            : '';
        if ($this->ur || $this->ue) {
            switch ($mme->getEmo_id()) {
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
                    if ($mme->getOmo_id() == 'E') {
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
        $btnNuevoMovimiento = is_object($btnNuevoMovimiento) ? $this->ponBoton($btnNuevoMovimiento)
            : '';
        $html = $this->vista->vwInformacionFolio($mme);
        //DEFINICIÓN DE LA TABLA DE OBRAS/ACTIVIDADES DEL FOLIO
        $pantalla = $this->pantalla;
        $tabla = $this->vista->vwTablaObrasActividades(
            $mme,
            $esUR,
            $this->planeacion,
            $pantalla,
            $this->nivelRevision
        );
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

        if ($this->planeacion) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('sefin', $mme);
        }

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Modificaciones';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

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
                    ' . (($this->nivelRevision != '') ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
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
            $unidadEjecutora->selecciona = $_SESSION[_S]['ue_id'];
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
                var sub = (($("#Subsecretaria").length) ? $("#Subsecretaria").find("option:selected").val():2);
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
                var sub = (($("#Subsecretaria").length) ? $("#Subsecretaria").find("option:selected").val():2);
                comunica("' . $this->pantalla . '",{
                    metodo:"abcFolios",
                    abc:"' . $abc . '",
                    ' . (isset($this->prm->omo_id) ? ' omo_id:"' . $this->prm->omo_id . '",'
                    : ' omo_id: "E",') . '
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
                alertify.confirm(
                    "<h5> ELIMINAR FOLIO </h5>",
                    "¿ Realmente desea eliminar el folio ?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"abcFolios",
                            abc:"b",
                            ' . (isset($this->prm->omo_id) ? ' omo_id:"' . $this->prm->omo_id . '",'
                            :
                            ' omo_id:$("#tmd_id").find("option:selected").attr("omo_id"),') . '
                            mme_id:$("#folio").val()
                            ' . (isset($this->prm->mme_gpo_id) && !empty($this->prm->mme_gpo_id)
                            ?
                            ',mme_gpo_id:"' . $this->prm->mme_gpo_id . '"' : '') . '
                            ' . (isset($this->prm->mme_ur_id) && !empty($this->prm->mme_ur_id)
                            ?
                            ',mme_ur_id:"' . $this->prm->mme_ur_id . '"' : '') . '
                            ' . (isset($this->prm->nivelRevision) ?
                            ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
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
            //$this->prm->omo_id = "E";
            $this->prm->mme_gpo_id = (isset($this->prm->mme_gpo_id) ?
                $this->prm->mme_gpo_id : $_SESSION[_S]['clasAdm-gpo']);
            $this->prm->mme_ur_id = (isset($this->prm->mme_ur_id) ?
                $this->prm->mme_ur_id : $_SESSION[_S]['clasAdm-ur']);
            $this->prm->emo_id = 17;
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
                $observacion = ' capturado desde PANTALLA S_1_8';
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
                $this->prm->origen = $mme->getOrigen();
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
                $params = [
                    'omo' => $mme->getOmo_id(),
                    'gpo' => $mme->getMme_gpo_id(),
                    'ur'  => $mme->getMme_ur_id(),
                    'id'  => $mme->getMme_id(),
                ];
                //Elimina las firmas
                $sql = 'DELETE FROM scp_ModificacionMetaFirma WHERE omo_id=:omo
                    AND mme_gpo_id=:gpo AND mme_ur_id =:ur
                    AND mme_id =:id;';
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
            $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
                != '::1') ?
                AccesoDatos::getIP() : '127.0.0.1';
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

        $params = [
            'omo' => $mod->getOmo_id(),
            'gpo' => $mod->getMme_gpo_id(),
            'ur'  => $mod->getMme_ur_id(),
            'id'  => $mod->getMme_id(),
        ];

        $sql = "
            SELECT DATE_FORMAT(b.usr_fecha,'%d/%m/%Y') fecha, time(b.usr_fecha) hora,mme_id, emo_nombre,
            if(b.emo_id=3 or b.emo_id=5 or b.emo_id=7 or b.emo_id=9 or b.emo_id=11 or b.emo_id=13,  trim(replace(bit_observacion,'\"',' ')),'') as bit_observacion, b.usr_id,usr_nombre,b.emo_id,usr_paterno,usr_materno
            FROM
            " . BD_BITACORA . '.bit_ModificacionMeta b
            INNER JOIN cat_EstadoModificacion using(emo_id)
            INNER JOIN scp_ModificacionMeta m using(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN cat_Usuario u on b.usr_id=u.usr_id
            INNER JOIN ' . BD_USUARIO . ".cat_Usuario us ON u.usr_id=us.usr_id
            WHERE m.omo_id=:omo
            AND m.mme_gpo_id=:gpo
            AND m.mme_ur_id=:ur
            AND m.mme_id=:id
            AND bit_observacion not in ('MOVIMIENTO AGREGADO','MOVIMIENTO ACTUALIZADO','MOVIMIENTO ELIMINADO')
            ORDER BY b.usr_fecha desc, bit_mme
        ";
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
        <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="false" style="display: block; padding-top: 100px;">
            <div class="modal-dialog modal-xl" style="width: 100% !IMPORTANT;">
                <div class="modal-content">
                    <div class="modal-header" style="background-color:#9d2449;">
                        <h4 class="modal-title" id="myModalLabel"></h4>
                        <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <button type="button" class="btn btn-outline-danger cerrar" data-bs-dismiss="modal">Cancelar</button>
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
        ');
    }

    /* MÉTODO PARA AGREGAR LA OBRA/ACTIVIDAD AL FOLIO */
    private function addMmeModificacion()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //Definicion de variables
        $ejecutora = '';
        $ue_id = '';
        $parametros = [
            'gpo' => $_SESSION[_S]['clasAdm-gpo'],
            'ur'  => $_SESSION[_S]['clasAdm-ur'],
        ];

        if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {
            $mme = new ModificacionMeta($this->prm);
            $ejecutora = 'AND ue_id = :ue ';
            $parametros['ue'] = $mme->getMme_ue_id();
            $ue_id = $mme->getMme_ue_id();
        }

        $sql = "
            SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id,'-',oa_nombre) enc_prog
            FROM cat_ObraActividad
            INNER JOIN enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            WHERE gpo_id = :gpo AND ur_id = :ur
                $ejecutora
            AND eoa_id = 5 AND toa_id = 2 AND oa_id!=00;
        ";

        //Combo encuadre programatico
        $encProgramatico = new stdClass();
        $encProgramatico->id = 'epg_id';
        $encProgramatico->width = '1000px';
        $encProgramatico->comoAtr = 'epg_id';
        $encProgramatico->requerid = true;
        $encProgramatico->sql = $sql;
        $encProgramatico->params = $parametros;
        $this->recargaCombo($encProgramatico);

        $parametros['filtro'] = 'planeacionValidada';
        $parametros['ue_id'] = $ue_id;
        $parametros['nivelRevision'] = $this->nivelRevision;
        $parametros['metodo'] = 'abcModificacion';
        $parametros['pantalla'] = $this->pantalla;
        $parametros['omo_id'] = $this->prm->omo_id;
        $parametros['mme_gpo_id'] = $this->prm->mme_gpo_id;
        $parametros['mme_ur_id'] = $this->prm->mme_ur_id;
        $parametros['mme_id'] = $this->prm->mme_id;
        $parametros['abc'] = 'a';
        $parametros['tri_id'] = $this->tri_id;
        $parametros['mact_modificado'] = 0;

        $html = '';
        $html .= CatalogoEncuadreProgramatico::ponModalEncuadre($parametros);
        $this->mixHtml($html);

        $inputEncProgramatico = new stdClass();
        $inputEncProgramatico->id = 'inputEncuadre';
        $inputEncProgramatico->name = 'inputEncuadre';
        $inputEncProgramatico->placeholder = 'Encuadre (F1)';
        $inputEncProgramatico->enDiv = true;
        $inputEncProgramatico->label = 'Encuadre Programático';
        $inputEncProgramatico->formato = 'text';
        $inputEncProgramatico->class = 'F1Catalogo';
        $inputEncProgramatico->epg_id = '';
        $inputEncProgramatico->readonly = true;

        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
            })
            $("#cerrar").click(function(){ $("#myModal").modal("hide"); });

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
                    DetalleModificacionMeta::abc($this->prm);
                    $mensaje = 'MOVIMIENTO AGREGADO AL FOLIO, ENCUADRE PROGRAMATICO ' . $this->prm->epg_id . '';
                    $men = 'Actividad agregada';
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
        $params = [
            'omo' => $this->prm->omo_id,
            'gpo' => $this->prm->mme_gpo_id,
            'ur'  => $this->prm->mme_ur_id,
            'id'  => $this->prm->mme_id,
            'epg' => $this->prm->epg_id,
        ];
        $sql = 'SELECT * FROM scp_DetalleModificacionMeta
            WHERE omo_id = :omo
            AND mme_gpo_id = :gpo
            AND mme_ur_id = :ur
            AND mme_id = :id
            AND epg_id = :epg;
        ';
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

                $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ?
                    $_SESSION[_S]['ses_ip'] : '127.0.0.1';

                $rutalog = 'log/AdecuacionesMetas-' . $_SESSION[_S]['usr_id'] . '-' . $mme_gpo_id . $mme_ur_id . '.txt';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d H-i-s');
                foreach ($prm as $field => $value) {
                    if (is_string($value)) {
                        $tempdata .= '|' . $field . ' = ' . $value;
                    }
                }
                $tempdata .= '|IPADDRESS=' . $ip;
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
        $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_8';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
            ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $id = $_SESSION[_S]['usr_id'];

        if (
            isset($this->prm->omo_id) && isset($this->prm->mme_gpo_id) && isset($this->prm->mme_ur_id)
            && isset($this->prm->mme_id)
        ) {
            //INSTANCIANDO LA ENTIDAD MODIFICACION META
            $mme = new ModificacionMeta($this->prm);
            $params = [
                'omo' => $mme->getOmo_id(),
                'gpo' => $mme->getMme_gpo_id(),
                'ur'  => $mme->getMme_ur_id(),
                'id'  => $mme->getMme_id(),
            ];
            $sql = 'select count(*) as numero from scp_DetalleModificacionMeta
                WHERE omo_id = :omo
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                AND mme_id = :id;';

            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $numeroMov = $this->extraer();

            $params = [$observacion, $ip, $id];
            $sql = 'SET @bit_observacion = ? ,@usr_ip = INET_ATON(?), @usr_id = ?';
            $this->setSql($sql);
            $this->consultar($params);

            $params = [
                'omo' => $mme->getOmo_id(),
                'gpo' => $mme->getMme_gpo_id(),
                'ur'  => $mme->getMme_ur_id(),
                'id'  => $mme->getMme_id(),
            ];
            $sqlObras = '
                SELECT omo_id, mme_gpo_id, mme_ur_id, mme_id, epg_id, pii_id, tri_id, mact_modificado
                FROM scp_DetalleModificacionMeta
                WHERE omo_id = :omo
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                AND mme_id = :id;
            ';
            $this->setSql($sqlObras);
            $this->consultar($params);
            while ($row = $this->extraer()) {
                $parametrosBitacora = (object) $row;
                $parametrosBitacora->observacion = 'MOVIMIENTO ELIMINADO DE MANERA MASIVA';
                $parametrosBitacora->ip = $ip;
                $this->bitacora('D', $parametrosBitacora);
            }
            //ELIMINA LA MODIFICACION DE METAS TEMPORAL
            $params = [
                'omo' => $this->prm->omo_id,
                'gpo' => $this->prm->mme_gpo_id,
                'ur'  => $this->prm->mme_ur_id,
                'id'  => $this->prm->mme_id,
            ];
            $this->setSql('
                DELETE FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                WHERE omo_id = :omo
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                AND mme_id = :id;
            ');
            $this->consultar($params);
            //ELIMINA LOS MOVIMIENTOS DEL FOLIO
            $params = [
                'omo' => $mme->getOmo_id(),
                'gpo' => $mme->getMme_gpo_id(),
                'ur'  => $mme->getMme_ur_id(),
                'id'  => $mme->getMme_id(),
            ];
            $sql2 = 'DELETE FROM scp_DetalleModificacionMeta
                WHERE omo_id = :omo
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                AND mme_id = :id;';
            $this->setSql($sql2);
            $this->consultar($params);
            $this->desconectar();

            $this->mixScript('
                notify("' . $numeroMov['numero'] . ' movimientos eliminados");
            ');

            $this->tablaModificaciones();
        }
    }

    private function modificarMetas()
    {
        $lectura = false;
        $omo_id = $this->prm->omo_id;
        $mme_gpo_id = $this->prm->mme_gpo_id;
        $mme_ur_id = $this->prm->mme_ur_id;
        $mme_id = $this->prm->mme_id;
        $botonRegresar = '';
        $botonEliminar = '';
        $mme = new ModificacionMeta($this->prm);
        $mme->select();
        $emo_id = $mme->getEmo_id();

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
                if ($omo_id == 'E') {
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
        $regresar->icon = 'fa-solid fa-circle-left me-2';
        $regresar->class = 'btn btn-outline-secondary me-1';
        $botonRegresar = $this->ponBoton($regresar);
        if (!$lectura) {
            //BOTON CANCELAR, CANCELA LA MODIFICACION DE METAS
            $eliminarMov = new stdClass();
            $eliminarMov->id = 'eliminar';
            $eliminarMov->value = 'Eliminar';
            $eliminarMov->class = 'btn-test btn-outline-danger me-1';
            $eliminarMov->icon = 'fa-solid fa-trash me-2';
            $botonEliminar = $this->ponBoton($eliminarMov);
        }

        $this->agregaTools($botonRegresar . ' ' . $botonEliminar);
        $this->conectar();
        $params = [$omo_id, $mme_gpo_id, $mme_ur_id, $mme_id, $this->prm->epg_id];
        $this->setSql('
            SELECT epg_id,prg_id,spr_id,py_id,oa_id,oa_nombre
            FROM scp_ModificacionMeta
            INNER JOIN scp_DetalleModificacionMeta USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN enc_Programatico  USING(epg_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            ' . (($lectura) ? 'INNER JOIN ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral USING(omo_id,mme_gpo_id,mme_ur_id,mme_id,epg_id)'
            : '') . '
            WHERE omo_id = ? AND mme_gpo_id = ? AND mme_ur_id = ? AND mme_id = ?
            AND epg_id = ?
            GROUP BY epg_id;
        ');
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
                    mme_gpo_id:"' . $mme_gpo_id . '",
                    mme_ur_id:"' . $mme_ur_id . '",
                    mme_id:' . $mme_id . ',
                    epg_id:$(this).attr("epg_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '
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

            $("#eliminar").click(function(){
                alertify.confirm(
                    "<h5> ELIMINAR MOVIMIENTO </h5>",
                    "¿ Realmente desea eliminar el movimiento ?",
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
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
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
        ');
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
        $regresar->icon = 'fa-solid fa-circle-left me-2';
        $regresar->class = 'btn btn-outline-secondary me-1';
        $botonRegresar = $this->ponBoton($regresar);
        if (!$lectura) {
            //BOTON CANCELAR, CANCELA LA MODIFICACION DE METAS
            $eliminarMov = new stdClass();
            $eliminarMov->id = 'eliminar';
            $eliminarMov->value = 'Eliminar';
            $eliminarMov->class = 'btn btn-outline-danger me-1';
            $eliminarMov->icon = 'fa-solid fa-trash me-2';
            $botonEliminar = $this->ponBoton($eliminarMov);
        }

        //AGREGA LOS BOTONES A LA BARRA DE HERRAMIENTAS
        $this->agregaTools($botonRegresar . ' ' . $botonEliminar . ' ' . $botonEndMeta);

        //CONSTRUYE LA CONSULTA PARA LAS METAS DE LA ACTIVIDAD
        $params = [$this->prm->epg_id, $this->prm->omo_id, $this->prm->mme_gpo_id, $this->prm->mme_ur_id, $this->prm->mme_id, $this->prm->epg_id];
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre,
            pdi_nombre,ctInd.umv_id,ctInd.umv_nombre,
            cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
            mac_descripcion,epg_id,mac_original,mac_modificado vigente
            FROM enc_Programatico enc
            JOIN cat_MetaActividad ma USING(epg_id)
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
                WHERE  epg_id = ? AND omo_id = ?
                    AND mme_gpo_id = ? AND mme_ur_id = ?
                    AND mme_id = ?
                    GROUP BY omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id
            ) b using (pii_id,epg_id)
            JOIN cat_Subprograma cs USING(gpo_id,ur_id,prg_id,spr_id)
            JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            JOIN cat_Indicador i USING(ind_id,nim_id)
            JOIN cat_ProductoIntermedio USING(pdi_id)
            JOIN cat_UnidadMedidaVariable ctInd USING(umv_id)
            WHERE
            epg_id = ?
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
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
                <th class="warning" style="text-align:center" colspan="1">Actividad MIR</th>
                <th class="warning" style="text-align:center" colspan="1">Indicador</th>
                <th class="warning" style="text-align:center" colspan="1">UnidadMedida</th>
                <th class="warning" style="text-align:center" colspan="8">Descripción</th>
                <th class="warning" style="text-align:center" colspan="1">Ver</th>
            </tr>
        ';
        while ($row = $this->extraer()) {
            //INFORMACIÓN DE METAS
            $htm .= '<tr>
                    <td style="background: rgb(255,255,255)" align="center" colspan="1">' . $row['pdi_nombre'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="1">' . $row['ind_nombre'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="1">' . $row['umv_nombre'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="8">' . $row['mac_descripcion'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="1" >
                        <button type="button" id="verMetasDet" icon="eye-open" title="Opción para ver metas de la actividad" class="verMetasDet btn btn-outline-secondary" epg_id=' . $row['epg_id'] . ' pii_id = ' . $row['pii_id'] . '>
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
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
                    ' . (isset($this->prm->mme_gpo_id) ? ',mme_gpo_id:"' . $this->prm->mme_gpo_id . '"'
                    : '') . '
                    ' . (isset($this->prm->mme_ur_id) ? ',mme_ur_id:"' . $this->prm->mme_ur_id . '"'
                    : '') . '
                    ' . (isset($this->prm->mme_id) ? ',mme_id:"' . $this->prm->mme_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '
                });
            });

            $("#eliminar").click(function(){
                alertify.confirm(
                    "<h5> ELIMINAR MOVIMIENTO </h5>",
                    "¿ Realmente desea eliminar el movimiento ?",
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
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
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

            $(".verMetasDet").click(function(){
                $(".divMetasDet").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"metas",
                    omo_id:"' . $this->prm->omo_id . '",
                    mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                    mme_ur_id:"' . $this->prm->mme_ur_id . '",
                    mme_id:"' . $this->prm->mme_id . '",
                    pii_id:$(this).attr("pii_id"),
                    epg_id:$(this).attr("epg_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                    : '') . '
                    ' . ((!$lectura) ? ',lectura:false' : ',lectura:true') . '
                });
            });
        ');
    }

    private function metaCuatroDecimales($numero)
    {
        if ($numero === null || $numero === '') {
            return 0.0;
        }
        return round((float) $numero, 4);
    }

    private function formatoMeta($numero)
    {
        return number_format($this->metaCuatroDecimales($numero), 4, '.', '');
    }

    //MUESTRA LA INFORMACION CALENDARIZADA DE LA META
    private function metas()
    {
        $this->setContenedor('oa' . $this->prm->epg_id . $this->prm->pii_id);
        $m = new ModificacionMeta($this->prm);
        $lectura = $this->prm->lectura;
        $consulta = ($lectura) ? ' INNER JOIN ' : ' LEFT JOIN ';
        //CONSTRUYE LA CONSULTA PARA OBTENER LA MODIFICACION DE METAS
        $params = [
            'ur'  => $this->prm->mme_ur_id,
            'epg' => $this->prm->epg_id,
            'pii' => $this->prm->pii_id,
            'omo' => $this->prm->omo_id,
            'gpo' => $this->prm->mme_gpo_id,
            'ur'  => $this->prm->mme_ur_id,
            'id'  => $this->prm->mme_id,
            'omo' => $this->prm->omo_id,
        ];
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre,
            pdi_nombre,ctInd.umv_id,ctInd.umv_nombre,
            cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
            mac_descripcion,epg_id,mac_original,mac_modificado vigente,
            ROUND(mac_modificado + IFNULL(b.mod_trimestre1,0) + IFNULL(b.mod_trimestre2,0)
            + IFNULL(b.mod_trimestre3,0) + IFNULL(b.mod_trimestre4,0), 4) modificado,
                (SELECT mact_modificado
                FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=1) AS vig_trimestre1,
                (SELECT mact_modificado FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=2) AS vig_trimestre2,
                (SELECT mact_modificado FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=3) AS vig_trimestre3,
                (SELECT mact_modificado FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=4) AS vig_trimestre4,
                (SELECT mact_capturaAvance
                FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=1) AS cap_trimestre1,
                (SELECT mact_capturaAvance FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=2) AS cap_trimestre2,
                (SELECT mact_capturaAvance FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=3) AS cap_trimestre3,
                (SELECT mact_capturaAvance FROM cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id=pii AND ct.epg_id=epg AND tri_id=4) AS cap_trimestre4,
                (SELECT etr_id1 FROM mir_TrimestreUr
                WHERE gpo_id = ' . $this->prm->mme_gpo_id . ' AND ur_id = :ur
                AND nim_id=4 AND tri_id=1) AS est_trimestre1,
                (SELECT etr_id1 FROM mir_TrimestreUr
                WHERE gpo_id = ' . $this->prm->mme_gpo_id . ' AND ur_id = :ur
                AND nim_id=4 AND tri_id=2) AS est_trimestre2,
                (SELECT etr_id1 FROM mir_TrimestreUr
                WHERE gpo_id = ' . $this->prm->mme_gpo_id . ' AND ur_id = :ur
                AND nim_id = 4 AND tri_id = 3) AS est_trimestre3,
                (SELECT etr_id1 FROM mir_TrimestreUr
                WHERE gpo_id = ' . $this->prm->mme_gpo_id . ' AND ur_id = :ur
                AND nim_id = 4 AND tri_id = 4) AS est_trimestre4,
                b.mod_trimestre1,b.mod_trimestre2,b.mod_trimestre3,b.mod_trimestre4
                FROM enc_Programatico enc
                JOIN cat_MetaActividad ma USING(epg_id)
                ' . $consulta . '  (
                    SELECT omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,
                    SUM(CASE WHEN tri_id =1 THEN
                    mact_modificado
                    ELSE 0.00 END) AS mod_trimestre1,
                    SUM(CASE WHEN tri_id =2 THEN
                    mact_modificado
                    ELSE 0.00 END) AS mod_trimestre2,
                    SUM(CASE WHEN tri_id =3 THEN
                    mact_modificado
                    ELSE 0.00 END) AS mod_trimestre3,
                    SUM(CASE WHEN tri_id =4 THEN
                    mact_modificado
                    ELSE 0.00 END) AS mod_trimestre4
                    FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                    WHERE  epg_id = :epg
                        AND pii_id = :pii
                        AND omo_id = :omo
                        AND mme_gpo_id = :gpo
                        AND mme_ur_id = :ur
                        AND mme_id = :id
                        GROUP BY omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id
                ) b using (pii_id,epg_id)
                JOIN cat_Subprograma cs USING(gpo_id,ur_id,prg_id,spr_id)
                JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                JOIN cat_Indicador i USING(ind_id,nim_id)
                JOIN cat_ProductoIntermedio USING(pdi_id)
                JOIN cat_UnidadMedidaVariable ctInd USING(umv_id)
            WHERE
            epg_id = :epg
            AND pii_id = :pii
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();

        $trimestres = '';
        $div = '<div class="row row-cols-md-4">';
        $trimestreLabel = ['Primer', 'Segundo', 'Tercer', 'Cuarto'];
        $datosMeta = '<form id="calendario">';
        $trimestreModificado = '';
        /*
        Para poder habilitar un trimestre deben cumplirse las siguientes reglas:
        1.Tener el trimestre abierto en avance de gestion o
        2. no tener captura en avance de gestion y estar bloqueados.
         */
        while ($datos = $this->extraer()) {
            $actividadMIR = $datos['pdi_nombre'];
            $nombreIndicador = $datos['ind_nombre'];
            $unidadMedida = $datos['umv_nombre'];
            $descripcion = $datos['mac_descripcion'];
            $datosMeta .= '
                <table class="table table-hover table-bordered" >
                    <tr class="warning border-warning" style="background: rgb(255, 234, 168)" >
                        <td colspan=13>
                            Actividad MIR: ' . $actividadMIR . '
                        </td>
                    </tr>
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        <td>Indicador:</td>
                        <td colspan=2>' . $nombreIndicador . '</td>
                    </tr>
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        <td>Unidad de Medida:</td>
                        <td colspan=2>' . $unidadMedida . '</td>
                    </tr>
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        <td>Descripción:</td>
                        <td colspan=2>' . $descripcion . '</td>
                    </tr>
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        <td>Anual Vigente:</td>
                        <td id="vigente" colspan=2>' . $this->formatoMeta($datos['vigente']) . '</td>
                    </tr>
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        <td>Anual Modificado:</td>
                        <td id="modificado" colspan=2>' . $this->formatoMeta(IS_NULL($datos['modificado'])
                ?
                $datos['vigente'] : $datos['modificado']) . '</td>
                    </tr>
                    <tr class="warning border-warning" style="background: rgb(255,255,255)" >
                        <td >CALENDARIO</td>
                        ' . ((!$lectura) ? '<td align="right">
                            <button type="button" id="guardar" icon="ok"
                                title="Guardar modificación de metas de la actividad"
                                class="btn btn-outline-success"
                                pii_id=' . $datos['pii_id'] . '
                                epg_id=' . $datos['epg_id'] . '>
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                            </button>
                        </td>' : '') . '
                    </tr>
                    <tr>
                        <td colspan=12 >

                        ';
            $almacenaModificado = 0;
            for ($x = 0; $x <= 3; $x++) {
                //Podrá modificar siempre y cuando se cumplan las siguientes condiciones
                $vigenteTrimestre = $this->metaCuatroDecimales($datos['vig_trimestre' . ($x + 1)]);
                $movimientoTrimestre = $this->metaCuatroDecimales($datos['mod_trimestre' . ($x + 1)]);
                $modificadoTrimestre = $this->metaCuatroDecimales($vigenteTrimestre + $movimientoTrimestre);
                $trimestreVigente = $this->formatoMeta($vigenteTrimestre);
                if ((($datos['est_trimestre' . ($x + 1) . ''] == 'A' || $datos['est_trimestre' . ($x
                    + 1) . ''] == 'C') &&
                    $datos['cap_trimestre' . ($x + 1) . ''] == 0) && !$lectura) {

                    //CANTIDAD PARA AMPLIAR O REDUCIR
                    $trimestre = new stdClass();
                    $trimestre->required = true;
                    $trimestre->id = 'mod_trimestre' . ($x + 1);
                    $trimestre->enDiv = true;
                    $trimestre->value = $this->formatoMeta(abs($movimientoTrimestre));
                    $trimestre->formato = 'numero';
                    $trimestre->decimales = 4;
                    $trimestre->class = 'cantidad';

                    $comboMovimiento = new stdClass();
                    $comboMovimiento->id = 'mod_tipoMovimientoT' . ($x + 1);
                    $comboMovimiento->class = 'tipoMovimiento';
                    $comboMovimiento->sql = 'SELECT mov_id,mov_nombre FROM cat_TipoMovimiento WHERE mov_id!="C";';
                    $comboMovimiento->search = true;

                    //vigente + - monto
                    $trim = new stdClass();
                    $trim->id = 'trimestre' . ($x + 1);
                    $trim->class = 'trim';
                    $trim->formato = 'numero';
                    $trim->decimales = 4;
                    $trim->disabled = true;
                    $trim->value = $this->formatoMeta($modificadoTrimestre);

                    if (!IS_NULL($datos['mod_trimestre' . ($x + 1)])) {
                        if ($movimientoTrimestre > 0) {
                            $comboMovimiento->selecciona = 'A';
                        } elseif ($movimientoTrimestre < 0) {
                            $comboMovimiento->selecciona = 'R';
                        }
                    }

                    $trimestre = $this->ponInput($trimestre);
                    $comboMovimiento = $this->ponCombo($comboMovimiento);
                    $trim = $this->ponInput($trim);
                    $activado = 'warning';
                } else {

                    //Modificado = Vigente + adecuacion meta
                    if ($lectura) {
                        $trimestreModificado = $this->formatoMeta(abs($movimientoTrimestre));
                    } else {
                        $trimestre = new stdClass();
                        $trimestre->required = true;
                        $trimestre->id = 'mod_trimestre' . ($x + 1);
                        $trimestre->enDiv = true;
                        $trimestre->value = $this->formatoMeta($movimientoTrimestre);
                        $trimestre->formato = 'numero';
                        $trimestre->decimales = 4;
                        $trimestre->class = 'cantidad';
                        $trimestre->readonly = true;
                        $trimestre = $this->ponInput($trimestre);
                    }

                    if ($datos['cap_trimestre' . ($x + 1)] == 1) {
                        $comboMovimiento = 'TIENE CAPTURA EN AVANCE DE GESTIÓN';
                    } else {
                        if ($movimientoTrimestre > 0) {
                            $comboMovimiento = 'AMPLIACIÓN';
                        } elseif ($movimientoTrimestre < 0) {
                            $comboMovimiento = 'REDUCCIÓN';
                        } else {
                            $comboMovimiento = 'SIN MOVIMIENTO';
                        }
                    }

                    $trim = $this->formatoMeta($modificadoTrimestre);
                    $almacenaModificado = $this->metaCuatroDecimales($almacenaModificado + $modificadoTrimestre);
                    $activado = 'info';
                }
                $trimestres .= '
                    <div class="col">
                        <div class="panel panel-' . $activado . '" >
                        <div class="panel-heading"> <h3 class="panel-title">' . $trimestreLabel[$x] . ' Trimestre</h3> </div>
                            <div class="panel-body">
                                <table class="table table-condensed">
                                    <tr class="d-flex">
                                        <td class="col-10">Vigente</td>
                                    </tr>
                                    <tr class="d-flex">
                                        <td class="col-10" id="vig' . ($x + 1) . '">' . $trimestreVigente . '</td>
                                    </tr>
                                    <tr class="d-flex">
                                        <td class="col-10">Adecuacion de Meta</td>
                                    </tr>
                                    <tr class="d-flex">
                                        <td class="col-10">' . $comboMovimiento . '</td>
                                    </tr>
                                    <tr class="d-flex">
                                        <th class="col-10">Cantidad</th>
                                    </tr>
                                    <tr class="d-flex">
                                        <td class="col-10">' . ($lectura ? $trimestreModificado : $trimestre) . '</td>
                                    </tr>
                                    <tr class="d-flex">
                                        <th class="col-10">Modificado</th>
                                    </tr>
                                    <tr class="d-flex">
                                        <td class="col-10">' . $trim . '</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                ';
            }
            $div .= $trimestres . '</div>';
            $datosMeta .= $div;
        }
        $datosMeta .= '</td>
            </tr> </table>
        ';

        $this->mixHtml($datosMeta);

        $this->mixScript('
            function redondeaMeta(n){
                return Math.round(Number(n) * 10000) / 10000;
            }

            function realizarOperacion( op ){
                var valido = true;
                if( op !== undefined )
                {
                    console.log(op + "calculando...");
                    var mov     = $("#mod_tipoMovimientoT" + op ).find("option:selected").val();
                    var vigente = parseFloat(  unformatNumber( $("#vig"+op).text() ) );
                    var cantidad= parseFloat( unformatNumber( $("#mod_trimestre" + op ).val() ) );
                    var total = 0;
                    var totala = 0;
                    var valido = true;
                    $("#guardar").prop("disabled", false);
                    $("#trimestre" + op ).css("border","1px solid #CCC");
                    switch ( mov)
                    {
                        case "R":
                            total = redondeaMeta(vigente - cantidad);
                            if ( total < 0 )
                            {
                                $("#guardar").prop("disabled",true);
                                $("#trimestre" + op ).css("border","1px solid #900");
                            }
                            else
                            {
                                $("#guardar").prop("disabled", false);
                                $("#trimestre" + op ).css("border","1px solid #CCC");
                            }
                            break;
                        case "A":
                            total = redondeaMeta(vigente + cantidad);
                            break;
                        default:
                            if(cantidad!=0){
                                valido = false;
                            }
                            break;
                    }
                    $("#trimestre" + op ).val(Number(total).toFixed(4)).blur();

                    $(".trim").each(function(){
                        totala = redondeaMeta(totala + parseFloat( unformatNumber( $(this).val() )));
                    });
                    totalModificado = redondeaMeta(totala + ' . $almacenaModificado . ');
                    $("#modificado").html(Number(totalModificado).toFixed(4));
                }
                else
                {
                    $(".trim").each(function(){
                        var valor = parseFloat(unformatNumber($(this).val()));
                        if( valor <0 )
                            valido = false;
                    });
                }
                return valido;
            }

            $(".cantidad").blur(function(){
                varn = $(this).attr("id");
                varn = varn.substring(13,14);
                realizarOperacion( parseInt(varn));
            });

            $(".tipoMovimiento").change(function(){
                varn = $(this).attr("id");
                varn = varn.substring(19,20);

                if($("#mod_tipoMovimientoT" + varn ).val()!== undefined && $("#mod_tipoMovimientoT" + varn ).val()!==""){
                    $("#mod_trimestre" + varn ).removeAttr("disabled");
                }else{
                    $("#mod_trimestre" + varn ).attr("disabled","disabled");
                }
                realizarOperacion( parseInt(varn));
            });

            $("#guardar").click(function(){
                //Obtenemos los valores de los trimestres para hacer la comparacion si anual del vigente y modificado es el mismo
                var vig1=$("#vig1").text();
                var mod1=$("#trimestre1").val();
                var vig2=$("#vig2").text();
                var mod2=$("#trimestre2").val();
                var vig3=$("#vig3").text();
                var mod3=$("#trimestre3").val();
                var vig4=$("#vig4").text();
                var mod4=$("#trimestre4").val();

                if( realizarOperacion() != true )
                {
                    notify("La meta no puede ser negativa","error");
                }else{
                    if($("#vigente").text()!=$("#modificado").text()){
                        comunica("' . $this->pantalla . '",{
                            metodo:"actualizarMetas",
                            omo_id:"' . $this->prm->omo_id . '",
                            mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                            mme_ur_id:"' . $this->prm->mme_ur_id . '",
                            mme_id:"' . $this->prm->mme_id . '",
                            pii_id:$(this).attr("pii_id"),
                            epg_id:$(this).attr("epg_id"),
                            movTrimestre1:$("#mod_tipoMovimientoT1").val(),
                            movTrimestre2:$("#mod_tipoMovimientoT2").val(),
                            movTrimestre3:$("#mod_tipoMovimientoT3").val(),
                            movTrimestre4:$("#mod_tipoMovimientoT4").val(),
                            mod_trimestre1:unformatNumber($("#mod_trimestre1").val()),
                            mod_trimestre2:unformatNumber($("#mod_trimestre2").val()),
                            mod_trimestre3:unformatNumber($("#mod_trimestre3").val()),
                            mod_trimestre4:unformatNumber($("#mod_trimestre4").val()),
                            mem_modificado:unformatNumber($("#modificado").text())
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                            : '') . '
                        });
                    }else if ((vig1!=mod1) || (vig2!=mod2) || (vig3!=mod3) || (vig4!=mod4)){
                        comunica("' . $this->pantalla . '",{
                            metodo:"actualizarMetas",
                            omo_id:"' . $this->prm->omo_id . '",
                            mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                            mme_ur_id:"' . $this->prm->mme_ur_id . '",
                            mme_id:"' . $this->prm->mme_id . '",
                            pii_id:$(this).attr("pii_id"),
                            epg_id:$(this).attr("epg_id"),
                            movTrimestre1:$("#mod_tipoMovimientoT1").val(),
                            movTrimestre2:$("#mod_tipoMovimientoT2").val(),
                            movTrimestre3:$("#mod_tipoMovimientoT3").val(),
                            movTrimestre4:$("#mod_tipoMovimientoT4").val(),
                            mod_trimestre1:unformatNumber($("#mod_trimestre1").val()),
                            mod_trimestre2:unformatNumber($("#mod_trimestre2").val()),
                            mod_trimestre3:unformatNumber($("#mod_trimestre3").val()),
                            mod_trimestre4:unformatNumber($("#mod_trimestre4").val()),
                            mem_modificado:unformatNumber($("#modificado").text())
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                            : '') . '
                        });
                    }
                    else{
                        notify("No hay cambios en el calendario","warning");
                    }
                }
            });
        ');
    }

    private function actualizarMetas()
    {

        $tmf = '';
        if (isset($this->prm->movTrimestre1) && $this->prm->movTrimestre1 == 'R') {
            $this->prm->mod_trimestre1 = $this->prm->mod_trimestre1 * (-1);
            $tmf = 'R';
        } elseif (isset($this->prm->movTrimestre1) && $this->prm->movTrimestre1 == 'A' && $this->prm->mod_trimestre1 != 0) {
            $this->prm->mod_trimestre1 = $this->prm->mod_trimestre1;
            $tmf = 'A';
        }

        if (isset($this->prm->movTrimestre2) && $this->prm->movTrimestre2 == 'R') {
            $this->prm->mod_trimestre2 = $this->prm->mod_trimestre2 * (-1);
            $tmf = 'R';
        } elseif (isset($this->prm->movTrimestre2) && $this->prm->movTrimestre2 == 'A' && $this->prm->mod_trimestre2 != 0) {
            $tmf = 'A';
        }

        if (isset($this->prm->movTrimestre3) && $this->prm->movTrimestre3 == 'R') {
            $this->prm->mod_trimestre3 = $this->prm->mod_trimestre3 * (-1);
            $tmf = 'R';
        } elseif (isset($this->prm->movTrimestre3) && $this->prm->movTrimestre3 == 'A' && $this->prm->mod_trimestre3 != '') {
            $tmf = 'A';
        }

        if (isset($this->prm->movTrimestre4) && $this->prm->movTrimestre4 == 'R') {
            $this->prm->mod_trimestre4 = $this->prm->mod_trimestre4 * (-1);
            $tmf = 'R';
        } elseif (isset($this->prm->movTrimestre4) && $this->prm->movTrimestre4 == 'A' && $this->prm->mod_trimestre4 != '') {
            $tmf = 'A';
        }

        foreach ([1, 2, 3, 4] as $numeroTrimestre) {
            $campo = 'mod_trimestre' . $numeroTrimestre;
            if (isset($this->prm->$campo) && $this->prm->$campo !== '') {
                $this->prm->$campo = $this->metaCuatroDecimales($this->prm->$campo);
            }
        }

        $bandera = true;
        $mensaje = '';

        $params = [
            't1'  => $this->prm->mod_trimestre1,
            't2'  => $this->prm->mod_trimestre2,
            't3'  => $this->prm->mod_trimestre3,
            't4'  => $this->prm->mod_trimestre4,
            'ur'  => $this->prm->mod_trimestre2,
            'epg' => $this->prm->epg_id,
            'pii' => $this->prm->pii_id,
            'gpo' => $this->prm->mme_gpo_id,
            'ur'  => $this->prm->mme_ur_id,
        ];
        $sqlDisponible = '
            SELECT
                (m.ori_trimestre1 + d.t1 + e.t11 + (:t1)) disponible1,
                (m.ori_trimestre2 + d.t2 + e.t22 + (:t2)) disponible2,
                (m.ori_trimestre3 + d.t3 + e.t33 + (:t3)) disponible3,
                (m.ori_trimestre4 + d.t4 + e.t44 + (:t4)) disponible4
            FROM enc_Programatico enc
            JOIN cat_MetaActividad ma USING(epg_id)
            INNER JOIN (
                SELECT pii_id,epg_id,
                SUM(CASE WHEN tri_id =1 THEN
                mact_original
                ELSE 0.00 END) AS ori_trimestre1,
                SUM(CASE WHEN tri_id =2 THEN
                mact_original
                ELSE 0.00 END) AS ori_trimestre2,
                SUM(CASE WHEN tri_id =3 THEN
                mact_original
                ELSE 0.00 END) AS ori_trimestre3,
                SUM(CASE WHEN tri_id =4 THEN
                mact_original
                ELSE 0.00 END) AS ori_trimestre4
                FROM cat_MetaActividadTrimestre
                WHERE epg_id = :epg
                AND pii_id = :pii
            )m using(pii_id,epg_id)
            INNER JOIN   (
                SELECT omo_id,mme_gpo_id,mme_ur_id,MAX(mme_id),pii_id,epg_id,
                SUM(CASE WHEN tri_id =1 THEN
                mact_modificado
                ELSE 0.00 END) AS t1,
                SUM(CASE WHEN tri_id =2 THEN
                mact_modificado
                ELSE 0.00 END) AS t2,
                SUM(CASE WHEN tri_id =3 THEN
                mact_modificado
                ELSE 0.00 END) AS t3,
                SUM(CASE WHEN tri_id =4 THEN
                mact_modificado
                ELSE 0.00 END) AS t4
                FROM aux_HistoricoModificacionMeta
                WHERE epg_id = :epg
                AND pii_id = :pii
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                GROUP BY omo_id
            ) d using (pii_id,epg_id)
            INNER JOIN (
                SELECT omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,
                SUM(CASE WHEN tri_id =1 THEN
                mact_modificado
                ELSE 0.00 END) AS t11,
                SUM(CASE WHEN tri_id =2 THEN
                mact_modificado
                ELSE 0.00 END) AS t22,
                SUM(CASE WHEN tri_id =3 THEN
                mact_modificado
                ELSE 0.00 END) AS t33,
                SUM(CASE WHEN tri_id =4 THEN
                mact_modificado
                ELSE 0.00 END) AS t44
                FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                WHERE epg_id = :epg
                AND pii_id = :pii
                AND mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                GROUP BY omo_id,mme_id
            )e using(pii_id,epg_id)
            WHERE
            epg_id = :epg
            AND pii_id = :pii
            ORDER BY ma.usr_fecha;
        ';

        $this->conectar();
        $this->setSql($sqlDisponible);
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            $extraer = $this->extraer();

            $trimestres = [
                $extraer['disponible1'],
                $extraer['disponible2'],
                $extraer['disponible3'],
                $extraer['disponible4'],
            ];
            $bandera = true;

            for ($x = 0; $x <= 3; $x++) {
                if ($this->metaCuatroDecimales($trimestres[$x]) < 0) {
                    $bandera = false;
                    $mensaje = 'La cantidad a reducir, supera el disponible en el trimestre ' . ($x
                        + 1) . ', en relación a la cantidad de folios que la modifican';
                    break;
                }
            }
        }

        if ($bandera) {
            for ($trimestre = 1; $trimestre <= 4; $trimestre++) {
                switch ($trimestre) {
                    case 1:
                        $mact_modificado = $this->prm->mod_trimestre1;
                        break;
                    case 2:
                        $mact_modificado = $this->prm->mod_trimestre2;
                        break;
                    case 3:
                        $mact_modificado = $this->prm->mod_trimestre3;
                        break;
                    case 4:
                        $mact_modificado = $this->prm->mod_trimestre4;
                        break;
                }
                //CONSULTAMOS SI ESTA META YA FUE MODIFICADA
                $numRegistros = $this->consultaMetaModificada(
                    $this->prm->omo_id,
                    $this->prm->mme_gpo_id,
                    $this->prm->mme_ur_id,
                    $this->prm->mme_id,
                    $this->prm->epg_id,
                    $this->prm->pii_id,
                    $trimestre
                );

                //SI YA EXISTE EL REGISTRO SE ACTUALIZA, SINO SE AGREGA
                $abcMeta = ($numRegistros > 0) ? 'U' : 'I';
                $registraMeta = $this->abcTrimestres(
                    $abcMeta,
                    $this->prm->omo_id,
                    $this->prm->mme_gpo_id,
                    $this->prm->mme_ur_id,
                    $this->prm->mme_id,
                    $this->prm->pii_id,
                    $this->prm->epg_id,
                    $trimestre,
                    $mact_modificado,
                    $tmf
                );
            }

            $this->mixScript('
                $("#endMetas").removeAttr("disabled");
                notify("La meta ha sido modificada temporalmente","success");
            ');
        } else {
            $this->mixScript('
                notify("' . $mensaje . '","warning");
            ');
        }
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
        $params = [$omo_id, $mme_gpo_id, $mme_ur_id, $mme_id, $epg_id, $pii_id, $tri_id];
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
        $this->consultar($params);
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
        $mact_modificado,
        $tmf
    ) {
        $this->conectar();
        $params = [];
        switch ($abc) {
            case 'I':
                $params = [
                    $omo_id,
                    $mme_gpo_id,
                    $mme_ur_id,
                    $mme_id,
                    $pii_id,
                    $epg_id,
                    $tri_id,
                    $mact_modificado,
                    $tmf,
                    $_SESSION[_S]['usr_id'],
                ];
                $this->setSql('
                    INSERT INTO ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                    (omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,tri_id,mact_modificado,tmf_id,usr_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?,?, ?);
                ');
                break;
            case 'U':
                $params = [$mact_modificado, $omo_id, $mme_gpo_id, $mme_ur_id, $mme_id, $pii_id, $epg_id, $tri_id];
                $this->setSql('
                    UPDATE ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                    SET mact_modificado = ?
                    WHERE
                    omo_id = ? AND
                    mme_gpo_id = ? AND
                    mme_ur_id = ? AND
                    mme_id = ? AND
                    pii_id = ? AND
                    epg_id = ? AND
                    tri_id = ?;
                ');
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
        $this->conectar();
        $params = [$this->prm->omo_id, $this->prm->mme_gpo_id, $this->prm->mme_ur_id, $this->prm->mme_id, $this->prm->epg_id];
        $this->setSql('
            SELECT * FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
            WHERE omo_id = ?
            AND mme_gpo_id = ?
            AND mme_ur_id = ?
            AND mme_id = ?
            AND epg_id = ?;
        ');
        $this->consultar($params);
        $metas = $this->extraer();
        $this->desconectar();

        $params = [
            ':omo_id' => $this->prm->omo_id,
            ':gpo_id' => $this->prm->mme_gpo_id,
            ':ur_id'  => $this->prm->mme_ur_id,
            ':mme_id' => $this->prm->mme_id,
            ':epg'    => $this->prm->epg_id,
        ];
        //Si hay registros de metas de este folio se borran , sino unicamente se dirige a la funcion tablaFolios
        if ($this->getNumeroRegistros() > 0) {
            $this->conectar();
            $this->setSql('
                DELETE FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                WHERE omo_id = :omo_id
                AND mme_gpo_id = :gpo_id
                AND mme_ur_id = :ur_id
                AND mme_id = :mme_id
                AND epg_id = :epg;
            ');
            $this->consultar($params);

            $this->desconectar();
        }
        $this->conectar();
        //ELIMINA LA OBRA/ACTIVIDAD DEL FOLIO
        $params[':pii'] = $metas['pii_id'];
        $this->prm->pii_id = $metas['pii_id'];
        $this->prm->tri_id = $this->tri_id;
        $this->prm->mact_modificado = $metas['mact_modificado'];

        $sql2 = '
            DELETE FROM scp_DetalleModificacionMeta
            WHERE omo_id = :omo_id
            AND mme_gpo_id = :gpo_id
            AND mme_ur_id = :ur_id
            AND mme_id = :mme_id
            AND epg_id = :epg;
            AND pii_id = :pii;
        ';

        $this->setSql($sql2);
        $this->consultar($params);
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

        //Verifica si el folio tiene movimientos
        $this->conectar();
        $params = [$omo_id, $mme_gpo_id, $mme_ur_id, $mme_id];
        $this->setSql('
            SELECT mme_id FROM scp_DetalleModificacionMeta
            WHERE omo_id = ?
            AND mme_gpo_id = ?
            AND mme_ur_id = ?
            AND mme_id = ?;
        ');
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
            $this->prm->origen = $mme->getOrigen();
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
            if (count($datos) >= 1) {
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
            case 1:
            case 2: //UR y Ue
                $metodo = 'main';
                break;
            default: //Sefin
                $metodo = 'tablaUrs';
                $this->mixScript('$("#combour").val("' . $this->prm->mme_gpo_id . $this->prm->mme_ur_id . '").change();');
                break;
        }

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->class = 'btn btn-outline-secondary me-1';

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
        $td->icono = 'fa-solid fa-eye';
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
                        $and->valor = 2;
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
                        $and->valor = 2;
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
                $td->casos->falso->class = $clase . ' btn-xs btn-outline-danger';
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
                $td->casos->falso->class = $clase . ' btn-xs btn-outline-danger';

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
                        $and->valor = 2;
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
        $td->casos->verdadero->class = $clase . ' btn-xs btn-outline-danger';
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
        $td->icono = 'fa-solid fa-eye';
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
        $params = [
            'gpo' => isset($this->prm->mme_gpo_id) ? $this->prm->mme_gpo_id : $_SESSION[_S]['clasAdm-gpo'],
            'ur'  => isset($this->prm->mme_ur_id) ? $this->prm->mme_ur_id : $_SESSION[_S]['clasAdm-ur'],
        ];

        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $and .= '
                    AND omo_id = :omo
                    AND emo_id IN (2,5)
                    AND mme_subsecretaria=2
                ';
                $params['omo'] = 'A';
                break;
            case 'autorizacionUR':
                $and .= '
                    AND omo_id = :omo
                    AND emo_id IN (4,7)
                    AND mme_subsecretaria=2
                ';
                $params['omo'] = 'A';
                break;
            case 'validacionSEFIN':
                $and .= ' AND emo_id IN (10,13) 
                AND mme_subsecretaria=2';
                break;
            case 'autorizacionSEFIN':
                $and .= ' AND emo_id IN (12) 
                AND mme_subsecretaria=2';
                break;
        }

        $sql = "
            SELECT *,omo_id origenModificacion,'ADECUACION DE METAS' AS tmd_nombre, mme_Subsecretaria,
            1 habilitado
            FROM scp_ModificacionMeta
            INNER JOIN cat_EstadoModificacion using(emo_id)
            INNER JOIN cat_ModalidadModificacion using(mmd_id)
            INNER JOIN cat_OrigenModificacion using(omo_id)
            WHERE
                mme_gpo_id = :gpo
                AND mme_ur_id = :ur
                $and AND tfm_id = 'M'
                ORDER BY mme_id;
        ";

        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Revision';
        $this->setParams($params);
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
                var t = $(this);
                ' . $script . '
                ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision
                    == 'autorizacionSEFIN' ? 'alertify.confirm("Autorizar",String.fromCharCode(191)+"Desea autorizar el folio ?",
                function (){'
                : '') . '
                ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision
                == 'autorizacionSEFIN' ? '$(this).attr("disabled","disabled")' : '') . '
                    comunica("' . $this->pantalla . '",{
                        metodo:"validacionFolio",
                        omo_id:t.attr("omo_id"),
                        mme_gpo_id:t.attr("mme_gpo_id"),
                        mme_ur_id:t.attr("mme_ur_id"),
                        mme_id:t.attr("mme_id")
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                        : '') . '
                    });
                

                    ' . (isset($this->prm->nivelRevision) && $this->prm->nivelRevision
                    == 'autorizacionSEFIN' ? '},undefined
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
                }}' : '}') . '
                    ' . $endScript . '
            );

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
        $params = [$this->usr_id];

        if (count($datos) == 0) {
            switch ($this->nivelRevision) {
                case 'validacionUR':
                    $emme->setEmo_id(4);
                    $mme->select();
                    $emme->select();
                    $mme->conectar();

                    $mme->setEmo_id($emme->getEmo_id());
                    $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id =?, @bit_observacion="Envia  Folio a validación";');
                    $mme->consultar($params);
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
                    $emme->setEmo_id(6);
                    $men = 'Planeacion';
                    $emme->select();

                    $mme->conectar();
                    $mme->setEmo_id($emme->getEmo_id());

                    $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id =? ,@bit_observacion="Envia  Folio a Autorización UR";');
                    $mme->consultar($params);
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
                    $vobo = 2;
                    $notify = 'Folio ' . $mme->getMme_id() . ' revisado por SEFIN';
                    $emme->select();
                    $mme->conectar();
                    $mme->setEmo_id($emme->getEmo_id());
                    $mme->setVobo($vobo);
                    $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id =?, @bit_observacion="Envia  Folio a revisión - SEFIN";');
                    $mme->consultar($params);
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
                    $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id = ?,@bit_observacion="Envia  Folio a validación -SEFIN";');
                    $mme->consultar($params);
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

                    $emme->setEmo_id(15);
                    $ban = 0;
                    $emme->select();
                    $mme->select();
                    $storeProcedure = new sp_AutorizarModificacionMetaInversion();
                    $storeProcedure->setOmo_id($mme->getOmo_id());
                    $storeProcedure->setMme_gpo_id($mme->getMme_gpo_id());
                    $storeProcedure->setMme_ur_id($mme->getMme_ur_id());
                    $storeProcedure->setMme_id($mme->getMme_id());
                    $storeProcedure->setMme_Subsecretaria($mme->getSubsecretaria());
                    $storeProcedure->setSis_id('SCP');
                    $storeProcedure->setEjercicio(APP_EJERCICIO);
                    $storeProcedure->setBit_observacion('SE AUTORIZA FOLIO DE ADECUACIÓN DE METAS EXTERNA DESDE PANTALLA S_1_8');
                    $storeProcedure->conectar();
                    $params = [$this->ip, $_SESSION[_S]['usr_id']];
                    $storeProcedure->setSql('SET @usr_ip =INET_ATON(?), @usr_id =?;');
                    $storeProcedure->consultar($params);
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
                            mme_gpo_id:' . $this->prm->mme_gpo_id . ',
                            mme_ur_id:"' . $this->prm->mme_ur_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                            : '') . '
                        });
                    })
                ');
            } else {
                $this->mixScript('notify("' . utf8Encode('Error ' . $datos[1]['numero'] . '. ' . $datos[1]['mensaje']) . '","error",false);');
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

        $modal = '<div id="myModalRechazo" class="modal fade in" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalRechazoLabel" aria-hidden="false" style="display: block; padding-top: 100px;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color:#9d2449;">
                        <h4 class="modal-title" id="myModalRechazoLabel">Motivo de rechazo</h4>
                        <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="Mensaje">
                        ' . $this->ponTextArea($mensajeRechazo) . '
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success" id="btnGuardarMensaje" form="">Aceptar</button>
                        <button type="button" class="btn btn-outline-danger cerrar" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);

        $this->mixScript('
            $("#myModalRechazo").modal("show");
            $(".cerrar").click(function(){ $("#myModalRechazo").remove(); });
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
                    $("#myModalRechazo").modal("hide");
                }
            });
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
        $params = [$this->usr_id, $this->prm->observacion];
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $emme->setEmo_id(3);
                $emme->select();
                $mme->select();
                $mme->conectar();
                $mme->setEmo_id($emme->getEmo_id());
                $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id = ?, @bit_observacion = ?;');
                $mme->consultar($params);
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
                $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id =?, @bit_observacion=?;');
                $mme->consultar($params);
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
                    $emme->setEmo_id(7);
                } else {
                    $emme->setEmo_id(11);
                }
                $emme->select();
                $mme->setEmo_id($emme->getEmo_id());
                $mme->setVobo(3);

                if ($mensaje) {
                    $mme->conectar();
                    $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id =?,@bit_observacion=?;');
                    $mme->consultar($params);
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
                $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id =?,@bit_observacion=?;');
                $mme->consultar($params);
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
                $mme->setSql('SET @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @usr_id =?,@bit_observacion=?;');
                $mme->consultar($params);
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
        $btn_firmar->class = 'me-1';
        $btn_firmar->icon = 'fa-solid fa-floppy-disk me-2';
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
        $btn_imprimir->class = 'me-1';
        $btn_imprimir->icon = 'file-pdf me-2';
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
        $cbo_firma->search = true;
        $parametros = [];
        if ($this->subsec == 3) {
            $parametros = [$this->subsec, $_SESSION[_S]['usr_id']];
            $cbo_firma->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE ssc_id = ?
                AND gpo_id = 1 AND ur_id=15 AND usr_id = ?
                AND sts_id=1;';
        } elseif ($this->subsec == 2) {
            $parametros = [$this->subsec];
            $cbo_firma->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE ssc_id = ?
                AND nvj_id=3
                AND gpo_id = 1 AND ur_id=15 AND dir_id=6
                AND sts_id=1;';
        }
        $cbo_firma->params = $parametros;
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
        $firma2->search = true;

        if ($this->subsec == 3) {
            $parametros = [$this->subsec];
            $unidadSel = ($unidad == 0) ? ' uni_id in(16,18) ' : ' uni_id = ' . $unidad;
            $departamento = ($departamento == 0) ? $departamentos : ' AND dep_id = ' . $departamento;
            $firma2->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=5
                AND ssc_id=? AND dir_id=7
                AND uni_id in(16,18)
                AND sts_id=1';
            $firma2->params = $parametros;

            $params = [
                'subsec' => $this->subsec,
                'direc'  => $direccion,
            ];
            if ($departamento == 0) {
            } else {
                $departamento = ' AND dep_id = :dep';
                $params['dep'] = $departamento;
            }

            if ($unidad == 0) {
                $unidadSel = ' uni_id in(16,18) ';
            } else {
                $unidadSel = ' uni_id = :unidad';
                $params['unidad'] = $unidad;
            }
            $jefeDpto = ' SELECT usr_id
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id= 5
                AND ssc_id=:subsec AND dir_id=:direc
                AND ' . $unidadSel . ' ' . $departamento . '
                AND sts_id=1 LIMIT 1';

            $c = new AccesoDatos();
            $c->conectar();
            $c->setSql($jefeDpto);
            $c->consultar($params);
            $d = $c->extraer();
            $c->desconectar();
            if ($c->getNumeroRegistros() > 0) {
                $firma2->selecciona = $d['usr_id'];
            }
        } elseif ($this->subsec == 2) {
            $parametros = [$this->subsec];
            $firma2->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=4
                AND ssc_id=? AND dir_id=6
                AND sts_id=1';
            $firma2->params = $parametros;
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
        $firma3->search = true;
        if ($this->subsec == 3) {
            $parametros = [$subSecretaria, $direccion, $unidad];
            $firma3->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,"3" AS level
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 5 AND ssc_id = ?
                AND dir_id = ?
                AND uni_id = ?
                AND sts_id = 1 ';
            $firma3->params = $parametros;
        } elseif ($this->subsec == 2) {
            $parametros = [$this->subsec];
            $firma3->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"3" AS level
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 5
                AND ssc_id = ?
                AND dir_id = 6
                AND sts_id = 1 ';
            $firma3->params = $parametros;
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
        $firma4->search = true;
        $parametros = [$this->subsec];
        $firma4->sql = '
            SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"4" AS level
            FROM cat_Usuario
            INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
            INNER JOIN enc_JerarquiaSefin USING (ejs_id)
            WHERE gpo_id= 1 AND ur_id=15 AND ssc_id = ?
            AND dir_id=6 AND nvj_id=6
            AND sts_id=1;
        ';
        $firma4->params = $parametros;
        $scri = '';
        $firma4 = $this->ponCombo($firma4);

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
        $html .= '
            <div id="infofirmas"  style="align:center">
                <center><legend>Firmas del Reporte</legend> ( Seleccione un usuario de firma)
                    <fieldset class="col-md-12 col-xs-12">
                        <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma1">
                            ...
                        </fieldset>
                        <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma2">
                            ...
                        </fieldset>
                        <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma3">
                            ...
                        </fieldset>
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <fieldset style="align:center" class="col-md-6 col-xs-6" id="firma4">
                            ...
                        </fieldset>
                    </fieldset>
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
                $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesMetasInversion.php");
                $("#omo_id").val($(this).attr("omo_id"));
                $("#mme_gpo_id").val($(this).attr("mme_gpo_id"));
                $("#mme_ur_id").val($(this).attr("mme_ur_id"));
                $("#mme_id").val($(this).attr("mme_id"));
                $("#mme_tipoMovimiento").val($(this).attr("mme_tipoMovimiento"));
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
        $params = [$this->prm->usr];
        $sql = 'SELECT usr_titulo, usr_puesto, usr_nombre,  usr_paterno, usr_materno
            FROM cat_Usuario
            INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
            WHERE usr_id = ?';
        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar($params);
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
                    $params = [
                        'omo'    => $this->prm->omo_id,
                        'gpo'    => $this->prm->mme_gpo_id,
                        'ur'     => $this->prm->mme_ur_id,
                        'id'     => $this->prm->mme_id,
                        'cargo1' => $this->prm->cargo1,
                        'firma1' => $this->prm->firma1,
                        'cargo2' => $this->prm->cargo2,
                        'firma2' => $this->prm->firma2,
                        'cargo3' => $this->prm->cargo3,
                        'firma3' => $this->prm->firma3,
                        'cargo4' => $this->prm->cargo4,
                        'firma4' => $this->prm->firma4,
                        'usr'    => $_SESSION[_S]['usr_id'],
                    ];
                    $sql = "INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                        mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                        mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                        mme_firma6,mme_cargo7,mme_firma7,usr_id)
                        VALUES (:omo, :gpo, :ur, :id, '', '', '', '', '', '',
                        :cargo1, :firma1, :cargo2, :firma2, :cargo3, :firma3, :cargo4, :firma4, :usr)
                        ON DUPLICATE KEY UPDATE
                        mme_cargo4= :cargo1,
                        mme_firma4= :firma1,
                        mme_cargo5= :cargo2,
                        mme_firma5= :firma2,
                        mme_cargo6= :cargo3,
                        mme_firma6= :firma3,
                        mme_cargo7= :cargo4,
                        mme_firma7= :firma4;";

                    $firmasReportes = new AccesoDatos();
                    $firmasReportes->conectar();
                    $firmasReportes->setSql($sql);
                    $firmasReportes->consultar($params);
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
$planeacion = isset($_SESSION[_S]['ssc_id']) && $_SESSION[_S]['ssc_id'] == 2 && $_SESSION[_S]['nvj_id']
    >= 2;
$ur = isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] <= 2;

if (isset($_SESSION[_S]['prm']['S']['1']['8']) && ($ur || $planeacion)) {
    new ControladorRegistroModificacionMetasInversion();
} else {
    WebResponse::sinPermisos();
}
