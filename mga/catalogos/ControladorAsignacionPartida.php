<?php

session_start();

require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorAsignacionPartida extends WebResponse
{
    private $escritura;

    public function __construct()
    {
        parent::__construct();
        $this->escritura = true;
        switch ($this->metodo) {
            case 'llenaTablaPartidas':
                $this->llenaTablaPartidas();
                break;
            case 'desasignarPartidasUr':
                $this->desasignarPartidasUr();
                break;
            case 'asignarPartidasUr':
                $this->asignarPartidasUr();
                break;
            case 'listarPartidas':
                $this->listarPartidas();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $botones = '';

        // Usuarios con derecho de Escritura en ésta pantalla podrán asignar nuevas Partidas Genéricas
        if ($this->escritura) {
            $botones .= '<button id="nuevaPG" type="button" class="btn btn-outline-primary me-1" title="Asignar una nueva Partida Genérica a la UR" tabindex = 30><i class="fa-solid fa-plus me-2"></i>Asignar</button>';
        }

        // Todos pueden imprimir
        $botones .= '<button id="imprimir" target="_blank" class="btn btn-outline-danger me-1" title="Imprimir Partidas Genéricas asignadas a la UR" tabindex=40><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>';

        // Todos pueden exportar a excel
        $botones .= '<button id="excel" type="button" target="_blank" class="btn btn-outline-success me-1" title="Exportar a Excel listado de Partidas Genéricas asignaddas a la UR" tabindex=50><i class="fa-solid fa-file-excel me-2"></i>Excel</button>';

        // Filtrado de los datos de la tabla
        $botones .= '<input type="search" width=50 id="filtroTabla" style="display: inline; float: right; width:300px; height: 30px;" class="form-control"  tabindex=60 placeholder="Filtrar Partida" />';

        $this->agregaTools($botones);

        $this->mixStyle('
            legend {
                font-size:14px!important;
                margin-bottom:3px!important;
                font-weight:bold;
            }

            .modal-header {
                padding:8px!important;
                background: #B6C2C9;
                color:black;
            }

            .modal-footer {
                padding:10px 20px!important;
            }

            .close {
                color:black;
                opacity: 0.8;
            }
        ');

        $tabla = new stdClass();
        $tabla->titulo = 'Listado de Partidas Genéricas asignadas a la UR';
        $tabla->id = 'PartidasUr';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaPartidas';
        $tabla->comunica->ur = '{$("#combour").val()}';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tabla->encabezado = [];
        $tr = [];
        $th = new stdClass();
        $th->texto = 'PARTIDA';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE DE LA PARTIDA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Quitar';
        $th->width = '60px';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->campoTxt = 'pg_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'pg_nombre';
        array_push($tabla->cuerpo, $td);

        // El parámetro CASOS sirve para llenar la celda dependiendo de una comparación
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'boton';
        $td->casos->valor = '1'; // La consulta regresará 1 en el campo 'boton' si puede borrarse una Partida

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'botonSmall';
        $td->casos->verdadero->class = 'delPG btn btn-outline-danger';
        $td->casos->verdadero->icono = 'fa-solid fa-trash';
        array_push($tabla->cuerpo, $td);

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaPartidas();

        $this->mixHtml('<fieldset id="modalPartidas" class="col-md-12 col-xs-12 "></fieldset>');

        $this->mixScript(ClasificacionAdministrativa::onChangeUr('
                    comunica("' . $this->pantalla . '", { "metodo": "llenaTablaPartidas", "ur": $("#combour").val() }); ') . '
        $("#combour").attr("disabled", false); $("#combour").selectpicker("refresh");

        DesactivaAtajosMenu();
        Mousetrap.bind("esc esc", function (e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

        $("#clasAdminSeccion").hide();

        $("#homeMenu").click(function (e) {
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            $("#clasAdminSeccion").show();
            ActivaAtajosMenu();
        });

        $("#imprimir").click(function (e) {
            var form = $("<form>")
                .attr("action", "rep/doc/mga/rep_MgaCatalogos.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#filtroTabla").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 2));
            $("body").append(form);
            form.submit();
        });

        $("#excel").click(function (e) {
            var form = $("<form>")
                .attr("action", "rep/excel/mga/exp_Asignacion_pg_ur.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#filtroTabla").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", 2))
            $("body").append(form);
            form.submit();
        });

        $("#excelConClasJuan").click(function (e) {
            var form = $("<form>")
                .attr("action", "rep/excel/mga/exp_Asignacion_pg_urConClaseJuan.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#filtroTabla").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", 2))
            $("body").append(form);
            form.submit();
        });

        $("#buscarUR").click(function (event) {
            if ($("#numeroUR").val() != "" && $("#numeroUR").val().length == 3) {
                $("#numeroUR").attr("ura", $("#numeroUR").val());
                comunica("' . $this->pantalla . '", { metodo: "llenaTablaPartidas", ur: $("#numeroUR").val() });
            } else {
                notify("Debes escribir el número correcto de la UR", "warning");
                $("#numeroUR").focus();
                $("#tbody-tabla-PartidasUr").empty();
                $("#PartidasUr h2").html("Listado de Partidas Genéricas asignadas a la UR");
            }
        });

        $("#nuevaPG").click(function (e) {
            e.preventDefault();
            comunica("' . $this->pantalla . '", {
                metodo: "listarPartidas",
                ur: $("#combour").val()
            });
        });

        setTimeout(function () { $("#numeroUR").focus() }, 300);
        if ($("#combour").val() != "") {
            comunica("' . $this->pantalla . '",
                { "metodo": "llenaTablaPartidas", "ur": $("#combour").val() });
        }
        ');
    }

    private function llenaTablaPartidas()
    {
        $where = '';
        $parametros = [];
        $c = 0;

        // Si pasaron un número válido de UR ( 3 dígitos )
        if (isset($this->prm->ur)) {
            if (strlen($this->prm->ur) == 3) {
                $where = ' gpo_id = :gpoId AND ur_id = :urId ';
                $parametros = [':gpoId' => substr($this->prm->ur, 0, 1), ':urId' => substr($this->prm->ur, 1, 2)];
                $c = 1;
            } elseif ($this->prm->ur != '' && strlen($this->prm->ur) < 3) {
                $this->mixScript('notify("El número de UR debe ser de 3 digitos", "warning");$("#numeroUR").focus();');
            }
        }

        // Consultamos las Partidas asignadas a la UR, y de una vez buscamos aquellas que ya han sido utilizadas
        if ($c == 1) {
            // Ubicamos el nombre de la UR actual
            $sql = "SELECT ur_nombre FROM cat_UnidadResponsable WHERE $where";
            $this->setSql($sql);
            $this->conectar();
            $this->consultar($parametros);
            $this->desconectar();
            $nombre = $this->extraer();
            $nombre = $nombre['ur_nombre'];
            $sql = 'SELECT pg.pg_id, pg.pg_nombre, CASE WHEN po.ccd_id IS NULL THEN 1 ELSE 0 END AS boton
                    FROM ' . BD_CTRL . '.cat_PartidaGenerica pg
                        INNER JOIN ' . BD_CTRL . '.enc_PartGenUr p USING(pg_id)
                        LEFT JOIN ' . BD_CTRL . '.presupuesto pre USING(gpo_id, ur_id, pg_id)
                        LEFT JOIN ' . BD_CTRL . '.presupuestoOriginal po USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        LEFT JOIN ' . BD_CTRL . ".enc_PartEspCed pe USING(pg_id)
                    WHERE $where
                    GROUP BY pg.pg_id, boton
                    ORDER BY pg.pg_id;";

            $this->setSql($sql);
            $this->setParams($parametros);
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
            $nav->idTabla = 'PartidasUr';
            $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;

            if ($this->wrLlenaTabla($nav) == 0) {
                $this->mixScript('notify("La Unidad Responsable solicitada no tiene Partidas asignadas", "warning");');
            } else {
                $this->mixScript('
                    $(document).ready(function() {
                        $("table").filterTable({ // apply filterTable to all tables on this page
                            inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
                        });
                    });

                    $(".col-md-9").html("' . $this->prm->ur . ' - ' . htmlentities($nombre) . '");

                    $(".delPG").click(function(){
                        let pgA = $(this).closest("tr").find("td:eq(0)").text();
                        alertify.confirm("DESASIGNAR PARTIDA GENÉRICA", "¿ Deseas desasignar la partida <h5 style=\"display:inline\"><b>" + $(this).closest("tr").find("td:eq(0)").text() + " - " + $(this).closest("tr").find("td:eq(1)").text() + "</b></h5> de la UR <h5 style=\"display:inline\"><b>" + $("#combour").val() + "</b></h5> ?",
                            function() {
                                comunica("' . $this->pantalla . '", {
                                    metodo: "desasignarPartidasUr",
                                    ur: $("#combour").val(),
                                    pg: pgA
                                });
                            },
                            function() {
                                // Cancelar
                            }
                        ).set("labels", {ok:"DESASIGNAR", cancel:"Cancelar"});
                    });
                ');
            }
        }
    }

    /*----------------Desasignamos una partida genérica de la ur actualmente seleccionada--------------------*/
    private function desasignarPartidasUr()
    {
        // Si pasaron un número válido de UR ( 3 dígitos )
        if (isset($this->prm->ur)) {
            if (strlen($this->prm->ur) == 3) {
                $gpo = substr($this->prm->ur, 0, 1);
                $ur = substr($this->prm->ur, 1, 2);
            } elseif ($this->prm->ur != '' && strlen($this->prm->ur) < 3) {
                $this->mixScript('
                    notify("El número de UR debe ser de 3 digitos", "warning");
                    $("#numeroUR").focus();');
            }
        }

        // Buscamos que aún no se haya usado la partida en las tablas base
        $presupuesto = new accesoDatos();
        $presupuesto->setSql('SELECT * FROM presupuesto WHERE gpo_id = :gpo AND ur_id = :ur AND pg_id = :pgId;');
        $parametros = [':gpo' => $gpo, ':ur' => $ur, ':pgId' => $this->prm->pg];
        $presupuesto->conectar();
        $presupuesto->consultar($parametros);
        $presupuesto->desconectar();

        // Si ya existen registros usando la partida en la UR, no se puede borrar
        if ($presupuesto->getNumeroRegistros() > 0) {
            $this->mixScript('notify("La partida ' . $this->prm->pg . ' ya se ha utilizado, no puede desasignar","error");');
            // Si no se ha usado, la borramos del encuadre
        } else {
            # Se agega validación por si la pg esta utilizando en alguna cedula 2020
            $presupuesto = new accesoDatos();
            $presupuesto->setSql('SELECT * FROM presupuesto WHERE CONCAT(gpo_id,ur_id) != :urClave AND pg_id = :pgId;');
            $parametros = [':urClave' => $gpo . $ur, ':pgId' => $this->prm->pg];
            $presupuesto->conectar();
            $presupuesto->consultar($parametros);
            $presupuesto->desconectar();
            if ($presupuesto->getNumeroRegistros() > 0) {
                $this->mixScript('notify("La partida ' . $this->prm->pg . ' ya se ha utilizado, no puede desasignar","error");');
            } else {
                // Si no se ha usado, la borramos del encuadre
                $encPartGenUr = new accesoDatos();
                $encPartGenUr->setSql('DELETE FROM enc_PartEspCed WHERE pg_id = :pg;');
                $parametros = [':pg' => $this->prm->pg];
                $encPartGenUr->conectar();
                $encPartGenUr->consultar($parametros);

                $encPartGenUr->setSql('DELETE FROM enc_PartGenUr WHERE gpo_id = :gpo AND ur_id = :ur AND pg_id = :pg;');
                $parametros = [':gpo' => $gpo, ':ur' => $ur, ':pg' => $this->prm->pg];
                $encPartGenUr->conectar();
                $encPartGenUr->consultar($parametros);
                $encPartGenUr->desconectar();
                // Avisamos que ya la borramos del encuadre
                $this->mixScript('notify("Se ha desasignado la partida ' . $this->prm->pg . ' para la UR ' . $this->prm->ur . '");');
                // Refrescamos la tabla
                $this->llenaTablaPartidas();
            }
        }
    }

    private function asignarPartidasUr()
    {
        if (isset($this->prm->ur)) {
            if (strlen($this->prm->ur) == 3) {
                $gpo = substr($this->prm->ur, 0, 1);
                $ur = substr($this->prm->ur, 1, 2);
            } elseif ($this->prm->ur != '' && strlen($this->prm->ur) < 3) {
                $this->mixScript(
                    '
                    notify("El número de UR debe ser de 3 digitos", "warning");
                    $("#numeroUR").focus();'
                );
            }
        }
        $usr_id = 2;
        $encPartGenUr = new accesoDatos();
        $encPartGenUr->setSql('INSERT INTO enc_PartGenUr (gpo_id,ur_id,pg_id, usr_id) VALUES(:gpo, :ur, :pgId,:usrId);');
        $parametros = [':gpo' => $gpo, ':ur' => $ur, ':pgId' => $this->prm->pg_id, ':usrId' => $usr_id];
        $encPartGenUr->conectar();
        $encPartGenUr->consultar($parametros);
        $encPartGenUr->desconectar();

        $this->mixScript('notify("La partida ' . $this->prm->pg_id . ' se ha asignado");');
        $this->llenaTablaPartidas();
    }

    private function listarPartidas()
    {
        $this->setContenedor('modalPartidas');
        if (isset($this->prm->ur) && strlen($this->prm->ur) == 3) {
            $gpo = substr($this->prm->ur, 0, 1);
            $ur = substr($this->prm->ur, 1, 2);
        }

        // Les permitimos escoger sólo partidas que aún no estén asignadas a la UR
        $comboPartidas = new stdClass();
        $comboPartidas->id = 'comboPartidas';
        $comboPartidas->sql = 'SELECT pg_id,pg_nombre FROM cat_PartidaGenerica WHERE pg_id NOT IN (SELECT pg_id FROM enc_PartGenUr WHERE gpo_id = :gpoId AND ur_id = :urId);';
        $comboPartidas->params = [':gpoId' => $gpo, ':urId' => $ur];
        $comboPartidas->width = '100%';
        $comboPartidas->required = true;
        $comboPartidas->search = true;

        $modal = '
        <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel">Asignar partidas</h5>
                        <button type="button" class="btn-close cerrarModal" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>
                    <div class="modal-body">
                        <form id="formPartida">
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12 style="color:#0431B4">
                                    ' . $this->ponCombo($comboPartidas) . '
                                </fieldset>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-success" id="AsignarPartida"><i class="fa-solid fa-floppy-disk me-2"></i>
                            Guardar
                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);
        $this->mixScript('
                $(".cerrarModal").click(function () { $("#myModal").remove(); });
                $("#AsignarPartida").click(function () {
                valido = true;
                $("#formPartida").find(":required").each(function () {
                    valido = valido && this.checkValidity();
                    if (!this.checkValidity()) {
                        $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
                        }
                });
                $(".formError").show("pulsate");
                setTimeout(function () { $(".formError").hide("puff"); }, 4000);
                setTimeout(function () { $(".formError").remove(); }, 4500);
                if (valido) {
                    comunica("' . $this->pantalla . '", {
                        metodo: "asignarPartidasUr",
                        ur: $("#combour").val(),
                        pg_id: $("#comboPartidas").val()
                    });
                    $("#myModal").remove();
                }
            });
        ');
    }
}

if (isset($_SESSION[_S]['prm']['M']['6']['2'][TAREA_ESCRITURA])) {
    new ControladorAsignacionPartida();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
