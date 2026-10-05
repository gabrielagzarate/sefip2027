<?php

require_once dirname(__FILE__) . '/../../lib/scp/vistaAdecuacionMetasMir.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scp/ModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraModificacionMetaMir.php';
require_once dirname(__FILE__) . '/../../ent/scp/DetalleModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/EstadoModificacion.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuacionMetasMir.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../lib/scp/dibujaEncuadreProgramatico.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaComponente.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoModificacionMetaComponente.php';
require_once dirname(__FILE__) . '/../../ent/scp/OrigenOperacion.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';

class ControladorRegistroModificacionMetasMir extends WebResponse
{
    private $trimestreCierre; #INDICA EL TRIMESTRE EN QUE SE REGISTRA
    private $condiciontrimestre;
    public vistaAdecuacionMetasMir $vista;
    public modelAdecuacionMetasMir $model;
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
    public $ur_id;
    public $perfil;
    public $nivel;
    public $subsec;
    public $ip;
    public $usr_id;
    public $nvj;
    public $lectura;

    public function __construct()
    {
        //Manda a llamar constructor base
        parent::__construct();
        $this->pantalla = 'scp/adecuaciones/ControladorRegistroModificacionMetasMir.php';
        $this->vista = new vistaAdecuacionMetasMir();
        $this->model = new modelAdecuacionMetasMir();
        //Verifica el tipo de usuario
        $this->ur = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id']
            == 2 &&
            isset($_SESSION[_S]['prm']['S']['1']['9']['112']) ? true : false);
        $this->ue = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id']
            == 1 ? true : false);
        $this->planeacion = (isset($_SESSION[_S]['ssc_id']) && $_SESSION[_S]['ssc_id']
            == 2 ? true : false);

        //Verificamos los permisos de escritura
        $this->escritura = (isset($_SESSION[_S]['prm']['S']['1']['9']['112']) ||
            (isset($_SESSION[_S]['prm']['S']['1']['9']['108']) && isset($this->prm->nivelRevision)
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
            case 'tablaMetas':
                $this->tablaMetas();
                break;
            case 'addMmeFolio':
                $this->addMmeFolio();
                break; //Metodo para agregar nuevo folio de la Dependencia, R.H Y SEFIN
            case 'abcFolios':
                $this->abcFolios();
                break; //Metodo que guarda la informacion del folio de origen A,B
            case 'muestraMetas':
                $this->muestraMetas();
                break;
            case 'cargaSbprg':
                $this->cargaSbprg();
                break;
            case 'abcMetas':
                $this->abcMetas();
                break; //Metodo que guarda la informacion
            case 'tablaHistorialFolio':
                $this->tablaHistorialFolio();
                break; // Tabla de historial del folios
            case 'modificarMetas':
                $this->modificarMetas();
                break;
            case 'borrarDatosModificados':
                $this->borrarDatosModificados();
                break;
            case 'verificarFolio':
                $this->verificarFolio();
                break;
            case 'revisarFolio':
                $this->revisarFolio();
                break;
            case 'tablaFoliosRead':
                $this->tablaFoliosRead();
                break; //Metodo para visualizar los folios en estado de validacion y autorizacion UR y validacion y autorizacion SEFIN
            case 'llenaTablaFoliosRead':
                $this->llenaTablaFoliosRead();
                break;
            case 'validacionFolio':
                $this->validacionFolio();
                break;
            case 'addMensajeRechazo':
                $this->addMensajeRechazo();
                break;
            case 'rechazarFolio':
                $this->rechazarFolio();
                break;
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
            $script = 'ify("No se establecieron los valores correctamente","error");';
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
        $pantalla = 'scp/adecuaciones/ControladorRegistroModificacionMetasMir.php';
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
            $td->casos->verdadero->color = 'verde';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->omo_id = 'origenModificacion';
            $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
            $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
            $td->casos->verdadero->attr->mme_id = 'mme_id';
            $td->casos->verdadero->class = 'btn btn-outline-success revisarFolio btn-xs';
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
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
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
                $("#reporte").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesMetasMir.php");
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
        $btnNuevoMovimiento = '';
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
                metodo:"tablaMetas",
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
        $tienemovimientos = $this->model->getEliminaFolio($mme);
        if (!$esUR) {
            //EVALUAMOS EL NIVEL DE REVISION DE SEFIN
            switch ($this->nivelRevision) {
                case 'alta':
                    $btnValidar = $btnRechazar = '';
                    //Verifica si el origen es de dependencia, sefin no puede eliminar o agregar movimientos
                    if ($mme->getOmo_id() == 'A') {
                        $btnNuevoMovimiento = '';
                    }
                    break;
                case 'revisionSEFIN':
                    if ($mme->getOmo_id() == 'E') {
                        $btnRechazar = ''; // no se puede rechazar el folio al estado 9 si el folio nace de SEFIN
                    } else {
                        $btnNuevoMovimiento = ''; // no se puede agregar nuevos movimientos al folio cuando viene de ur
                    }
                    break;
                default:
                    $btnValidar = $btnRechazar = '';
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
        $this->agregaTools($btnRegresar . ' ' . $btnNuevoMovimiento . ' ' . $btnValidar . ' ' . $btnRechazar);
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
                    metodo:addMmeModificacion,
                    omo_id:$(this).attr("omo_id"),
                    mme_id:$(this).attr("mme_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id")
                    ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });
            $(".addModModificacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"modificarMetas",
                    nim_id:3,
                    ind_id:  $(this).attr("ind_id"),
                    prg_id: $(this).attr("prg_id"),
                    sprs_id: $(this).attr("sprs_id"),
                    lectura:1,
                    pdf_id:$(this).attr("pdf_id"),
                    eri_id:$(this).attr("eri_id"),
                    trim:$(this).attr("tri_id"),
                    lectura:1,
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id")
                    ' . (($this->nivelRevision != '') ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $(".deleteMeta").click(function() {
                var $this = $(this);
                alertify.confirm("ELIMINAR META", "¿Realmente desea eliminar la meta <h5 style=\"display:inline\"></h5> ?",
                    function() {
                        comunica("' . $this->pantalla . '", {
                            metodo: "borrarDatosModificados",
                            abc: "b",
                            ' . (isset($this->prm->omo_id) ? ' omo_id: "' . $this->prm->omo_id . '",' : '') . '
                            ' . (isset($this->prm->mme_id) ? 'mme_id: ' . $this->prm->mme_id . ' ' : '') . '
                            ' . (isset($this->prm->mme_gpo_id) && !empty($this->prm->mme_gpo_id) ? ',mme_gpo_id: "' . $this->prm->mme_gpo_id . '"' : '') . '
                            ' . (isset($this->prm->mme_ur_id) && !empty($this->prm->mme_ur_id) ? ',mme_ur_id: "' . $this->prm->mme_ur_id . '"' : '') . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision: "' . $this->prm->nivelRevision . '"' : '') . '
                            ,prgs_id: $this.attr("prg_id"),
                            sprs_id: $this.attr("sprs_id"),
                            epfi_id: $this.attr("eri_id")
                        });
                    },
                    function() {}
                ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
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
    /* METODO PARA AGREGAR NUEVO FOLIO DE LA DEPENDENCIA */

    private function tablaMetas()
    {
        //Metodo que agrega previsiones para el caso de la compensacion de folios en la fase
        //de revision y agrega nuevos movimientos al folio de origen A,B,C,D.
        /*se instancia el folio de modificacion para tener la informacion disponible*/
        $script = 'bloquearClasAdmin();';
        $this->pintaScript($script);
        $mod = new ModificacionMeta($this->prm);
        $meta = new MetaComponente($this->prm);
        $historicoMeta = new HistoricoModificacionMetaComponente($this->prm);
        $subprg = $this->vista->vwCombo('subprograma', $mod->getMme_id());

        $this->conectar();
        $this->setSql('
            SELECT gpo_id, ur_id, MAX(tri_id) AS trimestre_abierto, etr_id
            FROM mir_TrimestreUr
            WHERE gpo_id = ?
                AND ur_id = ?
                AND nim_id IN (1, 2, 3) 
                AND etr_id = "A"
            GROUP BY gpo_id, ur_id;
        ');
        $this->consultar([$mod->getMme_gpo_id(),$mod->getMme_ur_id()]);
        $this->desconectar();
        $tri_id = $this->extraer();

        if (isset($tri_id['trimestre_abierto']) && $tri_id['trimestre_abierto'] == 1) {
            $this->condiciontrimestre = 'IN (0,' . $tri_id['trimestre_abierto'] . ')';
            # code...
        } elseif (isset($tri_id['trimestre_abierto']) && $tri_id['trimestre_abierto'] == 2) {
            $this->condiciontrimestre = 'IN (0,1,' . $tri_id['trimestre_abierto'] . ')';
            # code...
        } elseif (isset($tri_id['trimestre_abierto']) && $tri_id['trimestre_abierto'] == 3) {
            $this->condiciontrimestre = 'IN (0,1,2,' . $tri_id['trimestre_abierto'] . ')';
            # code...
        } elseif (isset($tri_id['trimestre_abierto']) && $tri_id['trimestre_abierto'] == 4) {
            $this->condiciontrimestre = 'IN (0,1,2,3,' . $tri_id['trimestre_abierto'] . ')';
        } else {
            $tri_id['trimestre_abierto'] = 1;
        }

        $hoy = date('Y-m-d H:i:s');
        if ($hoy < APP_EJERCICIO . '-01-01') {
            # code...
            $tri_id['trimestre_abierto'] = 0;
        }

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
        $folio->value = $mod->getMme_id();
        $folio->required = true;
        $folio->disabled = true;

        $modalidad = new stdClass();
        $modalidad->id = 'modalidadM';
        $modalidad->enDiv = true;
        $modalidad->value = 'EXTERNAS';
        $modalidad->required = true;
        $modalidad->disabled = true;

        $sqlprg = '
            SELECT prg_id,prg_nombre FROM cat_Programa WHERE gpo_id=' . $mod->getMme_gpo_id() . ' AND ur_id=' . $mod->getMme_ur_id() . '
        ';

        $prg = new stdClass();
        $prg->id = 'prg';
        $prg->sql = $sqlprg;
        $prg->soloText = true;
        $prg->comoAtr = 'prg_id,prg_nombre';
        $prg->camposVal = 'prg_id';
        $prg->camposText = 'prg_id,prg_nombre';
        $prg->search = true;
        $prg->required = true;
        $prg->change = '
            if($("#prg").val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo: "cargaSbprg",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mme_id:' . $mod->getMme_id() . ',
                    gpo_id: "' . $mod->getMme_gpo_id() . '",  // Asegúrate de que esto se evalúe correctamente
                    ur_id: "' . $mod->getMme_ur_id() . '", // Asegúrate de que esto se evalúe correctamente
                    prg_id: $("#prg :selected").attr("prg_id"),
                    condiciontrimestre: "' . $this->condiciontrimestre . '",
                    tri_id: ' . $tri_id['trimestre_abierto'] . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    ' . (isset($this->prm->sprs) ? ',sprs_id:"' . $this->prm->sprs . '"' : '') . '
                    ' . (isset($this->prm->ind) ? ',ind_id:"' . $this->prm->ind . '"' : '') . '
                });
            }
        ';

        $subprograma = new stdClass();
        $subprograma->id = 'subprg';
        $subprograma->enDiv = true;
        $subprograma->required = true;
        $subprograma->disabled = true;

        $form = new stdClass();
        $form->id = 'ModificacionesSefin';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcModificacion';
        $form->datosExtra = new stdClass();

        $form = new form($form);

        $html = '
            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-3">
                    <legend>Folio</legend>
                    ' . $this->ponInput($folio) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    <legend>Modalidad</legend>
                    ' . $this->ponInput($modalidad) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Programa</legend>
                    <div class="row col-md-12 col-xs-12">
                        ' . $this->ponCombo($prg) . '
                    </div>
                </fieldset>
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Componente</legend>
                    <div id="subprg" class="row col-md-12 col-xs-12">
                    </div>
                </fieldset>
            </div>
        ';
        $form->mixHtml($html);
        $this->mixHtml($form->ponForm());
        $this->agregaTools($this->ponBoton($btnRegresarTablaModificacion));

        $script .= '

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaModificacion").click(); e.stopPropagation(); });
            $("#btnRegresarTablaModificacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mme_gpo_id:"' . $mod->getMme_gpo_id() . '",
                    mme_ur_id:"' . $mod->getMme_ur_id() . '",
                    mme_id:' . $mod->getMme_id() . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
                return false;
            });

            $("#btnEliminarModificacion").click(function(){
                alertify.confirm("ELIMINAR META", String.fromCharCode(191)+"Realmente desea eliminar el movimiento ?",
                    function() {
                        var objMod = {
                            metodo:"abcModificacion",
                            abc: "b"
                        }
                        comunica("' . $this->pantalla . '", objMod );
                    }.undefined
                ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
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

        ';

        $this->mixScript($script);
    } // Fin addModPrevision

    private function cargaSbprg()
    {
        $this->setContenedor('subprg');
        $htmlfolio = '';
        $comboSubprograma = '';

        $this->conectar();
        $this->setSql('
            SELECT m.omo_id, m.emo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, prgs_id, sprs_id, d.epfi_id, tri_id,ind_id, CONCAT(prgs_id," - ",prg_nombre) programa,
            CONCAT(sprs_id," - ",sprs_nombre) subprograma, CONCAT(ind_id," - ",ind_nombre) indicador
            FROM ' . BD_CTRL . '.scp_ModificacionMeta m
            INNER JOIN scp_DetalleModificacionMetaComponente d USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN ' . BD_CTRL . '.enc_ProductoFinalIndicador pi USING (epfi_id)
            INNER JOIN ' . BD_CTRL . '.cat_Programa USING(gpo_id, ur_id)
            INNER JOIN ' . BD_CTRL . '.cat_Subprogramas USING (prgs_id, sprs_id)
            INNER JOIN ' . BD_CTRL . '.cat_Indicador USING(nim_id, ind_id)
            WHERE omo_id= ?
            AND mme_gpo_id= ?
            AND mme_ur_id= ?
            AND prgs_id = prgs_id
            AND ind_id = ind_id
            GROUP BY prgs_id, sprs_id;
        ');
        $params = [
            $this->prm->omo_id,
            $this->prm->gpo_id,
            $this->prm->ur_id,
        ];
        $this->consultar($params);
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            while ($registro = $this->extraer()) {
                $sprs_ids[] = $registro['sprs_id']; // Agrega el valor de sprs_id al array
                $indi[] = $registro['ind_id'];
            }
            $this->prm->sprs = implode(',', $sprs_ids);
            $this->prm->ind = implode(',', $indi);
        }

        $comboSubprograma = $this->vista->vwSubprograma($this->prm, '');

        $comboSubprograma->change = '
            if($("#prg").val() != "" && $("#prg").val() != "" && $("#subprg :selected").val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo: "muestraMetas",
                    nim_id:3,
                    ur: "' . $this->prm->gpo_id . $this->prm->ur_id . '", 
                    ind_id:  $("#subprg :selected").attr("ind_id"),
                    prg_id: ' . $this->prm->prg_id . ',
                    sbprg_id: $("#subprg :selected").attr("com_id"),
                    lectura:1,
                    pdf_id:$("#subprg :selected").attr("pdf_id"),
                    eri_id:$("#subprg :selected").attr("epfi_id"),
                    trim:' . $this->prm->tri_id . ',
                    omo_id:"' . $this->prm->omo_id . '",
                    mme_id:' . $this->prm->mme_id . ',
                    gpo_id: "' . $this->prm->gpo_id . '", 
                    ur_id: "' . $this->prm->ur_id . '",
                    abc:"a"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            }
        ';

        $comboSubprograma = is_object($comboSubprograma) ? $this->ponCombo($comboSubprograma) : '';
        $htmlfolio .= '
            <fieldset class="col-md-4 col-xs-4 ms-3">
                ' . $comboSubprograma .
            '</fieldset>
        ';

        $this->mixHtml('
            <div id="tf" class="row">
                ' . $htmlfolio . '
            </div>
        ');

        $this->mixHtml('
            <div id="div-metas" class="row">
            </div>
        ');
    }

    private function muestraMetas()
    {
        $numRegistros = $this->consultaMetaModificada($this->prm->prg_id, $this->prm->sbprg_id, $this->prm->eri_id, $this->prm->trim);
        if ($numRegistros > 0) {
            # code...
            $this->mixScript('
                notify("La meta ya está siendo modificada.","error");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:"' . $this->prm->omo_id . '",
                    mme_gpo_id:' . $this->prm->gpo_id . ',
                    mme_ur_id:' . $this->prm->ur_id . ',
                    mme_id:' . $this->prm->mme_id . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            ');
        } else {

            $this->setContenedor('div-metas');
            $this->prm->ind_id = isset($this->prm->ind_id) ? $this->prm->ind_id : '';

            $btnGuardarM = $this->vista->vwBotones('guardarMeta');
            $btnGuardarM->form = 'ModificacionesSefin';

            $btnRegresarTablaModificacion = new stdClass();
            $btnRegresarTablaModificacion->id = 'btnRegresarTablaModificacion';
            $btnRegresarTablaModificacion->icon = 'fa-solid fa-circle-left me-2';
            $btnRegresarTablaModificacion->class = 'btn-outline-secondary regresar me-1';
            $btnRegresarTablaModificacion->text = 'Regresar';
            $btnRegresarTablaModificacion->value = 'Regresar';
            $btnRegresarTablaModificacion->color = 'gris';

            if ($this->prm->ind_id != '') {
                $this->conectar();
                //La FRECUENCIA DE Medición A NIVEL COMPONENTE SIEMPRE SERÁ SEMESTRAL AUNQUE EN EL CATALOGO TENGA OTRA INFORMACIÓN.
                // Y el tipo de valor siempre será sumable.
                $this->setSql(
                    '
                    SELECT "S" AS tiv_id, ind_nombre, ind_definicion, ind_nombreMIR,
                    2 AS frm_id, umi_id, nim_id, ind_definicionMIR,
                    dim_id,dim_nombre, frm_id,frm_nombre, tpi_id,tpi_nombre,
                    ses_id,ses_nombre,umv_id,umv_nombre, tiv_id AS tiv,tiv_nombre,
                    ind_formulaCalculo
                    FROM cat_Indicador
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    INNER JOIN cat_UnidadMedidaVariable USING(umv_id)
                    INNER JOIN cat_Dimension USING (dim_id)
                    INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
                    INNER JOIN cat_TipoIndicador USING(tpi_id)
                    INNER JOIN cat_SentidoEsperado USING(ses_id)
                    INNER JOIN cat_TipoValor USING(tiv_id)
                    WHERE nim_id = 3
                    AND ind_id = ?;'
                );

                $this->consultar([$this->prm->ind_id]);
                $datos = $this->extraer();
                $tiv = $datos['tiv_id'];
                $tiv_nombre = $datos['tiv_nombre'];
                $nom_ind = $datos['ind_nombre'];
                $nom_MIR = $datos['ind_nombreMIR'];
                $def_ind = $datos['ind_definicion'];
                $frm = $datos['frm_id'];
                $umi = $datos['umi_id'];
                $umv = $datos['umv_id'];
                $unom = $datos['umv_nombre'];
                $nivelInd = $datos['nim_id'];
                $calculo = $datos['ind_formulaCalculo'];
                $this->desconectar();

                $indsql = '
                    SELECT COUNT(DISTINCT nim_id,ind_id) cuantos, MAX(ind_id) AS ind_id, MAX(epfi_id) AS eri_id,MAX(umi_id) AS umi_id, pdf_id,tiv_nombre
                    FROM enc_ProductoFinalIndicador enc
                    JOIN cat_Subprograma USING(pdf_id)
                    INNER JOIN cat_Indicador USING(ind_id,nim_id)
                    INNER JOIN cat_TipoValor USING(tiv_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    WHERE enc.sts_id = 1 AND nim_id = 3 AND pdf_id = ?
                    AND prg_id = ?
                    AND gpo_id = ?
                    AND ur_id = ?
                    HAVING cuantos != 0;
                ';
                $params = [
                    $this->prm->pdf_id,
                    $this->prm->prg_id,
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                ];
                $indicador = new AccesoDatos();
                $indicador->conectar();
                $indicador->setSql($indsql);
                $indicador->consultar($params);
                $indicador->desconectar();

                if ($this->getNumeroRegistros() > 0) {
                    $cuantos = $indicador->extraer();
                    $ind_id = $cuantos['ind_id'];
                    $eri_id = $cuantos['eri_id'];
                    $umi_id = $cuantos['umi_id'];
                    $pdf_id = $cuantos['pdf_id'];
                    $cuantos = $cuantos['cuantos'] * 1;
                }

                $this->prm->prgs_id = $this->prm->prg_id;
                $this->prm->sprs_id = $this->prm->sbprg_id;
                $this->prm->epfi_id = $this->prm->eri_id;
                $this->prm->tri_id = $this->prm->trim;

                //INICIA MUESTRA MODAL
                $meta = new MetaComponente($this->prm);
                $historicoMeta = new HistoricoModificacionMetaComponente($this->prm);

                $descripcion = new stdClass();
                $descripcion->id = 'descripcion_mod';
                $descripcion->enDiv = true;
                $descripcion->maxlength = 500;
                $descripcion->width = '300px';
                $descripcion->height = '100px';
                $descripcion->disabled = true;

                $descripcion->texto = $nom_MIR;

                $variable = new stdClass();
                $variable->id = 'variable_mod';
                $variable->enDiv = true;
                $variable->maxlength = 100;
                $variable->width = '300px';
                $variable->height = '100px';
                $variable->disabled = true;
                $variable->texto = $nom_ind;

                $metodocal = new stdClass();
                $metodocal->id = 'calculo_mod';
                $metodocal->enDiv = true;
                $metodocal->maxlength = 100;
                $metodocal->width = '300px';
                $metodocal->height = '100px';
                $metodocal->disabled = true;
                $metodocal->texto = $calculo;

                $medida = new stdClass();
                $medida->id = 'umi_mod';
                $medida->enDiv = true;
                $medida->maxlength = 100;
                $medida->width = '300px';
                $medida->height = '100px';
                $medida->disabled = true;
                $medida->texto = $umv . '-' . $unom;

                $tipo = new stdClass();
                $tipo->id = 'tiv_nombre';
                $tipo->enDiv = true;
                $tipo->maxlength = 100;
                $tipo->width = '300px';
                $tipo->height = '100px';
                $tipo->disabled = true;
                $tipo->texto = $tiv_nombre;

                //***********************LINEA BASE***************************************//
                $mcoLb = new stdClass();
                $mcoLb->id = 'lb';
                $mcoLb->readonly = true;
                $mcoLb->enDiv = true;
                $mcoLb->value = !empty($meta->getLb()) ? $meta->getLb() : APP_EJERCICIO;
                $mcoLb->maxlength = 4;
                $mcoLb->formato = 'entero';

                $mcoLbValor = new stdClass();
                $mcoLbValor->readonly = true;
                $mcoLbValor->id = 'lbValor';
                $mcoLbValor->enDiv = true;
                $mcoLbValor->value = $meta->getLbValor();
                $mcoLbValor->formato = 'numero';

                $mcoLbIndicador = new stdClass();
                $mcoLbIndicador->readonly = true;
                $mcoLbIndicador->id = 'lbIndicador';
                $mcoLbIndicador->enDiv = true;
                $mcoLbIndicador->value = $meta->getLbIndicador();
                $mcoLbIndicador->formato = 'numero';

                //***********************AÑO ANTERIOR***************************************//
                $mcoAnterior = new stdClass();
                $mcoAnterior->id = 'anterior';
                $mcoAnterior->enDiv = true;
                $mcoAnterior->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO;
                $mcoAnterior->maxlength = 4;
                $mcoAnterior->formato = 'entero';

                $mcoAnteriorValor = new stdClass();
                $mcoAnteriorValor->id = 'anteriorValor';
                $mcoAnteriorValor->enDiv = true;
                $mcoAnteriorValor->value = $historicoMeta->getAnteriorValor();
                $mcoAnteriorValor->formato = 'numero';

                $mcoOriginal = new stdClass();
                $mcoOriginal->id = 'original';
                $mcoOriginal->enDiv = true;
                $mcoOriginal->value = $historicoMeta->getModificado();
                $mcoOriginal->formato = 'numero';

                $mcoAnteriorIndicador = new stdClass();
                $mcoAnteriorIndicador->id = 'anteriorIndicador';
                $mcoAnteriorIndicador->enDiv = true;
                $mcoAnteriorIndicador->value = $historicoMeta->getAnteriorIndicador();
                $mcoAnteriorIndicador->formato = 'numero';

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
                $trimestre1Cal1->id = 'num_trimestre1';
                $trimestre1Cal1->enDiv = true;
                $trimestre1Cal1->label = '1° Trimestre';
                $trimestre1Cal1->formato = 'numero';

                $trimestre2Cal1 = new stdClass();
                $trimestre2Cal1->id = 'num_trimestre2';
                $trimestre2Cal1->enDiv = true;
                $trimestre2Cal1->label = '2° Trimestre';
                $trimestre2Cal1->formato = 'numero';

                $trimestre3Cal1 = new stdClass();
                $trimestre3Cal1->id = 'num_trimestre3';
                $trimestre3Cal1->enDiv = true;
                $trimestre3Cal1->label = '3° Trimestre';
                $trimestre3Cal1->formato = 'numero';

                $trimestre4Cal1 = new stdClass();
                $trimestre4Cal1->id = 'num_trimestre4';
                $trimestre4Cal1->enDiv = true;
                $trimestre4Cal1->label = '4° Trimestre';
                $trimestre4Cal1->formato = 'numero';

                $totalCal1 = new stdClass();
                $totalCal1->disabled = true;
                $totalCal1->id = 'num_total';
                $totalCal1->enDiv = true;
                $totalCal1->label = 'Total';

                //****************************CALENDARIO 2*************************************//
                $trimestre1Cal2 = new stdClass();
                $trimestre1Cal2->id = 'den_trimestre1';
                $trimestre1Cal2->enDiv = true;
                $trimestre1Cal2->label = '1° Trimestre';
                $trimestre1Cal2->formato = 'numero';

                $trimestre2Cal2 = new stdClass();
                $trimestre2Cal2->id = 'den_trimestre2';
                $trimestre2Cal2->enDiv = true;
                $trimestre2Cal2->label = '2° Trimestre';
                $trimestre2Cal2->formato = 'numero';

                $trimestre3Cal2 = new stdClass();
                $trimestre3Cal2->id = 'den_trimestre3';
                $trimestre3Cal2->enDiv = true;
                $trimestre3Cal2->label = '3° Trimestre';
                $trimestre3Cal2->formato = 'numero';

                $trimestre4Cal2 = new stdClass();
                $trimestre4Cal2->id = 'den_trimestre4';
                $trimestre4Cal2->enDiv = true;
                $trimestre4Cal2->label = '4° Trimestre';
                $trimestre4Cal2->formato = 'numero';

                $totalCal2 = new stdClass();
                $totalCal2->disabled = true;
                $totalCal2->id = 'den_total';
                $totalCal2->enDiv = true;
                $totalCal2->label = 'Total';
                $totalCal2->formato = 'numero';

                //********************************LINEA BASE Numerador Denominador****************************************************//
                $mcoNumerador = new stdClass();
                $mcoNumerador->readonly = true;
                $mcoNumerador->id = 'numerador';
                $mcoNumerador->enDiv = true;
                $mcoNumerador->label = 'Numerador';
                $mcoNumerador->formato = 'numero';

                $mcoDenominador = new stdClass();
                $mcoDenominador->readonly = true;
                $mcoDenominador->id = 'denominador';
                $mcoDenominador->enDiv = true;
                $mcoDenominador->label = 'Denominador';
                $mcoDenominador->formato = 'numero';

                //********************************VALOR DESEADO Y PROGRAMADO (RAZON Y TASA) *********************//
                $mcoDeseadoNumerador = new stdClass();
                $mcoDeseadoNumerador->id = 'deseadoNumerador';
                $mcoDeseadoNumerador->enDiv = true;
                $mcoDeseadoNumerador->label = 'Numerador';
                $mcoDeseadoNumerador->formato = 'numero';
                $mcoDeseadoNumerador->disabled = true;

                $mcoDeseadoDenominador = new stdClass();
                $mcoDeseadoDenominador->id = 'deseadoDenominador';
                $mcoDeseadoDenominador->enDiv = true;
                $mcoDeseadoDenominador->label = 'Denominador';
                $mcoDeseadoDenominador->formato = 'numero';
                $mcoDeseadoDenominador->disabled = true;

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
                $resParcialTo->id = 'totalParcial';
                $resParcialTo->enDiv = true;
                $resParcialTo->label = 'Total';
                $resParcialTo->disabled = true;
                //********************************FIN RESULTADO PARCIAL *********************//

                $mcoT0 = new stdClass();
                $mcoT0->id = 't0';
                $mcoT0->enDiv = true;
                $mcoT0->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO;
                $mcoT0->maxlength = 4;
                $mcoT0->formato = 'entero';
                $mcoT0->label = 'AÑO T0';
                $mcoT0->disabled = true;

                $mcoT0Valor = new stdClass();
                $mcoT0Valor->required = true;
                $mcoT0Valor->id = 't0Valor';
                $mcoT0Valor->enDiv = true;
                $mcoT0Valor->value = $meta->getAnteriorValor();
                $mcoT0Valor->formato = 'numero';
                $mcoT0Valor->label = 'VALOR AÑO T0';
                $mcoT0Valor->disabled = true;

                $mcoT1 = new stdClass();
                $mcoT1->id = 't1';
                $mcoT1->enDiv = true;
                $mcoT1->value = !empty($meta->getAnio()) ? $meta->getAnio() : APP_EJERCICIO;
                $mcoT1->maxlength = 4;
                $mcoT1->formato = 'entero';
                $mcoT1->label = 'AÑO T1';
                $mcoT1->disabled = true;

                $mcoT1Valor = new stdClass();
                $mcoT1Valor->id = 't1Valor';
                $mcoT1Valor->enDiv = true;
                $mcoT1Valor->formato = 'numero';
                $mcoT1Valor->label = 'VALOR AÑO T1';
                $mcoT1Valor->disabled = true;

                $hoy = date('Y-m-d H:i:s');
                if ($hoy > APP_EJERCICIO . '-06-30 00:00:00') {
                    $trimestre2->readonly = true;
                    $trimestre2Cal1->readonly = true;
                    $trimestre2Cal2->readonly = true;
                } else {
                    $trimestre4->readonly = true;
                    $trimestre4Cal1->readonly = true;
                    $trimestre4Cal2->readonly = true;
                }

                //*********************************************************************************************//
                //semestral
                $trimestre1->disabled = true;
                $trimestre2->value = $historicoMeta->getModificadoT2();
                $trimestre3->disabled = true;
                $trimestre4->value = $historicoMeta->getModificadoT4();

                $trimestre1Cal1->disabled = true;
                $trimestre2Cal1->value = $historicoMeta->getNumeradorT2();
                $trimestre3Cal1->disabled = true;
                $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

                $trimestre1Cal2->disabled = true;
                $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();

                $trimestre3Cal2->disabled = true;
                $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();

                if ($datos['tiv'] == 1) {
                    $mcoDeseadoNumerador->value = number_format($trimestre2Cal1->value + $trimestre4Cal1->value, 4);
                    $mcoDeseadoDenominador->value = number_format($trimestre2Cal2->value + $trimestre4Cal2->value, 4);
                    $mcoT1Valor->value = number_format($trimestre2Cal1->value + $trimestre4Cal1->value, 4);
                } else {
                    $mcoDeseadoNumerador->value = number_format($trimestre4Cal1->value, 4);
                    $mcoDeseadoDenominador->value = number_format($trimestre4Cal2->value, 4);
                    $mcoT1Valor->value = number_format($trimestre4Cal1->value, 4);
                }

                $lb = '';
                $lbValor = '';
                $lbIndicador = '';

                $anterior = '';
                $anteriorValor = '';
                $anteriorIndicador = '';

                $original = '';

                $numerador = '';
                $denominador = '';
                $deseadoNumerador = '';
                $deseadoDenominador = '';
                $t0 = '';
                $t0Valor = '';
                $t1 = '';
                $t1Valor = '';

                $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
                $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';
                $trim1 = $trim2 = $trim3 = $trim4 = '';
                $legendCal1 = $legendCal2 = '';

                switch ($umi) {
                    case 2:
                    case 3:
                    case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                        if ($trimestre2Cal1->value != null && $trimestre4Cal1->value != null &&
                            $trimestre2Cal2->value != null && $trimestre4Cal2->value != null) {
                            if ($umi == 2) {
                                $resParcialT1->value = '0';
                                $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format((($trimestre2Cal1->value / $trimestre2Cal2->value) * 100), 4) . '%' : '0%');
                                $resParcialT3->value = '0';
                                $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format((($trimestre4Cal1->value / $trimestre4Cal2->value) * 100), 4) . '%' : '0%');
                            } else {
                                $resParcialT1->value = '0';
                                $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format(($trimestre2Cal1->value / $trimestre2Cal2->value), 4) : '0');
                                $resParcialT3->value = '0';
                                $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format(($trimestre4Cal1->value / $trimestre4Cal2->value), 4) : '0');
                            }
                            if ($datos['tiv'] == 1) {
                                if ($umi == 2) {
                                    $totalCal1->value = number_format(($trimestre2Cal1->value + $trimestre4Cal1->value), 4);
                                    $totalCal2->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                                    $resParcialTo->value = ($mcoOriginal->value) . '%';
                                } else {
                                    $resParcialTo->value = ($mcoOriginal->value);
                                    $totalCal1->value = number_format(($trimestre2Cal1->value + $trimestre4Cal1->value), 4);
                                    $totalCal2->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                                }
                            } else {
                                if ($umi == 2) {
                                    $resParcialTo->value = $resParcialT4->value . '%';
                                    $totalCal1->value = $trimestre4Cal1->value;
                                    $totalCal2->value = $trimestre4Cal2->value;
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
                        $legendp = 'LÍNEA BASE';
                        $legend = 'VALOR DESEADO';
                        $legendCal1 = '
                            <tr>
                                <td colspan=5><legend><small>Numerador</small></legend></td>
                            </tr>
                        ';
                        $legendCal2 = '
                            <tr>
                                <td colspan=5><legend><small>Denominador</small></legend></td>
                            </tr>
                        ';
                        $legendCalT = '
                            <tr>
                                <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                            </tr>
                        ';
                        $mcoLb->value = !empty($meta->getLb()) ? $meta->getLb() : APP_EJERCICIO;

                        $mcoLb->label = 'Año';
                        $mcoLbIndicador->label = 'Valor LB';
                        $mcoLbIndicador->disabled = true;
                        $mcoOriginal->label = APP_EJERCICIO;
                        $mcoOriginal->disabled = true;

                        $mcoNumerador->value = $historicoMeta->getNumerador();
                        $mcoDenominador->value = $historicoMeta->getDenominador();
                        $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();

                        $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                        $numerador = '<td>' . $this->ponInput($mcoNumerador) . '</td>';
                        $denominador = '<td>' . $this->ponInput($mcoDenominador) . '</td>';
                        $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';

                        $deseadoNumerador = '<td>' . $this->ponInput($mcoDeseadoNumerador) . '</td>';
                        $deseadoDenominador = '<td>' . $this->ponInput($mcoDeseadoDenominador) . '</td>';
                        $original = '<td>' . $this->ponInput($mcoOriginal) . '</td>';

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
                    case 5:
                        if ($trimestre2Cal1->value != null && $trimestre4Cal1->value != null &&
                            $trimestre2Cal2->value != null && $trimestre4Cal2->value != null) {

                            $resParcialT1->value = '0';
                            $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format(((($trimestre2Cal1->value / $trimestre2Cal2->value) - 1) * 100), 4) : '0');
                            $resParcialT3->value = '0';
                            $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format(((($trimestre4Cal1->value / $trimestre4Cal2->value) - 1) * 100), 4) : '0');

                            if ($datos['tiv'] == 1) {

                                $totalCal1->value = number_format(($trimestre2Cal1->value + $trimestre4Cal1->value), 4);
                                $totalCal2->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                                $resParcialTo->value = (($totalCal1->value != 0 || $totalCal2->value != 0) ? number_format(((($totalCal1->value / $totalCal2->value) - 1) * 100), 4) . '%' : '0%');
                                $mcoT0Valor->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                                $mcoAnteriorIndicador->value = $resParcialTo->value;
                            } else {
                                $resParcialTo->value = $resParcialT4->value . '%';
                                $totalCal1->value = (number_format(($trimestre4Cal1->value), 4));
                                $totalCal2->value = (number_format(($trimestre4Cal2->value), 4));
                                $mcoT0Valor->value = (number_format(($trimestre4Cal2->value), 4));
                                $mcoAnteriorIndicador->value = $resParcialTo->value;
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
                        $legendp = 'LÍNEA BASE';
                        $legend = 'VALORES PROGRAMADOS';
                        $legendCal1 = '
                            <tr>
                                <td colspan=5><legend><small>T1</small></legend></td>
                            </tr>
                        ';
                        $legendCal2 = '
                            <tr>
                                <td colspan=5><legend><small>T0</small></legend></td>
                            </tr>
                        ';

                        $legendCalT = '
                            <tr>
                                <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                            </tr>
                        ';

                        $mcoLb->value = !empty($meta->getLb()) ? $meta->getLb() : APP_EJERCICIO;
                        $mcoAnterior->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO;

                        $mcoLb->label = 'AÑO T-1';
                        $mcoLbValor->label = 'VALOR AÑO T-1';
                        $mcoAnterior->label = 'AÑO T0';
                        $mcoAnteriorValor->label = 'VALOR AÑO T0';
                        $mcoLbIndicador->label = 'VALOR INDICADOR';
                        $mcoAnteriorIndicador->label = 'VALOR INDICADOR';
                        $mcoOriginal->label = 'VALOR AÑO T1';

                        $mcoLbIndicador->disabled = true;
                        $mcoOriginal->disabled = true;
                        $mcoAnteriorIndicador->disabled = true;

                        $anterior = '<td>' . $this->ponInput($mcoAnterior) . '</td>';
                        $anteriorValor = '<td>' . $this->ponInput($mcoAnteriorValor) . '</td>';
                        $anteriorIndicador = '<td>' . $this->ponInput($mcoAnteriorIndicador) . '</td>';

                        $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                        $lbValor = '<td>' . $this->ponInput($mcoLbValor) . '</td>';
                        $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';

                        $t0 = '<td>' . $this->ponInput($mcoT0) . '</td>';
                        $t0Valor = '<td>' . $this->ponInput($mcoT0Valor) . '</td>';
                        $t1 = '<td>' . $this->ponInput($mcoT1) . '</td>';
                        $original = '<td>' . $this->ponInput($mcoT1Valor) . '</td>';

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
                        if ($trimestre2->value != null && $trimestre4->value != null) {
                            $resParcialT1->value = '0';
                            $resParcialT2->value = number_format(($trimestre2->value), 4);
                            $resParcialT3->value = '0';
                            $resParcialT4->value = number_format(($trimestre4->value), 4);

                            if ($datos['tiv'] == 1) {
                                $totalCal1->value = number_format(($resParcialT2->value + $resParcialT4->value), 4);
                                $total->value = number_format(($resParcialT2->value + $resParcialT4->value), 4);
                            } else {
                                $totalCal1->value = number_format(($resParcialT4->value), 4);
                                $total->value = number_format(($resParcialT4->value), 4);
                            }
                        } else {
                            $resParcialT1->value = '0%';
                            $resParcialT2->value = '0%';
                            $resParcialT3->value = '0%';
                            $resParcialT4->value = '0%';
                            $resParcialTo->value = '0%';
                            $total->value = '0%';
                        }
                        $legendp = 'Línea Base';
                        $legend = 'Valor Programado Anual';

                        $legendCalT = '
                            <tr>
                                <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                            </tr>
                        ';

                        $mcoLb->label = 'Año';
                        $mcoLbIndicador->label = 'Valor';

                        $mcoOriginal->disabled = true;

                        $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                        $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';
                        $original = '<td>' . $this->ponInput($mcoOriginal) . '</td>';

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

                $form = new stdClass();
                $form->id = 'ModificacionesSefin';
                $form->controlador = $this->pantalla;
                $form->metodo = 'abcMetas';
                $form->datosExtra = new stdClass();
                $form->datosExtra->abc = $this->prm->abc;
                $form->datosExtra->nim_id = $this->prm->nim_id;
                $form->datosExtra->ind_id = $this->prm->ind_id;
                $form->datosExtra->eri_id = $this->prm->eri_id;
                $form->datosExtra->epfi_id = $this->prm->eri_id;
                $form->datosExtra->gpo_id = $this->prm->gpo_id;
                $form->datosExtra->ur_id = $this->prm->ur_id;
                $form->datosExtra->prgs_id = $this->prm->prgs_id;
                $form->datosExtra->sprs_id = $this->prm->sprs_id;
                $form->datosExtra->pdf_id = $this->prm->pdf_id;
                $form->datosExtra->lectura = (string) 0;
                $form->datosExtra->tipoCalendario = $tiv;
                $form->datosExtra->unidadMedida = $umi;
                $form->datosExtra->descripcion = $nom_ind;
                $form->datosExtra->anio = (string) APP_EJERCICIO;
                $form->datosExtra->umi_id = $umi;
                $form->datosExtra->omo_id = $this->prm->omo_id;
                $form->datosExtra->mme_id = $this->prm->mme_id;
                $form->datosExtra->nivelRevision = $this->prm->nivelRevision;
                $form->datosExtra->tipoMov2 = '"{$("#mov1").val()}"';
                $form->datosExtra->tipoMov4 = '"{$("#mov3").val()}"';

                switch ($umi) {
                    case 2:
                    case 3:
                    case 4: // RAZÓN
                        $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                        $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                        $form->datosExtra->original = '"{$("#original").val()}"';
                        break;
                    case 5: // TASA
                        $form->datosExtra->lbValor = '"{$("#lbValor").val()}"';
                        $form->datosExtra->original = '"{$("#anteriorIndicador").val()}"';
                        $form->datosExtra->anteriorValor = '"{$("#anteriorValor").val()}"';
                        $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                        break;
                    default:
                        $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                        $form->datosExtra->original = '"{$("#original").val()}"';
                        break;
                }

                $tipoM = new stdClass();
                $tipoM->id = 'mov';
                $tipoM->label = 'Tipo Movimiento';
                $tipoM->opciones = ['' => 'Seleccione una opción'];
                $comboMovimiento = $this->ponCombo($tipoM);

                $tipoM1 = new stdClass();
                $tipoM1->id = 'mov1';
                $tipoM1->label = 'Tipo Movimiento';
                $tipoM1->opciones = ['' => 'Seleccione una opción', 'A' => 'AMPLIACIÓN', 'R' => 'REDUCCIÓN'];
                $comboMovimiento1 = $this->ponCombo($tipoM1);

                $tipoM2 = new stdClass();
                $tipoM2->id = 'mov2';
                $tipoM2->label = 'Tipo Movimiento';
                $tipoM2->opciones = ['' => 'Seleccione una opción'];
                $comboMovimiento2 = $this->ponCombo($tipoM2);

                $tipoM3 = new stdClass();
                $tipoM3->id = 'mov3';
                $tipoM3->label = 'Tipo Movimiento';
                $tipoM3->opciones = ['' => 'Seleccione una opción', 'A' => 'AMPLIACIÓN', 'R' => 'REDUCCIÓN'];
                $comboMovimiento3 = $this->ponCombo($tipoM3);

                $form = new form($form);

                $wr = new WebResponse();

                $form->mixHtml('
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>NOMBRE DEL INDICADOR</legend>
                        ' . $this->ponTextArea($descripcion) . '
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <label for="ind_nombre" class="col-sm-3 control-label">NOMBRE DE LA VARIABLE</label>
                        ' . $this->ponTextArea($variable) . '
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <label for="ind_nombre" class="col-sm-3 control-label">MÉTODO DE CÁLCULO</label>
                        ' . $this->ponTextArea($metodocal) . '
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <label for="ind_nombre" class="col-sm-3 control-label">UNIDAD DE MEDIDA</label>
                        ' . $this->ponTextArea($medida) . '
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <label for="ind_nombre" class="col-sm-3 control-label">TIPO DE INDICADOR</label>
                        ' . $this->ponTextArea($tipo) . '
                    </fieldset>
                    <fieldset class="col-md-8 col-xs-8">
                        <legend><small>' . $legendp . '</small></legend>
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    ' . $lb . $lbValor . $anterior . $anteriorValor . $numerador . $denominador . $lbIndicador . '
                                </tr>
                            </tbody>
                        </table>
                        <legend><small>' . $legend . '</small></legend>
                        <table class="table table-bordered table-striped ">
                            <tbody>
                                <tr>
                                    ' . $deseadoNumerador . $deseadoDenominador . $t0 . $t0Valor . $t1 . $original . $anteriorIndicador . '
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
                                                <div class="table-responsive">
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
                                        </div>
                                        <div class="col">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">2° Trimestre</h3>
                                                </div>
                                                <div class="table-responsive">
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
                                        </div>
                                        <div class="col">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">3° Trimestre</h3>
                                                </div>
                                                <div class="table-responsive">
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
                                        </div>
                                        <div class="col">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">4° Trimestre</h3>
                                                </div>
                                                <div class="table-responsive">
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
                                        </div>
                                        <div class="col">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">TOTALES PARCIALES</h3>
                                                </div>
                                                <div class="table-responsive">
                                                    <div class="panel-body">
                                                        <table class="table table-condensed">
                                                            <tbody>
                                                                <tr class="d-flex">
                                                                    <td class="col-10"><br><br><br></td>
                                                                </tr>
                                                                <tr class="d-flex">
                                                                    <td class="col-10"><br></td>
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
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                ');

                $this->mixHtml('
                    <div class="row" id="ModificacionesSefin">
                        <h4><br>Meta de Indicador ' . $this->prm->ind_id . ' de nivel Componente</h4>
                        <div class="panel-body">
                            <div class="row">' . $form->ponForm() . '
                        </div>
                    </div>
                ');

                $this->setContenedor('div-metas');

                $this->agregaTools($this->ponBoton($btnRegresarTablaModificacion));
                $btnGuardarMeta = is_object($btnGuardarM) ? $this->ponBoton($btnGuardarM) : '';
                $this->agregaTools($btnGuardarMeta);

                $this->mixScript('
                    var calculoLBIndicador=0;
                    var calculoVPIndicador=0;

                    switch(' . $umi . '){
                        case 2:case 3:case 4:    // RAZÓN
                            $("#numerador,#denominador").blur(function() {
                                if($("#denominador").val() != 0){
                                    if(' . $umi . ' == 2){ //porcentaje
                                        calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) ) * 100;
                                    }else{ // razón
                                        calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) );
                                    }
                                    $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                                }else{
                                    $("#lbIndicador").val(0);
                                }
                            });

                            //calendario 1
                            $("#num_trimestre2,#num_trimestre4").blur(function() {

                                if(' . $umi . ' == 2){
                                    porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())*100);
                                    porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())*100);
                                } else {
                                    porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val()));
                                    porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()));
                                }

                                if(' . $datos['tiv'] . ' == 1){
                                    $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                                    $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                    $("#deseadoNumerador").val(formatNumber($("#num_total").val()));
                                    if(' . $umi . ' == 2){
                                        $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                        $("#totalParcial").val($("#original").val() + "%");
                                    } else {
                                        $("#original").val((($("#num_total").val() / $("#den_total").val())).toFixed(4));
                                        $("#totalParcial").val($("#original").val());
                                    }
                                } else {
                                    if(' . $umi . ' == 2){ //porcentaje
                                        $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                                    } else {
                                        $("#totalParcial").val(formatNumber(porc_trimestre4));
                                    }
                                    $("#num_total").val(unformatNumber($("#num_trimestre4").val()));
                                    $("#deseadoNumerador").val(formatNumber($("#num_total").val()));
                                    $("#original").val(formatNumber(porc_trimestre4));
                                }

                                if(' . $umi . ' == 2){ //porcentaje
                                    $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                                    $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                                } else {
                                    $("#rpar_trimestre2").val(formatNumber(porc_trimestre2));
                                    $("#rpar_trimestre4").val(formatNumber(porc_trimestre4));
                                }
                            });

                            //calendario 2
                            $("#den_trimestre2,#den_trimestre4").blur(function() {

                                if(' . $umi . ' == 2){
                                    porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())*100);
                                    porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())*100);
                                } else {
                                    porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val()));
                                    porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()));
                                }

                                if(' . $datos['tiv'] . ' == 1){

                                    $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                    $("#deseadoDenominador").val(formatNumber($("#den_total").val()));
                                    if(' . $umi . ' == 2){
                                        $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                        $("#totalParcial").val($("#original").val() + "%");
                                    } else {
                                        $("#original").val((($("#num_total").val() / $("#den_total").val())).toFixed(4));
                                        $("#totalParcial").val($("#original").val());
                                    }
                                } else {
                                    if(' . $umi . ' == 2){ //porcentaje
                                        $("#totalParcial").val(formatNumber(porc_trimestre4) +"%");
                                    } else {
                                        $("#totalParcial").val(formatNumber(porc_trimestre4));
                                    }
                                    $("#den_total").val(unformatNumber($("#den_trimestre4").val()));
                                    $("#deseadoDenominador").val(formatNumber($("#den_total").val()));
                                    $("#original").val(formatNumber(porc_trimestre4));
                                }

                                if(' . $umi . ' == 2){ //porcentaje
                                    $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                                    $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                                } else {
                                    $("#rpar_trimestre2").val(formatNumber(porc_trimestre2));
                                    $("#rpar_trimestre4").val(formatNumber(porc_trimestre4));
                                }
                            });
                            break;
                        case 5: // TASA
                            $("#anteriorValor, #lbValor").blur(function(){
                                calculoLBIndicador=((unformatNumber($("#anteriorValor").val()) / unformatNumber($("#lbValor").val()))-1) * 100;
                                $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                            });

                            //calendario 1
                            $("#num_trimestre2,#num_trimestre4").blur(function() {

                                porc_trimestre2 = formatNumber((unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())-1)*100);
                                porc_trimestre4 = formatNumber((unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())-1)*100);

                                if(' . $datos['tiv'] . ' == 1){
                                    $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                                    $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                    $("#totalParcial").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100) + "%");
                                    $("#original").val(formatNumber($("#num_total").val()));
                                    $("#anteriorIndicador").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100));
                                    $("#t0Valor").val(formatNumber($("#den_total").val()));
                                } else {
                                    $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                                    $("#num_total").val(unformatNumber($("#num_trimestre4").val()));
                                    $("#den_total").val(unformatNumber($("#den_trimestre4").val()));
                                    $("#original").val(formatNumber($("#num_trimestre4").val()));
                                    $("#anteriorIndicador").val(porc_trimestre4);
                                    $("#t0Valor").val(formatNumber($("#den_total").val()));
                                }

                                $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                                $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                            });


                            //calendario 2
                            $("#den_trimestre2,#den_trimestre4,#lbValor").blur(function(){

                                porc_trimestre2 = formatNumber((unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())-1)*100);
                                porc_trimestre4 = formatNumber((unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())-1)*100);

                                if(' . $datos['tiv'] . ' == 1){
                                    $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                                    $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                    $("#totalParcial").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100) + "%");
                                    $("#macm_original").val(formatNumber($("#num_total").val()));
                                    $("#anteriorIndicador").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100));
                                    $("#t0Valor").val(formatNumber($("#den_total").val()));
                                } else {
                                    $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                                    $("#num_total").val(formatNumber($("#num_trimestre4").val()));
                                    $("#den_total").val(formatNumber($("#den_trimestre4").val()));
                                    $("#macm_original").val(formatNumber($("#num_total").val()));
                                    $("#anteriorIndicador").val(porc_trimestre4);
                                    $("#t0Valor").val(formatNumber($("#den_total").val()));
                                }

                                $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                                $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                            });

                            break;
                        default: // todas las demás unidades de medida del indicador

                            $("#ori_trimestre2,#ori_trimestre4").blur(function() {

                                $("#rpar_trimestre2").val($("#ori_trimestre2").val());
                                $("#rpar_trimestre4").val($("#ori_trimestre4").val());

                                if(' . $datos['tiv'] . ' == 1){
                                    $("#num_total").val(formatNumber(unformatNumber($("#ori_trimestre2").val())+ unformatNumber($("#ori_trimestre4").val())));
                                    $("#ori_total").val(formatNumber(unformatNumber($("#ori_trimestre2").val()) + unformatNumber($("#ori_trimestre4").val())));
                                    $("#original").val(formatNumber(unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre4").val())));
                                } else {
                                    $("#ori_total").val(unformatNumber($("#ori_trimestre4").val()));
                                    $("#num_total").val(unformatNumber($("#ori_trimestre4").val()));
                                    $("#original").val(unformatNumber($("#ori_trimestre4").val()));
                                }
                            });

                            if("' . $this->prm->abc . '" == "c"){
                                $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur();
                            }
                            break;
                    }
                    Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaModificacion").click(); e.stopPropagation(); });
                    $("#btnRegresarTablaModificacion").click(function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"tablaMetas",
                            omo_id:"' . $this->prm->omo_id . '",
                            mme_gpo_id:"' . $this->prm->gpo_id . '",
                            mme_ur_id:"' . $this->prm->ur_id . '",
                            mme_id:' . $this->prm->mme_id . '
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        });
                        return false;
                    });
                ');
            }
        }
    }

    private function abcMetas()
    {
        $ip = ($this->getIp() != '::1') ? $this->getIp() : '127.0.0.1';
        $id = $_SESSION[_S]['usr_id'];
        $movimiento = 'success';
        $this->conectar();
        $this->setSql('
            SELECT gpo_id, ur_id, MAX(tri_id) AS trimestre_abierto, etr_id
            FROM mir_TrimestreUr
            WHERE gpo_id = ?
                AND ur_id = ?
                AND nim_id IN (1, 2, 3) 
                AND etr_id = "A"
            GROUP BY gpo_id, ur_id;
        ');
        $this->consultar([$this->prm->gpo_id,$this->prm->ur_id]);
        $this->desconectar();
        $tri_id = $this->extraer();

        if ($this->prm->abc == 'a') {
            switch ($this->prm->unidadMedida) {
                case 2:
                case 4: //RAZÓN
                    $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;

                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;

                    $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    $numerador = $this->prm->num_trimestre2 + $this->prm->num_trimestre4;
                    $denominador = $this->prm->den_trimestre2 + $this->prm->den_trimestre4;
                    if ($denominador != 0) {
                        if ($this->prm->unidadMedida == 2) {

                            $this->prm->original = ($numerador / $denominador) * 100;
                            $this->prm->modificado = ($numerador / $denominador) * 100;
                        } else {
                            $this->prm->original = $numerador / $denominador;
                            $this->prm->modificado = $numerador / $denominador;
                        }
                    } else {
                        $this->prm->original = 0;
                        $this->prm->modificado = 0;
                    }

                    break;
                case 5: //TASA
                    $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    $t1 = $this->prm->num_trimestre2 + $this->prm->num_trimestre4;
                    $t0 = $this->prm->den_trimestre2 + $this->prm->den_trimestre4;

                    $this->prm->lbValor = $this->prm->lbValor;
                    $this->prm->lbIndicador = $this->prm->lbIndicador;
                    break;
                case 3: // PORCENTAJE
                    $this->prm->modificado = $this->prm->original;
                    break;
                default:
                    $this->prm->original = $this->prm->ori_trimestre2 + $this->prm->ori_trimestre4;
                    $this->prm->modificado = $this->prm->ori_trimestre2 + $this->prm->ori_trimestre4;
                    break;
            }
            $this->prm->trimestreCierre = $tri_id['trimestre_abierto'];
        } elseif ($this->prm->abc == 'c') {
            switch ($this->prm->unidadMedida) {
                case 2:
                case 4: //RAZÓN
                    $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;

                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;

                    $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    $numerador = $this->prm->num_trimestre2 + $this->prm->num_trimestre4;
                    $denominador = $this->prm->den_trimestre2 + $this->prm->den_trimestre4;
                    if ($denominador != 0) {
                        if ($this->prm->unidadMedida == 2) {

                            $this->prm->original = ($numerador / $denominador) * 100;
                            $this->prm->modificado = ($numerador / $denominador) * 100;
                        } else {
                            $this->prm->original = $numerador / $denominador;
                            $this->prm->modificado = $numerador / $denominador;
                        }
                    } else {
                        $this->prm->original = 0;
                        $this->prm->modificado = 0;
                    }

                    break;
                case 5: //TASA
                    $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    $t1 = $this->prm->num_trimestre2 + $this->prm->num_trimestre4;
                    $t0 = $this->prm->den_trimestre2 + $this->prm->den_trimestre4;

                    $this->prm->lbValor = $this->prm->lbValor;
                    $this->prm->lbIndicador = $this->prm->lbIndicador;
                    break;

                    break;
                case 3: // PORCENTAJE
                    $this->prm->modificado = $this->prm->original;
                    break;
                default:
                    $this->prm->modificado = $this->prm->original;
                    break;
            }
            $this->prm->trimestreCierre = null;
        }

        $this->prm->origen = 'Controlador Modificacion Metas Componente';
        $this->prm->tri_id = $tri_id['trimestre_abierto'];
        $this->prm->tmf_id = '';
        $this->prm->auxoriginal = $this->prm->original;
        if ($this->prm->tipoMov2 != '') {
            $this->prm->tmf_id = $this->prm->tipoMov2;
        } elseif ($this->prm->tipoMov4 != '') {
            $this->prm->tmf_id = $this->prm->tipoMov4;
        }

        $this->conectar();
        switch ($this->prm->abc) {
            case 'a':
                $this->setSql('
                    SELECT * FROM scp_DetalleModificacionMetaComponente
                    WHERE omo_id = ? AND mme_gpo_id = ? AND mme_ur_id = ? AND mme_id = ? AND gpo_id = ? AND ur_id = ? AND prgs_id = ? AND sprs_id = ? AND epfi_id = ? AND tri_id = ?;
                ');
                $params = [
                    $this->prm->omo_id,
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                    $this->prm->mme_id,
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                    $this->prm->prgs_id,
                    $this->prm->sprs_id,
                    $this->prm->epfi_id,
                    $this->prm->tri_id,
                ];
                $this->consultar($params);
                $reg = $this->getNumeroRegistros();
                if ($reg > 0) {
                    $movimiento = 'error';
                    $mensaje = 'Movimiento no permitido, la meta ya se ha registrado con anterioridad';
                } else {
                    $this->setSql('
                        SET @bit_observacion = ?,
                        @usr_ip=INET_ATON(?),
                        @usr_id = ?;
                    ');
                    $params = [
                        'Registro de movimiento de metas',
                        $ip,
                        $id,
                    ];
                    $this->consultar($params);

                    $this->setSql('
                        INSERT INTO scp_DetalleModificacionMetaComponente
                        (omo_id, mme_gpo_id, mme_ur_id, mme_id, gpo_id, ur_id, prgs_id, sprs_id, epfi_id, tri_id,
                        mco_lb, mco_lbValor, mco_lbIndicador,
                        mco_anio, mco_original, mco_numerador, mco_denominador, mco_modificado, mco_alcanzado, mco_trimestreCierre,
                        mcot_numeradorT2, mcot_numeradorT4, mcot_denominadorT2, mcot_denominadorT4, tmf_id, usr_id
                        )
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);
                    ');
                    $params = [
                        $this->prm->omo_id,
                        $this->prm->gpo_id,
                        $this->prm->ur_id,
                        $this->prm->mme_id,
                        $this->prm->gpo_id,
                        $this->prm->ur_id,
                        $this->prm->prgs_id,
                        $this->prm->sprs_id,
                        $this->prm->epfi_id,
                        $this->prm->tri_id,
                        $this->prm->lb,
                        $this->prm->lbValor,
                        $this->prm->lbIndicador,
                        $this->prm->anio,
                        $this->prm->original,
                        $this->prm->numerador,
                        $this->prm->denominador,
                        $this->prm->modificado,
                        0,
                        $this->prm->trimestreCierre,
                        $this->prm->num_trimestre2,
                        $this->prm->num_trimestre4,
                        $this->prm->den_trimestre2,
                        $this->prm->den_trimestre4,
                        $this->prm->tmf_id,
                        $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($params);
                    $mensaje = 'Meta registrada exitosamente';
                }
                break;
            case 'c':
                $this->setSql('
                        SET @bit_observacion = ?,
                        @usr_ip=INET_ATON(?),
                        @usr_id = ?;
                    ');
                $params = [
                    'Actualizacion de movimiento de metas',
                    $ip,
                    $id,
                ];
                $this->consultar($params);
                $this->setSql('
                    UPDATE scp_DetalleModificacionMetaComponente
                    SET mco_original =' . $this->prm->original . ', 
                    mco_numerador =' . $this->prm->numerador . ', mco_denominador =' . $this->prm->denominador . ',mco_modificado =' . $this->prm->modificado . ',
                    mcot_numeradorT2 =' . $this->prm->num_trimestre2 . ', mcot_numeradorT4=' . $this->prm->num_trimestre4 . ',
                    mcot_denominadorT2=' . $this->prm->den_trimestre2 . ', mcot_denominadorT4=' . $this->prm->den_trimestre4 . ',
                    tmf_id="' . $this->prm->tmf_id . '", usr_id=' . $this->usr_id . '
                    WHERE omo_id = ?
                    AND mme_gpo_id = ?
                    AND mme_ur_id =  ?
                    AND mme_id = ?
                    AND prgs_id = ?
                    AND sprs_id = ?
                    AND epfi_id = ?
                    AND tri_id = ?;
                ');
                $params = [
                    $this->prm->omo_id,
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                    $this->prm->mme_id,
                    $this->prm->prgs_id,
                    $this->prm->sprs_id,
                    $this->prm->epfi_id,
                    $this->prm->tri_id,
                ];
                $this->consultar($params);
                $mensaje = 'Meta actualizada exitosamente';
                break;
        } // Fin switch

        $this->desconectar();

        $this->mixScript('
            notify("' . $mensaje . '","' . $movimiento . '");
            $("#myModalMetaComponente,#modalDeTablatbl-metasIndicador").modal("hide");
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
    }

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
        $nivelRevision = $this->nivelRevision; //VERIFICAMOS EL NIVEL DE REVISION
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

        $deshabilitarRadios = ($elimina['registros']) ? ',deshabilitado:true' : ''; //deshabilitar radios de tipo modificacion y de tipo de folio.

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
                alertify.confirm("ELIMINAR FOLIO","¿ Realmente desea eliminar el folio <h5 style=\"display:inline\"></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"abcFolios",
                            abc:"b",
                                ' . (isset($this->prm->omo_id) ? ' omo_id:"' . $this->prm->omo_id . '",'
                                : ' omo_id:$("#tmd_id").find("option:selected").attr("omo_id"),') . '
                                mme_id:$("#folio").val()
                                ' . (isset($this->prm->mme_gpo_id) && !empty($this->prm->mme_gpo_id)
                                ? ',mme_gpo_id:"' . $this->prm->mme_gpo_id . '"' : '') . '
                                ' . (isset($this->prm->mme_ur_id) && !empty($this->prm->mme_ur_id)
                                ? ',mme_ur_id:"' . $this->prm->mme_ur_id . '"' : '') . '
                                ' . (isset($this->prm->nivelRevision) ?
                                ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
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
        $fechaActual = strtotime(date('Y-m-d H:i:00', time()));

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
                $observacion = ' capturado desde PANTALLA S_1_9';
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
                $sql = '
                    DELETE FROM ' . BD_CTRL . '.scp_ModificacionMetaFirma WHERE omo_id= ?
                    AND mme_gpo_id= ? AND mme_ur_id = ?
                    AND mme_id = ?;
                ';
                $params = [
                    $mme->getOmo_id(),
                    $mme->getMme_gpo_id(),
                    $mme->getMme_ur_id(),
                    $mme->getMme_id(),
                ];
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

    // METODO QUE VERIFICA SI LA META YA FUE MODIFICADA
    private function consultaMetaModificada($prgs_id, $sprs_id, $epfi_id, $tri_id)
    {
        $this->conectar();
        $this->setSql('
            SELECT *
            FROM scp_DetalleModificacionMetaComponente
            WHERE prgs_id = ?
            AND sprs_id = ?
            AND epfi_id = ?
            AND tri_id = ?;
        ');
        $params = [
            $prgs_id,
            $sprs_id,
            $epfi_id,
            $tri_id,
        ];
        $this->consultar($params);
        $this->desconectar();
        $numeroRegistros = $this->getNumeroRegistros();
        return $numeroRegistros;
    } // FIN consultaMetaModificada

    /* METODO QUE MUESTRA EL HISTORIAL DEL FOLIO */

    public function tablaHistorialFolio()
    {
        $body = '';
        $this->setContenedor('divContenedorGeneralModal');
        $mod = new ModificacionMeta($this->prm);
        $tmf_id = 'C';

        $sql = "
            SELECT DATE_FORMAT(b.usr_fecha,'%d/%m/%Y') fecha, time(b.usr_fecha) hora,mme_id, emo_nombre,
            if(b.emo_id=3 or b.emo_id=5 or b.emo_id=7 or b.emo_id=9 or b.emo_id=11 or b.emo_id=13,  trim(replace(bit_observacion,'\"',' ')),'') as bit_observacion, b.usr_id,usr_nombre,b.emo_id,usr_paterno,usr_materno
            FROM
            " . BD_BITACORA . ".bit_ModificacionMetaMir b
            INNER JOIN cat_EstadoModificacion using(emo_id)
            INNER JOIN scp_ModificacionMeta m using(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN cat_Usuario u on b.usr_id=u.usr_id
            INNER JOIN ctrlUsuario.cat_Usuario us ON u.usr_id=us.usr_id
            WHERE m.omo_id= ?
            AND m.mme_gpo_id= ?
            AND m.mme_ur_id= ?
            AND m.mme_id= ?
            AND m.tfm_id = ?
            AND m.tmd_id = ?
            AND bit_observacion not in ('MOVIMIENTO AGREGADO','MOVIMIENTO ACTUALIZADO','MOVIMIENTO ELIMINADO')
            ORDER BY b.usr_fecha desc, bit_mme
        ";
        $params = [
            $mod->getOmo_id(),
            $mod->getMme_gpo_id(),
            $mod->getMme_ur_id(),
            $mod->getMme_id(),
            $tmf_id,
            $tmf_id,
        ];
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
            <div id="myModalMetaComponente" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabelComponente" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="false" style="display: block; padding-top: 100px;">
                <div class="modal-dialog modal-xl" style="width: 100% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabelComponente"></h4>
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
                                            <td width="28%">ADECUACION DE METAS MIR</td>
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
            </div>
        ';

        $this->mixHtml($modalHistorial);

        $this->mixScript('
            $("#myModalMetaComponente").modal("show");
            $("#myModalMetaComponente").on("hidden.bs.modal", function (e) {
                $("#myModalMetaComponente").remove();
            });
        ');
    }

    private function modificarMetas()
    {
        $lectura = (!isset($this->prm->lectura) ? false : true);
        $omo_id = $this->prm->omo_id;
        $mme_gpo_id = $this->prm->mme_gpo_id;
        $mme_ur_id = $this->prm->mme_ur_id;
        $mme_id = $this->prm->mme_id;
        $epfi_id = $this->prm->eri_id;
        $botonRegresar = '';
        $botonEliminar = '';
        $mme = new ModificacionMeta($this->prm);
        $mme->select();
        $emo_id = $mme->getEmo_id();
        $abc = 'c';

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
        $this->setSql('
            SELECT *
            FROM scp_ModificacionMeta
            INNER JOIN scp_DetalleModificacionMetaComponente USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            WHERE omo_id = ? AND mme_gpo_id = ? AND mme_ur_id = ? AND mme_id = ?
            AND prgs_id = ? AND sprs_id = ? AND epfi_id = ?
            GROUP BY mme_gpo_id, mme_ur_id;
        ');
        $params = [
            $omo_id,
            $mme_gpo_id,
            $mme_ur_id,
            $mme_id,
            $this->prm->prg_id,
            $this->prm->sprs_id,
            $epfi_id,
        ];
        $this->consultar($params);
        $this->desconectar();
        $metasm = $this->extraer();

        $this->prm->ind_id = isset($this->prm->ind_id) ? $this->prm->ind_id : '';
        if ($emo_id == 17 || $emo_id == 1) {
            $btnGuardarM = $this->vista->vwBotones('guardarMeta');
            $btnGuardarM->form = 'ModificacionesSefin';
        } else {
            $btnGuardarM = '';
        }

        if ($this->prm->ind_id != '') {

            $this->conectar();
            //La FRECUENCIA DE Medición A NIVEL COMPONENTE SIEMPRE SERÁ SEMESTRAL AUNQUE EN EL CATALOGO TENGA OTRA INFORMACIÓN.
            // Y el tipo de valor siempre será sumable.
            $this->setSql('
                SELECT  "S" AS tiv_id, ind_nombre, ind_definicion, ind_nombreMIR,
                    2 AS frm_id, umi_id, nim_id, ind_definicionMIR,
                    dim_id,dim_nombre, frm_id,frm_nombre, tpi_id,tpi_nombre,
                    ses_id,ses_nombre,umv_id,umv_nombre, tiv_id AS tiv,tiv_nombre,
                    ind_formulaCalculo
                FROM cat_Indicador
                INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                INNER JOIN cat_UnidadMedidaVariable USING(umv_id)
                INNER JOIN cat_Dimension USING (dim_id)
                INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
                INNER JOIN cat_TipoIndicador USING(tpi_id)
                INNER JOIN cat_SentidoEsperado USING(ses_id)
                INNER JOIN cat_TipoValor USING(tiv_id)
                WHERE nim_id = 3
                AND ind_id = ?;
            ');

            $this->consultar([$this->prm->ind_id]);
            $datos = $this->extraer();
            $tiv = $datos['tiv_id'];
            $tiv_nombre = $datos ['tiv_nombre'];
            $nom_ind = $datos['ind_nombre'];
            $nom_MIR = $datos['ind_nombreMIR'];
            $def_ind = $datos['ind_definicion'];
            $frm = $datos['frm_id'];
            $umi = $datos['umi_id'];
            $umv = $datos['umv_id'];
            $unom = $datos['umv_nombre'];
            $nivelInd = $datos['nim_id'];
            $calculo = $datos['ind_formulaCalculo'];
            $this->desconectar();

            $indsql = '
                SELECT COUNT(DISTINCT nim_id,ind_id) cuantos, MAX(ind_id) AS ind_id, MAX(epfi_id) AS eri_id,MAX(umi_id) AS umi_id, pdf_id,tiv_nombre
                FROM enc_ProductoFinalIndicador enc
                JOIN cat_Subprograma USING(pdf_id)
                INNER JOIN cat_Indicador USING(ind_id,nim_id)
                INNER JOIN cat_TipoValor USING(tiv_id)
                INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                WHERE enc.sts_id = 1 AND nim_id = 3 AND pdf_id = ?
                AND prg_id = ?
                AND gpo_id = ?
                AND ur_id = ?
                HAVING cuantos != 0;
            ';
            $params = [
                $this->prm->pdf_id,
                $this->prm->prg_id,
                $this->prm->mme_gpo_id,
                $this->prm->mme_ur_id,
            ];
            $indicador = new AccesoDatos();
            $indicador->conectar();
            $indicador->setSql($indsql);
            $indicador->consultar($params);
            $indicador->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $cuantos = $indicador->extraer();
                $ind_id = $cuantos['ind_id'];
                $eri_id = $cuantos['eri_id'];
                $umi_id = $cuantos['umi_id'];
                $pdf_id = $cuantos['pdf_id'];
                $cuantos = $cuantos['cuantos'] * 1;
            }

            $this->prm->prgs_id = $this->prm->prg_id;
            $this->prm->sprs_id = $this->prm->sprs_id;
            $this->prm->epfi_id = $this->prm->eri_id;
            (isset($this->prm->trim) && $this->prm->trim != '' ? $this->prm->tri_id = $this->prm->trim : '');

            //INICIA MUESTRA MODAL
            $meta = new MetaComponente($this->prm);
            $historicoMeta = new HistoricoModificacionMetaComponente($this->prm);

            $descripcion = new stdClass();
            $descripcion->id = 'descripcion_mod';
            $descripcion->enDiv = true;
            $descripcion->maxlength = 500;
            $descripcion->width = '300px';
            $descripcion->height = '100px';
            $descripcion->disabled = true;

            $descripcion->texto = $nom_MIR;

            $variable = new stdClass();
            $variable->id = 'variable_mod';
            $variable->enDiv = true;
            $variable->maxlength = 100;
            $variable->width = '300px';
            $variable->height = '100px';
            $variable->disabled = true;
            $variable->texto = $nom_ind;

            $metodocal = new stdClass();
            $metodocal->id = 'calculo_mod';
            $metodocal->enDiv = true;
            $metodocal->maxlength = 100;
            $metodocal->width = '300px';
            $metodocal->height = '100px';
            $metodocal->disabled = true;
            $metodocal->texto = $calculo;

            $medida = new stdClass();
            $medida->id = 'umi_mod';
            $medida->enDiv = true;
            $medida->maxlength = 100;
            $medida->width = '300px';
            $medida->height = '100px';
            $medida->disabled = true;
            $medida->texto = $umv . '-' . $unom;

            $tipo = new stdClass();
            $tipo->id = 'tiv_nombre';
            $tipo->enDiv = true;
            $tipo->maxlength = 100;
            $tipo->width = '300px';
            $tipo->height = '100px';
            $tipo->disabled = true;
            $tipo->texto = $tiv_nombre;

            //***********************LINEA BASE***************************************//
            $mcoLb = new stdClass();
            $mcoLb->readonly = true;
            $mcoLb->id = 'lb';
            $mcoLb->enDiv = true;
            $mcoLb->value = $metasm['mco_lb'];
            $mcoLb->maxlength = 4;
            $mcoLb->formato = 'entero';

            $mcoLbValor = new stdClass();
            $mcoLbValor->readonly = true;
            $mcoLbValor->id = 'lbValor';
            $mcoLbValor->enDiv = true;
            $mcoLbValor->value = $metasm['mco_lbValor'];
            $mcoLbValor->formato = 'numero';

            $mcoLbIndicador = new stdClass();
            $mcoLbIndicador->readonly = true;
            $mcoLbIndicador->id = 'lbIndicador';
            $mcoLbIndicador->enDiv = true;
            $mcoLbIndicador->value = $metasm['mco_lbIndicador'];
            $mcoLbIndicador->formato = 'numero';

            //***********************AÑO ANTERIOR***************************************//
            $mcoAnterior = new stdClass();
            $mcoAnterior->id = 'anterior';
            $mcoAnterior->enDiv = true;
            $mcoAnterior->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO;
            $mcoAnterior->maxlength = 4;
            $mcoAnterior->formato = 'entero';

            $mcoAnteriorValor = new stdClass();
            $mcoAnteriorValor->id = 'anteriorValor';
            $mcoAnteriorValor->enDiv = true;
            $mcoAnteriorValor->value = $historicoMeta->getAnteriorValor();
            $mcoAnteriorValor->formato = 'numero';

            $mcoOriginal = new stdClass();
            $mcoOriginal->id = 'original';
            $mcoOriginal->enDiv = true;
            $mcoOriginal->value = $metasm['mco_modificado'];
            $mcoOriginal->formato = 'numero';

            $mcoAnteriorIndicador = new stdClass();
            $mcoAnteriorIndicador->id = 'anteriorIndicador';
            $mcoAnteriorIndicador->enDiv = true;
            $mcoAnteriorIndicador->value = $historicoMeta->getAnteriorIndicador();
            $mcoAnteriorIndicador->formato = 'numero';

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
            $trimestre1Cal1->id = 'num_trimestre1';
            $trimestre1Cal1->enDiv = true;
            $trimestre1Cal1->label = '1° Trimestre';
            $trimestre1Cal1->formato = 'numero';

            $trimestre2Cal1 = new stdClass();
            $trimestre2Cal1->id = 'num_trimestre2';
            $trimestre2Cal1->enDiv = true;
            $trimestre2Cal1->label = '2° Trimestre';
            $trimestre2Cal1->formato = 'numero';

            $trimestre3Cal1 = new stdClass();
            $trimestre3Cal1->id = 'num_trimestre3';
            $trimestre3Cal1->enDiv = true;
            $trimestre3Cal1->label = '3° Trimestre';
            $trimestre3Cal1->formato = 'numero';

            $trimestre4Cal1 = new stdClass();
            $trimestre4Cal1->id = 'num_trimestre4';
            $trimestre4Cal1->enDiv = true;
            $trimestre4Cal1->label = '4° Trimestre';
            $trimestre4Cal1->formato = 'numero';

            $totalCal1 = new stdClass();
            $totalCal1->disabled = true;
            $totalCal1->id = 'num_total';
            $totalCal1->enDiv = true;
            $totalCal1->label = 'Total';

            //****************************CALENDARIO 2*************************************//
            $trimestre1Cal2 = new stdClass();
            $trimestre1Cal2->id = 'den_trimestre1';
            $trimestre1Cal2->enDiv = true;
            $trimestre1Cal2->label = '1° Trimestre';
            $trimestre1Cal2->formato = 'numero';

            $trimestre2Cal2 = new stdClass();
            $trimestre2Cal2->id = 'den_trimestre2';
            $trimestre2Cal2->enDiv = true;
            $trimestre2Cal2->label = '2° Trimestre';
            $trimestre2Cal2->formato = 'numero';

            $trimestre3Cal2 = new stdClass();
            $trimestre3Cal2->id = 'den_trimestre3';
            $trimestre3Cal2->enDiv = true;
            $trimestre3Cal2->label = '3° Trimestre';
            $trimestre3Cal2->formato = 'numero';

            $trimestre4Cal2 = new stdClass();
            $trimestre4Cal2->id = 'den_trimestre4';
            $trimestre4Cal2->enDiv = true;
            $trimestre4Cal2->label = '4° Trimestre';
            $trimestre4Cal2->formato = 'numero';

            $totalCal2 = new stdClass();
            $totalCal2->disabled = true;
            $totalCal2->id = 'den_total';
            $totalCal2->enDiv = true;
            $totalCal2->label = 'Total';
            $totalCal2->formato = 'numero';

            //********************************LINEA BASE Numerador Denominador****************************************************//

            $mcoNumerador = new stdClass();
            $mcoNumerador->readonly = true;
            $mcoNumerador->id = 'numerador';
            $mcoNumerador->enDiv = true;
            $mcoNumerador->label = 'Numerador';
            $mcoNumerador->formato = 'numero';

            $mcoDenominador = new stdClass();
            $mcoDenominador->readonly = true;
            $mcoDenominador->id = 'denominador';
            $mcoDenominador->enDiv = true;
            $mcoDenominador->label = 'Denominador';
            $mcoDenominador->formato = 'numero';

            //********************************VALOR DESEADO Y PROGRAMADO (RAZON Y TASA) *********************//

            $mcoDeseadoNumerador = new stdClass();
            $mcoDeseadoNumerador->id = 'deseadoNumerador';
            $mcoDeseadoNumerador->enDiv = true;
            $mcoDeseadoNumerador->label = 'Numerador';
            $mcoDeseadoNumerador->formato = 'numero';
            $mcoDeseadoNumerador->disabled = true;

            $mcoDeseadoDenominador = new stdClass();
            $mcoDeseadoDenominador->id = 'deseadoDenominador';
            $mcoDeseadoDenominador->enDiv = true;
            $mcoDeseadoDenominador->label = 'Denominador';
            $mcoDeseadoDenominador->formato = 'numero';
            $mcoDeseadoDenominador->disabled = true;

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
            $resParcialTo->id = 'totalParcial';
            $resParcialTo->enDiv = true;
            $resParcialTo->label = 'Total';
            $resParcialTo->disabled = true;
            //********************************FIN RESULTADO PARCIAL *********************//

            $mcoT0 = new stdClass();
            $mcoT0->id = 't0';
            $mcoT0->enDiv = true;
            $mcoT0->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO;
            $mcoT0->maxlength = 4;
            $mcoT0->formato = 'entero';
            $mcoT0->label = 'AÑO T0';
            $mcoT0->disabled = true;

            $mcoT0Valor = new stdClass();
            $mcoT0Valor->id = 't0Valor';
            $mcoT0Valor->enDiv = true;
            $mcoT0Valor->value = $meta->getAnteriorValor();
            $mcoT0Valor->formato = 'numero';
            $mcoT0Valor->label = 'VALOR AÑO T0';
            $mcoT0Valor->disabled = true;

            $mcoT1 = new stdClass();
            $mcoT1->id = 't1';
            $mcoT1->enDiv = true;
            $mcoT1->value = $metasm['mco_anio'];
            $mcoT1->maxlength = 4;
            $mcoT1->formato = 'entero';
            $mcoT1->label = 'AÑO T1';
            $mcoT1->disabled = true;

            $mcoT1Valor = new stdClass();
            $mcoT1Valor->id = 't1Valor';
            $mcoT1Valor->enDiv = true;
            $mcoT1Valor->formato = 'numero';
            $mcoT1Valor->label = 'VALOR AÑO T1';
            $mcoT1Valor->disabled = true;

            $hoy = date('Y-m-d H:i:s');
            if ($hoy > APP_EJERCICIO . '-06-30 00:00:00') {
                $trimestre2->readonly = true;
                $trimestre2Cal1->readonly = true;
                $trimestre2Cal2->readonly = true;
                $btnGuardarMeta = '';
            } else {
                $trimestre4->readonly = true;
                $trimestre4Cal1->readonly = true;
                $trimestre4Cal2->readonly = true;
            }

            //*********************************************************************************************//
            //semestral
            $trimestre1Cal1->disabled = true;
            $trimestre2Cal1->value = $metasm['mcot_numeradorT2'];
            $trimestre3Cal1->disabled = true;
            $trimestre4Cal1->value = $metasm['mcot_numeradorT4'];

            $trimestre1Cal2->disabled = true;
            $trimestre2Cal2->value = $metasm['mcot_denominadorT2'];

            $trimestre3Cal2->disabled = true;
            $trimestre4Cal2->value = $metasm['mcot_denominadorT4'];

            if ($datos['tiv'] == 1) {
                $mcoDeseadoNumerador->value = number_format($trimestre2Cal1->value + $trimestre4Cal1->value, 4);
                $mcoDeseadoDenominador->value = number_format($trimestre2Cal2->value + $trimestre4Cal2->value, 4);
                $mcoT1Valor->value = number_format($trimestre2Cal1->value + $trimestre4Cal1->value, 4);
            } else {
                $mcoDeseadoNumerador->value = number_format($trimestre4Cal1->value, 4);
                $mcoDeseadoDenominador->value = number_format($trimestre4Cal2->value, 4);
                $mcoT1Valor->value = number_format($trimestre4Cal1->value, 4);
            }

            $lb = '';
            $lbValor = '';
            $lbIndicador = '';

            $anterior = '';
            $anteriorValor = '';
            $anteriorIndicador = '';

            $original = '';

            $numerador = '';
            $denominador = '';
            $deseadoNumerador = '';
            $deseadoDenominador = '';
            $t0 = '';
            $t0Valor = '';
            $t1 = '';
            $t1Valor = '';

            $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
            $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';
            $trim1 = $trim2 = $trim3 = $trim4 = '';
            $legendCal1 = $legendCal2 = '';

            switch ($umi) {
                case 2:
                case 3:
                case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                    if ($trimestre2Cal1->value != null && $trimestre4Cal1->value != null &&
                        $trimestre2Cal2->value != null && $trimestre4Cal2->value != null) {
                        if ($umi == 2) {
                            $resParcialT1->value = '0';
                            $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format((($trimestre2Cal1->value / $trimestre2Cal2->value) * 100), 4) . '%' : '0%');
                            $resParcialT3->value = '0';
                            $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format((($trimestre4Cal1->value / $trimestre4Cal2->value) * 100), 4) . '%' : '0%');
                        } else {
                            $resParcialT1->value = '0';
                            $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format(($trimestre2Cal1->value / $trimestre2Cal2->value), 4) : '0');
                            $resParcialT3->value = '0';
                            $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format(($trimestre4Cal1->value / $trimestre4Cal2->value), 4) : '0');
                        }
                        if ($datos['tiv'] == 1) {
                            if ($umi == 2) {
                                $totalCal1->value = number_format(($trimestre2Cal1->value + $trimestre4Cal1->value), 4);
                                $totalCal2->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                                $resParcialTo->value = ($mcoOriginal->value) . '%';
                            } else {
                                $resParcialTo->value = ($mcoOriginal->value);
                                $totalCal1->value = number_format(($trimestre2Cal1->value + $trimestre4Cal1->value), 4);
                                $totalCal2->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                            }
                        } else {
                            if ($umi == 2) {
                                $resParcialTo->value = $resParcialT4->value . '%';
                                $totalCal1->value = $trimestre4Cal1->value;
                                $totalCal2->value = $trimestre4Cal2->value;
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
                    $legendp = 'LÍNEA BASE';
                    $legend = 'VALOR DESEADO';
                    $legendCal1 = '
                        <tr>
                            <td colspan=5><legend><small>Numerador</small></legend></td>
                        </tr>
                    ';
                    $legendCal2 = '
                        <tr>
                            <td colspan=5><legend><small>Denominador</small></legend></td>
                        </tr>
                    ';
                    $legendCalT = '
                        <tr>
                            <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                        </tr>
                    ';
                    $mcoLb->value = !empty($meta->getLb()) ? $meta->getLb() : APP_EJERCICIO;

                    $mcoLb->label = 'Año';
                    $mcoLbIndicador->label = 'Valor LB';
                    $mcoLbIndicador->disabled = true;
                    $mcoOriginal->label = APP_EJERCICIO;
                    $mcoOriginal->disabled = true;

                    $mcoNumerador->value = $metasm['mco_numerador'];
                    $mcoDenominador->value = $metasm['mco_denominador'];
                    $trimestre2Cal2->value = $metasm['mcot_denominadorT2'];

                    $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                    $numerador = '<td>' . $this->ponInput($mcoNumerador) . '</td>';
                    $denominador = '<td>' . $this->ponInput($mcoDenominador) . '</td>';
                    $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';

                    $deseadoNumerador = '<td>' . $this->ponInput($mcoDeseadoNumerador) . '</td>';
                    $deseadoDenominador = '<td>' . $this->ponInput($mcoDeseadoDenominador) . '</td>';
                    $original = '<td>' . $this->ponInput($mcoOriginal) . '</td>';

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
                case 5:
                    if ($trimestre2Cal1->value != null && $trimestre4Cal1->value != null &&
                        $trimestre2Cal2->value != null && $trimestre4Cal2->value != null) {

                        $resParcialT1->value = '0';
                        $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format(((($trimestre2Cal1->value / $trimestre2Cal2->value) - 1) * 100), 4) : '0');
                        $resParcialT3->value = '0';
                        $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format(((($trimestre4Cal1->value / $trimestre4Cal2->value) - 1) * 100), 4) : '0');

                        if ($datos['tiv'] == 1) {

                            $totalCal1->value = number_format(($trimestre2Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                            $resParcialTo->value = (($totalCal1->value != 0 || $totalCal2->value != 0) ? number_format(((($totalCal1->value / $totalCal2->value) - 1) * 100), 4) . '%' : '0%');
                            $mcoT0Valor->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                            $mcoAnteriorIndicador->value = $resParcialTo->value;
                        } else {
                            $resParcialTo->value = $resParcialT4->value . '%';
                            $totalCal1->value = (number_format(($trimestre4Cal1->value), 4));
                            $totalCal2->value = (number_format(($trimestre4Cal2->value), 4));
                            $mcoT0Valor->value = (number_format(($trimestre4Cal2->value), 4));
                            $mcoAnteriorIndicador->value = $resParcialTo->value;
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
                    $legendp = 'LÍNEA BASE';
                    $legend = 'VALORES PROGRAMADOS';
                    $legendCal1 = '
                        <tr>
                            <td colspan=5><legend><small>T1</small></legend></td>
                        </tr>
                    ';
                    $legendCal2 = '
                        <tr>
                            <td colspan=5><legend><small>T0</small></legend></td>
                        </tr>
                    ';

                    $legendCalT = '
                        <tr>
                            <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                        </tr>
                    ';

                    $mcoLb->value = $metasm['mco_lb'];
                    $mcoAnterior->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO;

                    $mcoLb->label = 'AÑO T-1';
                    $mcoLbValor->label = 'VALOR AÑO T-1';
                    $mcoAnterior->label = 'AÑO T0';
                    $mcoAnteriorValor->label = 'VALOR AÑO T0';
                    $mcoLbIndicador->label = 'VALOR INDICADOR';
                    $mcoAnteriorIndicador->label = 'VALOR INDICADOR';
                    $mcoOriginal->label = 'VALOR AÑO T1';

                    $mcoLbIndicador->disabled = true;
                    $mcoOriginal->disabled = true;
                    $mcoAnteriorValor->disabled = true;
                    $mcoAnteriorIndicador->disabled = true;

                    $anterior = '<td>' . $this->ponInput($mcoAnterior) . '</td>';
                    $anteriorValor = '<td>' . $this->ponInput($mcoAnteriorValor) . '</td>';
                    $anteriorIndicador = '<td>' . $this->ponInput($mcoAnteriorIndicador) . '</td>';

                    $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                    $lbValor = '<td>' . $this->ponInput($mcoLbValor) . '</td>';
                    $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';

                    $t0 = '<td>' . $this->ponInput($mcoT0) . '</td>';
                    $t0Valor = '<td>' . $this->ponInput($mcoT0Valor) . '</td>';
                    $t1 = '<td>' . $this->ponInput($mcoT1) . '</td>';
                    $original = '<td>' . $this->ponInput($mcoT1Valor) . '</td>';

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
                    if ($trimestre2->value != null && $trimestre4->value != null) {
                        $resParcialT1->value = '0';
                        $resParcialT2->value = number_format(($trimestre2->value), 4);
                        $resParcialT3->value = '0';
                        $resParcialT4->value = number_format(($trimestre4->value), 4);

                        if ($datos['tiv'] == 1) {
                            $totalCal1->value = number_format(($resParcialT2->value + $resParcialT4->value), 4);
                            $total->value = number_format(($resParcialT2->value + $resParcialT4->value), 4);
                        } else {
                            $totalCal1->value = number_format(($resParcialT4->value), 4);
                            $total->value = number_format(($resParcialT4->value), 4);
                        }
                    } else {
                        $resParcialT1->value = '0%';
                        $resParcialT2->value = '0%';
                        $resParcialT3->value = '0%';
                        $resParcialT4->value = '0%';
                        $resParcialTo->value = '0%';
                        $total->value = '0%';
                    }
                    $legendp = 'Línea Base';
                    $legend = 'Valor Programado Anual';

                    $legendCalT = '
                        <tr>
                            <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                        </tr>
                    ';

                    $mcoLb->label = 'Año';
                    $mcoLbIndicador->label = 'Valor';

                    $mcoOriginal->disabled = true;

                    $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                    $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';
                    $original = '<td>' . $this->ponInput($mcoOriginal) . '</td>';

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

            $form = new stdClass();
            $form->id = 'ModificacionesSefin';
            $form->controlador = $this->pantalla;
            $form->metodo = 'abcMetas';
            $form->datosExtra = new stdClass();
            $form->datosExtra->abc = $abc;
            $form->datosExtra->nim_id = $this->prm->nim_id;
            $form->datosExtra->ind_id = $this->prm->ind_id;
            $form->datosExtra->epfi_id = $this->prm->eri_id;
            $form->datosExtra->gpo_id = $mme_gpo_id;
            $form->datosExtra->ur_id = $mme_ur_id;
            $form->datosExtra->prgs_id = $this->prm->prg_id;
            $form->datosExtra->sprs_id = $this->prm->sprs_id;
            $form->datosExtra->pdf_id = $this->prm->pdf_id;
            $form->datosExtra->lectura = (string) 0;
            $form->datosExtra->tipoCalendario = $tiv;
            $form->datosExtra->unidadMedida = $umi;
            $form->datosExtra->descripcion = $nom_ind;
            $form->datosExtra->anio = (string) APP_EJERCICIO;
            $form->datosExtra->umi_id = $umi;
            $form->datosExtra->omo_id = $this->prm->omo_id;
            $form->datosExtra->mme_id = $this->prm->mme_id;
            $form->datosExtra->nivelRevision = $this->prm->nivelRevision;
            $form->datosExtra->tipoMov2 = '"{$("#mov1").val()}"';
            $form->datosExtra->tipoMov4 = '"{$("#mov3").val()}"';

            switch ($umi) {
                case 2:
                case 3:
                case 4: // RAZÓN
                    $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                    $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                    $form->datosExtra->original = '"{$("#original").val()}"';
                    break;
                case 5: // TASA
                    $form->datosExtra->lbValor = '"{$("#lbValor").val()}"';
                    $form->datosExtra->original = '"{$("#anteriorIndicador").val()}"';
                    $form->datosExtra->anteriorValor = '"{$("#anteriorValor").val()}"';
                    $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                    break;
                default:
                    $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                    $form->datosExtra->original = '"{$("#original").val()}"';
                    break;
            }

            $tipoM = new stdClass();
            $tipoM->id = 'mov';
            $tipoM->label = 'Tipo Movimiento';
            $tipoM->opciones = ['' => 'Seleccione una opción'];
            $comboMovimiento = $this->ponCombo($tipoM);
            $tipoM->readonly = true;

            $tipoM1 = new stdClass();
            $tipoM1->id = 'mov1';
            $tipoM1->label = 'Tipo Movimiento';
            if ($metasm['tmf_id'] == 'A' && ($metasm['tri_id'] == 2 || $metasm['tri_id'] == 1)) {
                // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
                $tipoM1->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM1->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
            } elseif ($metasm['tmf_id'] == 'R' && ($metasm['tri_id'] == 2 || $metasm['tri_id'] == 1)) {
                // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
                $tipoM1->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM1->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
            } else {
                // Si no es "A", puedes dejarlo en blanco o definir otro comportamiento
                $tipoM1->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM1->selecciona = '';  // Ninguna seleccionada
            }
            $comboMovimiento1 = $this->ponCombo($tipoM1);

            $tipoM2 = new stdClass();
            $tipoM2->id = 'mov2';
            $tipoM2->label = 'Tipo Movimiento';
            $tipoM2->opciones = ['' => 'Seleccione una opción'];
            $comboMovimiento2 = $this->ponCombo($tipoM2);
            $tipoM2->disabled = true;

            $tipoM3 = new stdClass();
            $tipoM3->id = 'mov3';
            $tipoM3->label = 'Tipo Movimiento';
            if ($metasm['tmf_id'] == 'A' && ($metasm['tri_id'] == 4 || $metasm['tri_id'] == 3)) {
                // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
                $tipoM3->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM3->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
            } elseif ($metasm['tmf_id'] == 'R' && ($metasm['tri_id'] == 4 || $metasm['tri_id'] == 3)) {
                // Si es igual a "A", marcar "AMPLIACIÓN" como seleccionada
                $tipoM3->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM3->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
            } else {
                // Si no es "A", puedes dejarlo en blanco o definir otro comportamiento
                $tipoM3->opciones = [
                    ''  => 'Seleccione una opción',
                    'A' => 'AMPLIACIÓN',
                    'R' => 'REDUCCIÓN',
                ];
                $tipoM3->selecciona = '';  // Ninguna seleccionada
            }
            $comboMovimiento3 = $this->ponCombo($tipoM3);

            $form = new form($form);

            $wr = new WebResponse();

            $form->mixHtml('

                <fieldset class="col-md-12 col-xs-12">
                    <legend>NOMBRE DEL INDICADOR</legend>
                    ' . $this->ponTextArea($descripcion) . '
                </fieldset>
                <fieldset class="col-md-12 col-xs-12">
                    <label for="ind_nombre" class="col-sm-3 control-label">NOMBRE DE LA VARIABLE</label>
                    ' . $this->ponTextArea($variable) . '
                </fieldset>
                <fieldset class="col-md-12 col-xs-12">
                    <label for="ind_nombre" class="col-sm-3 control-label">MÉTODO DE CÁLCULO</label>
                    ' . $this->ponTextArea($metodocal) . '
                </fieldset>
                <fieldset class="col-md-12 col-xs-12">
                    <label for="ind_nombre" class="col-sm-3 control-label">UNIDAD DE MEDIDA</label>
                    ' . $this->ponTextArea($medida) . '
                </fieldset>
                <fieldset class="col-md-12 col-xs-12">
                        <label for="ind_nombre" class="col-sm-3 control-label">TIPO DE INDICADOR</label>
                        ' . $this->ponTextArea($tipo) . '
                </fieldset>
                <fieldset class="col-md-8 col-xs-8">
                    <legend><small>' . $legendp . '</small></legend>
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                ' . $lb . $lbValor . $anterior . $anteriorValor . $numerador . $denominador . $lbIndicador . '
                            </tr>
                        </tbody>
                    </table>
                    <legend><small>' . $legend . '</small></legend>
                    <table class="table table-bordered table-striped ">
                        <tbody>
                            <tr>
                                ' . $deseadoNumerador . $deseadoDenominador . $t0 . $t0Valor . $t1 . $original . $anteriorIndicador . '
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
                                            <div class="table-responsive">
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
                                    </div>
                                    <div class="col">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">
                                                <h3 class="panel-title">2° Trimestre</h3>
                                            </div>
                                            <div class="table-responsive">
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
                                    </div>
                                    <div class="col">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">
                                                <h3 class="panel-title">3° Trimestre</h3>
                                            </div>
                                            <div class="table-responsive">
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
                                    </div>
                                    <div class="col">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">
                                                <h3 class="panel-title">4° Trimestre</h3>
                                            </div>
                                            <div class="table-responsive">
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
                                    </div>
                                    <div class="col">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">TOTALES PARCIALES</h3>
                                                </div>
                                                <div class="table-responsive">
                                                    <div class="panel-body">
                                                        <table class="table table-condensed">
                                                            <tbody>
                                                                <tr class="d-flex">
                                                                    <td class="col-10"><br><br><br></td>
                                                                </tr>
                                                                <tr class="d-flex">
                                                                    <td class="col-10"><br></td>
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
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
            ');

            $this->mixHtml('
                    <h4><br>Meta de Indicador ' . $this->prm->ind_id . ' de nivel Componente</h4>
                    <div class="panel-body">
                        <div class="row">' . $form->ponForm() . '
                    </div>
            ');

            $this->setContenedor('div-metas');

            $btnGuardarMeta = is_object($btnGuardarM) ? $this->ponBoton($btnGuardarM) : '';
            $this->agregaTools($btnGuardarMeta);

            $this->mixScript('
                var calculoLBIndicador=0;
                var calculoVPIndicador=0;

                switch(' . $umi . '){
                    case 2:case 3:case 4:    // RAZÓN
                        $("#numerador,#denominador").blur(function() {
                            if($("#denominador").val() != 0){
                                if(' . $umi . ' == 2){ //porcentaje
                                    calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) ) * 100;
                                }else{ // razón
                                    calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) );
                                }
                                $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                            }else{
                                $("#lbIndicador").val(0);
                            }
                        });

                        //calendario 1
                        $("#num_trimestre2,#num_trimestre4").blur(function() {

                            if(' . $umi . ' == 2){
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())*100);
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())*100);
                            } else {
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val()));
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()));
                            }

                            if(' . $datos['tiv'] . ' == 1){
                                $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                                $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                $("#deseadoNumerador").val(formatNumber($("#num_total").val()));
                                if(' . $umi . ' == 2){
                                    $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                    $("#totalParcial").val($("#original").val() + "%");
                                } else {
                                    $("#original").val((($("#num_total").val() / $("#den_total").val())).toFixed(4));
                                    $("#totalParcial").val($("#original").val());
                                }
                            } else {
                                if(' . $umi . ' == 2){ //porcentaje
                                    $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                                } else {
                                    $("#totalParcial").val(formatNumber(porc_trimestre4));
                                }
                                $("#num_total").val(unformatNumber($("#num_trimestre4").val()));
                                $("#deseadoNumerador").val(formatNumber($("#num_total").val()));
                                $("#original").val(formatNumber(porc_trimestre4));
                            }

                            if(' . $umi . ' == 2){ //porcentaje
                                $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                                $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                            } else {
                                $("#rpar_trimestre2").val(formatNumber(porc_trimestre2));
                                $("#rpar_trimestre4").val(formatNumber(porc_trimestre4));
                            }
                        });

                        //calendario 2
                        $("#den_trimestre2,#den_trimestre4").blur(function() {

                            if(' . $umi . ' == 2){
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())*100);
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())*100);
                            } else {
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val()));
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()));
                            }

                            if(' . $datos['tiv'] . ' == 1){

                                $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                $("#deseadoDenominador").val(formatNumber($("#den_total").val()));
                                if(' . $umi . ' == 2){
                                    $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                    $("#totalParcial").val($("#original").val() + "%");
                                } else {
                                    $("#original").val((($("#num_total").val() / $("#den_total").val())).toFixed(4));
                                    $("#totalParcial").val($("#original").val());
                                }
                            } else {
                                if(' . $umi . ' == 2){ //porcentaje
                                    $("#totalParcial").val(formatNumber(porc_trimestre4) +"%");
                                } else {
                                    $("#totalParcial").val(formatNumber(porc_trimestre4));
                                }
                                $("#den_total").val(unformatNumber($("#den_trimestre4").val()));
                                $("#deseadoDenominador").val(formatNumber($("#den_total").val()));
                                $("#original").val(formatNumber(porc_trimestre4));
                            }

                            if(' . $umi . ' == 2){ //porcentaje
                                $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                                $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                            } else {
                                $("#rpar_trimestre2").val(formatNumber(porc_trimestre2));
                                $("#rpar_trimestre4").val(formatNumber(porc_trimestre4));
                            }
                        });
                        break;
                    case 5: // TASA
                        $("#anteriorValor, #lbValor").blur(function(){
                            calculoLBIndicador=((unformatNumber($("#anteriorValor").val()) / unformatNumber($("#lbValor").val()))-1) * 100;
                            $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                        });

                        //calendario 1
                        $("#num_trimestre2,#num_trimestre4").blur(function() {

                            porc_trimestre2 = formatNumber((unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())-1)*100);
                            porc_trimestre4 = formatNumber((unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())-1)*100);

                            if(' . $datos['tiv'] . ' == 1){
                                $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                                $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                $("#totalParcial").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100) + "%");
                                $("#original").val(formatNumber($("#num_total").val()));
                                $("#anteriorIndicador").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100));
                                $("#t0Valor").val(formatNumber($("#den_total").val()));
                            } else {
                                $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                                $("#num_total").val(unformatNumber($("#num_trimestre4").val()));
                                $("#den_total").val(unformatNumber($("#den_trimestre4").val()));
                                $("#original").val(formatNumber($("#num_trimestre4").val()));
                                $("#anteriorIndicador").val(porc_trimestre4);
                                $("#t0Valor").val(formatNumber($("#den_total").val()));
                            }

                            $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                            $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                        });


                        //calendario 2
                        $("#den_trimestre2,#den_trimestre4,#lbValor").blur(function(){

                            porc_trimestre2 = formatNumber((unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())-1)*100);
                            porc_trimestre4 = formatNumber((unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())-1)*100);

                            if(' . $datos['tiv'] . ' == 1){
                                $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                                $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                                $("#totalParcial").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100) + "%");
                                $("#macm_original").val(formatNumber($("#num_total").val()));
                                $("#anteriorIndicador").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100));
                                $("#t0Valor").val(formatNumber($("#den_total").val()));
                            } else {
                                $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                                $("#num_total").val(formatNumber($("#num_trimestre4").val()));
                                $("#den_total").val(formatNumber($("#den_trimestre4").val()));
                                $("#macm_original").val(formatNumber($("#num_total").val()));
                                $("#anteriorIndicador").val(porc_trimestre4);
                                $("#t0Valor").val(formatNumber($("#den_total").val()));
                            }

                            $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                            $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                        });

                        break;
                    default: // todas las demás unidades de medida del indicador

                        $("#ori_trimestre2,#ori_trimestre4").blur(function() {

                            $("#rpar_trimestre2").val($("#ori_trimestre2").val());
                            $("#rpar_trimestre4").val($("#ori_trimestre4").val());

                            if(' . $datos['tiv'] . ' == 1){
                                $("#num_total").val(formatNumber(unformatNumber($("#ori_trimestre2").val())+ unformatNumber($("#ori_trimestre4").val())));
                                $("#ori_total").val(formatNumber(unformatNumber($("#ori_trimestre2").val()) + unformatNumber($("#ori_trimestre4").val())));
                                $("#original").val(formatNumber(unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre4").val())));
                            } else {
                                $("#ori_total").val(unformatNumber($("#ori_trimestre4").val()));
                                $("#num_total").val(unformatNumber($("#ori_trimestre4").val()));
                                $("#original").val(unformatNumber($("#ori_trimestre4").val()));
                            }
                        });

                        if("' . $abc . '" == "c"){
                            $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur();
                        }
                        break;
                }
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaModificacion").click(); e.stopPropagation(); });
                $("#btnRegresarTablaModificacion").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaMetas",
                        omo_id:"' . $this->prm->omo_id . '",
                        mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                        mme_ur_id:"' . $this->prm->mme_ur_id . '",
                        mme_id:' . $this->prm->mme_id . '
                        ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                        ' . (isset($this->prm->prg_id) ? ',prg_id:"' . $this->prm->prg_id . '"' : '') . '
                        ' . (isset($this->prm->sprs_id) ? ',sprs_id:"' . $this->prm->sprs_id . '"' : '') . '
                        ' . (isset($this->prm->eri_id) ? ',eri_id:"' . $this->prm->eri_id . '"' : '') . '
                    });
                    return false;
                });
            ');
        }

        $this->mixScript('
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
                alertify.confirm("ELIMINAR MOVIMIENTO","¿ Realmente desea eliminar el movimiento <h5 style=\"display:inline\"></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"borrarDatosModificados",
                            omo_id:"' . $this->prm->omo_id . '",
                            mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                            mme_ur_id:"' . $this->prm->mme_ur_id . '",
                            mme_id:"' . $this->prm->mme_id . '",
                            prgs_id:"' . $this->prm->prg_id . '",
                            sprs_id:"' . $this->prm->sprs_id . '",
                            epfi_id:"' . $this->prm->eri_id . '"
                            ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                                : '') . '
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
        ');
    }

    //METODO QUE BORRA LAS METAS MODIFICADAS
    private function borrarDatosModificados()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
        ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $id = $_SESSION[_S]['usr_id'];
        $this->conectar();
        $this->setSql('
            SELECT * FROM scp_DetalleModificacionMetaComponente
            WHERE omo_id= ?
            AND mme_gpo_id= ?
            AND mme_ur_id= ?
            AND mme_id= ?
            AND prgs_id = ?
            AND sprs_id = ?
            AND epfi_id = ?;
        ');
        $params = [
            $this->prm->omo_id,
            $this->prm->mme_gpo_id,
            $this->prm->mme_ur_id,
            $this->prm->mme_id,
            $this->prm->prgs_id,
            $this->prm->sprs_id,
            $this->prm->epfi_id,
        ];
        $this->consultar($params);
        $this->desconectar();

        //Si hay registros de metas de este folio se borran , sino unicamente se dirige a la funcion tablaFolios
        if ($this->getNumeroRegistros() > 0) {

            $this->conectar();
            $this->setSql('
                SET @bit_observacion = ?,
                @usr_ip=INET_ATON(?),
                @usr_id = ?;
            ');
            $params = [
                'Movimiento eliminado',
                $ip,
                $id,
            ];
            $this->consultar($params);

            $this->setSql('
                DELETE FROM scp_DetalleModificacionMetaComponente
                WHERE omo_id= ?
                AND mme_gpo_id= ?
                AND mme_ur_id= ?
                AND mme_id= ?
                AND prgs_id = ?
                AND sprs_id = ?
                AND epfi_id = ?;
            ');
            $params = [
                $this->prm->omo_id,
                $this->prm->mme_gpo_id,
                $this->prm->mme_ur_id,
                $this->prm->mme_id,
                $this->prm->prgs_id,
                $this->prm->sprs_id,
                $this->prm->epfi_id,
            ];
            $this->consultar($params);
            $this->desconectar();

            $this->mixScript('
                notify("Movimiento eliminado");
            ');
        }

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
        $this->setSql('
            SELECT mme_id FROM scp_DetalleModificacionMetaComponente
            WHERE omo_id = ?
            AND mme_gpo_id = ?
            AND mme_ur_id = ?
            AND mme_id = ?;
        ');
        $params = [
            $omo_id,
            $mme_gpo_id,
            $mme_ur_id,
            $mme_id,
        ];
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
        $btnRechazar = '';

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
        $btnRegresar->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->class = 'me-2';
        $btnRegresar->color = 'gris';

        $tabla = new stdClass();
        $tabla->titulo = 'ADECUACION DE METAS MIR';
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
        $td->casos->verdadero->class = $clase . ' btn btn-outline-success btn-xs';
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
                        $and->valor = 2;
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

        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $and .= "
                    AND omo_id = 'A'
                    AND emo_id IN (2,5)
                    AND mme_subsecretaria=2
                ";
                break;
            case 'autorizacionUR':
                $and .= "
                    AND omo_id = 'A'
                    AND emo_id IN (4,7)
                    AND mme_subsecretaria=2
                ";
                break;
            case 'validacionSEFIN':
                $and .= ' AND emo_id IN (10,13) ';
                break;
            case 'autorizacionSEFIN':
                $and .= ' AND emo_id IN (12) ';
                break;
        }

        $sql = "
            SELECT *,omo_id origenModificacion,'ADECUACION DE METAS MIR' AS tmd_nombre, mme_Subsecretaria,
            1 habilitado
            FROM scp_ModificacionMeta
            INNER JOIN cat_EstadoModificacion using(emo_id)
            INNER JOIN cat_ModalidadModificacion using(mmd_id)
            INNER JOIN cat_OrigenModificacion using(omo_id)
            INNER JOIN scp_DetalleModificacionMetaComponente d USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            WHERE
            mme_gpo_id = " . (isset($this->prm->mme_gpo_id) ? $this->prm->mme_gpo_id : $_SESSION[_S]['clasAdm-gpo']) . '
            AND mme_ur_id = ' . (isset($this->prm->mme_ur_id) ? $this->prm->mme_ur_id : $_SESSION[_S]['clasAdm-ur']) . "
            $and
            GROUP BY mme_id
            ORDER BY mme_id;
        ";

        $this->setSql($sql);

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
                    ' . (($this->nivelRevision != '') ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . ',
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

    /*METODO QUE CAMBIA DE ESTADO EL FOLIO A PARTIR DEL ESTADO 2*/
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
                    $emme->setEmo_id(6);
                    $men = 'Planeacion';
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
                    $vobo = 2;
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
                    $observacion = 'Folio ' . $this->prm->mme_id . 'autorizado por PLANEACIÓN';
                    $this->prm->observacion = $observacion;
                    $emme->setEmo_id(15);
                    $emme->select();
                    $mme->conectar();
                    $operacion = "
                        SELECT IF (MAX(mme_operacion) IS NULL,1,MAX(mme_operacion)+1) AS operacion
                        FROM scp_ModificacionMeta
                        WHERE tfm_id = 'C';
                    ";
                    $numOperacion = new AccesoDatos();
                    $numOperacion->conectar();
                    $numOperacion->setSql($operacion);
                    $numOperacion->consultar();
                    $rOperacion = $numOperacion->extraer();
                    $mme->setEmo_id($emme->getEmo_id());
                    $mme->setFechaAutorizacion(date('Y-m-d H:i:s'));
                    $mme->setOperacion($rOperacion['operacion']);
                    $mme->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $this->usr_id . '",@bit_observacion="' . $this->prm->observacion . '";');
                    $mme->consultar();
                    $mme->update(false);
                    $mme->select();

                    $this->prm->abc = 'c';

                    $metas = '
                        SELECT * FROM scp_DetalleModificacionMetaComponente
                        WHERE omo_id = ?
                        AND mme_gpo_id = ?
                        AND mme_ur_id = ?
                        AND mme_id = ?
                    ';
                    $params = [
                        $this->prm->omo_id,
                        $this->prm->mme_gpo_id,
                        $this->prm->mme_ur_id,
                        $this->prm->mme_id,
                    ];
                    $metasmod = new AccesoDatos();
                    $metasmod->conectar();
                    $metasmod->setSql($metas);
                    $metasmod->consultar($params);

                    while ($registros = $metasmod->extraer()) {
                        $indsql = '
                            SELECT COUNT(DISTINCT nim_id,ind_id) cuantos, MAX(ind_id) AS ind_id, ind_nombre, MAX(epfi_id) AS eri_id,MAX(umi_id) AS umi_id, pdf_id
                            FROM enc_ProductoFinalIndicador enc
                            JOIN cat_Subprograma USING(pdf_id)
                            INNER JOIN cat_Indicador USING(ind_id,nim_id)
                            INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                            WHERE enc.sts_id = 1 AND nim_id = 3 
                            AND prg_id = ?
                            AND spr_id = ?
                            AND gpo_id = ?
                            AND ur_id = ?
                            HAVING cuantos != 0;
                        ';
                        $params = [
                            $registros['prgs_id'],
                            $registros['sprs_id'],
                            $this->prm->mme_gpo_id,
                            $this->prm->mme_ur_id,
                        ];
                        $indicador = new AccesoDatos();
                        $indicador->conectar();
                        $indicador->setSql($indsql);
                        $indicador->consultar($params);

                        $datos = $indicador->extraer();
                        $this->prm->unidadMedida = $datos['umi_id'];
                        $this->prm->den_trimestre4 = $registros['mcot_denominadorT4'];
                        $this->prm->den_trimestre2 = $registros['mcot_denominadorT2'];
                        $this->prm->num_trimestre2 = $registros['mcot_numeradorT2'];
                        $this->prm->num_trimestre4 = $registros['mcot_numeradorT4'];
                        $this->prm->lbValor = $registros['mco_lbValor'];
                        $this->prm->original = $registros['mco_original'];
                        $this->prm->gpo_id = $registros['gpo_id'];
                        $this->prm->ur_id = $registros['ur_id'];
                        $this->prm->prgs_id = $registros['prgs_id'];
                        $this->prm->sprs_id = $registros['sprs_id'];
                        $this->prm->epfi_id = $registros['epfi_id'];
                        $this->trimestreCierre = $registros['tri_id'];
                        $this->prm->trimestreCierre = $this->trimestreCierre;
                        $this->prm->descripcion = $datos['ind_nombre'];
                        $this->prm->anio = $registros['mco_anio'];
                        $this->prm->lbIndicador = $registros['mco_lbIndicador'];
                        $this->prm->lb = $registros['mco_lb'];

                        switch ($this->prm->unidadMedida) {
                            case 2:
                            case 4: //RAZÓN

                                $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                                $this->prm->num_trimestre4 = $this->prm->num_trimestre4;
                                if ($this->prm->den_trimestre4 != 0) {
                                    $this->prm->ori_trimestre2 = ($this->prm->num_trimestre2 / $this->prm->den_trimestre4) * 100;
                                } else {
                                    $this->prm->ori_trimestre2 = 0;
                                }
                                if ($this->prm->den_trimestre4 != 0) {
                                    $this->prm->ori_trimestre4 = ($this->prm->num_trimestre4 / $this->prm->den_trimestre4) * 100;
                                } else {
                                    $this->prm->ori_trimestre4 = 0;
                                }

                                $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                                $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                                $numerador = $this->prm->num_trimestre2 + $this->prm->num_trimestre4;
                                $denominador = $this->prm->den_trimestre2 + $this->prm->den_trimestre4;
                                if ($denominador != 0) {
                                    if ($this->prm->unidadMedida == 2) {
                                        $this->prm->original = ($numerador / $denominador) * 100;
                                        $this->prm->modificado = ($numerador / $denominador) * 100;
                                    } else {
                                        $this->prm->original = $numerador / $denominador;
                                        $this->prm->modificado = $numerador / $denominador;
                                    }
                                } else {
                                    $this->prm->original = 0;
                                    $this->prm->modificado = 0;
                                }
                                break;
                            case 5: //TASA
                                $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                                $this->prm->num_trimestre4 = $this->prm->num_trimestre4;
                                $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                                $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;
                                $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                                $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                                $t1 = $this->prm->num_trimestre2 + $this->prm->num_trimestre4;
                                $t0 = $this->prm->den_trimestre2 + $this->prm->den_trimestre4;

                                if ($this->prm->lbValor != 0) {
                                    $this->prm->lbIndicador = ($t0 / $this->prm->lbValor - 1) * 100;
                                } else {
                                    $this->prm->lbIndicador = 0;
                                }

                                if ($t0 != 0) {
                                    $this->prm->anteriorIndicador = ($t1 / $t0 - 1) * 100;
                                    $this->prm->modificado = ($t1 / $t0 - 1) * 100;
                                } else {
                                    $this->prm->anteriorIndicador = 0;
                                }

                                break;
                            case 3: // PORCENTAJE
                                $this->prm->original = $this->prm->original;
                                $this->prm->modificado = $this->prm->original;
                                break;
                            default:
                                $this->prm->original = $this->prm->ori_trimestre2 + $this->prm->ori_trimestre4;
                                $this->prm->modificado = $this->prm->ori_trimestre2 + $this->prm->ori_trimestre4;
                                break;
                        }

                        $this->prm->origen = 'ControladorRegistroModificaciones MIR S_1_9';
                        $this->prm->tri_id = $this->trimestreCierre;
                        $this->prm->auxoriginal = $this->prm->original;
                        HistoricoModificacionMetaComponente::abc($this->prm);
                    }
                    $metasmod->desconectar();

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
                    break;
            }

            $this->prm->omo_id = $mme->getOmo_id();
            $this->prm->mme_gpo_id = $mme->getMme_gpo_id();
            $this->prm->mme_ur_id = $mme->getMme_ur_id();
            $this->prm->mme_id = $mme->getMme_id();
            $this->prm->emo_id = $emme->getEmo_id();
            $this->prm->observacion = (isset($this->prm->observacion)) ? $this->prm->observacion
                : $emme->getNombre();
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
            $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
                != '::1') ? AccesoDatos::getIP() : '127.0.0.1';
            if ($ban == 1) {
                $this->bitacora('M', $this->prm);
            }
        } else {
            $this->mixScript('notify("' . utf8Encode('Error ' . $datos[1]['numero'] . '. ' . $datos[1]['mensaje']) . '","error",false);');
        }
    } // Fin validacionFolio

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

        $modal = '
            <div id="myModalMetaComponente" class="modal fade in" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabelComponente" aria-hidden="false" style="display: block; padding-top: 100px;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabelComponente">Motivo de rechazo</h4>
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
            </div>
        ';

        $this->mixHtml($modal);

        $this->mixScript('
            $("#myModalMetaComponente").modal("show"); 
            $(".cerrar").click(function(){ $("#myModalMetaComponente").remove(); });
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
                    $("#myModalMetaComponente").modal("hide");
                }
            });
        ');
    }

    private function rechazarFolio()
    {
        $mme = new ModificacionMeta($this->prm);
        /* Instanciamos el estado de la modificacion */
        $emme = new EstadoModificacion();
        $mensaje = true;
        $notify = '';
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
                    $emme->setEmo_id(7);
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
        $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
            != '::1') ? AccesoDatos::getIP() : '127.0.0.1';
        if ($mensaje) {
            $this->bitacora('M', $this->prm);
        }
    }

    private function bitacora($tipo, $prm)
    {
        $sql = '';
        if ($tipo != '') {
            if (isset($prm->mme_id) && $prm->mme_id != '') {
                $prm->abc = 'a';
                if ($tipo == 'M') {

                    BitacoraModificacionMetaMir::abc($prm);
                }
                $mme_gpo_id = $prm->mme_gpo_id;
                $mme_ur_id = $prm->mme_ur_id;

                $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
                    != '::1') ?
                    $_SESSION[_S]['ses_ip'] : '127.0.0.1';
                $usr_id = $_SESSION[_S]['usr_id'];

                $rutalog = 'log/AdecuacionesMetas-' . $_SESSION[_S]['usr_id'] . '-' . $mme_gpo_id . $mme_ur_id . '.txt';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d H-i-s');
                foreach ($prm as $field => $value) {
                    if (is_string($value)) {
                        $tempdata .= '|' . $field . ' = ' . $value;
                    }
                }
                $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
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
        $btn_imprimir->color = 'rojo';
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
                AND gpo_id = 1 AND ur_id=15 AND dir_id=6
                AND sts_id=1 OR usr_id = 449;';
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
        $firma2->label = 'Firma 3';
        $firma2->comoAtr = 'usr_id,level';
        $firma2->search = true;

        if ($this->subsec == 3) {
            $unidadSel = ($unidad == 0) ? ' uni_id in(16,18) ' : ' uni_id = ' . $unidad;
            $departamento = ($departamento == 0) ? $departamentos : ' AND dep_id = ' . $departamento;
            $firma2->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=5
                AND ssc_id=' . $this->subsec . ' AND dir_id=7
                AND uni_id in(16,18)
                AND sts_id=1';

            $jefeDpto = '
                SELECT usr_id
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id= 5
                AND ssc_id=? AND dir_id=?
                AND ' . $unidadSel . ' ' . $departamento . '
                AND sts_id=1 LIMIT 1';

            $c = new AccesoDatos();
            $c->conectar();
            $c->setSql($jefeDpto);
            $c->consultar([$this->subsec,$direccion]);
            $d = $c->extraer();
            $c->desconectar();
            $idJefeDpto = $d['usr_id'];
            if ($c->getNumeroRegistros() > 0) {
                $firma2->selecciona = $d['usr_id'];
            }
        } elseif ($this->subsec == 2) {
            $firma2->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15 AND nvj_id=5
                AND ssc_id=' . $this->subsec . ' AND dir_id=6
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
        $firma3->label = 'Firma 4';
        $firma3->comoAtr = 'usr_id,level';
        $firma3->search = true;
        if ($this->subsec == 3) {
            $firma3->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,"3" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 5 AND ssc_id = ' . $subSecretaria . '
                AND dir_id = ' . $direccion . '
                AND uni_id=' . $unidad . '
                AND sts_id = 1 ';
        } elseif ($this->subsec == 2) {
            $firma3->sql = '
                SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"3" AS level
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = 1 AND ur_id = 15
                AND nvj_id = 6
                AND ssc_id=' . $this->subsec . '
                AND dir_id = 6
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
        $firma4->search = true;
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

        $firma5 = new stdClass();
        $firma5->id = 'cbofirma5';
        $firma5->name = 'control';
        $firma5->class = 'cbofirmas';
        $firma5->required = true;
        $firma5->enDiv = true;
        $firma5->camposVal = 'usr_id';
        $firma5->agrupaPor = 'usr_id';
        $firma5->campoLabelAgrupacion = 'usr_nombre';
        $firma5->mensaje = 'Selecciona';
        $firma5->label = 'Firma 2';
        $firma5->comoAtr = 'usr_id,level';
        $firma5->search = true;
        if ($this->subsec == 3) {
            $firma5->sql = '
                    SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"5" AS level
                    FROM cat_Usuario
                    INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                    INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                    WHERE ssc_id = ' . $this->subsec . '
                    AND gpo_id = 1 AND ur_id=15 AND usr_id = ' . $_SESSION[_S]['usr_id'] . '
                    AND sts_id=1;';
        } elseif ($this->subsec == 2) {
            $firma5->sql = '
                    SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"5" AS level
                    FROM cat_Usuario
                    INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                    INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                    WHERE ssc_id = ' . $this->subsec . '
                    AND nvj_id=3
                    AND gpo_id = 1 AND ur_id=15 AND dir_id=6
                    AND sts_id=1 OR usr_id = 449;';
        }

        $scri = '';

        $firma5 = $this->ponCombo($firma5);

        $scri = '$(".cbofirmas").change();';

        $html = '';
        $html .= '
            <fieldset class="col-md-12 col-xs-12">
                <legend>ADECUACIONES DE METAS MIR COMPONENTE</legend>
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
                        ' . $firma5 . '
                        </fieldset>
                    <fieldset style="align:center" class="col-md-4" >
                    ' . $firma2 . '
                    </fieldset>
                    <div class="row mb-4">
                        <fieldset style="align:center" class="col-md-4" >
                            ' . $firma3 . '
                        </fieldset>
                            <fieldset style="align:center" class="col-md-4" >
                            ' . $firma4 . '
                        </fieldset>
                    </div>
                    
                    
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
                        <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma5">
                            ...
                        </fieldset>
                        <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma2">
                            ...
                        </fieldset>
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <fieldset style="align:center" class="col-md-6 col-xs-6" id="firma3">
                            ...
                        </fieldset>
                        <fieldset style="align:center" class="col-md-6 col-xs-6 id="firma4">
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
                fm5 ={t:$("#t5").val(),n:$("#n5").val(),c:$("#c5").val()};
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
                    f5:fm5,
                    dep:$("#usr_idfirma").find("option:selected").val(),
                },true);
            });

            $("#btn_reporte").click(function(){
                $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesMetasMir.php");
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
        $this->setContenedor('firma' . $l);
        $sql = 'SELECT usr_titulo, usr_puesto, usr_nombre,  usr_paterno, usr_materno
        FROM cat_Usuario
        INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
        WHERE usr_id = ?;';
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

                    $this->prm->firma4 = $this->strtMayus(' ');

                    $this->prm->cargo4 = $this->strtMayus(' ');

                    $this->prm->firma5 = $this->strtMayus($this->prm->f5->t == '[ sin titulo ]'
                        ?
                        $this->prm->f5->n : $this->prm->f5->t . ' ' . $this->prm->f5->n);

                    $this->prm->cargo5 = $this->strtMayus($this->prm->f5->c == '[ sin puesto ]'
                        ?
                        ' ' : $this->prm->f5->c);

                    $this->prm->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
                        != '::1') ?
                        $_SESSION[_S]['ses_ip'] : '127.0.0.1';
                    $this->prm->usr_id = $_SESSION[_S]['usr_id'];

                    //Registra o actualiza las firmas
                    $sql = "
                        INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                        mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                        mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                        mme_firma6,mme_cargo7,mme_firma7,usr_id)
                        VALUES (
                            ?,?,?,?,?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?
                        )
                        ON DUPLICATE KEY UPDATE
                        mme_cargo3='" . $this->prm->cargo5 . "',
                        mme_firma3='" . $this->prm->firma5 . "',
                        mme_cargo4='" . $this->prm->cargo1 . "',
                        mme_firma4='" . $this->prm->firma1 . "',
                        mme_cargo5='" . $this->prm->cargo2 . "',
                        mme_firma5='" . $this->prm->firma2 . "',
                        mme_cargo6='" . $this->prm->cargo3 . "',
                        mme_firma6='" . $this->prm->firma3 . "',
                        mme_cargo7='" . $this->prm->cargo4 . "',
                        mme_firma7='" . $this->prm->firma4 . "';";

                    $params = [
                        $this->prm->omo_id,
                        $this->prm->mme_gpo_id,
                        $this->prm->mme_ur_id,
                        $this->prm->mme_id,
                        '', '', '', '', // se agregan los cargos ya que con el modo estricto no se permite guardar el registro
                        $this->prm->cargo5,
                        $this->prm->firma5,
                        $this->prm->cargo1,
                        $this->prm->firma1,
                        $this->prm->cargo2,
                        $this->prm->firma2,
                        $this->prm->cargo3,
                        $this->prm->firma3,
                        $this->prm->cargo4,
                        $this->prm->firma4,
                        $_SESSION[_S]['usr_id'],
                    ];
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

if (isset($_SESSION[_S]['prm']['S']['1']['9']) && ($ur || $planeacion)) {
    new ControladorRegistroModificacionMetasMir();
} else {
    WebResponse::sinPermisos();
}
