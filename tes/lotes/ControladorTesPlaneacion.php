<?php
/**
 * Description of ControladorTesPlaneacion
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Created: 18-Nov-2018
 * Comment: -----
 * Objective: Visualizar lotes generados, se podran autorizar o cancelar, se podrá
visualizar y editar su detalle

 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de lectura, escritura y validación
 **/

/*
 * En esta clase se manejarán los lotes generados, se podrán autorizar o cancelar
 * segun se requiera, además se podra ver su detalle con la posibilidad de editar
 * el lote.
 * 01-03-18 Se modifico la condición que verifica si la cuenta es federal en el caso de tipo de lote de municipios.
 * 01-03-18 Se agrego la condicion de las cuentas federales con tratemiento estatal
 */

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_EliminarClcLotePlaneacion.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_AutorizarLotePlaneacion.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_CancelarLotePlaneacion.php';

class ControladorTesPlaneacion extends WebResponse
{
    private $anioEjercicio;
    private $usuario_id;
    private $ip;

    public function __construct()
    {

        parent::__construct();
        $this->pantalla = 'tes/lotes/ControladorTesPlaneacion.php';
        $this->anioEjercicio = APP_EJERCICIO;
        $this->usuario_id = $_SESSION[_S]['usr_id'];
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        switch ($this->metodo) {
            case 'detalleLote':
                $this->detalleLote();
                break;
            case 'llenaTablaLote':
                $this->llenaTablaLote();
                break;
            case 'llenaTablaDetLote':
                $this->llenaTablaDetLote();
                break;
            case 'cambiaEstadoLotePlaneacion':
                $this->cambiaEstadoLotePlaneacion();
                break;
            case 'quitaClc':
                $this->quitaClc();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    /* Se declaran los botones y tablas que contendrá la vista/*/
    public function main()
    {

        //Crea el boton de regreso
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

        $this->agregaTools('');

        /**
         * Declaramos la tabla.
         */
        $tabla = new stdClass();
        $tabla->id = 'tblLote';
        $tabla->titulo = 'LISTADO DE LOTES';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        //Array para dibujar la tabla
        $tr = array();

        //Encabezados
        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC\'s';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AUTORIZAR';
        $th->width = '8rem';
        $th->align = 'center';
        if (isset($_SESSION[_S]['prm']['T']['3']['3']['3'])) {
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-eye';
        $td->class = 'lotDet';
        $td->id = 'lot_id';
        $td->attr = new stdClass();
        $td->attr->lotDet = 'lot_id';
        $td->attr->lotFpa = 'fpa_id';
        $td->attr->lotMsc = 'msc_id';
        $td->attr->lotEje = 'lot_ejercicio';
        $td->attr->lotMod = 'lot_tipo';
        $td->attr->tlo_id = 'tlo_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_tipo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fecha';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_responsable';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_clcs';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_total';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'check';
        $td->class = 'lotAutorizar btn-outline-success';
        $td->attr = new stdClass();
        $td->attr->lotAutorizar = 'lot_id';
        $td->attr->lotFpa = 'fpa_id';
        $td->attr->lotMsc = 'msc_id';
        $td->attr->lotEje = 'lot_ejercicio';
        $td->attr->lotMod = 'lot_tipo';
        $td->attr->tlo_id = 'tlo_id';
        /* Verificamos permisos de Autorización para dibujar el elemento/*/
        if (isset($_SESSION[_S]['prm']['T']['3']['3']['3'])) {
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->class = 'lotEliminar btn-outline-danger';
        $td->attr = new stdClass();
        $td->attr->lotEliminar = 'lot_id';
        $td->attr->lotFpa = 'fpa_id';
        $td->attr->lotMsc = 'msc_id';
        $td->attr->lotEje = 'lot_ejercicio';
        $td->attr->lotMod = 'lot_tipo';
        $td->attr->tlo_id = 'tlo_id';
        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if (isset($_SESSION[_S]['prm']['T']['3']['3']['2'])) {
            array_push($trbody, $td);
        }

        //Llenamos tabla
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $tabla->comunica->metodo = 'llenaTablaLote';
        $tabla->numFilas = 50;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);

        $this->mixHtml('
            <div class="row">
                <legend>PLANEACIÓN</legend>
            </div>
            <!--div class="row">
                <fieldset class="col-xs-4 col-md-4">

                </fieldset-->
            </div>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ');
        $this->llenaTablaLote();
    }

    /* Se declaran los botones y tablas que contendrá la vista/*/
    private function detalleLote()
    {

        /* Vemos si extiste el id de la propuesta para ver su detalle, si no existe lo hace una cadena vacia/*/
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : '';
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : '';
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $lot_tipo = isset($this->prm->lot_tipo) ? $this->prm->lot_tipo : '';
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';

        /* Crea el boton de regreso /*/
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
        $btnRegresar->class = "btn btn-outline-secondary me-2";
        $btnRegresar->icon = "fa-solid fa-circle-left me-1";
        $btnRegresar->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnAutorizar = new stdClass();
        $btnAutorizar->id = 'btnAutorizar';
        $btnAutorizar->name = 'btnAutorizar';
        $btnAutorizar->text = 'Autorizar';
        $btnAutorizar->value = 'Autorizar';
        $btnAutorizar->type = 'button';
        $btnAutorizar->class = "btn btn-outline-success me-2";
        $btnAutorizar->icon = "fa fa-check me-1";
        $btnAutorizar->click = '
            //Mandamos a llamar al metodo para cambiar el estado del lote a Autorizado
            alertify.confirm(
                "<h5> AUTORIZAR LOTE </h5>",
                "¿Realmente desea autorizar el lote?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "cambiaEstadoLotePlaneacion",
                        accionLote: "autorizar",
                        lot_id: ' . $lot_id . ',
                        fpa_id: ' . $fpa_id . ',
                        msc_id: ' . $msc_id . ',
                        lot_ejercicio: ' . $lot_ejercicio . ',
                        lot_tipo: "' . $lot_tipo . '",
                        tlo_id: ' . $tlo_id . '
                    });
                },
                function(){},
            ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'btnEliminar';
        $btnEliminar->name = 'btnEliminar';
        $btnEliminar->text = 'Eliminar';
        $btnEliminar->value = 'Eliminar';
        $btnEliminar->type = 'button';
        $btnEliminar->color = 'rojo';
        $btnEliminar->icon = "fa fa-trash me-2";
        $btnEliminar->click = '
            alertify.confirm(
                "<h5> CANCELAR LOTE </h5>",
                "¿Realmente desea cancelar el lote?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "cambiaEstadoLotePlaneacion",
                        accionLote: "cancelar",
                        lot_id: ' . $lot_id . ',
                        fpa_id: ' . $fpa_id . ',
                        msc_id: ' . $msc_id . ',
                        lot_ejercicio: ' . $lot_ejercicio . ',
                        lot_tipo: "' . $lot_tipo . '",
                        tlo_id: ' . $tlo_id . '
                    });
                },
                function(){},
            ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        /* Dibuja los botones /*/
        $this->agregaTools($this->ponBoton($btnRegresar));

        /* Verificamos permisos de Autorización para dibujar el elemento/*/
        if (isset($_SESSION[_S]['prm']['T']['3']['3']['3'])) {
            $this->agregaTools($this->ponBoton($btnAutorizar));
        }

        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if (isset($_SESSION[_S]['prm']['T']['3']['3']['2'])) {
            $this->agregaTools($this->ponBoton($btnEliminar));
        }

        $tabla = new stdClass();
        $tabla->id = 'tblDetLote';
        $tabla->titulo = 'DETALLE DEL LOTE ' . $lot_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetLote';
        $tabla->comunica->lot_id = $this->prm->lot_id;
        $tabla->comunica->fpa_id = $this->prm->fpa_id;
        $tabla->comunica->msc_id = $this->prm->msc_id;
        $tabla->comunica->lot_ejercicio = $this->prm->lot_ejercicio;
        $tabla->comunica->lot_tipo = $this->prm->lot_tipo;
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
        $td->formato = 'moneda';
        $td->align = 'center';
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
        $td->class = 'lotDetEliminar btn-outline-danger';
        $td->attr = new stdClass();
        $td->attr->lotDetEliminar = 'lotDet';
        $td->attr->lotDetFpa = 'lotDet_fpa';
        $td->attr->lotDetMsc = 'lotDet_msc';
        $td->attr->lotDetEjercicio = 'lotDet_ejercicio';
        $td->attr->lotDetGpo = 'lotDet_gpo';
        $td->attr->lotDetUr = 'lotDet_ur';
        $td->attr->lotDetClc = 'lotDet_clc';
        $td->attr->lom_id = 'lom_id';
        if (isset($_SESSION[_S]['prm']['T']['3']['3']['2'])) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div class="row">
                <legend>PLANEACIÓN</legend>
            </div>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ');
        $this->llenaTablaDetLote();
    }

    /* Llenamos la tabla de Lotes /*/
    private function llenaTablaLote()
    {

        /**
         * Se arma la consulta.
         */
        $sql2026 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = ' . APP_EJERCICIO . '
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';
        $sql2025 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2025 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2025
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';
        $sql2024 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2024 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2024
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';
        $sql2023 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2023 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2023
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';
        $sql2022 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2022 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2022
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';
        $sql2021 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2021 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2021
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';
        $sql2020 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2020 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2020
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';
        $sql2019 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2019 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2019
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';

        $sql2018 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2018 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS clc ON (ld.gpo_id = clc.gpo_id AND ld.ur_id = clc.ur_id AND ld.clc_id = clc.clc_id)
            WHERE l.elo_id = 3
                AND l.lot_ejercicio = 2018
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';

        $sql2017 = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.clc_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            INNER JOIN ' . BD_CTRL_2017 . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
                INNER JOIN ctrlAnterior.scp_Clc AS clcA ON (ld.gpo_id = clcA.gpo_id AND ld.ur_id = clcA.ur_id AND ld.clc_id = clcA.clc_id)
            INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS clc ON (clcA.gpo_id = clc.gpo_id AND clcA.ur_id = clc.ur_id AND clcA.clc_id = clc.clc_id)
            WHERE
                l.elo_id = 3
                AND l.lot_ejercicio = 2017
                AND l.lot_ejr = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total
            ';

        $sqlManuales = '
            SELECT
                l.lot_id,
                tl.tlo_nombre AS lot_tipo,
                l.tlo_id,
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_clave AS lot,
                l.lot_fechaGeneracion AS lot_fecha,
                MAX(ur.ur_nombre) AS lot_responsable,
                COUNT(ld.lom_id) AS lot_clcs,
                l.lot_importe AS lot_total
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
            LEFT JOIN ' . BD_CTRL . '.cat_UnidadResponsable AS ur
                ON(ld.ur_id = ur.ur_id AND ld.gpo_id = ur.gpo_id)
            WHERE
                l.elo_id = 3
                AND l.lot_ejercicio = ' . APP_EJERCICIO . '
            GROUP BY
                l.lot_id, lot_tipo, l.tlo_id, l.fpa_id, l.msc_id,l.lot_ejercicio,lot,lot_fecha, lot_total';
        if ($this->serverProduccion) {
            $sql = '
                SELECT
                    lot_id,
                    lot_tipo,
                    tlo_id,
                    fpa_id,
                    msc_id,
                    lot_ejercicio,
                    lot,
                    lot_fecha,
                    lot_responsable,
                    lot_clcs,
                    lot_total
                FROM
                    (' . $sql2017
                . ' UNION ALL ' . $sql2018
                . ' UNION ALL ' . $sql2019
                . ' UNION ALL ' . $sql2020
                . ' UNION ALL ' . $sql2021
                . ' UNION ALL ' . $sql2022
                . ' UNION ALL ' . $sql2023
                . ' UNION ALL ' . $sql2024
                . ' UNION ALL ' . $sql2025
                . ' UNION ALL ' . $sql2026
                . ' UNION ALL ' . $sqlManuales . '
                    ) AS sub
                ORDER BY
                    lot_fecha DESC';
        } else {
            $sql = '
                SELECT
                    lot_id,
                    lot_tipo,
                    tlo_id,
                    fpa_id,
                    msc_id,
                    lot_ejercicio,
                    lot,
                    lot_fecha,
                    lot_responsable,
                    lot_clcs,
                    lot_total
                FROM
                    (' . $sql2021
                . ' UNION ALL ' . $sql2022
                . ' UNION ALL ' . $sql2023
                . ' UNION ALL ' . $sql2024
                . ' UNION ALL ' . $sql2025
                . ' UNION ALL ' . $sql2026
                . ' UNION ALL ' . $sqlManuales . '
                    ) AS sub
                ORDER BY
                    lot_fecha DESC';
        }

        $this->conectar();
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = "tblLote";
        $this->wrLlenaTabla($nav);

        /*Creamos el script para darle funcionalidad a los botones /*/
        $this->mixScript('
            $(".lotDet").click(function(){
                //Mandamos a llamar al metodo para mostrar el detalle del lote
                comunica("' . $this->pantalla . '",{
                    metodo: "detalleLote",
                    lot_id: $(this).attr("lotDet"),
                    fpa_id: $(this).attr("lotFpa"),
                    msc_id: $(this).attr("lotMsc"),
                    lot_ejercicio: $(this).attr("lotEje"),
                    lot_tipo: $(this).attr("lotMod"),
                    tlo_id: $(this).attr("tlo_id")
                });
            });

            $(".lotAutorizar").click(function(){
                var lot_id = $(this).attr("lotAutorizar");
                var fpa_id = $(this).attr("lotFpa");
                var msc_id = $(this).attr("lotMsc");
                var lot_ejercicio = $(this).attr("lotEje");
                var lot_tipo = $(this).attr("lotMod");
                var tlo_id = $(this).attr("tlo_id");
                //Mandamos a llamar al metodo para cambiar el estado del lote a Autorizado
                alertify.confirm(
                    "<h5> AUTORIZAR LOTE </h5>",
                    "¿Realmente desea autorizar el lote?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "cambiaEstadoLotePlaneacion",
                            accionLote: "autorizar",
                            lot_id: lot_id,
                            fpa_id: fpa_id,
                            msc_id: msc_id,
                            lot_ejercicio: lot_ejercicio,
                            lot_tipo: lot_tipo,
                            tlo_id: tlo_id
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".lotEliminar").click(function(){
                var lot_id = $(this).attr("lotEliminar");
                var fpa_id = $(this).attr("lotFpa");
                var msc_id = $(this).attr("lotMsc");
                var lot_ejercicio = $(this).attr("lotEje");
                var tlo_id = $(this).attr("tlo_id");
                alertify.confirm(
                    "<h5> CANCELAR LOTE </h5>",
                    "¿Realmente desea cancelar el lote?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "cambiaEstadoLotePlaneacion",
                            accionLote: "cancelar",
                            lot_id: lot_id,
                            fpa_id: fpa_id,
                            msc_id: msc_id,
                            lot_ejercicio: lot_ejercicio,
                            tlo_id: tlo_id
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    /* Llenamos la tabla Detalle Propuesta /*/
    private function llenaTablaDetLote()
    {

        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $lot_tipo = isset($this->prm->lot_tipo) ? $this->prm->lot_tipo : '';
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';

        $params = [];
        $sql = '';
        $sqlExtra = '';

        switch ($tlo_id) {
            case 6: //Lote Manual
            case 7: //Lote Federal
                $sql .= '
                    SELECT
                        dl.lom_id,
                        l.fpa_id AS lotDet_fpa,
                        l.msc_id AS lotDet_msc,
                        l.lot_ejercicio AS lotDet_ejercicio,
                        l.lot_id AS lotDet,
                        dl.gpo_id AS lotDet_gpo,
                        dl.ur_id AS lotDet_ur,
                        dl.clc_id AS lotDet_clc,
                        dl.lom_importe AS lotDet_importe,
                        dl.lom_beneficiario AS lotDet_beneficiario,
                        dl.lom_concepto AS lotDet_concepto,
                        l.lot_fechaGeneracion AS lotDet_fecha
                    FROM
                        tesoreria.lot_Lote AS l
                    INNER JOIN tesoreria.lot_PropuestaManual AS dl USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                    ';

                switch ($lot_ejercicio) {
                    case 2026:
                        $sql .= 'LEFT JOIN ' . BD_CTRL . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        ';
                        break;
                    case 2025:
                        $sql .= '
                            LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2025 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2025
                        ';
                        break;
                    case 2024:
                        $sql .= '
                            LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2024 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2024
                        ';
                        break;
                    case 2023:
                        $sql .= '
                            LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2023 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2023
                        ';
                        break;
                    case 2022:
                        $sql .= '
                            LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2022 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2022
                        ';
                        break;
                    case 2021:
                        $sql .= '
                            LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2021 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2021
                        ';
                        break;
                    case 2020:
                        $sql .= '
                            LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2020 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2020
                        ';
                        break;
                    case 2019:
                        $sql .= '
                            LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2019 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2019
                        ';
                        break;
                    case 2018:
                        $sql .= 'LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            LEFT JOIN ' . BD_CTRL_2018 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2018
                        ';
                        break;
                    case 2017:
                        $sql .= 'LEFT JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            LEFT JOIN ' . BD_CTRL_2017 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2017
                        ';
                        break;
                }

                break;
            default:
                $sql .= '
                    SELECT
                        l.fpa_id AS lotDet_fpa,
                        l.msc_id AS lotDet_msc,
                        l.lot_ejercicio AS lotDet_ejercicio,
                        l.lot_id AS lotDet,
                        dl.gpo_id AS lotDet_gpo,
                        dl.ur_id AS lotDet_ur,
                        dl.clc_id AS lotDet_clc,
                        dl.lot_importeClc AS lotDet_importe,
                        clc.clc_beneficiario AS lotDet_beneficiario,
                        clc.clc_concepto AS lotDet_concepto,
                        l.lot_fechaGeneracion AS lotDet_fecha
                    FROM
                        tesoreria.lot_Lote AS l
                    INNER JOIN tesoreria.lot_LoteClc AS dl USING (lot_id, fpa_id, msc_id, lot_ejercicio)';

                switch ($lot_ejercicio) {
                    case 2026:
                        $sql .= 'INNER JOIN ' . BD_CTRL . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        ';
                        break;
                    case 2025:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2025
                        ';
                        break;
                    case 2024:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2024
                        ';
                        break;
                    case 2023:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2023
                        ';
                        break;
                    case 2022:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2022
                        ';
                        break;
                    case 2021:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2021
                        ';
                        break;
                    case 2020:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2020
                        ';
                        break;
                    case 2019:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2019
                        ';
                        break;
                    case 2018:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2018
                        ';
                        break;
                    case 2017:
                        $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                            ';
                        $sqlExtra .= 'WHERE ejr_id_clc = 2017
                        ';
                        break;
                }

                break;
        }

        if (!empty($sql)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $params = [$lot_id,$fpa_id,$msc_id,$lot_ejercicio];
            $sqlExtra .= 'l.lot_id = ?
                        AND dl.fpa_id= ?
                        AND dl.msc_id= ?
                        AND dl.lot_ejercicio= ?';
        }

        /* Hacemos la consulta y llenamos la tabla con el resultado /*/
        $this->conectar();
        $this->setSql($sql . $sqlExtra);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tblDetLote";
        $this->setParams($params);
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".lotDetEliminar").click(function(){
                var lot_id = $(this).attr("lotDetEliminar");
                var fpa_id = $(this).attr("lotDetFpa");
                var msc_id = $(this).attr("lotDetMsc");
                var lot_ejercicio = $(this).attr("lotDetEjercicio");
                var gpo_id = $(this).attr("lotDetGpo");
                var ur_id = $(this).attr("lotDetUr");
                var clc_id = $(this).attr("lotDetClc");
                var lom_id = $(this).attr("lom_id");

                alertify.confirm(
                    "<h5> ELIMINAR CLC </h5>",
                    "¿Realmente desea eliminar este CLC?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "quitaClc",
                            lot_id: lot_id,
                            fpa_id: fpa_id,
                            msc_id: msc_id,
                            lot_ejercicio: lot_ejercicio,
                            gpo_id: gpo_id,
                            ur_id: ur_id,
                            clc_id: clc_id,
                            lom_id: lom_id,
                            tlo_id:"' . $tlo_id . '",
                            lot_tipo: "' . $lot_tipo . '"
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    private function cambiaEstadoLotePlaneacion()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';

        switch ($this->prm->accionLote) {
            case 'autorizar':
                $autorizaLote = new sp_AutorizarLotePlaneacion();
                $autorizaLote->setLot_id($lot_id);
                $autorizaLote->setFpa_id($fpa_id);
                $autorizaLote->setMsc_id($msc_id);
                $autorizaLote->setLot_ejercicio($ejercicio);
                $autorizaLote->setTlo_id($tlo_id);
                $autorizaLote->setSis_id("TES");
                $autorizaLote->setEjercicio($this->anioEjercicio);
                $autorizaLote->setBit_observacion("LOTE AUTORIZADO DESDE PANTALLA T_3_3");
                $autorizaLote->execute();
                if (!$autorizaLote->getBandera()) {
                    $mensaje = $autorizaLote->getMensaje();
                    $this->mixScript('
                            notify(" ' . $mensaje . '", "error",
                        );
                    ');
                } else {
                    $mensaje = $autorizaLote->getMensaje();
                    //Muestra mensaje
                    $this->mixScript('
                            notify(" ' . $mensaje . '", "success",
                            );
                        ');
                }
                break;
            case 'cancelar':
                $cancelaLote = new sp_CancelarLotePlaneacion();
                $cancelaLote->setLot_id($lot_id);
                $cancelaLote->setFpa_id($fpa_id);
                $cancelaLote->setMsc_id($msc_id);
                $cancelaLote->setLot_ejercicio($ejercicio);
                $cancelaLote->setTlo_id($tlo_id);
                $cancelaLote->setSis_id("TES");
                $cancelaLote->setEjercicio($this->anioEjercicio);
                $cancelaLote->setBit_observacion("LOTE CANCELADO DESDE PANTALLA T_3_3");
                $cancelaLote->execute();
                if (!$cancelaLote->getBandera()) {
                    $mensaje = $cancelaLote->getMensaje();
                    $this->mixScript('
                                notify(" ' . $mensaje . '", "error",
                            );
                        ');
                } else {
                    $mensaje = $cancelaLote->getMensaje();
                    //Muestra mensaje
                    $this->mixScript('
                            notify(" ' . $mensaje . '", "success",
                            );
                        ');
                }
                break;
        }
        $this->main();
    }

    // Quita la CLC del lote
    private function quitaClc()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : "";
        $ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : "";
        $clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : "";

        $lot_tipo = isset($this->prm->lot_tipo) ? $this->prm->lot_tipo : "";
        $lom_id = isset($this->prm->lom_id) && $this->prm->lom_id != "" ? $this->prm->lom_id : '0';

        $quitaClc = new sp_EliminarClcLotePlaneacion();
        $quitaClc->setLot_id($lot_id);
        $quitaClc->setFpa_id($fpa_id);
        $quitaClc->setMsc_id($msc_id);
        $quitaClc->setLot_ejercicio($ejercicio);
        $quitaClc->setGpo_id($gpo_id);
        $quitaClc->setUr_id($ur_id);
        $quitaClc->setClc_id($clc_id);
        $quitaClc->setLom_id($lom_id);
        $quitaClc->setSis_id("TES");
        $quitaClc->setEjercicio($this->anioEjercicio);
        $quitaClc->setBit_observacion("ELIMINA CLC DEL LOTE DESDE PANTALLA T_3_3");
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
            /* Si ya no quedan clc's, la propuesta se elimina*/
            if ($numReg == 0) {
                $this->main();
            } else {
                $this->prm->fpa_id = $fpa_id;
                $this->prm->msc_id = $msc_id;
                $this->prm->lot_ejercicio = $ejercicio;
                $this->prm->lot_id = $lot_id;
                $this->prm->lot_tipo = $lot_tipo;
                $this->detalleLote();
            }
        }
    }
}

if (isset($_SESSION[_S]['prm']['T']['3']['3']['1'])) {
    new ControladorTesPlaneacion();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
