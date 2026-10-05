<?php
session_start();
/**
 * la presente clase solo permite desplegar una modal con una tabla de proveedores
 * con filtros de los mismos
 * permite al dar doble clicl en la fila o dar click en el botón seleccionar
 * poner el RFC en el input requerido
 * adicional permite poner
 * nombre, apellidos (si aplica ) y razón social ( si aplica ) en un segundo input
 * Tecnologías usadas
 * Jquery
 * PHP
 * funcion princial 
 * WebResponse::ponF1Proveedores
 *  
 */

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class CatalogoProveedores extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'srv/CatalogoProveedores.php';
        switch ($this->metodo) {
            default:
                $this->main();
                break;
            case 'llenaTablaProveedores';
                $this->llenaTablaProveedores();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->setContenedor(CONTENEDOR_CATALOGO_PROVEEDORES);
        $tabla = new stdClass();
        $tabla->id = "TABLA_" . CONTENEDOR_CATALOGO_PROVEEDORES;
        $tabla->comunica = new stdClass();
        $tabla->tools = $this->filtros();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaProveedores';
        $tabla->seleccionable = true;

        $tr = array();
        $th = new stdClass();
        $th->texto = 'RFC';
        $th->width = '15%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre o Razón Social';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'rfc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));

        $this->mixScript(
            '
            $("#modal_proveedores_btn_cerrar").on("click", function(){
                $("#' . CONTENEDOR_CATALOGO_PROVEEDORES . '").dialog("close");
            });
            $("#modal_proveedores_btn_mostrar").on("click", function(){
                var data = new FormData(document.getElementById("modal_form_filtros_proveedor"));
                var comunincaObj ={metodo: "llenaTablaProveedores"};

                for (var n of data.entries()) {
                    comunincaObj[n[0]] = n[1];
                }
                comunica("' . $this->pantalla . '", comunincaObj);
            });

            $("#' . CONTENEDOR_CATALOGO_PROVEEDORES . '").dialog({
                modal: true,
                autoOpen: true,
                title: "Selecciona un Proveedor",
                dragable: false,
                resizable: false,
                width: Math.abs(window.innerWidth * .8),
                height: Math.abs(window.innerHeight * .7),
                position: {my: "center center", at: "center top"},
                open: function(e) {
                    $(".ui-widget-overlay").css("z-index", 10000);
                    $("#' . CONTENEDOR_CATALOGO_PROVEEDORES . '").parent().css("z-index", 10001);
                }
            });
            $(".ui-dialog-titlebar-close").addClass("btn btn-outline-danger btn-small").append("X");
            ' . $this->wrLlamaLlenaTabla("TABLA_" . CONTENEDOR_CATALOGO_PROVEEDORES)
        );
    }

    public function llenaTablaProveedores()
    {
        $params = [];
        $sql = ' SELECT 
            rfc_id,CONCAT(rfc_nombre, " ", rfc_paterno, " " , rfc_materno, " " , " ", soc_nombre) as nombre,
            soc_id, rfc_nombre, rfc_paterno, rfc_materno, soc_nombre
            FROM cat_Proveedor
            INNER JOIN sinpres.cat_Proveedores using( rfc_id )
            INNER JOIN sinpres.cat_SociedadProveedor USING (soc_id)
            WHERE rfc_id IS NOT NULL
        ';

        if (isset($this->prm->modal_proveedores_rfc)) {
            $sql .= " AND rfc_id LIKE :rfc_id";
            $params = [':rfc_id' => '%' . $this->prm->modal_proveedores_rfc . '%'];
        }

        if (isset($this->prm->modal_proveedor_nombre) && $this->prm->modal_proveedor_nombre != '') {
            $sql .= " AND (
                rfc_nombre LIKE :proveedor_nombre 
                OR rfc_paterno LIKE :proveedor_nombre
                OR rfc_materno LIKE :proveedor_nombre
                OR soc_nombre LIKE :proveedor_nombre
                )";
            $params[':proveedor_nombre'] = '%' . $this->prm->modal_proveedor_nombre . '%';
        }
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav) ? $this->prm->nav : new stdClass());
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $nav->idTabla = "TABLA_" . CONTENEDOR_CATALOGO_PROVEEDORES;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript(' notify("No hay registros");');
            return;
        }

        $this->mixScript('
            $("#tbody-tabla-TABLA_' . CONTENEDOR_CATALOGO_PROVEEDORES . ' tr").on("dblclick", function(){
                seleccion = $("#TABLA_' . CONTENEDOR_CATALOGO_PROVEEDORES . '").tabla("getFilaSeleccion");
                $(this).find("button").click();
                    target = $("#' . CONTENEDOR_CATALOGO_PROVEEDORES . '").attr("aria-target");
                    attributes = $("#' . CONTENEDOR_CATALOGO_PROVEEDORES . '").attr("aria-attributes").split(",");
                    secondary = $("#' . CONTENEDOR_CATALOGO_PROVEEDORES . '").attr("aria-secondary");

                $("#"+ target).attr("rfc_nombre", seleccion["rfc_nombre"]);
                $("#"+ target).val(seleccion["rfc_id"]);
                if (secondary.length > 0) {
                    $("#"+ secondary).val(seleccion["nombre"]);
                }
                $("#' . CONTENEDOR_CATALOGO_PROVEEDORES . '").remove();
                $("#" + target).focus();
            });
        ');
    }

    public function filtros()
    {
        $inputRfc = new stdClass();
        $inputRfc->id = "modal_proveedores_rfc";
        $inputRfc->enDiv = true;
        $inputRfc->label = "RFC a buscar";
        $inputRfc = $this->ponInput($inputRfc);

        $inputNombre = new stdClass();
        $inputNombre->id = "modal_proveedor_nombre";
        $inputNombre->enDiv = true;
        $inputNombre->label = "Nombre o Razón social";
        $inputNombre = $this->ponInput($inputNombre);

        $limpiar = new stdClass();
        $limpiar->id = 'limpiar';
        $limpiar->name = 'limpiar';
        $limpiar->value = 'Buscar';
        $limpiar->color = 'gris';
        $limpiar->icon = 'refresh me-2';
        $limpiar = $this->ponBoton($limpiar);

        $buscar = new stdClass();
        $buscar->id = 'modal_proveedores_btn_mostrar';
        $buscar->color = 'verde';
        $buscar->value = 'Buscar';
        $buscar->icon = 'search me-2';
        $buscar->class = 'mb-4';
        $buscar = $this->ponBoton($buscar);

        $form = <<<HTML
            <div class="">
                <form id="modal_form_filtros_proveedor">
                    <div class="row">
                        <div class="col-md-6">
                            {$inputRfc}
                        </div>
                        <div class="col-md-6">
                            {$inputNombre}
                        </div>
                    </div>
                </form>
                <div>
                {$buscar}
                </div>
            </div>
        HTML;

        return $form;
    }
}
if ($_SESSION[_S]['usr_id']) {
    new CatalogoProveedores();
}
