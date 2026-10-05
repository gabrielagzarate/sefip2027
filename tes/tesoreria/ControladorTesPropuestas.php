<?php

/**
 * Description of ControladorTesPropuestas
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 18-Dic-2018
 * Update: 14-Oct-2022
 * Comment: -----
 * Objective:
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de, lectura, escritura y validación
 **/
/*
 * En esta clase se manejarán las propuestas generadas, se podrán autorizar o cancelar
 * segun se requiera, además se podra ver su detalle con la posibilidad de editar
 * la propuesta.
 */

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_AutorizarPropuesta.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_CancelarPropuesta.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_EliminaDetallePropuesta.php';

class ControladorTesPropuestas extends WebResponse
{
    private $escritura;
    private $validacion;
    private $ip;
    private $usuario_id;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "tes/tesoreria/ControladorTesPropuestas.php";
        $this->escritura = isset($_SESSION[_S]['prm']['T']['3']['2']['2']) ? true : false;
        $this->validacion = isset($_SESSION[_S]['prm']['T']['3']['2']['3']) ? true : false;
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->usuario_id = $_SESSION[_S]['usr_id'];

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                switch ($this->metodo) {
                    case 'detallePropuesta':
                        $this->detallePropuesta();
                        break;
                    case 'llenaTablaProp':
                        $this->llenaTablaProp();
                        break;
                    case 'llenaTablaDetProp':
                        $this->llenaTablaDetProp();
                        break;
                    case 'cambiaEstadoPropuesta':
                        $this->cambiaEstadoPropuesta();
                        break;
                    case 'eliminaDetalle':
                        $this->eliminaDetalle();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    //Se declaran los botones y tablas que contendrá la vista
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

        $tabla = new stdClass();
        $tabla->id = 'tblProp';
        $tabla->titulo = 'LISTADO DE PROPUESTAS';
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
        $th->texto = 'PROUPESTA';
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

        if ($this->validacion) {
            $th = new stdClass();
            $th->texto = 'AUTORIZAR';
            $th->width = '8rem';
            $th->align = 'center';
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
        $td->class = 'proDetalle';
        $td->attr = new stdClass();
        $td->attr->proDetalle = 'pro_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_tipo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_anio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_fecha';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_responsable';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_clcs';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'check';
        $td->class = 'proAutorizar btn-outline-success';
        $td->attr = new stdClass();
        $td->attr->proAutorizar = 'pro_id';
        if ($this->validacion) {
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->class = 'proEliminar btn-outline-danger';
        $td->attr = new stdClass();
        $td->attr->proEliminar = 'pro_id';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        //Llenamos tabla
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $tabla->comunica->metodo = 'llenaTablaProp';

        $html = '
            <div class="row">
                <legend>PROPUESTAS</legend>
            </div>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';
        $this->mixHtml($html);
        $this->llenaTablaProp();
    }

    //Se declaran los elementos a utilizar
    private function detallePropuesta()
    {
        //Vemos si extiste el id de la propuesta para ver su detalle, si no existe lo hace una cadena vacia
        $det_prop = isset($this->prm->det_prop) ? $this->prm->det_prop : "";

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
                "<h5> AUTORIZAR PROPUESTA </h5>",
                "¿Realmente desea autorizar la propuesta?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "cambiaEstadoPropuesta",
                        accionPropuesta: "autorizar",
                        proId: ' . $det_prop . '
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
        $btnEliminar->class = "btn btn-outline-danger me-2'";
        $btnEliminar->icon = "fa-solid fa-trash me-1";
        $btnEliminar->click = '
            alertify.confirm(
                "<h5> CANCELAR PROPUESTA </h5>",
                "¿Realmente desea cancelar la propuesta?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "cambiaEstadoPropuesta",
                        accionPropuesta: "cancelar",
                        proId: ' . $det_prop . '
                    });
                },
                function(){},
            ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        //Dibuja los botones
        $this->agregaTools($this->ponBoton($btnRegresar));

        if ($this->validacion) {
            $this->agregaTools($this->ponBoton($btnAutorizar));
        }

        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnEliminar));
        }

        $tabla = new stdClass();
        $tabla->id = 'tblDetProp';
        $tabla->titulo = 'DETALLE DE LA PROPUESTA ' . $det_prop;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetProp';

        //Array para dibujar la tabla
        $tr = array();

        //Encabezados
        $th = new stdClass();
        $th->texto = 'PROPUESTA';
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

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->campoTxt = 'proDet_propuesta';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_gpo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_ur';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_clc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_importe';
        $td->align = 'center';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_fecha';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->class = 'proDetEliminar btn-danger';
        $td->id = 'proDet_eliminar';
        $td->attr = new stdClass();
        $td->attr->proDetPropuesta = 'proDet_propuesta';
        $td->attr->proDetGpo = 'proDet_gpo';
        $td->attr->proDetUr = 'proDet_ur';
        $td->attr->proDetClc = 'proDet_clc';
        $td->attr->proDetEje = 'pro_ejercicio';
        if (isset($_SESSION[_S]['prm']['T']['3']['2']['2'])) {
            array_push($trbody, $td);
        }

        //Llenamos tabla
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <legend>PLANEACIÓN</legend>
            </div>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';
        $this->mixHtml($html);
        $this->llenaTablaDetProp();
    }

    //Llenamos la tabla de Propuestas
    private function llenaTablaProp()
    {
        //Se arma la consulta
        $sql = "
            SELECT
                p.pro_id,
                MAX(tp.tlo_nombre) AS pro_tipo,
                MAX(p.pro_ejercicio) AS pro_anio,
                MAX(p.pro_fechaGeneracion) AS pro_fecha,
                MAX(ur.ur_nombre) AS pro_responsable,
                IFNULL(COUNT(pd.clc_id), 0) AS pro_clcs,
                IFNULL(SUM(pd.pro_importeClc), 0) AS pro_total
            FROM
                " . BD_TESORERIA . ".pro_Propuesta AS p
            LEFT JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pd USING (pro_id)
            INNER JOIN " . BD_TESORERIA . ".cat_TipoLote AS tp USING (tlo_id)
            INNER JOIN " . BD_CTRL . ".cat_Usuario AS u ON (p.usr_id = u.usr_id)
            INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable AS ur
                ON(pd.ur_id = ur.ur_id AND pd.gpo_id = ur.gpo_id)
            WHERE
                p.elo_id = 1 AND p.pro_ejr = " . APP_EJERCICIO . "
            GROUP BY
                p.pro_id
        ";

        //Hacemos la consulta y llenamos la tabla con el resultado
        $this->conectar();
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = "tblProp";
        $this->wrLlenaTabla($nav);

        //Creamos el script para darle funcionalidad a los botones
        $this->mixScript('
            $(".proDetalle").click(function(){
                //Mandamos a llamar al metodo para mostrar el detalle de la propuesta
                comunica("' . $this->pantalla . '",{
                    metodo: "detallePropuesta",
                    det_prop: $(this).attr("proDetalle")
                });
            });

            $(".proAutorizar").click(function(){
                var pro_id = $(this).attr("proAutorizar");
                //Mandamos a llamar al metodo para cambiar el estado de la propuesta a Autorizada
                alertify.confirm(
                    "<h5> AUTORIZAR PROPUESTA </h5>",
                    "¿Realmente desea autorizar la propuesta?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "cambiaEstadoPropuesta",
                            accionPropuesta: "autorizar",
                            proId: pro_id
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".proEliminar").click(function(){
                var pro_id = $(this).attr("proEliminar");
                //Mandamos a llamar al metodo para cambiar el estado de la propuesta a Cancelada
                alertify.confirm(
                    "<h5> CANCELAR PROPUESTA </h5>",
                    "¿Realmente desea cancelar la propuesta?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "cambiaEstadoPropuesta",
                            accionPropuesta: "cancelar",
                            proId: pro_id
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    //Llenamos la tabla Detalle Propuesta
    private function llenaTablaDetProp()
    {
        //Vemos si extiste el id de la propuesta para ver su detalle, si no existe lo hace una cadena vacia
        $det_prop = isset($this->prm->det_prop) ? $this->prm->det_prop : "";

        if (empty($det_prop)) {
            return;
        }

        $sql = "
            SELECT p.pro_ejercicio
            FROM
                " . BD_TESORERIA . ".pro_Propuesta AS p
            WHERE
                p.pro_id = ?;";

        $pro_ejercicio = '';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$det_prop]);
        if ($r = $this->extraer()) {
            $pro_ejercicio = $r['pro_ejercicio'];
        }

        //Se arma la consulta, hay que tomar en cuenta que se hará un join con los CLC
        $sql = '
            SELECT
                p.pro_id AS proDet_propuesta,
                p.pro_ejercicio,
                dp.gpo_id AS proDet_gpo,
                dp.ur_id AS proDet_ur,
                dp.clc_id AS proDet_clc,
                dp.pro_importeClc AS proDet_importe,
                clc.clc_beneficiario AS proDet_beneficiario,
                clc.clc_concepto AS proDet_concepto,
                clc.clc_fechaAutorizacion AS proDet_fecha
            FROM
                ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS dp USING (pro_id)
        ';

        $sqlExtra = '';

        switch ($pro_ejercicio) {
            case 2026:
                $sql .= 'INNER JOIN ' . BD_CTRL . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)';
                break;
            case 2025:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2025 ';
                break;
            case 2024:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2024 ';
                break;
            case 2023:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2023 ';
                break;
            case 2022:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2022 ';
                break;
            case 2021:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2021 ';
                break;
            case 2020:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2020 ';
                break;
            case 2019:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2019 ';
                break;
            case 2018:
                $sql .= '
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2018 ';
                break;
            case 2017:
                $sql .= 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS clcA USING (gpo_id, ur_id, clc_id)
                    INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                    ';
                $sqlExtra .= 'WHERE clcA.ejr_id_clc = 2017 ';
                break;
        }

        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'p.pro_id = ?';

        //Hacemos la consulta y llenamos la tabla con el resultado
        $this->conectar();
        $this->setSql($sql . $sqlExtra);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tblDetProp";
        $this->setParams([$this->prm->det_prop]);
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".proDetEliminar").click(function(){
                var proId = $(this).attr("proDetPropuesta");
                var proGpo = $(this).attr("proDetGpo");
                var proUr = $(this).attr("proDetUr");
                var proClc = $(this).attr("proDetClc");
                var proEje = $(this).attr("proDetEje");

                //Mandamos a llamar al metodo para eliminar el detalle de la propuesta
                alertify.confirm(
                    "<h5> ELIMINAR CLC DE LA PROPUESTA </h5>",
                    "¿Realmente desea eliminar la CLC?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "eliminaDetalle",
                            proId: proId,
                            proGpo: proGpo,
                            proUr: proUr,
                            proClc: proClc,
                            proEje: proEje
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    //Hacemos los cambios de estado de la propuesta
    private function cambiaEstadoPropuesta()
    {
        $prop = new stdClass();
        $detProp = new stdClass();
        $docProp = new stdClass();
        $fechaActual = '';

        $prop->abc = "c";
        $detProp->abc = $docProp->abc = "b";
        $prop->pro_id = $detProp->pro_id = $docProp->pro_id = isset($this->prm->proId) ? $this->prm->proId : "";

        $this->setSql('SELECT CURDATE() as fecha');
        $this->conectar();
        $this->consultar();
        if ($row = $this->extraer()) {
            $fechaActual = $row['fecha'];
        }

        switch ($this->prm->accionPropuesta) {
            case 'autorizar':
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $params = [$this->usuario_id, $this->ip];
                $sql = "set @usr_id=? , @usr_ip=INET_ATON(?) , @bit_observacion='Actualizacion'";
                $conexion->setSql($sql);
                $conexion->consultar($params);
                $datosSP = new stdClass();
                $datosSP->pro_id = $prop->pro_id;
                $datosSP->bit_observacion = 'Insertado desde pantalla';
                $datosSP->sis_id = "TES";
                //Ejercicio del sistema
                $datosSP->ejercicio = APP_EJERCICIO;
                $respuesta = sp_AutorizarPropuesta::call($datosSP);
                $this->mixScript("notify('" . $respuesta->mensaje . "');");
                break;
            case 'cancelar':
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $params = [$this->usuario_id, $this->ip];
                $sql = "set @usr_id=? , @usr_ip=INET_ATON(?) , @bit_observacion='Actualizacion'";
                $conexion->setSql($sql);
                $conexion->consultar($params);
                $datosSP = new stdClass();
                $datosSP->pro_id = $prop->pro_id;
                $datosSP->bit_observacion = 'Cancelado desde pantalla';
                $datosSP->sis_id = "TES";
                //Ejercicio del sistema
                $datosSP->ejercicio = APP_EJERCICIO;
                $respuesta = sp_CancelarPropuesta::call($datosSP);
                $this->mixScript("notify('" . $respuesta->mensaje . "');");
                break;
        }
        $this->main();
    }

    //Eliminanos el contenido del detalle
    private function eliminaDetalle()
    {
        $detProp = new stdClass();
        $docProp = new stdClass();
        $clc = new stdClass();

        $detProp->abc = $docProp->abc = 'b';

        $detProp->pro_id = $docProp->pro_id = isset($this->prm->proId) ? $this->prm->proId : "";
        $clc->gpo_id = $detProp->gpo_id = $docProp->gpo_id = isset($this->prm->proGpo) ? $this->prm->proGpo : "";
        $clc->ur_id = $detProp->ur_id = $docProp->ur_id = isset($this->prm->proUr) ? $this->prm->proUr : "";
        $clc->clc_id = $detProp->clc_id = $docProp->clc_id = isset($this->prm->proClc) ? $this->prm->proClc : "";
        $ejercicio = isset($this->prm->proEje) ? $this->prm->proEje : "";

        $datosSP = new stdClass();
        $datosSP->pro_id = $detProp->pro_id;
        $datosSP->gpo_id = $clc->gpo_id;
        $datosSP->ur_id = $clc->ur_id;
        $datosSP->clc_id = $clc->clc_id;
        $datosSP->ejercicioCLC = $ejercicio;
        $datosSP->bit_observacion = 'Eliminado desde pantalla T_3_2';
        $datosSP->sis_id = "TES";
        $datosSP->ejercicio = APP_EJERCICIO;
        $respuesta = sp_EliminaDetallePropuesta::call($datosSP);
        $this->mixScript("notify('" . $respuesta->mensaje . "');");

        //Consultamos los clc's que quedan
        $sql = '
            SELECT
                pc.clc_id
            FROM
                ' . BD_TESORERIA . '.pro_Propuesta p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc pc
            WHERE
                pc.pro_id = ?' . '
            AND p.pro_ejr = ' . APP_EJERCICIO;

        $con4 = new AccesoDatos();
        $con4->setSql($sql);
        $con4->conectar();
        $con4->consultar([$detProp->pro_id]);
        $numReg = $con4->getNumeroRegistros();
        //Si ya no quedan clc's, la propuesta se elimina
        if ($numReg == 0) {
            $datosSP = new stdClass();
            $datosSP->pro_id = $detProp->pro_id;
            $datosSP->bit_observacion = 'Cancelado desde pantalla T_3_2';
            $datosSP->sis_id = "TES";
            $datosSP->ejercicio = APP_EJERCICIO;
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $params = [$this->usuario_id, $this->ip];
            $sql = "set @usr_id=? , @usr_ip=INET_ATON(?) , @bit_observacion='Actualizacion'";
            $conexion->setSql($sql);
            $conexion->consultar($params);
            $respuesta = sp_CancelarPropuesta::call($datosSP);
            $this->mixScript("notify('" . $respuesta->mensaje . "');");
            $this->main();
        } else {
            $this->llenaTablaDetProp();
            $this->mixScript(
                'comunica("' . $this->pantalla . '",{
              metodo: "llenaTablaDetProp",
              det_prop: "' . $detProp->pro_id . '",
            });'
            );
        }
    }
}

if (isset($_SESSION[_S]['prm']['T']['3']['2']['1'])) {
    new ControladorTesPropuestas();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
