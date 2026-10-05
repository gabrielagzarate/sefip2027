<?php
/**
 * Description of ControladorMinFondeo
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective:
 * Regla de Negocio:
 * Permits:
 * */

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scp/Clc.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/Lote.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDetalle.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDocumento.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/EntidadMovimientoBancario.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/ProductosFinancieros.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/LoteFondeo.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/PropuestaManual.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_EliminarClcLoteFondeo.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_CancelarLoteFondeo.php';

class ControladorMinFondeo extends WebResponse
{
    public $escritura;
    public $validacion;
    public $historial;
    public $pantalla;
    public $usr_id;
    public $ip;
    public $prm2;

    public function __construct()
    {
        parent::__construct();

        $this->escritura = isset($_SESSION[_S]['prm']['T']['4']['4']['2']);
        $this->validacion = isset($_SESSION[_S]['prm']['T']['4']['4']['3']);
        $this->historial = isset($_SESSION[_S]['prm']['T']['4']['4']['87']);
        $this->pantalla = "tes/ministracion/ControladorMinFondeo.php";
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] :
        '127.0.0.1';

        switch ($this->metodo) {
            case 'generacionFondeo':
                $this->generacionFondeo();
                break;
            case 'validacionFondeo':
                $this->validacionFondeo();
                break;
            case 'llenaTablaGenFondeo':
                $this->llenaTablaGenFondeo();
                break;
            case 'detalleGenFondeo':
                $this->detalleGenFondeo();
                break;
            case 'llenaTablaDetGenFondeo':
                $this->llenaTablaDetGenFondeo();
                break;
            case 'cambiaEstadoLote':
                $this->cambiaEstadoLote();
                break;
            case 'llenaTablaAutorizaLote':
                $this->llenaTablaAutorizaLote();
                break;
            case 'eliminaDetalle':
                $this->eliminaDetalle();
                break;
            case 'generacionFondeoDetalles':
                $this->generacionFondeoDetalles();
                break;
            case 'registraFondeo':
                $this->registraFondeo();
                break;
            case 'detalleFondeo':
                $this->detalleFondeo();
                break;
            case 'validaFondeo':
                $this->validaFondeo();
                break;
            case 'eliminaFondeo':
                $this->eliminaFondeo();
                break;
            case 'actualizaSaldos':
                $this->actualizaSaldos();
                break;
            case 'validarSALDO':
                $this->validarSALDO();
                break;
            case 'llenaTablaSaldos':
                $this->llenaTablaSaldos();
                break;
            case 'historialFondeo':
                $this->historialFondeo();
                break;
            case 'llenaTablaHistorial':
                $this->llenaTablaHistorial();
                break;
            case 'detalleHistorial':
                $this->detalleHistorial();
                break;
            case 'llenaTablaDetallesHistorial':
                $this->llenaTablaDetallesHistorial();
                break;
            case 'inversiones':
                $this->inversiones();
                break;
            case 'llenaTablaInversiones':
                $this->llenaTablaInversiones();
                break;
            case 'agregarInvercion':
                $this->agregarInvercion();
                break;
            case 'eliminarInversion':
                $this->eliminarInversion();
                break;
            case 'dibujaTablaValidacion':
                $this->dibujaTablaValidacion();
                break;
            case 'pintaTablaInversion':
                $this->pintaTablaInversion();
                break;
            case 'llenaTablaAutorizaInversion':
                $this->llenaTablaAutorizaInversion();
                break;
            case 'autorizaInvercion':
                $this->autorizaInvercion();
                break;
            case 'eliminarInversionAutoriza':
                $this->eliminarInversionAutoriza();
                break;
            case 'pintaTablaHistorial':
                $this->pintaTablaHistorial();
                break;
            case 'pintaTablaHisInversion':
                $this->pintaTablaHisInversion();
                break;
            case 'llenaTablaHistorialInversion':
                $this->llenaTablaHistorialInversion();
                break;
            case 'verificaSaldos':
                $this->verificaSaldos();
                break;
            case 'pintaTablaTraspasos':
                $this->pintaTablaTraspasos();
                break;
            case 'traspasos':
                $this->traspasos();
                break;
            case 'agregarTraspaso':
                $this->agregarTraspaso();
                break;
            case 'eliminarTraspaso':
                $this->eliminarTraspaso();
                break;
            case 'pintaHistorialMovimientos':
                $this->pintaHistorialMovimientos();
                break;
            case 'pintaTablaMovimiento':
                $this->pintaTablaMovimiento();
                break;
            case 'llenaTablaMovimiento':
                $this->llenaTablaMovimiento();
                break;
            case 'pintaTablaDetalleMovimiento':
                $this->pintaTablaDetalleMovimiento();
                break;
            case 'llenaTablaDetalleMovimiento':
                $this->llenaTablaDetalleMovimiento();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->mixScript('
            $("#toolsPantalla").html("");
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');
        if ($this->historial) {
            $this->mixHtml('
                <div class="row class="col-md-12 col-xs-12 "">
                    <legend>FONDEO</legend>

                    <fieldset class="col-md-2 col-xs-2 " id="">
                            <div titpp="HistorialFondeo" class="boton_opcion" id="historialFondeo">
                                <div class="boton_opcion_titulo" >Historial Fondeo</div>
                                <strong><img height="60" width="60" src="img/Revision_Folio.png"><br/></strong>
                                <small>Historial </small>
                            </div>
                    </fieldset>
                </div>
            ');
        } else {
            $this->mixHtml('
                <div class="row">
                    <legend>FONDEO</legend>
                </div>
                <div class="row">
                    <fieldset class="col-md-2 col-xs-2 col-md-offset-1"  id="">
                            <div titpp="Generar Fondeo" class="boton_opcion" id="genera_fondeo">
                                <div class="boton_opcion_titulo" >Generar Fondeo</div>
                                <strong><img height="60" width="60" src="img/Alta_Folio.png"><br/></strong>
                                <small>Generar </small>
                            </div>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2 " id="">
                            <div titpp="Autorizar Movimiento" class="boton_opcion" id="autoriza_fondeo">
                                <div class="boton_opcion_titulo" >Autorizar Fondeo</div>
                                <strong><img  height="60" width="60" src="img/Autoriza_Folio.png"><br/></strong>
                                <small>Autorizar </small>
                            </div>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2 " id="">
                            <div titpp="HistorialFondeo" class="boton_opcion" id="historialFondeo">
                                <div class="boton_opcion_titulo" >Historial Fondeo</div>
                                <strong><img height="60" width="60" src="img/Revision_Folio.png"><br/></strong>
                                <small>Historial </small>
                            </div>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2 " id="">
                            <div titpp="Saldos" class="boton_opcion" id="saldos">
                                <div class="boton_opcion_titulo" >Saldos</div>
                                <strong><img height="60" width="60" src="img/generalizacuenta.png"><br/></strong>
                                <small>Saldos </small>
                            </div>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2" id="">
                            <div titpp="Inversion" class="boton_opcion" id="inversion">
                                <div class="boton_opcion_titulo" >Inversión</div>
                                <strong><img height="60" width="60" src="img/generalizacuenta.png"><br/></strong>
                                <small>Inversión </small>
                            </div>
                    </fieldset>
                </div>
                <br>
                <div class="row">
                    <fieldset class="col-md-2 col-xs-2 col-md-offset-1" id="">
                            <div titpp="Traspasos" class="boton_opcion" id="traspasos">
                                <div class="boton_opcion_titulo" >Traspasos</div>
                                <strong><img height="60" width="60" src="img/generalizacuenta.png"><br/></strong>
                                <small>Traspasos </small>
                            </div>
                    </fieldset>
                </div>
            ');
        }

        $this->mixScript('
                $("#genera_fondeo").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"generacionFondeo"
                    });
                });

                $("#autoriza_fondeo").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"validacionFondeo"
                    });
                });

                $("#saldos").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"actualizaSaldos"
                    });
                });

                $("#historialFondeo").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"pintaHistorialMovimientos"
                    });
                });

                $("#inversion").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"inversiones"
                    });
                });

                $("#traspasos").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"traspasos"
                    });
                });

            ');

    }

    private function agregarInvercion()
    {
        //insertamos la inversion
        $recaudadora = isset($this->prm->cmb_recaudodora) ? $this->prm->cmb_recaudodora : "";
        $concentradora = isset($this->prm->cmb_concentradora) ? $this->prm->cmb_concentradora : "";
        $inv_importe = isset($this->prm->inv_importe) ? $this->prm->inv_importe : "";
        $inv_concepto = isset($this->prm->inv_concepto) ? $this->prm->inv_concepto : "";
        if ($inv_importe != "") {
            $inv_importe = trim($inv_importe, " $");
            $inv_importe = str_replace(',', '', $inv_importe);
        }
        if ($recaudadora == "" || $concentradora == "" || $inv_importe == "" || $inv_concepto == "") {
            $this->mixScript(' notify("FALTAN DATOS","error")');
            return 0;
        }
        $this->prm->ejercicio = date('Y');
        $this->prm->importe = $inv_importe;
        $this->prm->concepto = "INVERSION";
        $this->prm->observacion = $inv_concepto;
        $this->prm->fechaMovimiento = date('Y-m-d');
        $this->prm->cbs_id_cargo = $recaudadora;
        $this->prm->cbs_id_abono = $concentradora;
        $this->prm->emb_id = 1; //registrado
        $this->prm->tmb_id = 2; //Inversion
        $this->prm->msc_id = date('m');
        $this->prm->abc = "a";
        EntidadMovimientoBancario::abc($this->prm);
        $this->llenaTablaInversiones();
    }

    private function eliminarInversion()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        if ($mbn_id == "") {
            $this->mixScript(' notify("FALTAN DATOS","error");');
            return 0;
        }
        $this->prm->abc = "c";
        $this->prm->emb_id = 4; //cancelado
        $this->prm->mbn_id = $mbn_id;
        EntidadMovimientoBancario::abc($this->prm);
        $this->llenaTablaInversiones();
    }
    private function inversiones()
    {
        $ponBtnAgregar = "";
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));
        //pintamos el formulario
        $inputRecaudadora = new stdClass();
        $inputRecaudadora->sql = "SELECT cbs_id FROM ".BD_TESORERIA.".ban_CuentaBancariaSefin WHERE cbs_recaudadora=1";
        $inputRecaudadora->id = 'cmb_recaudodora';
        $inputRecaudadora->name = 'cmb_recaudodora';
        $inputRecaudadora->label = 'RECAUDADORA';
        $inputRecaudadora->enDiv = true;
        $inputRecaudadora->search = true;
        $inputRecaudadora->required = true;

        $inputConcentradora = new stdClass();
        $inputConcentradora->sql = "SELECT cbs_id FROM ".BD_TESORERIA.".ban_CuentaBancariaSefin WHERE cbs_concentradora=1";
        $inputConcentradora->id = 'cmb_concentradora';
        $inputConcentradora->name = 'cmb_concentradora';
        $inputConcentradora->label = 'CONCENTRADORA';
        $inputConcentradora->enDiv = true;
        $inputConcentradora->search = true;
        $inputConcentradora->required = true;

        $inputImporte = new stdClass();
        $inputImporte->id = 'inv_importe';
        $inputImporte->name = 'inv_importe';
        $inputImporte->placeholder = 'IMPORTE';
        $inputImporte->label = 'IMPORTE';
        $inputImporte->formato = 'moneda';
        $inputImporte->enDiv = true;
        $inputImporte->required = true;

        $fecha = date('Y-m-d');
        $inputConcepto = new stdClass();
        $inputConcepto->id = 'inv_concepto';
        $inputConcepto->name = 'inv_concepto';
        $inputConcepto->placeholder = 'CONCEPTO';
        $inputConcepto->value = 'BARRIDO DEL DIA ' . $fecha;
        $inputConcepto->label = 'CONCEPTO';
        $inputConcepto->enDiv = true;
        $inputConcepto->required = true;

        $botonAgregar = new stdClass();
        $botonAgregar->id = 'btn_AgragaInv';
        $botonAgregar->name = 'btn_AgragaInv';
        $botonAgregar->class = "btn btn-outline-success me-1";
        $botonAgregar->value = "Agregar";
        $botonAgregar->icon = "solid fa-floppy-disk me-2";
        $botonAgregar->click = '
          comunica("' . $this->pantalla . '",{
              metodo:"agregarInvercion",
              cmb_recaudodora: $("#cmb_recaudodora option:selected").val().split(" ")[0].trim(),
              cmb_concentradora: $("#cmb_concentradora option:selected").val().split(" ")[0].trim(),
              inv_importe: $("#inv_importe").val(),
              inv_concepto: $("#inv_concepto").val()
          });';

        if ($this->escritura) {
            $ponBtnAgregar = $this->ponBoton($botonAgregar);
        }
        $html = '
        <div class="row mb-2">
            <legend> INVERSIÓN
            </legend>
            <fieldset class="col-md-3 col-xs-3 " id="">
              ' . $this->ponCombo($inputRecaudadora) . '
            </fieldset>
            <fieldset class="col-md-3 col-xs-3 " id="">
              ' . $this->ponCombo($inputConcentradora) . '
            </fieldset>
            <fieldset class="col-md-3 col-xs-3 " id="">
              ' . $this->ponInput($inputImporte) . '
            </fieldset>
            <fieldset class="col-md-3 col-xs-3 " id="">
              ' . $this->ponInput($inputConcepto) . '
            </fieldset>
        </div>
        <div class="row mb-2">
            <fieldset class="col-md-3 col-xs-3 " id="">
              ' . $ponBtnAgregar . '
            </fieldset>
        </div>
        <div class="row mb-2">
          <fieldset id="divTablaCere" class="col-md-12 col-xs-12">
        </div>
      ';
        $this->mixHtml($html);
        //pintamos la tabla
        $tabla = new stdClass();

        $tabla->id = 'tbl-inversiones';
        $tabla->titulo = 'Inversiones';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaInversiones';
        $tabla->comunica->mbn_id = 'mbn_id';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DE CTA RECAUDADORA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARA CTA INVERSION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CANCELAR';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_importe';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_observacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->color = 'rojo';
        $td->icono = 'trash';
        $td->class = 'del_inversion';
        $td->id = 'del_inversion';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaInversiones();

    }

    private function llenaTablaInversiones()
    {
        $fecha = date('Y-m-d');
        $parametro = [
            'fecha' => $fecha,
        ];
        $sql = "SELECT
                cbs_id_cargo,
                cbs_id_abono,
                FORMAT(mbn_importe, 2) as mbn_importe,
                mbn_observacion,
                emb_nombre,
                mbn_id
            FROM ".BD_TESORERIA.".ban_MovimientoBancario
            INNER JOIN ".BD_TESORERIA.".cat_EstadoMovimientoBancario USING(emb_id)
            WHERE emb_id=1 AND tmb_id=2 AND mbn_fechaMovimiento= :fecha";

        $this->setSql($sql);
        $this->setParams($parametro);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-inversiones";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
        $(".del_inversion").click(function(){
            let mbn_id = $(this).attr("mbn_id");
            alertify.confirm("CANCELAR INVERSIÓN", "¿Realmente desea cancelar la inversión?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "eliminarInversion",
                        mbn_id: mbn_id
                    });
                },
                function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }
    private function llenaTablaSaldos()
    {
        $clabe = isset($this->prm->clabe) ? $this->prm->clabe : "";
        $fecha = isset($this->prm->fecha) ? $this->prm->fecha : "";
        $parametros = [];

        $sql = '
            SELECT
                pfi.cbs_id,
                CONCAT(pfi.pfi_ejercicio,"-",pfi.msc_id,"-",pfi.pfi_dia) as pfi_fecha,
                SUM(pfi.pfi_capital) AS pfi_capital,
                SUM(pfi.pfi_productoFinanciero) AS pfi_productoFinanciero,
                SUM(pfi.pfi_egreso) AS pfi_egreso,
                pfi.usr_fecha,
                CASE WHEN pfi.pfi_id = (
                    SELECT MIN(pfi_aux.pfi_id)
                    FROM '.BD_TESORERIA.'.ban_ProductoFinanciero AS pfi_aux
                    WHERE cbs_id = pfi.cbs_id

                    GROUP BY pfi_aux.cbs_id)
                THEN
                    ROUND(@s := pfi.pfi_capital + pfi.pfi_productoFinanciero - pfi.pfi_egreso, 2)
                ELSE
                    ROUND(@s := @s + pfi.pfi_capital + pfi.pfi_productoFinanciero - pfi.pfi_egreso, 2)
                END AS saldo
            FROM '.BD_TESORERIA.'.ban_ProductoFinanciero AS pfi
            INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING (cbs_id)
        ';

        if (empty($clabe)) {
            $this->mixScript('notify("FALTAN DATOS","error");');
            return 0;
        } else {
            $parametros += [
                'clabe' => $clabe,
            ];
            $sql .= 'WHERE pfi.cbs_id = :clabe
            ';
        }

        if (!empty($fecha)) {
            date_default_timezone_set('America/Mexico_City');
            $fecha = DateTime::createFromFormat('d/m/Y', $fecha);
            $parametros += [
                'pfi_ejercicio' => $fecha->format('Y'),
                'msc_id' => $fecha->format('m'),
                'pfi_dia' => $fecha->format('d'),
            ];
            $sql .= '
                AND pfi.pfi_ejercicio = :pfi_ejercicio
                AND pfi.msc_id = :msc_id
                AND pfi.pfi_dia = :pfi_dia
            ';
        }

        $sql .= 'GROUP BY pfi.cbs_id, pfi.pfi_ejercicio, pfi.msc_id, pfi.pfi_dia, pfi_id
                ORDER BY pfi.cbs_id, pfi.usr_fecha
            ';

        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-saldos";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

    }
    private function validarSALDO()
    {
        $clabe = isset($this->prm->clabe) ? $this->prm->clabe : "";
        //cargamos los datos del formulario
        if (trim($_FILES['arch']['name']) == "") {
            $this->mixScript('
                notify( "Especifica el archivo a cargar", "warning" );
                $("#cargarValidar").val("CARGAR Y VALIDAR");
                window.espera.stop();
            ');
            return false;
        }

        // Validar tipo de archivo permitido
        $archivo = $_FILES['arch']['name'];
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $ext_permitidas = ['txt'];

        if (!in_array($extension, $ext_permitidas)) {
            $this->mixScript('
                notify("Tipo de archivo no permitido", "error");
                $("#cargarValidar").val("CARGAR Y VALIDAR");
                window.espera.stop();
            ');
            return false;
        }

        if ($_FILES['arch']['error'] == 1 || $_FILES['arch']['size'] == 0) {
            $this->mixScript('
                notify("El tamaño del archivo excede límite máximo permitido", "error");
                $("#cargarValidar").val("CARGAR Y VALIDAR");
                window.espera.stop();
            ');
            return false;
        }
        $arch = $_FILES['arch']['tmp_name'];
        //cargamos los datos del archivo
        $texto = file_get_contents($arch);
        //procesamos los datos del archivo
        $tok = strtok($texto, ", \n\t");
        $ban = 0;

        // Verificar que la informacion del archivo sea la correcta.
        while ($tok !== false) {
            switch ($ban) {

                case 1:
                    $capital = $tok;

                    $ban++;
                    break;
                case 2:
                    $prod_financieros = $tok;
                    $ban = 0;
                    $parametro = [
                        'cbs_id' => $clabe,
                    ];
                    //verificamos que la cuenta contable exista
                    $sql = "SELECT COUNT(cbs_id) AS contador FROM ".BD_TESORERIA.".ban_CuentaBancariaSefin WHERE cbs_id= :cbs_id";
                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar($parametro);
                    if ($row = $this->extraer()) {
                        if ($row['contador'] == 0) {
                            $this->mixScript('
                                notify("LA CUENTA ' . $clabe . ' NO EXISTE","error");
                                $("#cargarValidar").val("CARGAR Y VALIDAR");
                            ');
                            return 0;
                        }
                    }
                    break;
                case 0:
                default:
                    $clabe = $tok;
                    if (strlen($clabe) < 18) {
                        $this->mixScript('
                            notify("ERROR EN EL FORMATO DE LA CLABE","error");
                            $("#cargarValidar").val("CARGAR Y VALIDAR");
                        ');
                        return 0;
                    }
                    $ban++;
                    break;
            }
            $tok = strtok(", \n\t");
        }

        date_default_timezone_set('America/Mexico_City');
        $anio = date("Y");
        $mes = date("m");
        $dia = date("d");
        $fecha = date("Y-m-d");

        // Insertar
        $tok = strtok($texto, ", \n\t");
        $ban = 0;
        while ($tok !== false) {
            switch ($ban) {

                case 1:
                    $capital = $tok;

                    $ban++;
                    break;
                case 2:
                    $prod_financieros = $tok;
                    $ban = 0;

                    $parametros = [
                        'cbs_id' => $clabe,
                        'pfi_ejercicio' => $anio,
                        'msc_id' => $mes,
                        'pfi_dia' => $dia,
                    ];
                    //optenemos el ultimo dato de egreso
                    $sql = "SELECT pfi_egreso FROM ".BD_TESORERIA.".ban_ProductoFinanciero
                        WHERE cbs_id= :cbs_id AND pfi_ejercicio= :pfi_ejercicio AND msc_id= :msc_id AND pfi_dia = :pfi_dia order by pfi_id desc limit 1";
                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar($parametros);
                    $egreso = "0.0";
                    $row = $this->extraer();
                    if ($this->getNumeroRegistros() > 0) {
                        if ($row['pfi_egreso'] != "") {
                            $egreso = $row['pfi_egreso'];
                        }

                    }

                    //insertamos el nuevo saldo
                    $this->prm->egreso = (string) $egreso;
                    $this->prm->cbs_id = $clabe;
                    $this->prm->ejercicio = $anio;
                    $this->prm->msc_id = $mes;
                    $this->prm->dia = $dia;
                    $this->prm->capital = $capital;
                    $this->prm->productoFinanciero = $prod_financieros;
                    $this->prm->observacion = "INSERTADO DESDE SALDOS";
                    $this->prm->abc = 'a';
                    ProductoFinanciero::abc($this->prm);

                    $this->desconectar();
                    break;
                case 0:
                default:
                    $clabe = $tok;
                    $ban++;
                    break;
            }
            $tok = strtok(", \n\t");
        }
        $this->mixScript('notify("SALDOS  ingresados Exitosamente");$("#cargarValidar").val("CARGAR Y VALIDAR");');

    }
    private function actualizaSaldos()
    {
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');
        //formulario para cargar el archivo
        $form = new stdClass();
        $form->id = "validar";
        $form->controlador = $this->pantalla;
        $form->metodo = "validarSALDO";
        $formulario = new form($form);

        if ($this->escritura) {
            $html = '
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Archivo de Saldos</legend>
                        <input type="file" name="arch" id="arch">
                    </fieldset>
                </div>
                <br />
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <input id="cargarValidar" type="submit" value="Cargar y Validar" class="btn btn-outline-success">
                    </fieldset>
                </div>
                <br />
                <div id="resultadoValidacion"></div>';

            $formulario->mixHtml($html);
        }
        $this->mixhtml($formulario->ponForm());

        $this->mixScript('

            $("#cargarValidar").click( function(e) {
                // Como no muestra el indicador de avance, cambiamos el texto
                $("#cargarValidar").val("Cargando ...");
            });
            $("#arch").click( function(e) {
                try {
                    window.espera.stop();
                    $("#cargarValidar").val("Cargar y Validar");
                } catch (e) {
                    // nada
                }
            });


        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $inputClabe = new StdClass();
        $inputClabe->id = 'tipoClabe';
        $inputClabe->name = 'tipoClabe';
        $inputClabe->enDiv = true;
        $inputClabe->placeholder = 'CLABE';
        $inputClabe->label = 'CLABE';

        $inputFechaSaldo = new StdClass();
        $inputFechaSaldo->id = 'fechaSaldo';
        $inputFechaSaldo->name = 'fechaSaldo';
        $inputFechaSaldo->placeholder = 'FECHA';
        $inputFechaSaldo->enDiv = true;
        $inputFechaSaldo->label = 'FECHA';
        $inputFechaSaldo->formato = 'fecha';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscarSaldo";
        $btnBuscar->name = "btnBuscarSaldo";
        $btnBuscar->value = "Buscar";
        $btnBuscar->class = "btn btn-outline-secondary me-1";
        $btnBuscar->icon = "search me-2";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"llenaTablaSaldos",
                clabe:$("#tipoClabe").val(),
                fecha:$("#fechaSaldo").val()
            })';

        $this->mixHtml('
                <div class ="row mb-2">
                    <div class="row col-md-6" >
                        <div class="col-md-6">' . $this->ponInput($inputClabe) . '</div>
                        <div class="col-md-6 mb-2">' . $this->ponInput($inputFechaSaldo) . '</div>
                        <div class="col-md-3">' . $this->ponBoton($btnBuscar) . '</div>
                    </div>
                </div>
                <div class="row">
                    <fieldset id="divSaldos" class="col-md-6 col-xs-6">
                    </fieldset>
                </div>
                ');
        //pintamos la tabla
        $tabla = new stdClass();

        $tabla->id = 'tbl-saldos';
        $tabla->titulo = 'Saldos';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaSaldos';
        $tabla->comunica->fecha = '{$(\'#fechaSaldo\').val()}';
        $tabla->comunica->clabe = '{$(\'#tipoClabe\').val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'CLABE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CAPITAL';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'INVERSION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EGRESO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SALDO';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'cbs_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pfi_fecha';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pfi_capital';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pfi_productoFinanciero';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pfi_egreso';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'saldo';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

    }

    private function llenaTablaHistorial()
    {
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $estado = isset($this->prm->estado) ? $this->prm->estado : '';
        $fecha = isset($this->prm->fecha) ? $this->prm->fecha : '';

        if ($tipo == "" || $estado == "" || $fecha == "") {
            $this->mixScript("notify(' FALTAN DATOS POR CAPTURAR','error')");
            return 0;
        }

        $sql = '
            SELECT
                mbn.mbn_id AS id,
                tmb.tmb_nombre AS tipo,
                mbn.mbn_fechaMovimiento,
                mbn_importe AS total,
                vlf.lot_id,
                vlf.lot_ejercicio,
                vlf.msc_id,
                vlf.fpa_id,COUNT(l.lot_id) AS registro
            FROM '.BD_TESORERIA.'.vin_LoteFondeo AS vlf
            INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l using(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN '.BD_TESORERIA.'.ban_MovimientoBancario AS mbn using (mbn_id)
            INNER JOIN '.BD_TESORERIA.'.cat_TipoMovimientoBancario AS tmb USING (tmb_id)
            WHERE ';

        $estado++;
        $parametros =[
            'emb_id' => $estado,
        ];
        $sql .= 'mbn.emb_id = :emb_id
        ';

        if (!empty($tipo)) {
            $parametros +=[
                'tmb_id' => $tipo,
            ];
            $sql .= 'AND mbn.tmb_id = :tmb_id
            ';
        }

        if (!empty($fecha)) {

            $fechaF = DateTime::createFromFormat('d/m/Y', $fecha);
            $parametros += [
                'mbn_fechaMovimiento' => $fechaF->format('Y-m-d'),
            ]; 
            $sql .= 'AND mbn.mbn_fechaMovimiento = :mbn_fechaMovimiento
            ';
        }

        $sql .= 'GROUP BY mbn.mbn_id
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-historial';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".detallesHistorial").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "detalleHistorial",
                    mbn_id: $(this).attr("id"),
                    tipo: $(this).attr("tipo"),
                    estado: $(this).attr("estado"),
                    fecha: $(this).attr("fecha")
                });
            });
        ');
    }
    private function llenaTablaDetallesHistorial()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        if ($mbn_id == "") {
            $this->mixScript('notify("FALTAN DATOS","warning")');
            return 0;
        }
        $parametro = [
            'mbn_id' => $mbn_id,
        ];
        $sql = "SELECT
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_id,
                tmb.tmb_nombre,
                @lote:=CONCAT( IF ( l.fpa_id = 1, 'T', IF (l.fpa_id = 2, 'C', 'L') ), LPAD(l.msc_id, 2, '0'), LPAD(l.lot_id, 4, '0'), SUBSTR(l.lot_ejercicio, 3) ) AS lot,
                mbn.mbn_fechaMovimiento,
                mbn.cbs_id_cargo,
                mbn.cbs_id_abono,
                l.lot_importe,
                CONCAT('PAGO VIA: ',fpa.fpa_nombre,' - ', @lote) as lot_concepto

            FROM
                ".BD_TESORERIA.".lot_Lote AS l
            INNER JOIN ".BD_TESORERIA.".vin_LoteFondeo AS vlf USING (
                fpa_id,
                msc_id,
                lot_ejercicio,
                lot_id
            )
            INNER JOIN ".BD_TESORERIA.".ban_MovimientoBancario AS mbn USING (mbn_id)
            INNER JOIN ".BD_TESORERIA.".cat_TipoMovimientoBancario AS tmb USING (tmb_id)
            INNER JOIN ".BD_TESORERIA.".cat_FormaPago AS fpa USING(fpa_id)
            WHERE
                mbn.mbn_id = :mbn_id";

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametro);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblDetallesHistorial';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }
    private function detalleHistorial()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $estado = isset($this->prm->estado) ? $this->prm->estado : "";
        $fecha = isset($this->prm->fecha) ? $this->prm->fecha : "";

        //Crea el boton de regreso
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "historialFondeo",
                tipo: "' . $tipo . '",
                estado: "' . $estado . '",
                fecha: "' . $fecha . '"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnImprimir = new stdClass();
        $btnImprimir->id = 'btnImprimir';
        $btnImprimir->name = 'btnImprimir';
        $btnImprimir->class = 'btn btn-outline-secondary';
        $btnImprimir->text = 'Imprimir';
        $btnImprimir->value = 'Imprimir';
        $btnImprimir->icon = "print";

        $this->agregaTools('<a id="imprimirFormato" target="_blank" class="btn btn-outline-secondary me-1" title="Imprimir PDF"><i class="fa-solid fa-file-pdf"></i></a>');
        $this->agregaTools('<a id="printExcel" target="_blank" class="btn btn-outline-success me-1" title="Imprimir Excel"><i class="fa-solid fa-file-excel"></i></a>');
        $this->mixScript('
                    $("#imprimirFormato").click(function(){
                    vinFondeo= ' . $this->prm->mbn_id . ';
                    usr_id= ' . $this->usr_id . ';

                    var form = $("<form>")
                        .attr("action", "rep/doc/tes/rep_tes01.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                        .append($("<input>").attr("type", "hidden").attr("name", "usr_id").attr("value", usr_id))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                    });

                    $("#printExcel").click(function(){
                    vinFondeo= ' . $this->prm->mbn_id . ';
                    usr_id= ' . $this->usr_id . ';

                    var form = $("<form>")
                        .attr("action", "rep/excel/rep_tes01Excel.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                    });
        ');

        /* Declaramos los elementos a utilizar/*/

        $tabla = new stdClass();
        $tabla->id = 'tblDetallesHistorial';
        $tabla->titulo = 'DETALLES ' . $mbn_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetallesHistorial';

        /* Array para dibujar la tabla /*/
        $tr = array();

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA REGISTRO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DE LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARA LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->formato = 'moneda';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla /*/
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tmb_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_fechaMovimiento';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_importe';
        $td->formato = 'moneda';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div class="row">
                <legend>HISTORIAL FONDEO</legend>
            </div>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ');
        $this->llenaTablaDetallesHistorial();
    }
    private function historialFondeo()
    {
        $estado = isset($this->prm->estado) ? $this->prm->estado : "";
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $fecha = isset($this->prm->fecha) ? $this->prm->fecha : "";

        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));

        $inputEstado = new StdClass();
        $datos1 = array();
        array_push($datos1, 'REGISTRADAS');
        array_push($datos1, 'VALIDADAS');
        array_push($datos1, 'AUTORIZADO');
        array_push($datos1, 'CANCELADAS');
        $inputEstado->opciones = $datos1;
        $inputEstado->id = 'hisEstado';
        $inputEstado->name = 'hisEstado';
        $inputEstado->enDiv = true;
        $inputEstado->mensaje = 'SELECCIONE';
        $inputEstado->label = 'ESTADO';

        $inputTipo = new StdClass();
        $datos2 = array();
        $datos2[1] = 'TRASPASO';
        $datos2[2] = 'INVERSION';
        $datos2[3] = 'FONDEO';
        $inputTipo->opciones = $datos2;
        $inputTipo->id = 'hisTipo';
        $inputTipo->name = 'hisTipo';
        $inputTipo->enDiv = true;
        $inputTipo->label = 'FECHA';
        $inputTipo->mensaje = 'SELECCIONE';
        $inputTipo->label = 'TIPO';

        $inputFecha = new StdClass();
        $inputFecha->id = 'hisFecha';
        $inputFecha->name = 'hisFecha';
        $inputFecha->required = true;
        $inputFecha->enDiv = true;
        $inputFecha->label = 'FECHA';
        $inputFecha->formato = 'fecha';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscaHisFondeo";
        $btnBuscar->name = "btnBuscaHisFondeo";
        $btnBuscar->value = "Buscar";
        $btnBuscar->class = "btn btn-outline-secondary me-1";
        $btnBuscar->icon = "search me-2";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"pintaTablaHistorial",
                estado:$("#hisEstado option:selected").text().split(" ")[0].trim(),
                tipo:$("#hisTipo option:selected").text().split(" ")[0].trim(),
                fecha:$("#hisFecha").val()
            })';

        $this->mixHtml('
            <div class ="row">
                <div class="row col-md-12" >
                    <div class="col-md-4">' . $this->ponCombo($inputEstado) . '</div>
                    <div class="col-md-4">' . $this->ponCombo($inputTipo) . '</div>
                    <div class="col-md-4">' . $this->ponInput($inputFecha) . '</div>
                </div>
            </div>
            <div class ="row">
                <div class="row col-md-12" >
                    <div class="col-md-3">' . $this->ponBoton($btnBuscar) . '</div>
                </div>
            </div>
            <div class ="row">
                <div class="row col-md-12" >
                    <fieldset id="divTablaHistorial" class="col-md-12 col-xs-12">
                </div>
            </div>
        ');

        if (!empty($estado) || !empty($tipo) || !empty($fecha)) {
            $this->mixScript('
                $("#inputEstado").val("' . $estado . '");
                $("#inputTipo").val("' . $tipo . '");
                $("#inputFecha").val("' . $fecha . '");
                $(".selectpicker").selectpicker("refresh");
            ');
        }
    }
    private function pintaTablaHistorial()
    {
        $this->setContenedor('divTablaHistorial');
        $this->prm->estado = isset($this->prm->estado) ? $this->prm->estado : "";
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $this->prm->fecha = isset($this->prm->fecha) ? $this->prm->fecha : "";

        switch ($this->prm->tipo) {
            // 0 -> traspaso
            case 1:
            //1 -> inversion
            case 2:
                $this->pintaTablaHisInversion($this->prm->tipo);
                break;
            //2->fondeo
            case 3:
            default:
                $this->pintaTablaHisFondeo();
                break;
        }
    }
    private function pintaTablaHisInversion($tipo)
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl-historialInversion';
        $tabla->titulo = 'HISTORIALES INVERSION';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaHistorialInversion';
        $tabla->comunica->fecha = 'fecha';
        $tabla->comunica->clabe = 'clabe';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA ABONO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE ';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_importe';
        $td->align = 'center';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_observacion';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'emb_nombre';
        $td->align = 'right';

        array_push($trbody, $td);

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
            });
        ';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar búsqueda';
        $btnLimpiar->value = 'Limpiar búsqueda';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-outline-warning";
        $btnLimpiar->icon = "fa fa-trash me-2";
        $btnLimpiar->click = '
            $("#btnLimpiar").hide();
            $("#imprimirFormato").hide();
            $("#divTablaHistorial").html("");
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        $this->agregaTools($this->ponBoton($btnLimpiar));

        //si el estado es autorizados muestra el boton
        $estado = isset($this->prm->estado) ? $this->prm->estado : "";
        if ($estado == 2) {
            $this->agregaTools('<a id="imprimirFormato" target="_blank" class="btn btn-outline-secondary me-1" title="Imprimir PDF"><i class="fa-solid fa-file-pdf"></i></a>');
            $this->agregaTools('<a id="printCmp" target="_blank" class="btn btn-outline-success me-1" title="Excel"><i class="fa-solid fa-file-excel me-2"></i></a>');
        }

        $this->mixScript('
                    $("#imprimirFormato").click(function(){
                        vinFondeo= $("#hisFecha").val() ;
                        usr_id= ' . $this->usr_id . ';
                        tipo = ' . $tipo . ';
                        var form = $("<form>")
                            .attr("action", "rep/doc/tes/rep_tes02.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                            .append($("<input>").attr("type", "hidden").attr("name", "usr_id").attr("value", usr_id))
                            .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", tipo))
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    });

                    $("#printCmp").click(function(){
                        vinFondeo= $("#hisFecha").val() ;
                        usr_id= ' . $this->usr_id . ';
                        tipo = ' . $tipo . ';
                        var form = $("<form>")
                            .attr("action", "rep/excel/rep_tes02Excel.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                            .append($("<input>").attr("type", "hidden").attr("name", "usr_id").attr("value", usr_id))
                            .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", tipo))
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    });
        ');
        //boton generar reporte
        $this->llenaTablaHistorialInversion($tipo);

    }
    private function llenaTablaHistorialInversion($tipo)
    {
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $estado = isset($this->prm->estado) ? $this->prm->estado : '';
        $fecha = isset($this->prm->fecha) ? $this->prm->fecha : '';

        if ($fecha == "" || $estado == "") {
            $this->mixScript(' notify("FALTAN DATOS","error")');
            return 0;
        }
        $estado++;
        $arrFecha = explode("/", $fecha);
        $fecha = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        $parametros = [
            'emb_id' => $estado,
            'tmb_id' => $tipo,
            'mbn_fechaMovimiento' => $fecha,
        ];
        $sql = "SELECT
                cbs_id_cargo,
                cbs_id_abono,
                mbn_importe,
                mbn_observacion,
                emb_nombre,
                mbn_id
                FROM ".BD_TESORERIA.".ban_MovimientoBancario
                INNER JOIN ".BD_TESORERIA.".cat_EstadoMovimientoBancario using(emb_id)
                WHERE emb_id= :emb_id AND tmb_id= :tmb_id AND mbn_fechaMovimiento= :mbn_fechaMovimiento";
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-historialInversion';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }
    private function pintaTablaHisFondeo()
    {
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $this->prm->estado = isset($this->prm->estado) ? $this->prm->estado : '';
        $this->prm->fecha = isset($this->prm->fecha) ? $this->prm->fecha : '';

        //limpiamos el tool;
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
            });
        ';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar búsqueda';
        $btnLimpiar->value = 'Limpiar búsqueda';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-outline-warning";
        $btnLimpiar->icon = "fa fa-trash me-2";
        $btnLimpiar->click = '
            $("#btnLimpiar").hide();
            $("#divTablaHistorial").html("");
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        $this->agregaTools($this->ponBoton($btnLimpiar));

        $tabla = new stdClass();
        $tabla->id = 'tbl-historial';
        $tabla->titulo = 'HISTORIALES';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaHistorial';
        $tabla->comunica->fecha = 'fecha';
        $tabla->comunica->clabe = 'clabe';
        $tabla->comunica->tipo = $this->prm->tipo;
        $tabla->comunica->estado = $this->prm->estado;

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DETALLES';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MOVIMIENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REGISTROS';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = "solid fa-magnifying-glass me-2";
        $td->class = "detallesHistorial btn btn-outline-secondary me-1";
        $td->name = "detallesHis";
        $td->id = "detallesHis";
        $td->attr = new stdClass();
        $td->attr->id = "id";
        $td->attr->tipo = $this->prm->tipo;
        $td->attr->estado = $this->prm->estado;
        $td->attr->fecha = $this->prm->fecha;

        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tipo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'registro';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaHistorial();

    }
    private function generacionFondeo()
    {
        $this->mixScript('

            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnRegistrarFondeo = new stdClass();
        $btnRegistrarFondeo->id = 'btnRegistrarFondeo';
        $btnRegistrarFondeo->name = 'btnRegistrarFondeo';
        $btnRegistrarFondeo->value = 'Registrar Fondeo';
        $btnRegistrarFondeo->icon = 'solid fa-floppy-disk me-2';
        $btnRegistrarFondeo->class = 'btn btn-outline-success me-1';
        $btnRegistrarFondeo->disabled = true;
        $btnRegistrarFondeo->click = '
            if(seleccion = $("#tblGenFondeo").tabla("getFilaSeleccion")){
                alertify.confirm("REGISTRO DE FONDEO", "¿Desea registrar el fondeo con los lotes seleccionados?",
                    function(){
                        strSeleccion = "";
                        seleccion.forEach(function (element){
                            strSeleccion += (strSeleccion.length > 0 ? "|" : "");
                            strSeleccion += element.lot + "-" + element.lot_total + "-" + element.fpa_id + "-" + element.msc_id + "-" + element.lot_ejercicio + "-" + element.lot_id + "-" + element.tlo_id;
                        });
                        comunica("' . $this->pantalla . '",{
                            metodo: "generacionFondeoDetalles",
                            fondeoDetalles: strSeleccion
                        });
                    },
                    function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            }
        ';

        $btnEnviarTodo = new stdClass();
        $btnEnviarTodo->id = 'btnEnviarTodo';
        $btnEnviarTodo->name = 'btnEnviarTodo';
        $btnEnviarTodo->value = 'Enviar Todo';
        $btnEnviarTodo->icon = 'regular fa-square-check me-2';
        $btnEnviarTodo->class = 'btn btn-outline-warning me-1';
        $btnEnviarTodo->disabled = true;

        //Dibuja el boton
        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnRegistrarFondeo));
            $this->agregaTools($this->ponBoton($btnEnviarTodo));
        }

        /* Declaramos los elementos a utilizar/*/
        $inputLotePago = new StdClass();
        $inputLotePago->id = 'lotePago';
        $inputLotePago->name = 'lotePago';
        $inputLotePago->placeholder = 'LOTE DE PAGO';
        $inputLotePago->enDiv = true;
        $inputLotePago->label = 'LOTE DE PAGO';

        $opcComboEjercicio = array();
        array_push($opcComboEjercicio, APP_EJERCICIO);
        array_push($opcComboEjercicio, '2025');
        array_push($opcComboEjercicio, '2024');
        array_push($opcComboEjercicio, '2023');
        array_push($opcComboEjercicio, '2022');
        array_push($opcComboEjercicio, '2021');
        if ($this->serverProduccion) {
            array_push($opcComboEjercicio, '2020');
            array_push($opcComboEjercicio, '2019');
            array_push($opcComboEjercicio, '2018');
            array_push($opcComboEjercicio, '2017');
        }
        $comboEjercicio = new stdClass();
        $comboEjercicio->id = 'comboEjercicio';
        $comboEjercicio->name = 'comboEjercicio';
        $comboEjercicio->opciones = $opcComboEjercicio;
        $comboEjercicio->enDiv = true;
        $comboEjercicio->search = true;
        $comboEjercicio->label = 'EJERCICIO';
        $comboEjercicio->mensaje = 'SELECCIONE EL EJERCICIO';

        $opcManual = array();
        $opcManual[0] = 'NO';
        $opcManual[1] = 'SI';
        $comboManual = new stdClass();
        $comboManual->id = 'comboManual';
        $comboManual->name = 'comboManual';
        $comboManual->opciones = $opcManual;
        $comboManual->enDiv = true;
        $comboManual->search = true;
        $comboManual->label = 'MANUAL';
        $comboManual->mensaje = 'SELECCIONE SI LA PROPUESTA ES MANUAL';

        $inputFechaMinAut = new StdClass();
        $inputFechaMinAut->id = 'fechaMinAut';
        $inputFechaMinAut->name = 'fechaMinAut';
        $inputFechaMinAut->placeholder = 'FECHA MÍNIMA';
        $inputFechaMinAut->enDiv = true;
        $inputFechaMinAut->label = 'FECHA MÍNIMA DE AUTORIZACIÓN';
        $inputFechaMinAut->formato = 'fecha';

        $inputFechaMaxAut = new StdClass();
        $inputFechaMaxAut->id = 'fechaMaxAut';
        $inputFechaMaxAut->name = 'fechaMaxAut';
        $inputFechaMaxAut->placeholder = 'FECHA MÁXIMA';
        $inputFechaMaxAut->enDiv = true;
        $inputFechaMaxAut->label = 'FECHA MÁXIMA DE AUTORIZACIÓN';
        $inputFechaMaxAut->formato = 'fecha';

        $inputFechaMinPago = new StdClass();
        $inputFechaMinPago->id = 'fechaMinPago';
        $inputFechaMinPago->name = 'fechaMinPago';
        $inputFechaMinPago->placeholder = 'FECHA MÍNIMA';
        $inputFechaMinPago->enDiv = true;
        $inputFechaMinPago->label = 'FECHA MÍNIMA DE PAGO';
        $inputFechaMinPago->formato = 'fecha';

        $inputFechaMaxPago = new StdClass();
        $inputFechaMaxPago->id = 'fechaMaxPago';
        $inputFechaMaxPago->name = 'fechaMaxPago';
        $inputFechaMaxPago->placeholder = 'FECHA MÁXIMA';
        $inputFechaMaxPago->enDiv = true;
        $inputFechaMaxPago->label = 'FECHA MÁXIMA DE PAGO';
        $inputFechaMaxPago->formato = 'fecha';

        $sqlEdoLote = '
            SELECT
                el.elo_id,
                el.elo_nombre
            FROM
                '.BD_TESORERIA.'.cat_EstadoLote AS el
            WHERE
                el.elo_id BETWEEN 4 AND 6
        ';

        $comboEdoLot = new stdClass();
        $comboEdoLot->id = 'edoLote';
        $comboEdoLot->name = 'edoLote';
        $comboEdoLot->sql = $sqlEdoLote;
        $comboEdoLot->enDiv = true;
        $comboEdoLot->search = true;
        $comboEdoLot->label = 'ESTADO';
        $comboEdoLot->mensaje = 'SELECCIONE EL ESTADO DE LA LISTA';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscar";
        $btnBuscar->name = "btnBuscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->icon = "solid fa-magnifying-glass me-2";
        $btnBuscar->class = 'btn btn-outline-success me-1';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"llenaTablaGenFondeo",
                lotePago:$("#lotePago").val(),
                lot_ejercicio:$("#comboEjercicio option:selected").text().split(" ")[2].trim(),
                fechaMinAut:$("#fechaMinAut").val(),
                fechaMaxAut:$("#fechaMaxAut").val(),
                fechaMinPago:$("#fechaMinPago").val(),
                fechaMaxPago:$("#fechaMaxPago").val(),
                edoLote:$("#edoLote option:selected").val(),
                lot_tipo:$("#comboManual option:selected").val()
            })';

        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = array('lot', 'lot_total', 'fpa_id', 'msc_id', 'lot_ejercicio', 'lot_id', 'tlo_id');

        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'elo_id';
        $tabla->casoSeleccionable->valor = 4;
        $tabla->casoSeleccionable->colorEnFalso = "rgba(8, 255, 18, 0.20)";

        $tabla->id = 'tblGenFondeo';
        $tabla->titulo = 'Resumen de lotes';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaGenFondeo';
        $tabla->comunica->lotePago = '{$("#lotePago").val()}';
        $tabla->comunica->lot_ejercicio = '{$("#comboEjercicio option:selected").text().split(" ")[2].trim()}';
        $tabla->comunica->fechaMinAut = '{$("#fechaMinAut").val()}';
        $tabla->comunica->fechaMaxAut = '{$("#fechaMaxAut").val()}';
        $tabla->comunica->fechaMinPago = '{$("#fechaMinPago").val()}';
        $tabla->comunica->fechaMaxPago = '{$("#fechaMaxPago").val()}';
        $tabla->comunica->edoLote = '{$("#edoLote option:selected").val()}';
        $tabla->comunica->lot_tipo = '{$("#comboManual option:selected").val()}';

        /* Array para dibujar la tabla /*/
        $tr = array();

        //Encabezados
        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '6rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC\'S';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA AUTORIZACIÓN';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA PAGO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EDO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->width = '8rem';
        $th->align = 'center';
        if ($this->escritura) {
            array_push($tr, $th);
        }

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-magnifying-glass-plus';
        $td->class = 'lotDet';
        $td->attr = new stdClass();
        $td->attr->lotFpa = 'fpa_id';
        $td->attr->lotMsc = 'msc_id';
        $td->attr->lotEje = 'lot_ejercicio';
        $td->attr->lotDet = 'lot_id';
        $td->attr->tlo_id = 'tlo_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_tipo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_clcs';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaAutorizacion';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaPago';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_edo';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'elo_id';
        $td->casos->valor = 4;
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'solid fa-trash';
        $td->casos->verdadero->class = 'lotEliminar btn btn-outline-danger me-1';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->lotEliminar = 'lot_id';
        $td->casos->verdadero->attr->lotFpa = 'fpa_id';
        $td->casos->verdadero->attr->lotMsc = 'msc_id';
        $td->casos->verdadero->attr->lotEje = 'lot_ejercicio';
        $td->casos->verdadero->attr->tlo_id = 'tlo_id';

        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row mb-2">
                <legend>GENERACIÓN FONDEO</legend>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputLotePago) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($comboEjercicio) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMinAut) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMaxAut) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMinPago) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMaxPago) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-5 col-md-5">
                    ' . $this->ponCombo($comboEdoLot) . '
                </fieldset>
            </div>
            </br>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($comboManual) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-2 col-md-2">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <hr>
            <div class="row mb-2">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
    }
    private function pintaTablaFondeo()
    {
        $this->setContenedor('divTabla');

        $tabla = new stdClass();
        $tabla->id = 'tblValidaFondeo';
        $tabla->titulo = 'VALIDACIÓN DE FONDEOS';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaAutorizaLote';

        /* Array para dibujar la tabla /*/
        $tr = array();

        //Encabezados

        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MOVIMIENTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA ';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = '# DE LOTES';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CANCELAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa-solid fa-magnifying-glass';
        $td->align = 'center';
        $td->class = 'fondeoDetalle';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_fechaMovimiento';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotes';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->align = 'center';
        $td->class = 'fondeoElimina btn btn-outline-danger me-1';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaAutorizaLote();
    }

    private function pintaTablaInversion($val)
    {
        $this->setContenedor('divTabla');

        $tabla = new stdClass();
        $tabla->id = 'tblValidaInversiones';
        $tabla->titulo = 'VALIDACIÓN DE ' . (($val == 1) ? 'TRANSPASOS' : 'INVERSIONES');
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaAutorizaInversion';

        /* Array para dibujar la tabla /*/
        $tr = array();

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA ABONO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE ';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AUTORIZAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CANCELAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_importe';
        $td->align = 'center';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_observacion';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'emb_nombre';
        $td->align = 'right';

        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'share';
        $td->align = 'center';
        $td->class = 'btn_autorizaInversion btn btn-outline-success me-1';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->align = 'center';
        $td->class = 'btn_cancelarInversion btn btn-outline-danger me-1';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        if ($val == 2) {
            $this->llenaTablaAutorizaInversion($val);
        } else {
            $this->pintaTablaTraspasos($val);
        }

    }

    private function pintaTablaTraspasos($val)
    {
        $tipoFondeo = isset($this->prm->tipoFondeo) ? $this->prm->tipoFondeo : '';
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : '';
        if ($fechaFondeo == "") {
            $this->mixScript(' notify("FALTAN DATOS","error")');
            return 0;
        }
        $arrFecha = explode("/", $fechaFondeo);
        $fechaFondeo = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        $parametro = [
            'mbn_fechaMovimiento' => $fechaFondeo,
        ];
        $sql = "SELECT
                cbs_id_cargo,
                cbs_id_abono,
                mbn_importe,
                mbn_observacion,
                emb_nombre,
                mbn_id
                FROM ".BD_TESORERIA.".ban_MovimientoBancario
                INNER JOIN ".BD_TESORERIA.".cat_EstadoMovimientoBancario using(emb_id)
                WHERE emb_id=1 AND tmb_id=1 AND mbn_fechaMovimiento= :mbn_fechaMovimiento";
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametro);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblValidaInversiones';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".btn_autorizaInversion").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "autorizaInvercion",
                    mbn_id: $(this).attr("mbn_id"),
                    fechaFondeo: $("#fechaFondeo").val(),
                    valo: ' . $val . '
                });
            });

            $(".btn_cancelarInversion").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "eliminarInversionAutoriza",
                    mbn_id: $(this).attr("mbn_id"),
                    fechaFondeo: $("#fechaFondeo").val(),
                    valo: ' . $val . '
                });
            });
        ');
    }

    private function eliminarInversionAutoriza()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : "";
        $valo = isset($this->prm->valo) ? $this->prm->valo : "";

        if ($mbn_id == "") {
            $this->mixScript(' notify("FALTAN DATOS","error");');
            return 0;
        }
        $this->prm->abc = "c";
        $this->prm->emb_id = 4; //cancelado
        $this->prm->mbn_id = $mbn_id;
        EntidadMovimientoBancario::abc($this->prm);
        if ($valo == 2) {
            $this->llenaTablaAutorizaInversion($valo);
        } else {
            $this->pintaTablaTraspasos($valo);
        }

    }
    private function autorizaInvercion()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : "";
        $valo = isset($this->prm->valo) ? $this->prm->valo : "";
        if ($mbn_id == "") {
            $this->mixScript(' notify("FALTAN DATOS","error");');
            return 0;
        }

        $fechaActual = new stdClass();

        $this->setSql('
            SELECT
                CURDATE() AS completa,
                YEAR(CURDATE()) AS anio,
                MONTH(CURDATE()) AS mes,
                DAY(CURDATE()) AS dia
            ');

        $this->conectar();
        $this->consultar();

        if ($fila = $this->extraer()) {
            $fechaActual->completa = $fila['completa'];
            $fechaActual->dia = $fila['dia'];
            $fechaActual->mes = $fila['mes'];
            $fechaActual->anio = $fila['anio'];
        }
        $this->prm->abc = "c";
        $this->prm->emb_id = 3; //autorizado
        $this->prm->mbn_id = $mbn_id;
        EntidadMovimientoBancario::abc($this->prm);
        if ($valo == 2) {
            $this->llenaTablaAutorizaInversion($valo);
        } else {
            $this->pintaTablaTraspasos($valo);
        }

        /**
         * Insertamos en Saldos
         */
        $conexion1 = new AccesoDatos();
        $conexion1->conectar();
        $parametro = [
            'mbn_id' => $mbn_id,
        ];
        $conexion1->setSql('SELECT * FROM '.BD_TESORERIA.'.ban_MovimientoBancario as mbn WHERE mbn.mbn_id = :mbn_id');
        $conexion1->consultar($parametro);

        if ($fila = $conexion1->extraer()) {

            $ctaRecaudadora = new stdClass();
            $ctaRecaudadora->abc = 'a';
            $ctaRecaudadora->cbs_id = $fila['cbs_id_cargo'];
            $ctaRecaudadora->ejercicio = $fechaActual->anio;
            $ctaRecaudadora->msc_id = $fechaActual->mes;
            $ctaRecaudadora->dia = $fechaActual->dia;
            $ctaRecaudadora->capital = '0';
            $ctaRecaudadora->productoFinanciero = '0';
            $ctaRecaudadora->egreso = $fila['mbn_importe'];
            $ctaRecaudadora->observacion = 'INSERTADO AL VALIDAR INVERSION';

            ProductoFinanciero::abc($ctaRecaudadora);

            $ctaConcentradora = new stdClass();
            $ctaConcentradora->abc = 'a';
            $ctaConcentradora->cbs_id = $fila['cbs_id_abono'];
            $ctaConcentradora->ejercicio = $fechaActual->anio;
            $ctaConcentradora->msc_id = $fechaActual->mes;
            $ctaConcentradora->dia = $fechaActual->dia;
            $ctaConcentradora->capital = '0';
            $ctaConcentradora->productoFinanciero = $fila['mbn_importe'];
            $ctaConcentradora->egreso = '0';
            $ctaConcentradora->observacion = 'INSERTADO AL VALIDAR INVERSION';

            ProductoFinanciero::abc($ctaConcentradora);
        }
    }
    private function llenaTablaAutorizaInversion($val)
    {

        $tipoFondeo = isset($this->prm->tipoFondeo) ? $this->prm->tipoFondeo : '';
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : '';
        if ($fechaFondeo == "") {
            $this->mixScript(' notify("FALTAN DATOS","error")');
            return 0;
        }
        $arrFecha = explode("/", $fechaFondeo);
        $fechaFondeo = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        $parametro = [
            'mbn_fechaMovimiento' => $fechaFondeo,
        ];
        $sql = "SELECT
                cbs_id_cargo,
                cbs_id_abono,
                mbn_importe,
                mbn_observacion,
                emb_nombre,
                mbn_id
                FROM ".BD_TESORERIA.".ban_MovimientoBancario
                INNER JOIN ".BD_TESORERIA.".cat_EstadoMovimientoBancario using(emb_id)
                WHERE emb_id=1 AND tmb_id=2 AND mbn_fechaMovimiento= :mbn_fechaMovimiento";
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametro);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblValidaInversiones';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".btn_autorizaInversion").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "autorizaInvercion",
                    mbn_id: $(this).attr("mbn_id"),
                    fechaFondeo: $("#fechaFondeo").val(),
                    valo: ' . $val . '
                });
            });

            $(".btn_cancelarInversion").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "eliminarInversionAutoriza",
                    mbn_id: $(this).attr("mbn_id"),
                    fechaFondeo: $("#fechaFondeo").val(),
                    valo: ' . $val . '
                });
            });
        ');
    }
    private function dibujaTablaValidacion()
    {
        //pintamos tablas diferentes segun el tipo :
        $tipoFondeo = isset($this->prm->tipoFondeo) ? $this->prm->tipoFondeo : "";
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : "";

        switch ($tipoFondeo) {
            //1 ->traspaso
            case 1:
                $this->pintaTablaInversion(1);
                break;
            //2 ->inversion
            case 2:
                $this->pintaTablaInversion(2);
                break;
            // 3 ->fondeo
            case 3:
            default:
                $this->pintaTablaFondeo();
                break;
        }
    }

    private function validacionFondeo()
    {
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnImprimir = new stdClass();
        $btnImprimir->id = 'btnImprimir';
        $btnImprimir->name = 'btnImprimir';
        $btnImprimir->text = 'Imprimir';
        $btnImprimir->value = 'Imprimir';
        $btnImprimir->icon = "print me-2";
        $btnImprimir->class = "btn btn-outline-secondary me-1";
        $btnImprimir->click = '
            // Regresa a la pantalla anterior
            /*comunica("' . $this->pantalla . '",{
                metodo: "main"
            });*/
        ';

        $this->mixScript('

            $("#imprimirFormato").click(function(){
                vinFondeo= $(this).attr("id");

                var form = $("<form>")
                    .attr("action", "rep/doc/tes/rep_tes01.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');

        /* Declaramos los elementos a utilizar/*/
        $sqlTipoFondeo = '
            SELECT
                tmb.tmb_id,
                tmb.tmb_nombre
            FROM
                '.BD_TESORERIA.'.cat_TipoMovimientoBancario AS tmb;
        ';

        $inputTipoFondeo = new StdClass();
        $inputTipoFondeo->sql = $sqlTipoFondeo;
        $inputTipoFondeo->id = 'tipoFondeo';
        $inputTipoFondeo->name = 'tipoFondeo';
        $inputTipoFondeo->mensaje = 'SELECCIONE EL TIPO MOVIMIENTO';
        $inputTipoFondeo->enDiv = true;
        $inputTipoFondeo->search = true;
        $inputTipoFondeo->label = 'TIPO DE MOVIMIENTO';

        $inputFechaFondeo = new StdClass();
        $inputFechaFondeo->id = 'fechaFondeo';
        $inputFechaFondeo->name = 'fechaFondeo';
        $inputFechaFondeo->placeholder = 'FECHA FONDEO';
        $inputFechaFondeo->enDiv = true;
        $inputFechaFondeo->label = 'FECHA MOVIMIENTO';
        $inputFechaFondeo->formato = 'fecha';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscarFondeo";
        $btnBuscar->name = "btnBuscarFondeo";
        $btnBuscar->class = "btn-success";
        $btnBuscar->value = "Buscar";
        $btnBuscar->class = "btn btn-outline-success me-1";
        $btnBuscar->icon = "solid fa-magnifying-glass me-2";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"dibujaTablaValidacion",
                tipoFondeo:$("#tipoFondeo").val(),
                fechaFondeo:$("#fechaFondeo").val()
            })';

        $html = '
            <div class="row mb-2">
                <legend>AUTORIZACIÓN DE MOVIMIENTOS BANCARIOS</legend>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($inputTipoFondeo) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaFondeo) . '
                </fieldset>
            </div>

            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <br>

            <div class="row mb-2">
                <fieldset id="divTabla" class="col-md-12 col-xs-12">
            </div>
        ';

        $this->mixHtml($html);
    }

    private function generacionFondeoDetalles()
    {
        $fondeoDetalles = isset($this->prm->fondeoDetalles) ? $this->prm->fondeoDetalles : '';

        if (!empty($fondeoDetalles)) {

            $loteArray = explode('|', $fondeoDetalles);

            $lotesSeleccionados = count($loteArray);
            $totalSeleccionados = 0;

            for ($i = 0; $i < $lotesSeleccionados; $i++) {

                $loteUnico = explode('-', $loteArray[$i]);
                $totalSeleccionados += floatval($loteUnico[1]);
            }
        }
        if (empty($fondeoDetalles)) {
            $this->mixScript('notify("Seleccionar por lo menos una fila de la tabla Resumen de lotes","warning");');
            return false;
        }

        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "generacionFondeo"
            });
        ';

        $btnRegistrarFondeo = new StdClass();
        $btnRegistrarFondeo->id = 'btnRegistrarFondeo';
        $btnRegistrarFondeo->name = 'btnRegistrarFondeo';
        $btnRegistrarFondeo->text = 'Registrar';
        $btnRegistrarFondeo->value = 'Registrar';
        $btnRegistrarFondeo->type = 'button';
        $btnRegistrarFondeo->class = 'btn btn-outline-success me-1';
        $btnRegistrarFondeo->icon = 'regular fa-square-check me-2';
        $btnRegistrarFondeo->disabled = 'true';
        $btnRegistrarFondeo->click = '
            alertify.confirm("GENERAR FONDEO", "¿Realmente desea generar el fondeo?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "registraFondeo",
                        ctaInversion: $("#ctaInversion option:selected").val(),
                        ctaPagadora: $("#ctaPagadora option:selected").val(),
                        monto: ' . $totalSeleccionados . ',
                        fondeoDetalles: "' . $fondeoDetalles . '"
                    });
                },
                function(){}
            )
            .set("defaultFocus", "cancel")
            .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        //Dibuja el boton
        $this->agregaTools($this->ponBoton($btnregresarindex));
        $this->agregaTools($this->ponBoton($btnRegistrarFondeo));

        /* Declaramos los elementos a utilizar/*/
        $btnSeleccionados = new StdClass();
        $btnSeleccionados->id = 'lotesSeleccionados';
        $btnSeleccionados->name = 'lotesSeleccionados';
        $btnSeleccionados->class = 'lotesSeleccionados btn btn-outline-success';
        $btnSeleccionados->enDiv = true;
        $btnSeleccionados->enabled = false;
        $btnSeleccionados->value = 'LOTES SELECCIONADOS: ' . $lotesSeleccionados;

        $totalConFormato = number_format($totalSeleccionados, 2, '.', ',');
        $btnImporte = new StdClass();
        $btnImporte->id = 'totalSeleccionados';
        $btnImporte->name = 'totalSeleccionados';
        $btnImporte->class = 'totalSeleccionados btn btn-outline-success';
        $btnImporte->enDiv = true;
        $btnImporte->enabled = false;
        $btnImporte->value = 'TOTAL: $ ' . $totalConFormato;

        $inputInversion = new StdClass();
        $inputInversion->id = 'ctaInversion';
        $inputInversion->name = 'ctaInversion';
        $inputInversion->sql = "SELECT * FROM ".BD_TESORERIA.".ban_CuentaBancariaSefin WHERE cbs_inversion=1  AND cbs_pagadora=2";
        $inputInversion->enDiv = true;
        $inputInversion->search = true;
        $inputInversion->label = 'CUENTA DE INVERSIÓN';
        $inputInversion->mensaje = "SELECCIONE LA CUENTA INVERSIÓN";
        $inputInversion->change = '
            if ($(this).val() != "") {
                comunica("' . $this->pantalla . '",{
                    metodo: "verificaSaldos",
                    ctaInversion: $("#ctaInversion").val(),
                    monto: ' . $totalSeleccionados . ',
                    tipo: "INV",
                    fondeoDetalles: "' . $fondeoDetalles . '"
                });
            }
        ';

        $inputPagadora = new StdClass();
        $inputPagadora->id = 'ctaPagadora';
        $inputPagadora->name = 'ctaPagadora';
        $inputPagadora->sql = "SELECT cbs_id,cbs_nombre FROM ".BD_TESORERIA.".ban_CuentaBancariaSefin WHERE (cbs_pagadora =1  and ter_id='A' )or (cbs_id in ('002640701323432588','002640701323432669','002640701323432740','002640701323833734') ) ";
        $inputPagadora->enDiv = true;
        $inputPagadora->search = true;
        $inputPagadora->label = 'CUENTA PAGADORA';
        $inputPagadora->mensaje = "SELECCIONE LA CUENTA PAGADORA";
        $inputPagadora->change = '
            if ($(this).val() != "") {
                comunica("' . $this->pantalla . '",{
                    metodo: "verificaSaldos",
                    ctaInversion: $("#ctaPagadora").val(),
                    monto: ' . $totalSeleccionados . ',
                    tipo: "PAG",
                    fondeoDetalles: "' . $fondeoDetalles . '"
                });
            }
        ';

        $inputsaldocta = new stdClass();
        $inputsaldocta->id = 'sdocta';
        $inputsaldocta->label = 'Saldo de la cuenta de inversión';
        $inputsaldocta->placeholder = "0";
        $inputsaldocta->value = "0";
        $inputsaldocta->formato = "moneda";
        $inputsaldocta->readonly = true;

        $inputSaldoCtaPag = new stdClass();
        $inputSaldoCtaPag->id = 'sdoctapag';
        $inputSaldoCtaPag->label = 'Saldo de la cuenta pagadora';
        $inputSaldoCtaPag->placeholder = "0";
        $inputSaldoCtaPag->value = "0";
        $inputSaldoCtaPag->formato = "moneda";
        $inputSaldoCtaPag->readonly = true;

        $tabla = new stdClass();
        $tabla->id = 'regFondeoDet';
        $tabla->titulo = 'VALIDACIÓN DE FONDEOS';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaFondeoDetalles';

        /* Array para dibujar la tabla /*/
        $tr = array();

        //Encabezados
        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row mb-2">
                <legend>GENERACIÓN DE FONDEO</legend>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                ' . $this->ponBoton($btnSeleccionados) . '
                </fieldset>

                <fieldset class="col-xs-3 col-md-3">
                ' . $this->ponBoton($btnImporte) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($inputInversion) . '
                </fieldset>

                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($inputPagadora) . '
                </fieldset>

            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputsaldocta) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputSaldoCtaPag) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
            <hr>
        ';

        $this->mixHtml($html);
        $this->llenaTablaRegFondeoDet();
    }

    private function detalleGenFondeo()
    {

        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';

        if (empty($lot_id) || empty($fpa_id) || empty($msc_id) || empty($lot_ejercicio)) {
            return;
        }

        $this->mixScript('

            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->class = "btn btn-outline-secondary me-1";
        $btnRegresar->icon = "solid fa-circle-left me-2";
        $btnRegresar->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "generacionFondeo"
            });
        ';

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'btnEliminar';
        $btnEliminar->name = 'btnEliminar';
        $btnEliminar->text = 'Eliminar';
        $btnEliminar->value = 'Eliminar';
        $btnEliminar->type = 'button';
        $btnEliminar->class = "btn btn-outline-danger me-1";
        $btnEliminar->icon = "solid fa-trash me-2";
        $btnEliminar->click = '
            //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
            alertify.confirm("CANCELAR LOTE", "¿Realmente desea cancelar el lote?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "cambiaEstadoLote",
                        accionLote: "cancelar",
                        lot_id: ' . $lot_id . ',
                        fpa_id: ' . $fpa_id . ',
                        msc_id: ' . $msc_id . ',
                        lot_ejercicio: ' . $lot_ejercicio . ',
                        tlo_id: ' . $tlo_id . '
                    });
                },
                function(){}
            )
            .set("defaultFocus", "cancel")
            .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        /* Dibuja los botones /*/
        $this->agregaTools($this->ponBoton($btnRegresar));

        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnEliminar));
        }

        $tabla = new stdClass();
        $tabla->id = 'tblDetGenFondeo';
        $tabla->titulo = 'DETALLE DEL LOTE ' . $lot_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetGenFondeo';
        $tabla->comunica->fpa_id = $this->prm->fpa_id;
        $tabla->comunica->msc_id = $this->prm->msc_id;
        $tabla->comunica->lot_ejercicio = $this->prm->lot_ejercicio;
        $tabla->comunica->lot_id = $this->prm->lot_id;
        $tabla->comunica->tlo_id = $this->prm->tlo_id;

        /* Array para dibujar la tabla /*/
        $tr = array();

        /* Encabezados /*/
        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'GRUPO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla /*/
        $trbody = array();

        /*Cuerpo de la tabla /*/
        $td = new stdClass();
        $td->campoTxt = 'lotDet';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_gpo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_ur';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_clc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_importe';
        $td->align = 'center';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_fecha';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->class = 'lotDetEliminar btn btn-outline-danger';
        $td->attr = new stdClass();
        $td->attr->lotDetEliminar = 'lotDet';
        $td->attr->lotDetFpa = 'lotDet_fpa';
        $td->attr->lotDetMsc = 'lotDet_msc';
        $td->attr->lotDetEjercicio = 'lotDet_ejercicio';
        $td->attr->lotDetGpo = 'lotDet_gpo';
        $td->attr->lotDetUr = 'lotDet_ur';
        $td->attr->lotDetClc = 'lotDet_clc';
        $td->attr->tlo_id = 'tlo_id';
        $td->attr->lom_id = 'lom_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div class="row">
                <legend>GENERACIÓN FONDEO</legend>
            </div>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ');
        $this->llenaTablaDetGenFondeo();
    }
    private function llenaTablaAutorizaLote()
    {
        $tipoFondeo = isset($this->prm->tipoFondeo) ? $this->prm->tipoFondeo : '';
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : '';
        $parametros =[
            'lot_ejr' => APP_EJERCICIO,
        ];
        $sql = '
            SELECT
                mbn.mbn_id,
                tmb.tmb_nombre AS mbn_nombre,
                mbn.mbn_fechaMovimiento,
                COUNT(l.lot_id) AS lotes,
                mbn_importe AS mbn_total
            FROM
                '.BD_TESORERIA.'.ban_MovimientoBancario AS mbn
            INNER JOIN '.BD_TESORERIA.'.cat_TipoMovimientoBancario AS tmb USING (tmb_id)
            INNER JOIN '.BD_TESORERIA.'.vin_LoteFondeo AS vlf USING (mbn_id)
            INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l ON
                (vlf.fpa_id = l.fpa_id AND vlf.msc_id = l.msc_id AND vlf.lot_ejercicio AND l.lot_ejercicio AND vlf.lot_id = l.lot_id)
            WHERE mbn.emb_id = 1
        ';

        if (!empty($tipoFondeo)) {
            $parametros += [
                'tmb_id' => $tipoFondeo,
            ];
            $sql .= 'AND mbn.tmb_id = :tmb_id
            ';
        }

        if (!empty($fechaFondeo)) {

            $fecha = DateTime::createFromFormat('d/m/Y', $fechaFondeo);
            $parametros += [
                'mbn_fechaMovimiento' => $fecha->format('Y-m-d'),
            ];
            $sql .= 'AND mbn.mbn_fechaMovimiento = :mbn_fechaMovimiento
            ';
        }

        $sql .= '
            AND l.lot_ejr= :lot_ejr
            GROUP BY
                mbn.mbn_id
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblValidaFondeo';

        $nav->nf = -1;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".fondeoDetalle").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "detalleFondeo",
                    mbn_id: $(this).attr("mbn_id")
                });
            });

            $(".fondeoElimina").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "eliminaFondeo",
                    mbn_id: $(this).attr("mbn_id")
                });
            });
        ');
    }

    private function detalleFondeo()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';

        if (empty($mbn_id)) {
            return;
        }

        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->class = "btn btn-outline-secondary me-1";
        $btnRegresar->icon = "solid fa-circle-left me-2";
        $btnRegresar->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "validacionFondeo"
            });
        ';

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'btnEliminar';
        $btnEliminar->name = 'btnEliminar';
        $btnEliminar->text = 'Eliminar';
        $btnEliminar->value = 'Eliminar';
        $btnEliminar->type = 'button';
        $btnEliminar->class = "btn btn-outline-danger me-1";
        $btnEliminar->icon = "solid fa-trash me-2";
        $btnEliminar->click = '
            //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
            if(confirm("¿Realmente desea cancelar el fondeo?")){
                comunica("' . $this->pantalla . '",{
                    metodo: "eliminaFondeo",
                    mbn_id: ' . $mbn_id . '
                });

            }else{

            }
        ';

        $btnValidar = new stdClass();
        $btnValidar->id = 'btnValidar';
        $btnValidar->name = 'btnValidar';
        $btnValidar->text = 'Eliminar';
        $btnValidar->value = 'Validar';
        $btnValidar->type = 'button';
        $btnValidar->class = "btn btn-outline-success me-1 ";
        $btnValidar->icon = "regular fa-square-check me-2";
        $btnValidar->click = '
            //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
            alertify.confirm("VALIDAR FONDEO", "¿Realmente desea validar el fondeo?",
                function(){
                    comunica("' . $this->pantalla . '",{
                    metodo: "validaFondeo",
                    mbn_id: ' . $mbn_id . '
                });                },
                function(){}
            )
            .set("defaultFocus", "cancel")
            .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        /* Dibuja los botones /*/
        $this->agregaTools($this->ponBoton($btnRegresar));

        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if ($this->validacion) {
            $this->agregaTools($this->ponBoton($btnValidar));
        }

        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnEliminar));
        }

        $tabla = new stdClass();
        $tabla->id = 'tblDetFondeo';
        $tabla->titulo = 'DETALLE DEL FONDEO ' . $mbn_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaValidaLote';

        /* Array para dibujar la tabla /*/
        $tr = array();

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA REGISTRO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DE LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARA LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->formato = 'moneda';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SEPARAR LOTE';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla /*/
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tmb_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_fechaMovimiento';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_importe';
        $td->formato = 'moneda';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-trash';
        $td->class = 'eliminaLote btn btn-outline-danger';
        $td->id = 'eliminaLote';
        $td->attr = new stdClass();
        $td->attr->lotEliminar = 'lot_id';
        $td->attr->lotFpa = 'fpa_id';
        $td->attr->lotMsc = 'msc_id';
        $td->attr->lotEje = 'lot_ejercicio';
        $td->attr->tlo_id = 'tlo_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla */
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div class="row">
                <legend>VALIDACIÓN FONDEO</legend>
            </div>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ');
        $this->llenaTablaValidaLote();

        $this->mixScript('

            $(".eliminaLote").click(function(){
                //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
                let lotEliminar = $(this).attr("lotEliminar");
                let lotFpa = $(this).attr("lotFpa");
                let lotMsc = $(this).attr("lotMsc");
                let lotEje = $(this).attr("lotEje");
                alertify.confirm("CANCELAR LOTE", "¿Realmente desea cancelar el lote?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "cambiaEstadoLote",
                            accionLote: "separar",
                            redirige: false,
                            redirigeDetalleFondeo: true,
                            mbn_id: ' . $mbn_id . ',
                            lot_id: lotEliminar,
                            fpa_id: lotFpa,
                            msc_id: lotMsc,
                            lot_ejercicio: lotEje
                        });
                    },
                    function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });
        ');
    }

    //Llenamos la tabla de Lote
    private function llenaTablaGenFondeo()
    {
        $lotePago = (isset($this->prm->lotePago)) ? $this->prm->lotePago : '';
        $fechaMinAut = (isset($this->prm->fechaMinAut)) ? $this->prm->fechaMinAut : '';
        $fechaMaxAut = (isset($this->prm->fechaMaxAut)) ? $this->prm->fechaMaxAut : '';
        $fechaMinPago = (isset($this->prm->fechaMinPago)) ? $this->prm->fechaMinPago : '';
        $fechaMaxPago = (isset($this->prm->fechaMaxPago)) ? $this->prm->fechaMaxPago : '';
        $edoLote = (isset($this->prm->edoLote)) ? $this->prm->edoLote : '';
        $lot_ejercicio = (isset($this->prm->lot_ejercicio)) ? $this->prm->lot_ejercicio : '';
        $lot_tipo = (isset($this->prm->lot_tipo)) ? $this->prm->lot_tipo : '';

        $sql = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.elo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                COUNT(ld.clc_id) as lot_clcs,
                l.lot_fechaAutorizacion,
                l.lot_fechaPago,
                CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) AS lot,
                MIN(ur.ur_nombre) AS lot_responsable,
                #IFNULL(SUM(ld.lot_importeClc), 0) AS lot_total,
                l.lot_importe AS lot_total,
                el.elo_nombre AS lot_edo
            FROM
                '.BD_TESORERIA.'.lot_Lote AS l
            INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN '.BD_TESORERIA.'.cat_EstadoLote AS el USING (elo_id)';

        if (!empty($lot_tipo) && $lot_tipo == '1') {
            $sql .= '
                INNER JOIN '.BD_TESORERIA.'.lot_PropuestaManual AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)';
        } else {
            $sql .= '
                INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)';
        }

        if (!empty($lot_ejercicio)) {
            if ($lot_ejercicio == 2017) {

                $sql .= '
                    INNER JOIN '.BD_CTRL_2017.'.cat_UnidadResponsable AS ur
                        ON (ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
                    INNER JOIN '.BD_CTRLANT.'.scp_Clc AS clcA
                        ON (ld.gpo_id = clcA.gpo_id AND ld.ur_id = clcA.ur_id AND ld.clc_id = clcA.clc_id)
                    INNER JOIN '.BD_CTRL_2017.'.scp_Clc AS clc
                        ON (clcA.gpo_id = clc.gpo_id AND clcA.ur_id = clc.ur_id AND clcA.clc_id = clc.clc_id)
                    WHERE l.lot_ejercicio = 2017 AND clcA.ejr_id_clc=2017';
            } elseif ($lot_ejercicio == 2018) {

                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2018.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2018.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2018 AND anclc.ejr_id_clc=2018
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14
                        ';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2018.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2018
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2019) {

                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2019.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2019.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2019 AND anclc.ejr_id_clc=2019
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2019.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2019
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2020) {

                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2020.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2020.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2020 AND anclc.ejr_id_clc=2020
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2020.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2020
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2021) {

                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2021.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2021.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2021 AND anclc.ejr_id_clc=2021
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2021.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2021
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2022) {
                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2022.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2022.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2022 AND anclc.ejr_id_clc=2022
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2022.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2022
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2023) {
                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2023.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2023.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2023 AND anclc.ejr_id_clc=2023
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2023.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2023
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2024) {
                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2024.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2024.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2024 AND anclc.ejr_id_clc=2024
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2024.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2024
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2025) {
                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL_2025.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL_2025.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        INNER JOIN '.BD_CTRLANT.'.scp_Clc AS anclc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2025 AND anclc.ejr_id_clc=2025
                        AND l.tlo_id != 3
                        #AND anclc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL_2025.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2025
                        AND l.tlo_id = 6';
                }
            } elseif ($lot_ejercicio == 2026) {
                if ($lot_tipo != '1') {
                    $sql .= '
                        INNER JOIN '.BD_CTRL.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        INNER JOIN '.BD_CTRL.'.scp_Clc AS clc using (gpo_id,ur_id,clc_id)
                        WHERE l.lot_ejercicio = 2026
                        AND l.tlo_id != 3
                        #AND clc.ecl_id=14';
                } else {
                    $sql .= '
                        LEFT JOIN '.BD_CTRL.'.cat_UnidadResponsable AS ur  using (gpo_id,ur_id)
                        WHERE l.lot_ejercicio = 2026
                        AND l.tlo_id = 6';
                }
            }

        }

        $parametros = [
            'lot_ejr' => APP_EJERCICIO,
        ];
        if (!empty($edoLote)) {
            $parametros += [
                'elo_id' => $edoLote,
            ];
            $sql .= '
            AND l.elo_id = :elo_id';
        } else {
            $sql .= '
            AND el.elo_id = 4';
        }

        if (!empty($lotePago)) {
            $sql .= '
                AND l.lot_clave LIKE "%'.$lotePago.'%"';
        }

        if (!empty($fechaMinAut)) {
            $fechaMinA = DateTime::createFromFormat('d/m/Y', $fechaMinAut);
            $parametros += [
                'lot_fechaAutorizacionMin' => $fechaMinA->format('Y-m-d'),
            ];
            $sql .= '
                AND l.lot_fechaAutorizacion >= :lot_fechaAutorizacionMin';
        }

        if (!empty($fechaMaxAut)) {
            $fechaMaxA = DateTime::createFromFormat('d/m/Y', $fechaMaxAut);
            $parametros += [
                'lot_fechaAutorizacionMax' => $fechaMaxA->format('Y-m-d'),
            ];
            $sql .= '
                AND l.lot_fechaAutorizacion <= :lot_fechaAutorizacionMax';
        }

        if (!empty($fechaMinPago)) {
            $fechaMinP = DateTime::createFromFormat('d/m/Y', $fechaMinPago);
            $parametros += [
                'lot_fechaPagoMin' => $fechaMinP->format('Y-m-d'),
            ];
            $sql .= '
                AND l.lot_fechaPago >= :lot_fechaPagoMin';
        }

        if (!empty($fechaMaxPago)) {
            $fechaMaxP = DateTime::createFromFormat('d/m/Y', $fechaMaxPago);
            $parametros += [
                'lot_fechaPagoMax' => $fechaMaxP->format('Y-m-d'),
            ];
            $sql .= '
                AND l.lot_fechaPago <= :lot_fechaPagoMax';
        }

        $sql .= '
            AND l.lot_ejr= :lot_ejr
            GROUP BY
                l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id
        ';

        if ($this->ip == '127.0.0.1' && $lot_ejercicio == 2017) {

            $name = 'sefip2019_localhost';
            $servidor = '172.17.90.24';
            $puerto = '5603';
            $usuario = 'jrojasg';
            $pass = 'WEQWYZ8H';
            $basedatos = 'ctrl2018';
            $tipo = 'MySql';

            // Lo habia creado un objeto vacio y posteriormente le estableci las propiedades,
            // pero hacian falta el metodo cypher y para que funcione correctamente, por ese motivo
            // opte por hacer referencia a la clase conexion
            $con = new Conexion();
            $con->setServidor($servidor);
            $con->setPuerto($puerto);
            $con->setUsuario($usuario);
            $con->setPassword($pass);
            $con->setBaseDatos($basedatos);
            $con->setType($tipo);
            $this->addServer($name, $con);
        }

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tblGenFondeo";
        $this->wrLlenaTabla($nav);

        if ($this->ip == '127.0.0.1' && $lot_ejercicio == 2017) {

            $name = 'sefip2019_localhost';
            $servidor = '172.17.90.24';
            $puerto = '5603';
            $usuario = 'jrojasg';
            $pass = 'WEQWYZ8H';
            $basedatos = 'ctrl2018';
            $tipo = 'MySql';

            // Lo habia creado un objeto vacio y posteriormente le estableci las propiedades,
            // pero hacian falta el metodo cypher y para que funcione correctamente, por ese motivo
            // opte por hacer referencia a la clase conexion
            $conn = new Conexion();
            $conn->setServidor($servidor);
            $conn->setPuerto($puerto);
            $conn->setUsuario($usuario);
            $conn->setPassword($pass);
            $conn->setBaseDatos($basedatos);
            $conn->setType($tipo);

            $con = new AccesoDatos();
            $con->addServer($name, $conn);
            $con->conectar();
            $con->setSql($sql);
            $con->consultar($parametros);

        } else {
            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql($sql);
            $con->consultar($parametros);

        }
        $strSeleccion = '';

        while ($fila = $con->extraer()) {
            $strSeleccion .= strlen($strSeleccion) > 0 ? '|' : '';
            $strSeleccion .= $fila['lot'] . '-';
            $strSeleccion .= $fila['lot_total'] . '-';
            $strSeleccion .= $fila['fpa_id'] . '-';
            $strSeleccion .= $fila['msc_id'] . '-';
            $strSeleccion .= $fila['lot_ejercicio'] . '-';
            $strSeleccion .= $fila['lot_id'] . '-';
            $strSeleccion .= $fila['tlo_id'];
        }

        $con->desconectar();

        if (!empty($edoLote) && $edoLote != 4) {
            $this->mixScript('
                $("#btnEnviarTodo").prop("disabled", "true");
                $("#btnRegistrarFondeo").prop("disabled", "true");
            ');
        } elseif (empty($edoLote) || $edoLote == 4) {
            $this->mixScript('
                $("#btnEnviarTodo").removeAttr("disabled");
                $("#btnRegistrarFondeo").removeAttr("disabled");
            ');
        }

        /*Creamos el script para darle funcionalidad a los botones */
        $this->mixScript('
            $("#btnEnviarTodo").click(function(){
                alertify.confirm("REGISTRO DE FONDEO", "¿Desea registrar el fondeo con todos los lotes?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "generacionFondeoDetalles",
                            fondeoDetalles: "' . $strSeleccion . '"
                        });
                    },
                    function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".lotDet").click(function(){
                //Mandamos a llamar al metodo para mostrar el detalle del lote
                comunica("' . $this->pantalla . '",{
                    metodo: "detalleGenFondeo",
                    lot_id: $(this).attr("lotDet"),
                    fpa_id: $(this).attr("lotFpa"),
                    msc_id: $(this).attr("lotMsc"),
                    lot_ejercicio: $(this).attr("lotEje"),
                    tlo_id: $(this).attr("tlo_id")
                });
            });

            $(".lotEliminar").click(function(){
                //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
                let lotEliminar = $(this).attr("lotEliminar");
                let lotFpa = $(this).attr("lotFpa");
                let lotMsc = $(this).attr("lotMsc");
                let lotEje = $(this).attr("lotEje");
                let tlo_id = $(this).attr("tlo_id");
                alertify.confirm("CANCELAR LOTE", "¿Realmente desea cancelar el lote?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "cambiaEstadoLote",
                        accionLote: "cancelar",
                        lot_id: lotEliminar,
                        fpa_id: lotFpa,
                        msc_id: lotMsc,
                        lot_ejercicio: lotEje,
                        tlo_id: tlo_id
                    });
                },
                function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    //Llenamos la tabla del detalle del Lote
    private function llenaTablaDetGenFondeo()
    {

        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : "";

        $sql = '';
        $sqlExtra = '';

        if ($tlo_id == 6 || $tlo_id == 7) { //Si es manual
            $sql = '
                SELECT
                    dl.lom_id,
                    l.lot_id AS lotDet,
                    dl.gpo_id AS lotDet_gpo,
                    dl.ur_id AS lotDet_ur,
                    dl.clc_id AS lotDet_clc,
                    dl.lom_importe AS lotDet_importe,
                    dl.lom_beneficiario AS lotDet_beneficiario,
                    dl.lom_concepto AS lotDet_concepto,
                    l.lot_fechaGeneracion AS lotDet_fecha,
                    l.lot_ejercicio AS lotDet_ejercicio,
                    l.fpa_id AS lotDet_fpa,
                    l.msc_id AS lotDet_msc,
                    l.tlo_id
                FROM
                    '.BD_TESORERIA.'.lot_Lote AS l
                INNER JOIN '.BD_TESORERIA.'.lot_PropuestaManual AS dl USING (lot_id, fpa_id, msc_id, lot_ejercicio)';
            $sqlExtra .= 'WHERE dl.elo_id=4 ';
        } else {

            $sql = '
                SELECT
                    l.lot_id AS lotDet,
                    dl.gpo_id AS lotDet_gpo,
                    dl.ur_id AS lotDet_ur,
                    dl.clc_id AS lotDet_clc,
                    dl.lot_importeClc AS lotDet_importe,
                    clc.clc_beneficiario AS lotDet_beneficiario,
                    clc.clc_concepto AS lotDet_concepto,
                    l.lot_fechaGeneracion AS lotDet_fecha,
                    l.lot_ejercicio AS lotDet_ejercicio,
                    dl.fpa_id AS lotDet_fpa,
                    dl.msc_id AS lotDet_msc,
                    l.tlo_id
                FROM
                    '.BD_TESORERIA.'.lot_Lote AS l
                INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS dl USING (lot_id, fpa_id, msc_id, lot_ejercicio)';

            switch ($lot_ejercicio) {
                case 2026:
                    $sql .= 'LEFT JOIN '.BD_CTRL.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2025:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2025.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2024:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2024.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2023:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2023.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2022:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2022.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2021:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2021.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2020:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2020.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2019:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2019.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2018:
                    $sql .= 'LEFT JOIN '.BD_CTRL_2018.'.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    ';
                    break;
                case 2017:
                    $sql .= 'LEFT JOIN '.BD_CTRLANT.'.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                        LEFT JOIN '.BD_CTRL_2017.'.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                    $sqlExtra .= 'WHERE ejr_id_clc = 2017
                    ';
                    break;
            }
        }

        if (!empty($sql)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $parametros = [
                'lot_id' => $lot_id,
                'fpa_id' => $fpa_id,
                'msc_id' => $msc_id,
                'lot_ejercicio' => $lot_ejercicio,
            ];
            $sqlExtra .= 'l.lot_id = :lot_id
                AND dl.fpa_id= :fpa_id
                AND dl.msc_id= :msc_id
                AND dl.lot_ejercicio= :lot_ejercicio';
        }

        /* Hacemos la consulta y llenamos la tabla con el resultado /*/
        $this->conectar();
        $this->setSql($sql . $sqlExtra);
        $this->setParams($parametros);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 5;
        $nav->idTabla = 'tblDetGenFondeo';
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".lotDetEliminar").click(function(){
            //Mandamos a llamar al metodo para eliminar el detalle del lote
            let lotDetEliminar = $(this).attr("lotDetEliminar");
            let lotDetFpa = $(this).attr("lotDetFpa");
            let lotDetMsc = $(this).attr("lotDetMsc");
            let lotDetEjercicio = $(this).attr("lotDetEjercicio");
            let lotDetGpo = $(this).attr("lotDetGpo");
            let lotDetUr = $(this).attr("lotDetUr");
            let lotDetClc = $(this).attr("lotDetClc");
            let tlo_id = $(this).attr("tlo_id");
            let lom_id = $(this).attr("lom_id");
            alertify.confirm("ELIMINAR CLC", "¿Realmente desea eliminar este CLC?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "eliminaDetalle",
                        lot_id: lotDetEliminar,
                        fpa_id: lotDetFpa,
                        msc_id: lotDetMsc,
                        lot_ejercicio: lotDetEjercicio,
                        gpo_id: lotDetGpo,
                        ur_id: lotDetUr,
                        clc_id: lotDetClc,
                        tlo_id: tlo_id,
                        lom_id: lom_id
                    });
                },
                function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    private function llenaTablaRegFondeoDet()
    {

        $fondeoDetalles = isset($this->prm->fondeoDetalles) ? $this->prm->fondeoDetalles : '';

        if (empty($fondeoDetalles) && count($fondeoDetalles) == 0) {
            return;
        }

        $sql = '
            SELECT
                lot_id,
                fpa_id,
                msc_id,
                lot_ejercicio,
                lot,
                lot_fechaAutorizacion,
                lot_fechaPago,
                lot_total,
                lot_edo,
                tlo_id,
                lot_concepto
            FROM (';

        if (!empty($fondeoDetalles)) {

            $loteArray = explode('|', $fondeoDetalles);

            for ($i = 0; $i < count($loteArray); $i++) {

                $loteUnico = explode('-', $loteArray[$i]);

                if ($i == 0) {

                    if ($loteUnico[6] == '6') {
                        $sql .= '
                            SELECT
                                l.lot_id,
                                l.fpa_id,
                                l.msc_id,
                                l.lot_ejercicio,
                                CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) lot,
                                l.lot_fechaAutorizacion,
                                l.lot_fechaPago,
                                l.lot_importe AS lot_total,
                                el.elo_nombre AS lot_edo,
                                l.tlo_id,
                                CONCAT("PAGO VIA: ",fpa.fpa_nombre," - ", IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3)) AS lot_concepto
                            FROM
                                '.BD_TESORERIA.'.lot_Lote AS l
                            INNER JOIN '.BD_TESORERIA.'.lot_PropuestaManual AS ld
                                ON (l.fpa_id = ld.fpa_id AND l.msc_id = ld.msc_id AND l.lot_id = ld.lot_id AND l.lot_ejercicio = ld.lot_ejercicio)
                            INNER JOIN '.BD_TESORERIA.'.cat_EstadoLote AS el ON (l.elo_id = el.elo_id)
                            INNER JOIN '.BD_TESORERIA.'.cat_FormaPago as fpa ON (l.fpa_id = fpa.fpa_id)
                            WHERE
                                l.elo_id = 4
                                AND l.fpa_id = ' . $loteUnico[2] . '
                                AND l.msc_id = ' . $loteUnico[3] . '
                                AND l.lot_ejercicio = ' . $loteUnico[4] . '
                                AND l.lot_id = ' . $loteUnico[5];
                    } else {
                        $sql .= '
                            SELECT
                                l.lot_id,
                                l.fpa_id,
                                l.msc_id,
                                l.lot_ejercicio,
                                CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) lot,
                                l.lot_fechaAutorizacion,
                                l.lot_fechaPago,
                                l.lot_importe AS lot_total,
                                el.elo_nombre AS lot_edo,
                                l.tlo_id,
                                CONCAT("PAGO VIA: ",fpa.fpa_nombre," - ", IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3)) AS lot_concepto
                            FROM
                                '.BD_TESORERIA.'.lot_Lote AS l
                            INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_id, lot_ejercicio)
                            INNER JOIN '.BD_TESORERIA.'.cat_EstadoLote AS el USING (elo_id)
                            INNER JOIN '.BD_TESORERIA.'.cat_FormaPago as fpa ON (l.fpa_id = fpa.fpa_id)
                            WHERE
                                l.elo_id = 4
                                AND l.fpa_id = ' . $loteUnico[2] . '
                                AND l.msc_id = ' . $loteUnico[3] . '
                                AND l.lot_ejercicio = ' . $loteUnico[4] . '
                                AND l.lot_id = ' . $loteUnico[5];
                    }

                } else {
                    //if ($fondeoDetalles[0]->tlo_id == '6') {
                    if ($loteUnico[6] == '6') {
                        $sql .= '
                            UNION ALL
                            SELECT
                                l.lot_id,
                                l.fpa_id,
                                l.msc_id,
                                l.lot_ejercicio,
                                CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) lot,
                                l.lot_fechaAutorizacion,
                                l.lot_fechaPago,
                                l.lot_importe AS lot_total,
                                el.elo_nombre AS lot_edo,
                                l.tlo_id,
                                CONCAT("PAGO VIA: ",fpa.fpa_nombre," - ", IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3)) AS lot_concepto
                            FROM
                                '.BD_TESORERIA.'.lot_Lote AS l
                            INNER JOIN '.BD_TESORERIA.'.lot_PropuestaManual AS ld
                                ON (l.fpa_id = ld.fpa_id AND l.msc_id = ld.msc_id AND l.lot_id = ld.lot_id AND l.lot_ejercicio = ld.lot_ejercicio)
                            INNER JOIN '.BD_TESORERIA.'.cat_EstadoLote AS el ON (l.elo_id = el.elo_id)
                            INNER JOIN '.BD_TESORERIA.'.cat_FormaPago as fpa ON (l.fpa_id = fpa.fpa_id)
                            WHERE
                                l.elo_id = 4
                                AND l.fpa_id = ' . $loteUnico[2] . '
                                AND l.msc_id = ' . $loteUnico[3] . '
                                AND l.lot_ejercicio = ' . $loteUnico[4] . '
                                AND l.lot_id = ' . $loteUnico[5];
                    } else {
                        $sql .= '
                            UNION ALL
                            SELECT
                                l.lot_id,
                                l.fpa_id,
                                l.msc_id,
                                l.lot_ejercicio,
                                CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) lot,
                                l.lot_fechaAutorizacion,
                                l.lot_fechaPago,
                                l.lot_importe AS lot_total,
                                el.elo_nombre AS lot_edo,
                                l.tlo_id,
                                CONCAT("PAGO VIA: ",fpa.fpa_nombre," - ", IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3)) AS lot_concepto
                            FROM
                                '.BD_TESORERIA.'.lot_Lote AS l
                            INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_id, lot_ejercicio)
                            INNER JOIN '.BD_TESORERIA.'.cat_EstadoLote AS el USING (elo_id)
                            INNER JOIN '.BD_TESORERIA.'.cat_FormaPago as fpa ON (l.fpa_id = fpa.fpa_id)
                            WHERE
                                l.elo_id = 4
                                AND l.fpa_id = ' . $loteUnico[2] . '
                                AND l.msc_id = ' . $loteUnico[3] . '
                                AND l.lot_ejercicio = ' . $loteUnico[4] . '
                                AND l.lot_id = ' . $loteUnico[5];
                    }
                }
            }
        }

        $sql .= '
            ) AS sub
                GROUP BY
                    lot_id,
                    fpa_id,
                    msc_id,
                    lot_ejercicio,
                    lot,
                    lot_fechaAutorizacion,
                    lot_fechaPago,
                    lot_total,
                    lot_edo,
                    tlo_id,
                    lot_concepto';

        $this->conectar();

        $this->setSql($sql);

        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();

        $nav->nf = -1;
        $nav->idTabla = "regFondeoDet";
        $this->wrLlenaTabla($nav);

    }

    private function llenaTablaValidaLote()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';

        if (empty($mbn_id)) {
            return;
        }

        $parametros = [
            'mbn_id' => $mbn_id,
            'lot_ejr' => APP_EJERCICIO,
        ];
        $sql = '
            SELECT
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_id,
                tmb.tmb_nombre,
                @lote:=CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) AS lot,
                mbn.mbn_fechaMovimiento,
                mbn.cbs_id_cargo,
                mbn.cbs_id_abono,
                l.lot_importe AS lot_importe,
                CONCAT("PAGO VIA: ",fpa.fpa_nombre," - ", @lote) as lot_concepto

            FROM
                '.BD_TESORERIA.'.lot_Lote AS l
            INNER JOIN '.BD_TESORERIA.'.vin_LoteFondeo AS vlf USING (
                fpa_id,
                msc_id,
                lot_ejercicio,
                lot_id
            )

            INNER JOIN '.BD_TESORERIA.'.ban_MovimientoBancario AS mbn USING (mbn_id)
            INNER JOIN '.BD_TESORERIA.'.cat_TipoMovimientoBancario AS tmb USING (tmb_id)
            INNER JOIN '.BD_TESORERIA.'.cat_FormaPago AS fpa USING(fpa_id)
            WHERE
                mbn.emb_id = 1
            #AND    l.elo_id = 4
            AND mbn.mbn_id = :mbn_id
            AND l.lot_ejr= :lot_ejr
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);

        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();

        $nav->nf = -1;
        $nav->idTabla = "tblDetFondeo";
        $this->wrLlenaTabla($nav);
    }

    /* Hacemos el cambio de estado del lote /*/
    private function cambiaEstadoLote()
    {
        /*  Se hace el cambio de estado mandando a llamar a la EntidadLote, hay que tomar en cuenta
        que tambien se hará el cambo de estado de la CLC (En cuanto esté la BD)
        /*/

        $lot = new stdClass();
        $detLot = new stdClass();
        $docLot = new stdClass();
        $vLoteFondeo = new stdClass();
        $clc = new stdClass();
        $fechaActual = '';

        $lot->abc = "c";
        $vLoteFondeo->abc = $detLot->abc = $docLot->abc = "b";

        $vLoteFondeo->lot_id = $lot->lot_id = $detLot->lot_id = $docLot->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $vLoteFondeo->fpa_id = $lot->fpa_id = $detLot->fpa_id = $docLot->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $vLoteFondeo->msc_id = $lot->msc_id = $detLot->msc_id = $docLot->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $vLoteFondeo->ejercicio = $lot->ejercicio = $detLot->ejercicio = $docLot->ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $vLoteFondeo->mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';
        $redirige = isset($this->prm->redirige) ? $this->prm->redirige : true;

        $redirigeDetalleFondeo = isset($this->prm->redirigeDetalleFondeo);

        $this->setSql('SELECT CURDATE() as fecha');
        $this->conectar();
        $this->consultar();
        if ($row = $this->extraer()) {
            $fechaActual = $row['fecha'];
        }

        switch ($this->prm->accionLote) {

            case 'separar':
                /* Regresa el estado a 4 (Lote autorizado en planeacion)*/
                $lot->elo_id = 4;

                LoteFondeo::abc($vLoteFondeo);
                $sql = '';
                if ($tlo_id == 6) { //Si es manual
                    $paramManual = [
                        'lot_id' => $detLot->lot_id,
                        'fpa_id' => $detLot->fpa_id,
                        'msc_id' => $detLot->msc_id,
                        'lot_ejercicio' => $detLot->ejercicio,
                    ];
                    $sql .= '
                        SELECT
                            dl.lom_id
                        FROM
                            '.BD_TESORERIA.'.lot_PropuestaManual AS dl
                        INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                        WHERE
                            lot_id = :lot_id
                            AND fpa_id= :fpa_id
                            AND msc_id= :msc_id
                            AND lot_ejercicio= :lot_ejercicio';

                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql($sql);
                    $con->consultar($paramManual);

                    $manual = new stdClass();
                    $manual->abc = 'c';
                    $manual->elo_id = $lot->elo_id;

                    while ($fila = $con->extraer()) {
                        $manual->lom_id = $fila['lom_id'];

                        PropuestaManual::abc($manual);
                    }
                } else {
                    /* Consultamos los registros del detalle*/
                    $paramReg = [
                        'lot_id' => $detLot->lot_id,
                        'fpa_id' => $detLot->fpa_id,
                        'msc_id' => $detLot->msc_id,
                        'lot_ejercicio' => $detLot->ejercicio,
                        'lot_ejr' => APP_EJERCICIO,
                    ];
                    $sql = '
                        SELECT
                            dl.lot_id,
                            dl.fpa_id,
                            dl.msc_id,
                            dl.lot_ejercicio,
                            dl.gpo_id,
                            dl.ur_id,
                            dl.clc_id,
                            l.lot_fechaPago
                        FROM
                            '.BD_TESORERIA.'.lot_LoteClc AS dl
                        INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                        WHERE
                            lot_id = :lot_id
                            AND fpa_id= :fpa_id
                            AND msc_id= :msc_id
                            AND lot_ejercicio= :lot_ejercicio
                            AND l.lot_ejr= :lot_ejr';

                    $con = new AccesoDatos();
                    $con->setSql($sql);
                    $con->conectar();
                    $con->consultar($paramReg);

                    /* Por cada elemento del detalle, mandamos a llamar el metodo para cambiar el estado de la clc*/
                    while ($row = $con->extraer()) {
                        $ejercicio = $row['lot_ejercicio'];
                        $clc->gpo_id = $row['gpo_id'];
                        $clc->ur_id = $row['ur_id'];
                        $clc->clc_id = $row['clc_id'];

                        $params =[
                            'usr_id' => $this->usr_id,
                            'ip' => $this->ip,
                        ];
                        $paramUpdate = [
                            'gpo_id' => $clc->gpo_id ,
                            'ur_id' => $clc->ur_id,
                            'clc_id' => $clc->clc_id,
                        ];

                        if ($ejercicio == 2018) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde Fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);

                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2018";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2019) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2019";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2020) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2020";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2021) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2021";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2022) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2022";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2023) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2023";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2024) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2024";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2025) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2025";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2026) {
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();

                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRL.".scp_ClcAuxiliar set ecl_id=14 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        }
                    }
                }

                $this->prm->mbn_id = $vLoteFondeo->mbn_id;
                Lote::abc($lot);

                if ($redirige) {
                    $this->validacionFondeo();
                }
                if ($redirigeDetalleFondeo) {
                    $this->detalleFondeo();
                }

                break;

            case 'registrar':
                /* Cambia el estado a 5 (Lote registrado en fondeo)/*/
                $lot->elo_id = 5;

                /* Consultamos los registros del detalle/*/
                $sql = '';

                if ($tlo_id == 6) { //Si es manual
                    $paramPm = [
                        'lot_id' => $detLot->lot_id ,
                        'fpa_id' => $detLot->fpa_id,
                        'msc_id' => $detLot->msc_id,
                        'lot_ejercicio' => $detLot->ejercicio,
                    ];
                    $sql .= '
                        SELECT
                            dl.lom_id
                        FROM
                            '.BD_TESORERIA.'.lot_PropuestaManual AS dl
                        INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                        WHERE
                            lot_id = :lot_id
                            AND fpa_id= :fpa_id
                            AND msc_id= :msc_id
                            AND lot_ejercicio= :lot_ejercicio';

                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql($sql);
                    $con->consultar($paramPm);

                    $manual = new stdClass();
                    $manual->abc = 'c';
                    $manual->elo_id = $lot->elo_id;

                    while ($fila = $con->extraer()) {
                        $manual->lom_id = $fila['lom_id'];

                        PropuestaManual::abc($manual);
                    }
                }

                Lote::abc($lot);
                if ($redirige) {
                    $this->generacionFondeo();
                }

                break;

            case 'validar':
                /* Cambia el estado a 6 (Lote validado en fondeo)/*/
                $lot->elo_id = 6;

                /* Consultamos los registros del detalle/*/
                $sql = '';
                $parametrosManual = [
                    'lot_id' => $detLot->lot_id ,
                    'fpa_id' => $detLot->fpa_id,
                    'msc_id' => $detLot->msc_id,
                    'lot_ejercicio' => $detLot->ejercicio,
                ];
                if ($tlo_id == 6) { //Si es manual
                    $sql .= '
                        SELECT
                            dl.lom_id
                        FROM
                            '.BD_TESORERIA.'.lot_PropuestaManual AS dl
                        INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                        WHERE
                            lot_id = :lot_id
                            AND fpa_id= :fpa_id
                            AND msc_id= :msc_id
                            AND lot_ejercicio= :lot_ejercicio';

                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql($sql);
                    $con->consultar($parametrosManual);

                    $manual = new stdClass();
                    $manual->abc = 'c';
                    $manual->elo_id = $lot->elo_id;

                    while ($fila = $con->extraer()) {
                        $manual->lom_id = $fila['lom_id'];
                        PropuestaManual::abc($manual);
                    }

                } else {
                    $sql = '
                        SELECT
                            dl.lot_id,
                            dl.fpa_id,
                            dl.msc_id,
                            dl.lot_ejercicio,
                            dl.gpo_id,
                            dl.ur_id,
                            dl.clc_id,
                            l.lot_fechaPago
                        FROM
                            '.BD_TESORERIA.'.lot_LoteClc AS dl
                        INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                        WHERE
                            lot_id = :lot_id
                            AND fpa_id= :fpa_id
                            AND msc_id= :msc_id
                            AND lot_ejercicio= :lot_ejercicio';

                    $con = new AccesoDatos();
                    $con->setSql($sql);
                    $con->conectar();
                    $con->consultar($parametrosManual);

                    /* Por cada elemento del detalle, mandamos a llamar el metodo para cambiar el estado de la clc*/
                    while ($row = $con->extraer()) {
                        $ejercicio = $row['lot_ejercicio'];
                        $clc->gpo_id = $row['gpo_id'];
                        $clc->ur_id = $row['ur_id'];
                        $clc->clc_id = $row['clc_id'];

                        $params = [
                            'usr_id' => $this->usr_id,
                            'ip' => $this->ip,
                        ];
                        $paramUpdate = [
                            'gpo_id' => $clc->gpo_id,
                            'ur_id' => $clc->ur_id ,
                            'clc_id' => $clc->clc_id,
                        ];

                        if ($ejercicio == 2018) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2018";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        } else if ($ejercicio == 2019) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2019";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        } else if ($ejercicio == 2020) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2020";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);
                        } else if ($ejercicio == 2021) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2021";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        } else if ($ejercicio == 2022) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2022";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        } else if ($ejercicio == 2023) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2023";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        } else if ($ejercicio == 2024) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON( :ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2024";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        } else if ($ejercicio == 2025) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON( :ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRLANT.".scp_Clc set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc = 2025";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        } else if ($ejercicio == 2026) {
                            $clc->abc = 'c';
                            $clc->ecl_id = 14;

                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualización desde fondeo'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($params);
                            $sql = " UPDATE ".BD_CTRL.".scp_ClcAuxiliar set ecl_id=14 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramUpdate);

                        }
                    }
                }

                Lote::abc($lot);
                if ($redirige) {
                    $this->validacionFondeo();
                }

                break;

            case 'cancelar':
                $cancelaLote = new sp_CancelarLoteFondeo();
                $cancelaLote->setLot_id($lot->lot_id);
                $cancelaLote->setFpa_id($lot->fpa_id);
                $cancelaLote->setMsc_id($lot->msc_id);
                $cancelaLote->setLot_ejercicio($lot->ejercicio);
                $cancelaLote->setTlo_id($tlo_id);
                $cancelaLote->setSis_id("TES");
                $cancelaLote->setEjercicio(APP_EJERCICIO);
                $cancelaLote->setBit_observacion("LOTE CANCELADO DESDE PANTALLA T_4_4");
                $cancelaLote->execute();
                if (!$cancelaLote->getBandera()) {
                    $mensaje = $cancelaLote->getMensaje();
                    $this->mixScript('
                                notify(" ' . $mensaje . '", "error",);
                    ');
                } else {
                    $mensaje = $cancelaLote->getMensaje();
                    //Muestra mensaje
                    $this->mixScript('
                        notify(" ' . $mensaje . '", "success",);
                    ');
                    $this->generacionFondeo();
                    $this->llenaTablaGenFondeo();
                }
                break;
        }
    }
    /* Eliminanos el contenido del detalle/*/
    private function eliminaDetalle()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : "";
        $ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : "";
        $clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : "";

        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : "";
        $lom_id = isset($this->prm->lom_id) && $this->prm->lom_id != "" ? $this->prm->lom_id : '0';
        $bit_observacion = "ELIMINA CLC DEL LOTE DESDE PANTALLA T_4_4";

        if ($tlo_id == 6 || $tlo_id == 7)
        {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql("SET @bit_observacion = :observacion, @usr_id = :usr_id, @usr_ip = :usr_ip;");
            $params = [
                'usr_id' => $this->usr_id,
                'usr_ip' => "sinpres.fn_get_ip()",
                'observacion' => $bit_observacion,
            ];
            $conexion->consultar($params);
            $conexion->setSql("CALL ".BD_TESORERIA.".sp_EliminarClcLoteFondeo".APP_EJERCICIO."(:lot, :fpa, :msc, :ejercicio, :gpo, :ur, :clc, :lom, @usr_ip, @usr_id);");
            $params = [
                'lot' => $lot_id,
                'fpa' => $fpa_id,
                'msc' => $msc_id,
                'ejercicio' => $ejercicio,
                'gpo' => $gpo_id,
                'ur' => $ur_id,
                'clc' => $clc_id,
                'lom' => $lom_id,
            ];
            $conexion->consultar($params);
            $conexion->setSql("SELECT @banderaTesoreria, @mensajeTesoreria as mensaje");
            $conexion->consultar();
            
            if ($r = $conexion->extraer()) {
                $bandera = $r['@banderaTesoreria'];
                $mensaje = $r['mensaje'];
                if (!$bandera) {
                    $this->mixScript('
                            notify(" ' . $mensaje . '", "error",);
                    ');
                } else {
                    //Muestra mensaje
                    $this->mixScript('
                        notify(" ' . $mensaje . '", "success",);
                    ');
                    //Consulta si hay mas registros de la propuesta manual
                    $params = [$lot_id,$fpa_id,$msc_id,$ejercicio];
                    $sql = '
                            SELECT
                                lom_id
                            FROM
                                tesoreria.lot_PropuestaManual
                            WHERE
                                lot_id = ?
                                AND fpa_id = ?
                                AND msc_id = ?
                                AND lot_ejercicio = ? ';

                    $conD4 = new AccesoDatos();
                    $conD4->conectar();
                    $conD4->setSql($sql);
                    $conD4->consultar($params);
                    $numReg = $conD4->getNumeroRegistros();
                    //Si ya no quedan clc's, la propuesta se elimina
                    if ($numReg == 0) {
                        $this->mixScript('
                            $("#btnEliminar").attr("hidden","true");
                        ');
                        $this->generacionFondeo();
                    } else {
                        $this->prm->fpa_id = $fpa_id;
                        $this->prm->msc_id = $msc_id;
                        $this->prm->lot_ejercicio = $ejercicio;
                        $this->prm->lot_id = $lot_id;
                        $this->prm->lot_tipo = $lot_tipo;
                        $this->llenaTablaDetGenFondeo();
                    }
                }

            }
        } else {
            $quitaClc = new sp_EliminarClcLoteFondeo();
            $quitaClc->setLot_id($lot_id);
            $quitaClc->setFpa_id($fpa_id);
            $quitaClc->setMsc_id($msc_id);
            $quitaClc->setLot_ejercicio($ejercicio);
            $quitaClc->setGpo_id($gpo_id);
            $quitaClc->setUr_id($ur_id);
            $quitaClc->setClc_id($clc_id);
            $quitaClc->setLom_id($lom_id);
            $quitaClc->setSis_id("TES");
            $quitaClc->setEjercicio(APP_EJERCICIO);
            $quitaClc->setBit_observacion($bit_observacion);
            $quitaClc->execute();
            if (!$quitaClc->getBandera()) {
                $mensaje = $quitaClc->getMensaje();
                $this->mixScript('
                        notify(" ' . $mensaje . '", "error",
                    );
                ');
            } else {
                $mensaje = $quitaClc->getMensaje();
                //Muestra mensaje
                $this->mixScript('
                        notify(" ' . $mensaje . '", "success",
                        );
                    ');
                //Consulta si hay mas CLC´s en el lote
                $params = [$lot_id,$fpa_id,$msc_id,$ejercicio];
                $sql = '
                        SELECT
                            clc_id
                        FROM
                            tesoreria.lot_LoteClc
                        WHERE
                            lot_id = ?
                            AND fpa_id = ?
                            AND msc_id = ?
                            AND lot_ejercicio = ? ';

                $conD4 = new AccesoDatos();
                $conD4->conectar();
                $conD4->setSql($sql);
                $conD4->consultar($params);
                $numReg = $conD4->getNumeroRegistros();
                //Si ya no quedan clc's, la propuesta se elimina
                if ($numReg == 0) {
                    $this->mixScript('
                        $("#btnEliminar").attr("hidden","true");
                    ');
                    $this->llenaTablaDetGenFondeo();
                } else {
                    $this->prm->fpa_id = $fpa_id;
                    $this->prm->msc_id = $msc_id;
                    $this->prm->lot_ejercicio = $ejercicio;
                    $this->prm->lot_id = $lot_id;
                    $this->prm->lot_tipo = $tlo_id;
                    $this->llenaTablaDetGenFondeo();
                }
            }
        }
    }

    private function registraFondeo()
    {

        $fondeoDetalles = isset($this->prm->fondeoDetalles) ? $this->prm->fondeoDetalles : '';
        $movimiento = new stdClass();
        $movimiento->abc = 'a';
        $movimiento->importe = isset($this->prm->monto) ? $this->prm->monto : '';
        $movimiento->cbs_id_cargo = isset($this->prm->ctaInversion) ? $this->prm->ctaInversion : '';
        $movimiento->cbs_id_abono = isset($this->prm->ctaPagadora) ? $this->prm->ctaPagadora : '';
        $movimiento->concepto = 'FONDEO';
        $movimiento->observacion = 'INSERTADO DESDE FONDEO';
        $movimiento->emb_id = 1;
        $movimiento->tmb_id = 3;

        if (empty($movimiento->cbs_id_cargo) || empty($movimiento->cbs_id_abono)) {
            $this->mixScript('
                notify("Seleccione las cuentas", "warning");
            ');
            return;
        }

        $this->conectar();
        $this->setSql('SELECT CURDATE() AS fecha, YEAR(CURDATE()) AS anio, MONTH(CURDATE()) AS mes, DAY(CURDATE()) AS dia');
        $this->consultar();

        if ($row = $this->extraer()) {
            $movimiento->fechaMovimiento = $row['fecha'];
            $movimiento->msc_id = $row['mes'];
            $movimiento->ejercicio = $row['anio'];
        }

        if (!empty($fondeoDetalles)) {

            $loteArray = explode('|', $fondeoDetalles);

            $vinLoteFondeo = new stdClass();
            $vinLoteFondeo->abc = 'a';
            $vinLoteFondeo->mbn_id = EntidadMovimientoBancario::abc($movimiento);

            $this->prm->redirige = false;
            $this->prm->accionLote = 'registrar';

            for ($i = 0; $i < count($loteArray); $i++) {

                $loteUnico = explode('-', $loteArray[$i]);

                $this->prm->fpa_id = $vinLoteFondeo->fpa_id = $loteUnico[2];
                $this->prm->msc_id = $vinLoteFondeo->msc_id = $loteUnico[3];
                $this->prm->lot_ejercicio = $vinLoteFondeo->ejercicio = $loteUnico[4];
                $this->prm->lot_id = $vinLoteFondeo->lot_id = $loteUnico[5];
                $this->prm->tlo_id = $vinLoteFondeo->tlo_id = $loteUnico[6];

                LoteFondeo::abc($vinLoteFondeo);

                $this->cambiaEstadoLote();

            }
            $this->generacionFondeo();
        } else {
            $this->mixScript('
                notify("Error al procesar la tabla", "warning");
            ');
            return;

        }
    }

    private function validaFondeo()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';
        $fechaActual = new stdClass();

        if (empty($mbn_id)) {
            return;
        }

        $this->setSql('
            SELECT
                CURDATE() AS completa,
                YEAR(CURDATE()) AS anio,
                MONTH(CURDATE()) AS mes,
                DAY(CURDATE()) AS dia
            ');

        $this->conectar();
        $this->consultar();

        if ($fila = $this->extraer()) {
            $fechaActual->completa = $fila['completa'];
            $fechaActual->dia = $fila['dia'];
            $fechaActual->mes = $fila['mes'];
            $fechaActual->anio = $fila['anio'];
        }

        $mbn = new stdClass();
        $mbn->abc = 'c';
        $mbn->mbn_id = $mbn_id;
        $mbn->fechaValidacion = $fechaActual->completa;
        $mbn->emb_id = 2;
        $temporal = "";
        EntidadMovimientoBancario::abc($mbn);

        $conexion3 = new AccesoDatos();
        $conexion3->conectar();
        $parametro = [
            'mbn_id' => $mbn_id,
        ];
        $conexion3->setSql('SELECT mbn_id, fpa_id, msc_id, lot_ejercicio, lot_id FROM '.BD_TESORERIA.'.vin_LoteFondeo as vlf WHERE vlf.mbn_id = :mbn_id');
        $conexion3->consultar($parametro);

        while ($fila = $conexion3->extraer()) {

            $this->prm->fpa_id = $fila['fpa_id'];
            $this->prm->msc_id = $fila['msc_id'];
            $this->prm->lot_ejercicio = $fila['lot_ejercicio'];
            $this->prm->lot_id = $fila['lot_id'];
            $this->prm->accionLote = 'validar';
            $this->prm->redirige = false;
            $this->cambiaEstadoLote();

            //cambiamos el estado del detalle si el lote es manual
            $paramLM = [
                'fpa_id' => $fila['fpa_id'],
                'msc_id' => $fila['msc_id'],
                'lot_ejercicio' => $fila['lot_ejercicio'],
                'lot_id' => $fila['lot_id'],
            ];
            $sql = "SELECT
                    tlo_id,lom_id
                    FROM ".BD_TESORERIA.".lot_Lote
                    INNER JOIN ".BD_TESORERIA.".lot_PropuestaManual using(fpa_id,msc_id,lot_ejercicio,lot_id)
                    WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND lot_id= :lot_id ";
            $conexion2 = new AccesoDatos();
            $conexion2->conectar();
            $conexion2->setSql($sql);
            $conexion2->consultar($paramLM);

            while ($row = $conexion2->extraer()) {
                //si es un lote manual actualizamos su detalle a 6
                $this->prm2->lom_id = $row['lom_id'];
                $this->prm2->elo_id = 6;
                $this->prm2->abc = 'c';
                PropuestaManual::abc($this->prm2);

            }

        }

        /**
         * Insertamos en Saldos
         */
        $conexion1 = new AccesoDatos();
        $conexion1->conectar();
        $paramMovBan =[
            'mbn_id' => $mbn_id,
        ];
        $conexion1->setSql('SELECT * FROM '.BD_TESORERIA.'.ban_MovimientoBancario as mbn WHERE mbn.mbn_id = :mbn_id');
        $conexion1->consultar($paramMovBan);

        if ($fila = $conexion1->extraer()) {

            $ctaInversion = new stdClass();
            $ctaInversion->abc = 'a';
            $ctaInversion->cbs_id = $fila['cbs_id_cargo'];
            $ctaInversion->ejercicio = $fechaActual->anio;
            $ctaInversion->msc_id = $fechaActual->mes;
            $ctaInversion->dia = $fechaActual->dia;
            $ctaInversion->capital = '0';
            $ctaInversion->productoFinanciero = '0';
            $ctaInversion->egreso = $fila['mbn_importe'];
            $ctaInversion->observacion = 'INSERTADO AL VALIDAR FONDEO';

            ProductoFinanciero::abc($ctaInversion);

            $ctaPagadora = new stdClass();
            $ctaPagadora->abc = 'a';
            $ctaPagadora->cbs_id = $fila['cbs_id_abono'];
            $ctaPagadora->ejercicio = $fechaActual->anio;
            $ctaPagadora->msc_id = $fechaActual->mes;
            $ctaPagadora->dia = $fechaActual->dia;
            $ctaPagadora->capital = '0';
            $ctaPagadora->productoFinanciero = $fila['mbn_importe'];
            $ctaPagadora->egreso = '0';
            $ctaPagadora->observacion = 'INSERTADO AL VALIDAR FONDEO';

            ProductoFinanciero::abc($ctaPagadora);
        }

        $this->validacionFondeo();
    }

    private function eliminaFondeo()
    {

        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';
        $fechaActual = '';

        if (empty($mbn_id)) {
            return;
        }

        $this->setSql('SELECT CURDATE() AS fecha');
        $this->conectar();
        $this->consultar();
        if ($row = $this->extraer()) {
            $fechaActual = $row['fecha'];
        }

        $mbn = new stdClass();
        $mbn->abc = 'c';
        $mbn->mbn_id = $mbn_id;
        $mbn->observacion = 'Movimiento cancelado el ' . $fechaActual;
        $mbn->emb_id = 4;

        EntidadMovimientoBancario::abc($mbn);
        $connectionForEliminate = new AccesoDatos();
        $paramLotF = [
            'mbn_id' => $mbn_id,
        ];
        $connectionForEliminate->setSql('SELECT * FROM '.BD_TESORERIA.'.vin_LoteFondeo as vlf WHERE vlf.mbn_id = :mbn_id');
        $connectionForEliminate->conectar();
        $connectionForEliminate->consultar($paramLotF);

        while ($fila = $connectionForEliminate->extraer()) {

            $this->prm->lot_id = $fila['lot_id'];
            $this->prm->fpa_id = $fila['fpa_id'];
            $this->prm->msc_id = $fila['msc_id'];
            $this->prm->lot_ejercicio = $fila['lot_ejercicio'];
            $this->prm->redirige = false;
            $this->prm->accionLote = 'separar';

            $this->cambiaEstadoLote();

        }
        $connectionForEliminate->desconectar();

        $this->validacionFondeo();
    }

    private function traspasos()
    {
        $btnAgregar = "";
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));
        //pintamos el formulario
        $inputRecaudadora = new stdClass();
        $inputRecaudadora->sql = "SELECT cbs_id FROM ".BD_TESORERIA.".ban_CuentaBancariaSefin ";
        $inputRecaudadora->id = 'cmb_recaudodora';
        $inputRecaudadora->name = 'cmb_recaudodora';
        $inputRecaudadora->label = 'CTA ORIGEN';
        $inputRecaudadora->enDiv = true;
        $inputRecaudadora->search = true;
        $inputRecaudadora->required = true;

        $inputConcentradora = new stdClass();
        $inputConcentradora->sql = "SELECT cbs_id FROM ".BD_TESORERIA.".ban_CuentaBancariaSefin ";
        $inputConcentradora->id = 'cmb_concentradora';
        $inputConcentradora->name = 'cmb_concentradora';
        $inputConcentradora->label = 'CTA DESTINO';
        $inputConcentradora->enDiv = true;
        $inputConcentradora->search = true;
        $inputConcentradora->required = true;

        $inputImporte = new stdClass();
        $inputImporte->id = 'inv_importe';
        $inputImporte->name = 'inv_importe';
        $inputImporte->placeholder = 'IMPORTE';
        $inputImporte->label = 'IMPORTE';
        $inputImporte->formato = 'moneda';
        $inputImporte->enDiv = true;
        $inputImporte->required = true;

        $fecha = date('Y-m-d');
        $inputConcepto = new stdClass();
        $inputConcepto->id = 'inv_concepto';
        $inputConcepto->name = 'inv_concepto';
        $inputConcepto->placeholder = 'CONCEPTO';
        $inputConcepto->value = 'TRASPASO DEL DIA ' . $fecha;
        $inputConcepto->label = 'CONCEPTO';
        $inputConcepto->enDiv = true;
        $inputConcepto->required = true;

        $botonAgregar = new stdClass();
        $botonAgregar->id = 'btn_AgragaInv';
        $botonAgregar->name = 'btn_AgragaInv';
        $botonAgregar->class = "btn btn-outline-success me-1";
        $botonAgregar->value = "Agregar";
        $botonAgregar->icon = "solid fa-floppy-disk me-2";
        $botonAgregar->click = '
          comunica("' . $this->pantalla . '",{
              metodo:"agregarTraspaso",
              cmb_recaudodora: $("#cmb_recaudodora option:selected").val().split(" ")[0].trim(),
              cmb_concentradora: $("#cmb_concentradora option:selected").val().split(" ")[0].trim(),
              inv_importe: $("#inv_importe").val(),
              inv_concepto: $("#inv_concepto").val()
          });';
        if ($this->escritura) {
            $btnAgregar = $this->ponBoton($botonAgregar);
        }
        $html = '
            <div class="row mb-2">
                <legend> TRASPASOS
                </legend>
                <fieldset class="col-md-3 col-xs-3 " id="">
                  ' . $this->ponCombo($inputRecaudadora) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 " id="">
                  ' . $this->ponCombo($inputConcentradora) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 " id="">
                  ' . $this->ponInput($inputImporte) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 " id="">
                  ' . $this->ponInput($inputConcepto) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-3 col-xs-3 " id="">
                  ' . $btnAgregar . '
                </fieldset>
            </div>
            <div class="row mb-2">
              <fieldset id="divTablaCere" class="col-md-12 col-xs-12">
            </div>
        ';
        $this->mixHtml($html);
        //pintamos la tabla
        $tabla = new stdClass();

        $tabla->id = 'tbl-traspasos';
        $tabla->titulo = 'TRASPASOS';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaTraspasos';
        $tabla->comunica->mbn_id = 'mbn_id';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DE CTA ORIGEN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARA CTA DESTINO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CANCELAR';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_importe';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_observacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->color = 'rojo';
        $td->icono = 'trash';
        $td->class = 'del_traspaso';
        $td->id = 'del_traspaso';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaTraspasos();

    }
    private function llenaTablaTraspasos()
    {
        $fecha = date('Y-m-d');
        $paramFecha = [
            'mbn_fechaMovimiento' => $fecha,
        ];
        $sql = "SELECT
                cbs_id_cargo,
                cbs_id_abono,
                FORMAT(mbn_importe, 2) as mbn_importe,
                mbn_observacion,
                emb_nombre,
                mbn_id
            FROM ".BD_TESORERIA.".ban_MovimientoBancario
            INNER JOIN ".BD_TESORERIA.".cat_EstadoMovimientoBancario using(emb_id)
            WHERE emb_id=1 AND tmb_id=1 AND mbn_fechaMovimiento= :mbn_fechaMovimiento";

        $this->setSql($sql);
        $this->setParams($paramFecha);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-traspasos";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('

            $(".del_traspaso").click(function(){
                let mbn_id = $(this).attr("mbn_id");
                alertify.confirm("CANCELAR TRASPASO", "¿Realmente desea cancelar el traspaso?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "eliminarTraspaso",
                            mbn_id: mbn_id
                        });
                    },
                    function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });
        ');
    }
    private function eliminarTraspaso()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        if ($mbn_id == "") {
            $this->mixScript(' notify("FALTAN DATOS","error");');
            return 0;
        }
        $this->prm->abc = "c";
        $this->prm->emb_id = 4; //cancelado
        $this->prm->mbn_id = $mbn_id;
        EntidadMovimientoBancario::abc($this->prm);
        $this->llenaTablaTraspasos();
    }
    private function agregarTraspaso()
    {
        //insertamos el traspaso
        $recaudadora = isset($this->prm->cmb_recaudodora) ? $this->prm->cmb_recaudodora : "";
        $concentradora = isset($this->prm->cmb_concentradora) ? $this->prm->cmb_concentradora : "";
        $inv_importe = isset($this->prm->inv_importe) ? $this->prm->inv_importe : "";
        $inv_concepto = isset($this->prm->inv_concepto) ? $this->prm->inv_concepto : "";
        if ($inv_importe != "") {
            $inv_importe = trim($inv_importe, " $");
            $inv_importe = str_replace(',', '', $inv_importe);
        }
        if ($recaudadora == "" || $concentradora == "" || $inv_importe == "" || $inv_concepto == "") {
            $this->mixScript(' notify("FALTAN DATOS","error")');
            return 0;
        }
        $this->prm->ejercicio = date('Y');
        $this->prm->importe = $inv_importe;
        $this->prm->concepto = "TRASPASO";
        $this->prm->observacion = $inv_concepto;
        $this->prm->fechaMovimiento = date('Y-m-d');
        $this->prm->cbs_id_cargo = $recaudadora;
        $this->prm->cbs_id_abono = $concentradora;
        $this->prm->emb_id = 1; //registrado
        $this->prm->tmb_id = 1; //TRASPASO
        $this->prm->msc_id = date('m');
        $this->prm->abc = "a";
        EntidadMovimientoBancario::abc($this->prm);
        $this->llenaTablaTraspasos();
    }

    private function verificaSaldos()
    {
        $this->prm->ctaInversion = isset($this->prm->ctaInversion) ? $this->prm->ctaInversion : '';
        $this->prm->monto = isset($this->prm->monto) ? $this->prm->monto : '';
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $this->prm->fondeoDetalles = isset($this->prm->fondeoDetalles) ? $this->prm->fondeoDetalles : '';

        $paramCuenta = [
            'cbs_id' => $this->prm->ctaInversion,
        ];
        $sql = '
            SELECT cbs.cbs_id, SUM(pfi.pfi_capital+pfi.pfi_productoFinanciero) as abono,
            SUM(pfi.pfi_egreso) as cargo, (SUM(pfi.pfi_capital+pfi.pfi_productoFinanciero)-SUM(pfi.pfi_egreso)) as saldo
            FROM '.BD_TESORERIA.'.ban_ProductoFinanciero pfi
            INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin cbs USING (cbs_id)
            WHERE cbs.cbs_id = :cbs_id
            GROUP BY cbs.cbs_id;
        ';

        $conSaldos = new AccesoDatos();
        $conSaldos->conectar();
        $conSaldos->setSql($sql);
        $conSaldos->consultar($paramCuenta);

        if ($this->prm->tipo == 'INV') {
            $saldo = 0;
            if ($fila = $conSaldos->extraer()) {
                $saldo = $fila['saldo'];
                $this->mixScript('$("#sdocta").val("' . $saldo . '");
                                  $("#sdocta").blur();');
            }
            if ($this->prm->monto > $saldo) {
                $this->mixScript('
                    notify("No hay suficiente saldo en la cuenta. Saldo actual=' . $saldo . '" , "warning");
                ');
                $this->mixScript('$("#sdocta").val("' . $saldo . '");
                                  $("#sdocta").blur();');
                $this->mixScript('$("#btnRegistrarFondeo").attr("disabled", "true");');
            } else {
                $this->mixScript('$("#btnRegistrarFondeo").removeAttr("disabled");');
            }
        } else {
            $saldo = 0;
        }

        if ($fila = $conSaldos->extraer()) {
            $saldo = $fila['saldo'];
            $this->mixScript('$("#sdoctapag").val("' . $saldo . '");
                                  $("#sdoctapag").blur();');
        }
    }

    /*
     * FUNCIONES PARA HISTORIAL DE MOVIMIENTOS
     */
    private function pintaHistorialMovimientos()
    {

        $this->prm->emb_id = isset($this->prm->emb_id) ? $this->prm->emb_id : '';
        $this->prm->tmb_id = isset($this->prm->tmb_id) ? $this->prm->tmb_id : '';
        $this->prm->mbn_fechaMovimiento = isset($this->prm->mbn_fechaMovimiento) ? $this->prm->mbn_fechaMovimiento : '';
        $this->prm->mbn_fechaMaxMovimiento = isset($this->prm->mbn_fechaMaxMovimiento) ? $this->prm->mbn_fechaMaxMovimiento : '';
        $this->prm->busqueda = isset($this->prm->busqueda) ? $this->prm->busqueda : false;

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnregresarindex';
        $btnRegresarIndex->name = 'btnregresarindex';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->type = 'button';
        $btnRegresarIndex->class = "btn btn-outline-secondary me-1";
        $btnRegresarIndex->icon = "solid fa-circle-left me-2";
        $btnRegresarIndex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscar";
        $btnBuscar->name = "btnBuscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->icon = "search me-2";
        $btnBuscar->class = "btn btn-outline-secondary me-1";
        $btnBuscar->click = '
            $("#btnPDF").remove();
            $("#btnExcel").remove();
            if ( $("#comboTipo").val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaMovimiento",
                    tmb_id: $("#comboTipo").val(),
                    emb_id: $("#comboEstado").val(),
                    mbn_fechaMovimiento: $("#inputFecha").val(),
                    mbn_fechaMaxMovimiento: $("#inputFechaMax").val()
                });
            } else{
                notify("Seleccione al menos el tipo de movimiento que desee consultar", "warning");
            }';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar búsqueda';
        $btnLimpiar->value = 'Limpiar búsqueda';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-outline-warning";
        $btnLimpiar->icon = "fa fa-trash me-2";
        $btnLimpiar->click = '
            $("#btnPDF").remove();
            $("#btnExcel").remove();
            $("#tblMbn").html("");
            $("#comboTipo").val("");
            $("#comboEstado").val("");
            $("#inputFecha").val("");
            $(".selectpicker").selectpicker("refresh");
        ';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $this->agregaTools($this->ponBoton($btnBuscar));
        $this->agregaTools($this->ponBoton($btnLimpiar));

        $comboTipo = new stdClass();
        $comboTipo->id = 'comboTipo';
        $comboTipo->name = 'comboTipo';
        $comboTipo->sql = 'SELECT tmb_id, tmb_nombre FROM '.BD_TESORERIA.'.cat_TipoMovimientoBancario';
        $comboTipo->enDiv = true;
        $comboTipo->search = true;
        $comboTipo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboTipo->label = 'TIPO DE MOVIMIENTO';

        $comboEstado = new stdClass();
        $comboEstado->id = 'comboEstado';
        $comboEstado->name = 'comboEstado';
        $comboEstado->sql = 'SELECT emb_id, emb_nombre FROM '.BD_TESORERIA.'.cat_EstadoMovimientoBancario';
        $comboEstado->enDiv = true;
        $comboEstado->search = true;
        $comboEstado->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboEstado->label = 'ESTADO DE MOVIMIENTO';

        $inputFecha = new StdClass();
        $inputFecha->id = 'inputFecha';
        $inputFecha->name = 'inputFecha';
        $inputFecha->required = true;
        $inputFecha->enDiv = true;
        $inputFecha->label = 'FECHA MÍNIMA';
        $inputFecha->formato = 'fecha';
        $inputFecha->placeholder = date('d/m/Y');

        $inputFechaMax = new StdClass();
        $inputFechaMax->id = 'inputFechaMax';
        $inputFechaMax->name = 'inputFechaMax';
        $inputFechaMax->required = true;
        $inputFechaMax->enDiv = true;
        $inputFechaMax->label = 'FECHA MÁXIMA';
        $inputFechaMax->formato = 'fecha';
        $inputFechaMax->placeholder = date('d/m/Y');

        $html = '
            <div class="row mb-2">
                <legend>Historial de movimientos</legend>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboTipo) . '</fieldset>
                <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboEstado) . '</fieldset>
                <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFecha) . '</fieldset>
                <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFechaMax) . '</fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-12 col-xs-12" id="tblMbn">
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);

        if ($this->prm->busqueda) {
            $this->mixScript('
                $("#comboTipo").val("' . $this->prm->tmb_id . '");
                $("#comboEstado").val("' . $this->prm->emb_id . '");
                $("#inputFecha").val("' . $this->prm->mbn_fechaMovimiento . '");
                $("#inputFechaMax").val("' . $this->prm->mbn_fechaMaxMovimiento . '");
                $(".selectpicker").selectpicker("refresh");
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaMovimiento",
                    tmb_id: $("#comboTipo").val(),
                    emb_id: $("#comboEstado").val(),
                    mbn_fechaMovimiento: $("#inputFecha").val(),
                    mbn_fechaMaxMovimiento: $("#inputFechaMax").val()
                });
            ');
        }

    }

    private function pintaTablaMovimiento()
    {

        $this->prm->emb_id = isset($this->prm->emb_id) ? $this->prm->emb_id : '';
        $this->prm->tmb_id = isset($this->prm->tmb_id) ? $this->prm->tmb_id : '';
        $this->prm->mbn_fechaMovimiento = isset($this->prm->mbn_fechaMovimiento) ? $this->prm->mbn_fechaMovimiento : '';

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnregresarindex';
        $btnRegresarIndex->name = 'btnregresarindex';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->type = 'button';
        $btnRegresarIndex->class = "btn btn-outline-secondary me-1";
        $btnRegresarIndex->icon = "solid fa-circle-left me-2";
        $btnRegresarIndex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscar";
        $btnBuscar->name = "btnBuscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->class = "btn btn-outline-secondary me-1";
        $btnBuscar->icon = "search me-2";
        $btnBuscar->click = '
            $("#btnPDF").remove();
            $("#btnExcel").remove();
            if ( $("#comboTipo").val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaMovimiento",
                    tmb_id: $("#comboTipo").val(),
                    emb_id: $("#comboEstado").val(),
                    mbn_fechaMovimiento: $("#inputFecha").val(),
                    mbn_fechaMaxMovimiento: $("#inputFechaMax").val()
                });
            } else{
                notify("Seleccione al menos el tipo de movimiento que desee consultar", "warning");
            }';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar búsqueda';
        $btnLimpiar->value = 'Limpiar búsqueda';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-outline-warning me-1";
        $btnLimpiar->icon = "fa fa-trash me-2";
        $btnLimpiar->click = '
            $("#btnPDF").remove();
            $("#btnExcel").remove();
            $("#tblMbn").html("");
            $("#comboTipo").val("");
            $("#comboEstado").val("");
            $("#inputFecha").val("");
            $(".selectpicker").selectpicker("refresh");
            $("#inputFechaMax").val("");
        ';

        $btnPDF = new stdClass();
        $btnPDF->id = 'btnPDF';
        $btnPDF->name = 'btnPDF';
        $btnPDF->icon = 'solid fa-file-pdf me-2';
        $btnPDF->class = 'btn btn-outline-danger me-1';
        $btnPDF->text = 'PDF';
        $btnPDF->value = 'PDF';

        $btnExcel = new stdClass();
        $btnExcel->id = 'btnExcel';
        $btnExcel->name = 'btnExcel';
        $btnExcel->text = 'Excel';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->class = 'btn btn-outline-success me-1';
        $btnExcel->value = 'Excel';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $this->agregaTools($this->ponBoton($btnBuscar));
        $this->agregaTools($this->ponBoton($btnLimpiar));

        if ($this->prm->emb_id == 3 && !empty($this->prm->mbn_fechaMovimiento)) {
            $this->agregaTools($this->ponBoton($btnPDF));
            $this->agregaTools($this->ponBoton($btnExcel));
        }

        $this->setContenedor('tblMbn');

        if (empty($this->prm->tmb_id)) {
            $this->mixScript('
                notify("Falta el tipo de movimiento", "error");
            ');
            return;
        }

        if ($this->prm->emb_id == 3 && !empty($this->prm->mbn_fechaMovimiento)) {
            $this->mixScript('
                $("#btnPDF").click(function(){
                    vinFondeo= "' . $this->prm->mbn_fechaMovimiento . '"
                    fechaMax = "' . $this->prm->mbn_fechaMaxMovimiento . '";
                    tipo = ' . $this->prm->tmb_id . ';
                    var form = $("<form>")
                        .attr("action", "rep/doc/tes/ministracion/rep_Fondeo.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                        .append($("<input>").attr("type", "hidden").attr("name", "fechaMax").attr("value", fechaMax))
                        .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", tipo))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                });

                $("#btnExcel").click(function(){
                    vinFondeo= "' . $this->prm->mbn_fechaMovimiento . '";
                    fechaMax = "' . $this->prm->mbn_fechaMaxMovimiento . '";
                    usr_id= ' . $this->usr_id . ';
                    tipo = ' . $this->prm->tmb_id . ';
                    var form = $("<form>")
                        .attr("action", "rep/excel/tes/ministracion/exp_Fondeo.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                        .append($("<input>").attr("type", "hidden").attr("name", "fechaMax").attr("value", fechaMax))
                        .append($("<input>").attr("type", "hidden").attr("name", "usr_id").attr("value", usr_id))
                        .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", tipo))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                });
            ');
        }

        $tabla = new stdClass();
        $tabla->id = 'tblMovimientos';
        $tabla->titulo = 'HISTORIAL DE MOVIMIENTOS';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMovimiento';
        $tabla->numFilas = 5;
        $tabla->comunica->emb_id = $this->prm->emb_id;
        $tabla->comunica->tmb_id = $this->prm->tmb_id;
        $tabla->comunica->mbn_fechaMovimiento = $this->prm->mbn_fechaMovimiento;
        $tabla->comunica->mbn_fechaMaxMovimiento = $this->prm->mbn_fechaMaxMovimiento;

        $tr = array();

        if ($this->prm->tmb_id == 3) {
            $th = new stdClass();
            $th->texto = 'DETALLE';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'MOVIMIENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CUENTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CUENTA ABONO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        if ($this->prm->tmb_id == 3) {
            $th = new stdClass();
            $th->texto = 'LOTES';
            array_push($tr, $th);
        }

        //CUERPO DE LA TABLA
        $trbody = array();

        if ($this->prm->tmb_id == 3) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-eye';
            $td->class = 'detalleMovimiento';
            $td->id = 'detalleMovimiento';
            $td->attr = new stdClass();
            $td->attr->mbn_id = 'mbn_id';
            $td->attr->tmb_id = '"' . $this->prm->tmb_id . '"';
            $td->attr->emb_id = '"' . $this->prm->emb_id . '"';
            $td->attr->mbn_fechaMovimiento = '"' . $this->prm->mbn_fechaMovimiento . '"';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'mbn_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tmb_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'emb_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_fechaMovimiento';
        array_push($trbody, $td);

        if ($this->prm->tmb_id == 3) {
            $td = new stdClass();
            $td->campoTxt = 'mbn_lotes';
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaMovimiento();

    }

    private function llenaTablaMovimiento()
    {

        $this->prm->emb_id = isset($this->prm->emb_id) ? $this->prm->emb_id : '';
        $this->prm->tmb_id = isset($this->prm->tmb_id) ? $this->prm->tmb_id : '';
        $this->prm->mbn_fechaMovimiento = isset($this->prm->mbn_fechaMovimiento) ? $this->prm->mbn_fechaMovimiento : '';
        $this->prm->mbn_fechaMaxMovimiento = isset($this->prm->mbn_fechaMaxMovimiento) ? $this->prm->mbn_fechaMaxMovimiento : '';

        if (empty($this->prm->tmb_id)) {
            $this->mixScript('
                notify("Falta el tipo de movimiento", "error");
            ');
            return;
        }

        $parametros = [
            'tmb_id' => $this->prm->tmb_id,
        ];
        $sql = '
            SELECT mbn_id, tmb_nombre, emb_nombre,
                cbs_id_cargo, cbs_id_abono, mbn_concepto,
                mbn_importe, COUNT(lf.fpa_id) AS mbn_lotes,
                mbn_fechaMovimiento, emb_id, tmb_id
            FROM '.BD_TESORERIA.'.ban_MovimientoBancario AS m
            INNER JOIN '.BD_TESORERIA.'.cat_EstadoMovimientoBancario AS e USING(emb_id)
            INNER JOIN '.BD_TESORERIA.'.cat_TipoMovimientoBancario AS t USING(tmb_id)
            LEFT JOIN '.BD_TESORERIA.'.vin_LoteFondeo AS lf USING (mbn_id)
            LEFT JOIN '.BD_TESORERIA.'.lot_Lote AS l ON(
                lf.fpa_id = l.fpa_id
                AND lf.msc_id = l.msc_id
                AND lf.lot_ejercicio = l.lot_ejercicio
                AND lf.lot_id = l.lot_id
            )
            WHERE tmb_id = :tmb_id
            ';

        if (!empty($this->prm->emb_id)) {
            $parametros += [
                'emb_id' => $this->prm->emb_id,
            ];
            $sql .= 'AND emb_id = :emb_id
            ';
        }

        if (!empty($this->prm->mbn_fechaMovimiento)) {
            $fecha = DateTime::createFromFormat('d/m/Y', $this->prm->mbn_fechaMovimiento);
            $parametros += [
                'mbn_fechaMovimiento' => $fecha->format('Y-m-d'),
            ];
            $sql .= 'AND mbn_fechaMovimiento >= :mbn_fechaMovimiento
            ';
        }

        if (!empty($this->prm->mbn_fechaMaxMovimiento)) {
            $fecha = DateTime::createFromFormat('d/m/Y', $this->prm->mbn_fechaMaxMovimiento);
            $parametros += [
                'mbn_fechaMaxMovimiento' => $fecha->format('Y-m-d'),
            ];
            $sql .= 'AND mbn_fechaMovimiento <= :mbn_fechaMaxMovimiento
            ';
        }

        $sql .= 'GROUP BY mbn_id';

        $this->setSql($sql);
        $this->setParams($parametros);

        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblMovimientos';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 5;

        $this->wrLlenaTabla($nav);

        $this->mixScript('

            $(".row_c").html("1");

            $(".detalleMovimiento").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaDetalleMovimiento",
                    mbn_id: $(this).attr("mbn_id"),
                    tmb_id: $(this).attr("tmb_id"),
                    emb_id: $(this).attr("emb_id"),
                    mbn_fechaMovimiento: $(this).attr("mbn_fechaMovimiento")
                });
            });

            $(".btnRepTbl").click(function(){
                vinFondeo= "' . $this->prm->mbn_fechaMovimiento . '";
                tipo = $(this).attr("tmb_id");
                mbn_id = $(this).attr("mbn_id");
                var form = $("<form>")
                    .attr("action", "rep/doc/tes/rep_tes02.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "vinFondeo").attr("value", vinFondeo))
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", tipo))
                    .append($("<input>").attr("type", "hidden").attr("name", "mbn_id").attr("value", mbn_id))
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    private function pintaTablaDetalleMovimiento()
    {

        $this->prm->mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';
        $this->prm->emb_id = isset($this->prm->emb_id) ? $this->prm->emb_id : '';
        $this->prm->tmb_id = isset($this->prm->tmb_id) ? $this->prm->tmb_id : '';
        $this->prm->mbn_fechaMovimiento = isset($this->prm->mbn_fechaMovimiento) ? $this->prm->mbn_fechaMovimiento : '';

        if (empty($this->prm->mbn_id)) {
            $this->mixScript('
                notify("Falta id del movimiento", "error");
            ');
            return;
        }

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnregresarindex';
        $btnRegresarIndex->name = 'btnregresarindex';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->type = 'button';
        $btnRegresarIndex->class = "btn btn-outline-secondary me-1";
        $btnRegresarIndex->icon = "solid fa-circle-left me-2";
        $btnRegresarIndex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "pintaHistorialMovimientos",
                tmb_id: "' . $this->prm->tmb_id . '",
                emb_id: "' . $this->prm->emb_id . '",
                mbn_fechaMovimiento: "' . $this->prm->mbn_fechaMovimiento . '",
                busqueda: true
            });
        ';

        $btnInfo = new stdClass();
        $btnInfo->id = 'btnInfo';
        $btnInfo->name = 'btnInfo';
        $btnInfo->icon = ' me-2';
        $btnInfo->class = 'btn btn-outline-secondary me-1';

        $parametro = [
            'mbn_id' => $this->prm->mbn_id,
        ];
        $sql = 'SELECT mbn_importe FROM '.BD_TESORERIA.'.ban_MovimientoBancario WHERE mbn_id = :mbn_id';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($parametro);
        $con->desconectar();

        $this->agregaTools($this->ponBoton($btnRegresarIndex));

        if ($fila = $con->extraer()) {
            $total = number_format($fila['mbn_importe'], 2, '.', ',');
            $btnInfo->value = 'Total del movimiento: $ ' . $total;
            $this->agregaTools($this->ponBoton($btnInfo));
        }

        $tabla = new stdClass();
        $tabla->id = 'tblDetalleMovimiento';
        $tabla->titulo = 'DETALLE DEL MOVIMIENTOS ' . $this->prm->mbn_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetalleMovimiento';
        $tabla->comunica->mbn_id = $this->prm->mbn_id;
        $tabla->comunica->emb_id = $this->prm->emb_id;
        $tabla->comunica->tmb_id = $this->prm->tmb_id;
        $tabla->comunica->mbn_fechaMovimiento = $this->prm->mbn_fechaMovimiento;

        $tr = array();

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA ABONO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tlo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaAutorizacion';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <legend>Detalle del movimiento ' . $this->prm->mbn_id . '</legend>
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</fieldset>
            </div>
        ';

        $this->mixHtml($html);
        $this->llenaTablaDetalleMovimiento();

    }

    private function llenaTablaDetalleMovimiento()
    {

        $this->prm->mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';
        $this->prm->emb_id = isset($this->prm->emb_id) ? $this->prm->emb_id : '';
        $this->prm->tmb_id = isset($this->prm->tmb_id) ? $this->prm->tmb_id : '';
        $this->prm->mbn_fechaMovimiento = isset($this->prm->mbn_fechaMovimiento) ? $this->prm->mbn_fechaMovimiento : '';

        if (empty($this->prm->mbn_id)) {
            $this->mixScript('
                notify("Falta id del movimiento", "error");
            ');
            return;
        }

        $parametro = [
            'mbn_id' => $this->prm->mbn_id,
        ];
        $sql = '
            SELECT l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id, l.lot_importe,
                l.lot_fechaAutorizacion, tlo_nombre, cbs_id_cargo, cbs_id_abono,
                CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ),
                    LPAD(l.msc_id, 2, "0"),
                    LPAD(l.lot_id, 4, "0"),
                    SUBSTR(l.lot_ejercicio, 3) )
                AS lote
            FROM '.BD_TESORERIA.'.ban_MovimientoBancario AS m
            INNER JOIN '.BD_TESORERIA.'.vin_LoteFondeo AS lf USING (mbn_id)
            INNER JOIN '.BD_TESORERIA.'.lot_Lote AS l ON (
                lf.fpa_id = l.fpa_id
                AND lf.msc_id = l.msc_id
                AND lf.lot_ejercicio = l.lot_ejercicio
                AND lf.lot_id = l.lot_id
            )
            INNER JOIN '.BD_TESORERIA.'.cat_TipoLote USING (tlo_id)
            WHERE mbn_id = :mbn_id';

        $this->setSql($sql);
        $this->setParams($parametro);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblDetalleMovimiento';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 5;
        $this->wrLlenaTabla($nav);

    }

}

if (isset($_SESSION[_S]['prm']['T']['4']['4']['1'])) {
    new ControladorMinFondeo();
} else {
    WebResponse::sinPermisos();
}
