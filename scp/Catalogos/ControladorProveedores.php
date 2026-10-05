<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
class ControladorProveedores extends WebResponse
{
    public $ur;
    public $nivel;
    public $ClasGpo;
    public $AltaProveedor;
    public $TesAltaProveedor;
    public $ModificarProveedor;
    public $ContAltaProveedor;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/Catalogos/ControladorProveedores.php';
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->ClasGpo = $_SESSION[_S]['gpo_id'];
        $this->AltaProveedor = isset($_SESSION[_S]['prm']['S']['6']['1']['2']) ? true : false;
        $this->TesAltaProveedor = isset($_SESSION[_S]['prm']['T']['6']['5']['2']) ? true : false;
        $this->ContAltaProveedor = isset($_SESSION[_S]['prm']['C']['2']['10']['2']) ? true : false;
        $this->ModificarProveedor = isset($_SESSION[_S]['prm']['S']['6']['1']['48']) ? true : false;
        $this->ur->select();

        switch ($this->metodo) {
            case 'abcProveedores':
                $this->abcProveedores();
                break;
            case 'llenaTablaProveedores':
                $this->llenaTablaProveedores();
                break;
            case 'addProveedor':
                $this->addProveedor();
                break;
            case 'cargaMunicipio':
                $this->cargaMunicipio();
                break;
            case 'activaRFCSAT':
                $this->activaRFCSAT();
                break;
            case 'guardaRFCSat':
                $this->guardaRFCSat();
                break;
            case 'habilitarRFC':
                $this->habilitarRFC();
                break;
            case 'inhabilitarRFC':
                $this->inhabilitarRFC();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->prm->rfc_id = (isset($this->prm->rfc_id)) ? $this->prm->rfc_id : '';
        $this->prm->tcy_id = (isset($this->prm->tcy_id)) ? $this->prm->tcy_id : '';

        $tipo = new StdClass();
        $tipo->sql = 'SELECT tcy_id, tcy_nombre FROM ' . BD_SINPRES . '.cat_TipoContribuyente;';
        $tipo->class = 'radio rango limpiar';
        $tipo->name = 'tipoP';
        if ($this->prm->tcy_id != '') {
            $tipo->selecciona = $this->prm->tcy_id;
        }
        $tipo->cols = 3;

        $bxnombre = new stdClass();
        $bxnombre->id = 'bxnombre';
        $bxnombre->name = 'bxnombre';
        $bxnombre->label = 'Nombre';
        $bxnombre->class = 'limpiar';
        $bxnombre->placeholder = 'Ingresa el nombre';
        $bxnombre->enDiv = true;

        $bxrfc = new stdClass();
        $bxrfc->id = 'bxrfc';
        $bxrfc->name = 'bxrfc';
        $bxrfc->label = 'RFC';
        $bxrfc->class = 'limpiar';
        $bxrfc->placeholder = 'Ingresa el RFC';
        $bxrfc->enDiv = true;

        $buscar = new stdClass();
        $buscar->value = 'Buscar';
        $buscar->text = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->color = 'gris';
        $buscar->class = 'buscar me-1';
        $buscar->icon = 'search me-2';

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->class = 'clean btn btn-outline-warning me-1';
        $clean->icon = 'trash me-2';

        $activaRFCSAT = new stdClass();
        $activaRFCSAT->value = 'Activar RFC';
        $activaRFCSAT->text = 'activaRFCSAT';
        $activaRFCSAT->id = 'activaRFCSAT';
        $activaRFCSAT->name = 'activaRFCSAT';
        $activaRFCSAT->icon = 'solid fa-plus me-2';
        $activaRFCSAT->class = 'btn btn-outline-success me-1';

        $radios = '';
        $radios = '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
                <legend align="center">Busqueda por:</legend>
                <div class="row mb-4">
                    <div class="col-md-12">
                        ' . $this->ponRadioFlex($tipo) . '
                    </div>
                    <div class="col-md-4">
                        ' . $this->ponInput($bxrfc) . '
                    </div>
                    <div class="col-md-4">
                        ' . $this->ponInput($bxnombre) . '
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class= "mt-3">
                        ' . $this->ponBoton($buscar) . ' ' . $this->ponBoton($clean) . '
                        </div></div>
                </div>
            </form>
        ';

        if ($this->agregaTools((isset($_SESSION[_S]['prm']['S']['6']['1']['85'])) ? $this->ponBoton($activaRFCSAT) : ''));
        // Permisos para el usuario de Edgar y Soporte  cambiar el 580 por el 735
        $tabla = new stdClass();
        $tabla->id = 'tbl-proveedores';
        $tabla->titulo = 'Proveedores/Beneficiarios';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaProveedores';
        $tabla->comunica->bxrfc = $this->prm->rfc_id;
        $tabla->comunica->tcy_id = '{$("input[name=tipoP]:checked").val()}';
        $tabla->numFilas = 200;
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = ['rfc_id', 'tcy_id', 'rfc_email'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'SEL';
        $tabla->casoSeleccionable->valor = '0';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';

        $tr = [];

        /* Editar */
        $th = new stdClass();
        if ($this->AltaProveedor || $this->TesAltaProveedor) {
            $th->texto = 'EDITAR';
            $th->width = '60px';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO CONTRIBUYENTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DIRECCION';
        array_push($tr, $th);

        // CUENTA
        if ($this->ContAltaProveedor) {
            $th = new stdClass();
            $th->texto = 'CUENTA';
            array_push($tr, $th);
        }
        //ESTADO
        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $trbody = [];
        //EDITAR
        $td = new stdClass();
        if ($this->AltaProveedor || $this->TesAltaProveedor) {
            $td->tipo = 'boton';
            $td->icono = 'pencil';
            $td->class = 'editar';
            $td->attr = new stdClass();

            $td->attr->rfc_id = 'rfc_id';
            $td->attr->tcy_id = 'tcy_id';
            $td->attr->gpo_id = 'gpo_id';
            $td->attr->ur_id = 'ur_id';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'rfc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcy_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rfc_calle';
        array_push($trbody, $td);

        if ($this->ContAltaProveedor) {
            $td = new stdClass();
            $td->campoTxt = 'rfc_cta_id';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'rfc_color';
        $td->color = 'rfc_color';
        $td->campoTxt = 'Estado';
        $td->lbl = 'Estado';
        $td->title = 'que_pasa';
        array_push($trbody, $td);
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;



        $btnaHabilitar = new stdClass();
        $btnaHabilitar->id = 'btnaHabilitar';
        $btnaHabilitar->class = 'me-1';
        $btnaHabilitar->icon = 'solid fa-check me-2';
        $btnaHabilitar->color = 'verde';
        $btnaHabilitar->value = 'Habilitar RFC';
        $btnaHabilitar->click = '
            if (seleccionados = $("#tbl-proveedores").tabla("getFilaSeleccion")) {
                validos = [];
                seleccionados.forEach(function(item, index) {
                    if (((item.tcy_id == 1 || item.tcy_id == 2) && item.rfc_email.trim() !== "" && item.rfc_email != undefined && item.rfc_email.indexOf("@") !== -1) || (item.tcy_id == 3 || item.tcy_id == 4)) {

                        validos.push(item);
                    } else {

                        notify("Debes editar el provedor " + item.rfc_id + " y agregar el correo electrónico", "warning");
                    }
                })
                if (validos.length > 0) {
                    comunica("' . $this->pantalla . '", {
                        metodo: "habilitarRFC",
                        rfc: validos
                    });
                }
            }
        ';
        $btnaHabilitar = $this->ponBoton($btnaHabilitar);
        $this->mixHtml($this->wrPonTabla($tabla));

        $botones = '';
        if ($this->AltaProveedor || $this->ContAltaProveedor || $this->TesAltaProveedor) {
            $botones = '
                <button id="addCuenta" type="button" class="btn btn-outline-primary nuevo me-1" title="Registrar nuevo Contribuyente ">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo
                </button>
            ';
            $this->agregaTools('' . $botones . '');
        } else {
            $this->agregaTools($botones);
        }

        if ($this->AltaProveedor || $this->TesAltaProveedor) {
            $this->agregaTools('' . $btnaHabilitar . '');
        }

        $this->mixScript('
            $("#bxrfc").blur(function() {
                if ($("#bxrfc").val().length < 3) {
                    notify("Debes  escribir al menos 3 digitos", "warning");
                }
                return false;
            });
            $("#bxnombre").blur(function() {
                if ($("#bxnombre").val().length < 4) {
                    notify("Debes  escribir al menos  4 digitos", "warning");
                }
            });
            $("#buscar").click(function() {
                var obj = {
                    "metodo": "llenaTablaProveedores"
                };
                obj.bxrfc = $("#bxrfc").val();
                obj.bxnombre = $("#bxnombre").val();
                obj.tcy_id = $("input[name=tipoP]:checked").val();

                if (!comunica("' . $this->pantalla . '", obj, true))
                    $(this).removeAttr("disabled");
                return false;
            });
            $("#clean").click(function() {
                $(".limpiar").val("");
                comunica("' . $this->pantalla . '", {
                    metodo: "llenaTablaProveedores",
                    tcy_id: $("input[name=tipoP]:checked").val()
                });
            });
            $("#activaRFCSAT").click(function() {
                validos = [];
                if (seleccionados = $("#tbl-proveedores").tabla("getFilaSeleccion")) {
                    seleccionados.forEach(function(item, index) {
                        if (((item.tcy_id == 1 || item.tcy_id == 2) && item.rfc_email.trim() != "" && item.rfc_email != undefined && item.rfc_email.indexOf("@") !== -1) || (item.tcy_id == 3 || item.tcy_id == 4)) {
                            validos.push(item);
                        } else {

                            notify("Debes editar el provedor " + item.rfc_id + " y agregar el correo electrónico", "warning");
                        }
                    })
                    if (validos.length > 0 && validos.length < 2) {
                        comunica("' . $this->pantalla . '", {
                            metodo: "activaRFCSAT",
                            rfcs: validos
                        });
                    } else {
                        notify("Debes seleccionar a un solo proveedor", "warning")
                    }
                } else {
                    comunica("' . $this->pantalla . '", {
                        metodo: "activaRFCSAT",
                        rfcs: validos
                    });
                }
            });
        ');

        $this->mixScript('
            $("input[name=tipoP]").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo: "llenaTablaProveedores",
                    tcy_id: $("input[name=tipoP]:checked").val()
                });
            });
        ');

        $this->mixScript('
            $(".nuevo").click(function() {
                if (!$("input[name=tipoP]").is(":checked")) {
                    notify("Elige el tipo de contribuyente o Beneficiario", "warning");
                } else {
                    comunica("' . $this->pantalla . '", {
                        "metodo": "addProveedor",
                        tcy_id: $("input[name=tipoP]:checked").val()
                    });
                }
            });
            $("#btnRegresarIndex").click(function() {
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');

        if ($this->nivel < 3) {
            $this->mixScript('$("#toggleCA").hide();');
        }

        if ($this->prm->rfc_id != '') {
            $this->prm->bxrfc = $this->prm->rfc_id;
            $this->llenaTablaProveedores();
        }
    }

    private function llenaTablaProveedores()
    {
        $wf = '';
        $params = [];
        if (isset($this->prm->tcy_id) && $this->prm->tcy_id != '') {
            $wf .= ($wf == '') ? 'where tcy_id = ?' : ' and tcy_id = ?';
            array_push($params, $this->prm->tcy_id);
        }
        if (isset($this->prm->bxrfc) && $this->prm->bxrfc != '') {
            $wf .= ($wf == '') ? 'where rfc_id LIKE ?' : ' and rfc_id LIKE ?';
            array_push($params, '%' . $this->prm->bxrfc . '%');
        }

        if (isset($this->prm->tcy_id) && $this->prm->tcy_id != '') {
            if (isset($this->prm->bxnombre) && $this->prm->bxnombre != '') {
                $wf .= ' HAVING nombre LIKE ?';
                array_push($params, '%' . $this->prm->bxnombre . '%');
            }
        } else {
            if (isset($this->prm->bxnombre) && $this->prm->bxnombre != '') {
                $wf .= ($wf == '') ? 'where rfc_nombre LIKE ?' : ' and rfc_nombre LIKE ?';
                array_push($params, '%' . $this->prm->bxnombre . '%');
            }
        }

        $this->conectar();
        $sql = '
            SELECT rfc_id,tcy_nombre,tcy_id,
                if(sts_id is null,"990000","009900" )as rfc_color,a.rfc_cta_id,
                if(sts_id is null,0,1) as SEL,if(sts_id is null,"INHABILITADO","HABILITADO") as Estado,
                concat(TRIM(rfc_nombre)," ",TRIM(rfc_paterno)," ",TRIM(rfc_materno)," ",TRIM(soc_nombre)) as nombre,
                soc_nombre,rfc_calle,rfc_email
            FROM ' . BD_SINPRES . '.cat_Proveedores a
            LEFT  JOIN cat_Proveedor USING(rfc_id)
            INNER JOIN  ' . BD_SINPRES . '.cat_TipoContribuyente using(tcy_id)
            INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor using(soc_id)
            ' . $wf . '';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-proveedores';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 200;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("NO HAY REGISTROS", "warning");');
        }

        $this->mixScript('
            $("#btnregresarindex").click(function() {
                document.location.href = "../sinpres2015";
            });
            $(".editar").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo: "addProveedor",
                    rfc_id: $(this).attr("rfc_id"),
                    gpo_id: $(this).attr("gpo_id"),
                    ur_id: $(this).attr("ur_id"),
                    tcy_id: $(this).attr("tcy_id")
                });
                return false;
            });
        ');
    }

    public function activaRFCSAT()
    { //Funcion para agregar el RFC al catálogo de ProveedoresSAT
        $rfc = '';
        $html = '';

        if (!empty($this->prm->rfcs) && $this->prm->rfcs != '') {
            foreach ($this->prm->rfcs as $key => $val) {
                $rfc = $val->rfc_id;
            }
        }
        /* ID_RFC */
        $rfc_id = new stdClass();
        $rfc_id->id = 'rfc_id';
        $rfc_id->name = 'rfc_id';
        $rfc_id->label = 'RFC';
        $rfc_id->placeholder = 'Ingresar RFC';
        $rfc_id->maxLength = 13;
        $rfc_id->enDiv = true;
        $rfc_id->required = true;
        $rfc_id->value = trim($rfc);

        /* ESTADO DEL CERTIFICADO */
        $edo_certificado = new stdClass();
        $edo_certificado->id = 'edo_certificado';
        $edo_certificado->name = 'edo_certificado';
        $edo_certificado->label = 'Estado de Certificado';
        $edo_certificado->placeholder = 'C';
        $edo_certificado->maxLength = 1;
        $edo_certificado->enDiv = true;
        $edo_certificado->required = true;
        $edo_certificado->value = 'certificado';
        $edo_certificado->readonly = 'readonly';

        /* FECHA INICIAL DEL CERTIFICADO */
        $inicial_cert = new stdclass();
        $inicial_cert->id = 'inicial_cert';
        $inicial_cert->name = 'inicial_cert';
        $inicial_cert->class = 'fecha';
        $inicial_cert->label = 'Fecha Inicial';
        $inicial_cert->placeHolder = APP_EJERCICIO . '-01-01 ';
        $inicial_cert->enDiv = true;
        $inicial_cert->formato = 'fecha';
        $inicial_cert->required = true;

        /* FECHA FINAL DEL CERTIFICADO */
        $final_cert = new stdclass();
        $final_cert->id = 'final_cert';
        $final_cert->name = 'final_cert';
        $final_cert->class = 'final_cert';
        $final_cert->label = 'Fecha Final';
        $final_cert->placeHolder = APP_EJERCICIO . '-12-31 24:00:00';
        $final_cert->enDiv = true;
        $final_cert->value = APP_EJERCICIO . '-12-31';
        $final_cert->required = true;
        $final_cert->readonly = 'readonly';

        $botones = '
            <button id="btnRegresarIndex" type="button" class="btn btn-outline-secondary regresar me-1" title="Regresar al Menú Principal">
                <i class="fa-solid fa-circle-left me-2"></i>Regresar
            </button>
            <button id="btnActivaRFC" type="button" form="btnActivaRFC" class="btn btn-outline-success me-1"  title="Activar RFC en el Catálogo del SAT">
                <i class="fa-solid fa-upload me-2"></i>Activar RFC
            </button>
        ';
        $this->agregaTools($botones);
        $html .= '
            <div class="col-md-6 col-xs-6">
                <div class="col-md-12 col-xs-12 mb-3">
                ' . $this->ponInput($rfc_id) . '
                </div>
                <div class="col-md-12 col-xs-12 mb-3">
                ' . $this->ponInput($edo_certificado) . '
                </div>
                <div class="col-md-6 col-xs-6 mb-3" vertical-align:middle;>
                ' . $this->ponInput($inicial_cert) . '
                </div>
                <div class="col-md-6 col-xs-6" vertical-align:middle;>
                ' . $this->ponInput($final_cert) . '
                </div>
            </div>
        ';
        $this->mixHtml($html);

        $this->mixScript('
        var permirtirRFC = /^[a-zA-Z\d]/;
        $("#btnActivaRFC").click(function() {
            var mensaje = "";
            if ($("#rfc_id").val() == "") {
                mensaje = "Ingrese un RFC válido";
            } else if ($("#inicial_cert").val() == "") {
                mensaje = "Ingrese la fecha inicial";
            }
            if (mensaje == "") {
                comunica("' . $this->pantalla . '", {
                    "metodo": "guardaRFCSat",
                    rfc_id: $("#rfc_id").val(),
                    inicial_cert: $("#inicial_cert").val(),
                    final_cert: $("#final_cert").val(),
                    certificado: $("#edo_certificado").val()
                });
            } else {
                notify(mensaje, "warning");
            }
        });
        $("#btnRegresarIndex").click(function() {
            $("nav").menusinpres("clickMenu");
            comunica("scp/Catalogos/ControladorProveedores.php", {
                metodo: "main"
            });
            return false;
        });
        $("#rfc_id").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8)(keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permirtirRFC.test(String.fromCharCode(keynum));
        });
        $("#rfc_id").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });
        ');
    }

    public function guardaRFCSat()
    { //Agrega el RFC al catálogo de ProveedoresSAT
        $sql = '
            SELECT rfc_id FROM ' . BD_SINPRES . '.cat_ProveedorSat WHERE
            rfc_id= ?;
        ';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->rfc_id]);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            $this->mixScript('
                notify("El RFC ' . strtoupper($this->prm->rfc_id) . ' ya existe en el catálogo del SAT","error");
            ');
        } else {
            $this->prm->final_cert = APP_EJERCICIO . '-12-31 23:59:59';
            $this->prm->rfc_noCertificado = '" "';
            $this->prm->rfc_validezObligaciones = '1';
            $this->prm->certificado = 'C';

            $sql = '
                INSERT INTO ' . BD_SINPRES . '.cat_ProveedorSat(rfc_id,rfc_noCertificado,rfc_validezObligaciones,rfc_EstadoCertificado,rfc_fechaInicio,rfc_fechaFinal,usr_id,usr_fecha) 
                VALUES( UPPER( ? ), ?, ?, ?, ?, ?, ?,  NOW())';
            $params = [$this->prm->rfc_id, $this->prm->rfc_noCertificado, $this->prm->rfc_validezObligaciones, $this->prm->certificado, $this->date_to_mysqldate($this->prm->inicial_cert), $this->prm->final_cert, $_SESSION[_S]['usr_id']];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $this->desconectar();
            $this->mixScript('
                notify("El RFC ha sido activado correctamente");
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    rfc_id:"' . $this->prm->rfc_id . '"
                })
            ');
        }
    }

    public function addProveedor()
    {
        $this->prm->tcy_id = (isset($this->prm->tcy_id)) ? $this->prm->tcy_id : '';
        $vinculadoACuenta = false;

        if (isset($this->prm->rfc_id)) {

            $sql = 'SELECT COUNT(*) AS Cuentas
            FROM vin_BancosUrProveedor
            INNER JOIN cat_BancosUr USING (bur_id)
            WHERE rfc_id = ?
            AND eba_id = 6';

            $this->setSql($sql);
            $this->conectar();
            $this->consultar([$this->prm->rfc_id]);
            $datos = $this->extraer();
            $this->desconectar();
            if ($datos['Cuentas'] > 0) {
                $vinculadoACuenta = true;
            }
        }

        $abc = '';
        $rfc = new Proveedores();

        if (!empty($this->prm->rfc_id)) {
            $rfc->setRfc_id($this->prm->rfc_id);
            $rfc->select();
            $abc = 'c';

            $this->conectar();
            $this->setSql('SELECT rfc_id FROM ' . BD_SINPRES . '.cat_Proveedores WHERE rfc_id = ?');
            $this->consultar([$rfc->getRfc_id()]);
            $this->desconectar();
            if ($this->ModificarProveedor || isset($_SESSION[_S]['prm']['T']['6']['5']['48'])) {
                $bandera = false;
            } else {
                $this->conectar();
                $this->setSql('
                    SELECT rfc_id FROM scp_Contable WHERE rfc_id = ?
                        UNION
                    SELECT rfc_id FROM scp_Presupuestal WHERE rfc_id = ? LIMIT 1
                ');
                $params = [$this->prm->rfc_id, $this->prm->rfc_id];
                $this->consultar($params);
                $this->desconectar();
                if ($this->getNumeroRegistros() != 0 && !isset($_SESSION[_S]['prm']['S']['6']['1']['82'])) {
                }
            }
        } else {
            $rfc->setRfc_id('');
            $abc = 'a';
        }

        $rfc_id = new stdClass();
        $rfc_id->id = 'rfc_id';
        $rfc_id->name = 'rfc_id';

        $localidad = new stdClass();
        $localidad->id = 'localidad';

        $colonia = new stdClass();
        $colonia->id = 'colonia';

        $calle = new stdClass();
        $calle->id = 'calle';

        $nombre = new stdClass();
        $nombre->id = 'nombre';

        $telefono = new stdClass();
        $telefono->id = 'telefono';
        $telefono->label = 'Teléfono';
        $telefono->placeholder = 'Teléfono';
        $telefono->enDiv = true;
        $telefono->maxlength = 10;
        $telefono->formato = 'entero';
        $telefono->value = $rfc->getTelefono();

        if ($this->AltaProveedor && $abc == 'a') {
            $telefono->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $telefono->enabled = 'enabled';
        } else {
            $telefono->disabled = 'disabled';
        }

        $referencia = new stdClass();
        $referencia->id = 'referencia';

        switch ($this->prm->tcy_id) {
            case 1:
                $nombre->label = 'Nombre';
                $nombre->placeholder = 'Nombre';
                $rfc_id->label = 'RFC';
                $rfc_id->placeholder = 'Ingresar RFC';
                $rfc_id->maxLength = 13;
                $colonia->label = 'Colonia';
                $colonia->placeholder = 'Colonia';
                $localidad->label = 'Localidad';
                $localidad->placeholder = 'Localidad';
                $calle->label = 'Calle';
                $calle->placeholder = 'Calle';
                $referencia->label = 'Actividad Preponderante';
                $referencia->placeholder = 'Actividad Preponderante';
                break;
            case 2:
                $nombre->label = 'Nombre/Razón Social';
                $nombre->placeholder = 'Nombre/Razón Social';
                $rfc_id->label = 'RFC';
                $rfc_id->placeholder = 'Ingresar RFC';
                $rfc_id->maxLength = 12;
                $localidad->label = 'Localidad';
                $localidad->placeholder = 'Localidad';
                $colonia->label = 'Colonia';
                $colonia->placeholder = 'Colonia';
                $calle->label = 'Calle';
                $calle->placeholder = 'Calle';
                $referencia->label = 'Actividad Preponderante';
                $referencia->placeholder = 'Actividad Preponderante';
                break;
            case 3:
                $nombre->label = 'Nombre/Denominación/Razón social';
                $nombre->placeholder = 'Nombre/Denominación/Razón social';
                $rfc_id->label = 'Control Fiscal';
                $rfc_id->placeholder = 'Control Fiscal';
                $rfc_id->maxLength = 26;
                $telefono->maxlength = 13;
                $localidad->label = 'País';
                $localidad->placeholder = 'País';
                $colonia->label = 'Estado';
                $colonia->placeholder = 'Estado';
                $calle->label = 'Ciudad/Localidad';
                $calle->placeholder = 'Ciudad/Localidad';
                $referencia->label = 'Actividad Preponderante';
                $referencia->placeholder = 'Actividad Preponderante';
                break;
            case 4:
                $nombre->label = 'Nombre';
                $nombre->placeholder = 'Nombre';
                $rfc_id->label = 'RFC';
                $rfc_id->placeholder = 'Ingresar RFC';
                $rfc_id->maxLength = 10;
                $localidad->label = 'Localidad';
                $localidad->placeholder = 'Localidad';
                $colonia->label = 'Colonia';
                $colonia->placeholder = 'Colonia';
                $calle->label = 'Calle';
                $calle->placeholder = 'Calle';
                $referencia->label = 'PUESTO';
                $referencia->placeholder = 'PUESTO';
                break;
        }

        $rfc_id->required = true;
        $rfc_id->enDiv = true;
        $rfc_id->value = $rfc->getRfc_id();
        if ($rfc->getRfc_id() != '') {
            $rfc_id->disabled = 'disabled';
        }

        $localidad->enDiv = true;
        $localidad->maxlength = 50;
        $localidad->value = $rfc->getLocalidad();

        if ($this->AltaProveedor && $abc == 'a') {
            $localidad->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $localidad->enabled = 'enabled';
        } else {
            $localidad->disabled = 'disabled';
        }

        $colonia->enDiv = true;
        $colonia->maxlength = 55;
        $colonia->value = $rfc->getColonia();
        if ($this->AltaProveedor && $abc == 'a') {
            $colonia->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $colonia->enabled = 'enabled';
        } else {
            $colonia->disabled = 'disabled';
        }

        $calle->enDiv = true;
        $calle->maxlength = 50;
        $calle->value = $rfc->getCalle();
        if ($this->AltaProveedor && $abc == 'a') {
            $calle->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $calle->enabled = 'enabled';
        } else {
            $calle->disabled = 'disabled';
        }

        $nombre->maxLength = 310;
        $nombre->required = true;
        $nombre->enDiv = true;
        $nombre->value = $rfc->getNombre();
        if ($this->AltaProveedor && $abc == 'a') {
            $nombre->enabled = 'enabled';
        } elseif (!$vinculadoACuenta) {
            $nombre->enabled = 'enabled';
        } else {
            $nombre->disabled = 'disabled';
            $nombre->label = $nombre->label . ' <span style="font-size:8px; color:#990000">(EL proveedor esta vinculado a una o mas cuentas bancarias)</span>';
        }

        $referencia->enDiv = true;
        $referencia->required = true;
        $referencia->maxlength = 100;
        $referencia->value = $rfc->getReferencia();
        if ($this->AltaProveedor && $abc == 'a') {
            $referencia->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $referencia->enabled = 'enabled';
        } else {
            $referencia->disabled = 'disabled';
        }

        $paterno = new stdClass();
        $paterno->id = 'paterno';
        $paterno->label = 'Apellido paterno';
        $paterno->placeholder = 'Apellido paterno';
        $paterno->enDiv = true;
        $paterno->required = true;
        $paterno->maxlength = 30;
        $paterno->value = $rfc->getPaterno();

        if ($this->AltaProveedor && $abc == 'a') {
            $paterno->enabled = 'enabled';
        } elseif (!$vinculadoACuenta) {
            $paterno->enabled = 'enabled';
        } else {
            $paterno->disabled = 'disabled';
        }

        $materno = new stdClass();
        $materno->id = 'materno';
        $materno->label = 'Apellido materno';
        $materno->placeholder = 'Apellido materno';
        $materno->enDiv = true;
        $materno->required = true;
        $materno->maxlength = 30;
        $materno->value = $rfc->getMaterno();

        if ($this->AltaProveedor && $abc == 'a') {
            $materno->enabled = 'enabled';
        } elseif (!$vinculadoACuenta) {
            $materno->enabled = 'enabled';
        } else {
            $materno->disabled = 'disabled';
        }

        $curp = new stdClass();
        $curp->id = 'curp';
        $curp->label = 'CURP';
        $curp->placeholder = 'CURP';
        $curp->enDiv = true;
        $curp->required = true;
        $curp->maxlength = 18;
        $curp->value = $rfc->getCurp();

        if ($this->AltaProveedor && $abc == 'a') {
            $curp->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $curp->enabled = 'enabled';
        } else {
            $curp->disabled = 'disabled';
        }

        $numero = new stdClass();
        $numero->id = 'numero';
        $numero->label = 'Exterior';
        $numero->placeholder = 'Exterior';
        $numero->enDiv = true;
        $numero->maxlength = 5;
        $numero->value = $rfc->getExterior();

        if ($this->AltaProveedor && $abc == 'a') {
            $numero->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $numero->enabled = 'enabled';
        } else {
            $numero->disabled = 'disabled';
        }

        $interior = new stdClass();
        $interior->id = 'interior';
        $interior->label = ' Interior';
        $interior->placeholder = 'Interior';
        $interior->enDiv = true;
        $interior->value = $rfc->getInterior();

        if ($this->AltaProveedor && $abc == 'a') {
            $interior->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $interior->enabled = 'enabled';
        } else {
            $interior->disabled = 'disabled';
        }

        $edo_id = new stdClass();
        $edo_id->sql = 'select edo_id,edo_nombre FROM ' . BD_SINPRES . '.cat_Entidad where edo_id!=0';
        $edo_id->id = 'edo_id';
        $edo_id->name = 'edo_id';
        $edo_id->required = true;
        $edo_id->search = true;
        $edo_id->mensaje = '';
        $edo_id->change = '
            if( $(this).val() != "" ){
                comunica("' . $this->pantalla . '",{
                    metodo:"cargaMunicipio",
                    edo_id:$("#edo_id").val(),
                    rfc_id:$("#rfc_id").val()
                });
            }
        ';

        $edo_id->label = 'Seleccion el estado';
        $edo_id->selecciona = $rfc->getEdo_id();

        if ($this->AltaProveedor && $abc == 'a') {
            $edo_id->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $edo_id->enabled = 'enabled';
        } else {
            $edo_id->enabled = 'enabled';
        }

        $mun_id = new stdClass();
        $mun_id->id = 'mun_id';
        $mun_id->width = '400px';
        $mun_id->label = 'Selecciona Municipio/Delegación';
        $mun_id->enRow = 1;
        $mun_id->required = true;
        $mun_id->search = true;

        if ($this->AltaProveedor && $abc == 'a') {
            $mun_id->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $mun_id->enabled = 'enabled';
        } else {
            $mun_id->disabled = 'disabled';
        }

        $mun_id->selecciona = $rfc->getMun_id();

        if ($rfc->getEdo_id() == '') {
            $mun_id = $this->templateCombo($mun_id);
        } else {
            $mun_id->sql = 'SELECT mun_id,mun_nombre FROM ' . BD_SINPRES . '.cat_Municipios WHERE edo_id = ? and mun_id!=0';
            $mun_id->params = [$rfc->getEdo_id()];
            $mun_id = $this->ponCombo($mun_id);

        }

        $tipoSociedad = new StdClass();
        $tipoSociedad->values = [1 => 'SIN SOCIEDAD', 2 => 'CON SOCIEDAD'];
        $tipoSociedad->id = 'gggg';
        $tipoSociedad->class = 'radio rango';
        $tipoSociedad->name = 'tipoSo';
        $tipoSociedad->cols = 3;
        $tipoSociedad->required = true;

        $tipoSociedad->class = 'deshabilitar';

        if ($this->AltaProveedor && $abc == 'a') {
            $tipoSociedad->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $tipoSociedad->enabled = 'enabled';
        } else {
            $tipoSociedad->disabled = 'disabled';
        }

        $soc_id = new stdClass();
        $soc_id->sql = 'SELECT soc_id,soc_nombre FROM ' . BD_SINPRES . '.cat_SociedadProveedor ORDER BY soc_id DESC';
        $soc_id->id = 'soc_id';
        $soc_id->name = 'soc_id';
        $soc_id->required = true;
        $soc_id->search = true;
        $soc_id->label = 'Selecciona el Tipo de Sociedad';
        $soc_id->selecciona = $rfc->getSoc_id();

        if ($this->AltaProveedor && $abc == 'a') {
            $soc_id->enabled = 'enabled';
        } elseif (!$vinculadoACuenta) {
            $soc_id->enabled = 'enabled';
        } else {
            $soc_id->disabled = 'disabled';
        }

        $codigo = new stdClass();
        $codigo->id = 'codigo';
        $codigo->label = 'C.P.';
        $codigo->placeholder = 'C.P.';
        $codigo->enDiv = true;
        $codigo->required = true;
        $codigo->formato = 'entero';
        $codigo->maxlength = 5;
        $codigo->value = $rfc->getCp();

        if ($this->AltaProveedor && $abc == 'a') {
            $codigo->enabled = 'enabled';
        } elseif ($this->nivel >= 3) {
            $codigo->enabled = 'enabled';
        } else {
            $codigo->disabled = 'disabled';
        }

        $email = new stdClass();
        $email->id = 'email';
        $email->label = 'Correo Electrónico del Proveedor';
        $email->placeholder = 'Correo Electrónico';
        $email->enDiv = true;
        $email->type = 'email';
        $email->maxlength = 50;
        $email->value = $rfc->getEmail();

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'deleteCedula';
        $btnEliminar->class = 'delete me-1';
        $btnEliminar->color = 'rojo';
        $btnEliminar->text = 'Eliminar';
        $btnEliminar->value = 'Eliminar';
        $btnEliminar->icon = 'solid fa-trash me-2';
        $btnEliminar->attr = new stdClass();
        $btnEliminar->attr->rfc_id = $rfc->getRfc_id();

        $btnGuardar = new stdClass();
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->id = 'guardar';
        $btnGuardar->text = 'Guardar';
        $btnGuardar->color = 'verde';
        $btnGuardar->value = 'Guardar';
        $btnGuardar->class = 'me-1';
        $btnGuardar = $this->ponBoton($btnGuardar);

        $btnaDeshabilitar = '';
        if (isset($this->prm->rfc_id)) {
            $sql = 'SELECT * from ' . BD_CTRL . '.cat_Proveedor WHERE rfc_id = ? LIMIT 1';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->prm->rfc_id]);
            $this->desconectar();
            if ($this->getNumeroRegistros() && $this->ClasGpo == 9 && $_SESSION[_S]['ur_id'] == '01') {
                $btnaDeshabilitar = new stdClass();
                $btnaDeshabilitar->id = 'btnaDeshabilitar';
                $btnaDeshabilitar->class = 'me-1';
                $btnaDeshabilitar->icon = 'solid fa-trash me-2';
                $btnaDeshabilitar->color = 'rojo';
                $btnaDeshabilitar->value = 'inhabilitar RFC';
                $btnaDeshabilitar->click = 'comunica("' . $this->pantalla . '", {metodo: "inhabilitarRFC",rfc_id: "' . $this->prm->rfc_id . '" });';
                $btnaDeshabilitar = $this->ponBoton($btnaDeshabilitar);
            }
        }

        $html = '';
        $html1 = '';
        $html2 = '';
        $html4 = '';
        $htmlrfc = '';
        $html3 = '';
        $html .= '<form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">';

        switch ($this->prm->tcy_id) {
            /* TIPO DE CONTRIBUYENTE  FISICA */
            case 1:
                $email->required = true;
                $colonia->required = true;
                $localidad->required = true;
                $calle->required = true;
                $numero->required = true;
                $interior->required = true;
                $telefono->required = true;

                $htmlrfc .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($rfc_id) . '
                        </div>
                    </div>
                ';

                $html1 .= '
                    <div class="row mb-4">
                        <div class="col-md-4" id="hidepaterno">
                            ' . $this->ponInput($paterno) . '
                        </div>
                        <div class="col-md-4" id="hidematerno">
                            ' . $this->ponInput($materno) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($nombre) . '
                        </div>
                    </div>
                ';

                $html2 .= '
                    <div id="hidestado">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                ' . $this->ponCombo($edo_id) . '
                            </div>
                            <div class="col-md-4" id="tr_municipio">
                            ' . $mun_id . '
                            </div>
                            <div class="col-md-4">
                                ' . $this->ponInput($localidad) . '
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                ' . $this->ponInput($colonia) . '
                            </div>
                            <div class="col-md-4">
                                ' . $this->ponInput($calle) . '
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        ' . $this->ponInput($numero) . '
                                    </div>
                                    <div class="col-md-4">
                                        ' . $this->ponInput($interior) . '
                                    </div>
                                    <div class="col-md-4">
                                        ' . $this->ponInput($codigo) . '
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ';

                $html3 .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($referencia) . '
                        </div>
                        <div  class="col-md-4">
                            ' . $this->ponInput($telefono) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($email) . '
                        </div>
                    </div>
                ';

                break;

                /* TIPO DE CONTRIBUYENTE MORAL  */
            case 2:
                $email->required = true;
                $colonia->required = true;
                $localidad->required = true;
                $calle->required = true;
                $numero->required = true;
                $telefono->required = true;

                $htmlrfc .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($rfc_id) . '
                        </div>
                        <div class="col-md-4"  id="hidesoc">
                            ' . $this->ponCombo($soc_id) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($nombre) . '
                        </div>
                    </div>
                
                ';
                $html2 .= '
                    <div id="hidestado">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                ' . $this->ponCombo($edo_id) . '
                            </div>
                            <div class="col-md-4" id="tr_municipio">
                                ' . $mun_id . '
                            </div>
                            <div class="col-md-4">
                                ' . $this->ponInput($localidad) . '
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                ' . $this->ponInput($colonia) . '
                            </div>
                            <div class="col-md-4">
                                ' . $this->ponInput($calle) . '
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        ' . $this->ponInput($numero) . '
                                    </div>
                                    <div class="col-md-4">
                                        ' . $this->ponInput($interior) . '
                                    </div>
                                    <div class="col-md-4">
                                        ' . $this->ponInput($codigo) . '
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ';

                $html3 .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($referencia) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($telefono) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($email) . '
                        </div>
                    </div>
                ';
                break;

                /* TIPO DE CONTRIBUYENTE EXTRANJERA */
            case 3:
                $colonia->required = true;
                $localidad->required = true;
                $calle->required = true;
                $telefono->required = true;

                $htmlrfc .= '
                    <div class="row mb-4 mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($rfc_id) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($nombre) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($referencia) . '
                        </div>
                    
                    </div>
                ';

                $html2 .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($localidad) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($colonia) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($calle) . '
                        </div>
                    </div>
                ';

                $html3 .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($codigo) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($telefono) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($email) . '
                        </div>
                    </div>
                ';

                break;

                /* ESTE ES UN NUEVO TIPO QUE SE AGREGO EN ESTE 2014 SON LOS BENEFICIARIOS  SOLO TIENES 10 DIGITOS */
            case 4:
                $telefono->required = true;

                $htmlrfc .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($rfc_id) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($curp) . '
                        </div>
                    </div>
                ';

                $html1 .= '
                    <div class="row mb-4">
                        <div class="col-md-4" id="hidepaterno">
                            ' . $this->ponInput($paterno) . '
                        </div>
                        <div class="col-md-4" id="hidematerno">
                            ' . $this->ponInput($materno) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($nombre) . '
                        </div>
                    </div>
                ';

                $html2 = '';
                $html3 .= '
                    <div class="row mb-4">
                        <div class="col-md-4">
                            ' . $this->ponInput($referencia) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($telefono) . '
                        </div>
                        <div class="col-md-4">
                            ' . $this->ponInput($email) . '
                        </div>
                    </div>
                ';
                break;
        }

        $html .= $htmlrfc;
        $html .= $html1;
        $html .= $html2;
        $html .= $html4;
        $html .= '';
        $html .= $html3;

        $btnRegresarIndex = '<button id="btnRegresarIndex" type="button" class="btn btn-outline-secondary regresar me-1" title="Regresar al Menú Principal"><i class="fa-solid fa-circle-left me-2"></i>Regresar</button>';
        $this->agregaTools('' . $btnRegresarIndex . ' ' . $btnGuardar . ' ' . $btnaDeshabilitar . ' ');

        $html .= '</form>';
        $this->mixHtml($html);

        $this->mixScript('
            $("#guardar").click(function(){
                var obj = {"metodo":"abcProveedores"};
                obj.abc = "' . $abc . '";
                guardar = parseInt(' . $this->prm->tcy_id . ');
                switch(guardar)
                {
                    case 1:
                        obj.rfc_id = $("#rfc_id").val();
                        obj.tcy_id = ' . $this->prm->tcy_id . ';
                        obj.nombre = $("#nombre").val();
                        obj.paterno = $("#paterno").val();
                        obj.materno = $("#materno").val();
                        obj.calle = $("#calle").val();
                        obj.exterior = $("#numero").val();
                        obj.interior = $("#interior").val();
                        obj.localidad = $("#localidad").val();
                        obj.colonia = $("#colonia").val();
                        obj.referencia = $("#referencia").val();
                        obj.cp = $("#codigo").val();
                        obj.telefono = $("#telefono").val();
                        obj.email = $("#email").val();
                        obj.edo_id = $("#edo_id").val();
                        obj.mun_id = $("#mun_id").val();
                        if(!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                    break;
                    case 2:
                        obj.rfc_id = $("#rfc_id").val();
                        obj.tcy_id = ' . $this->prm->tcy_id . ';
                        obj.nombre = $("#nombre").val();
                        obj.calle = $("#calle").val();
                        obj.exterior = $("#numero").val();
                        obj.interior = $("#interior").val();
                        obj.localidad = $("#localidad").val();
                        obj.colonia = $("#colonia").val();
                        obj.referencia = $("#referencia").val();
                        obj.cp = $("#codigo").val();
                        obj.telefono = $("#telefono").val();
                        obj.email = $("#email").val();
                        obj.edo_id = $("#edo_id").val();
                        obj.mun_id = $("#mun_id").val();
                        obj.soc_id = $("#soc_id").val();
                        if(!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                    break;
                    case 3:
                        obj.rfc_id = $("#rfc_id").val();
                        obj.tcy_id = ' . $this->prm->tcy_id . ';
                        obj.nombre = $("#nombre").val();
                        obj.calle = $("#calle").val();
                        obj.localidad = $("#localidad").val();
                        obj.colonia = $("#colonia").val();
                        obj.referencia = $("#referencia").val();
                        obj.cp = $("#codigo").val();
                        obj.telefono = $("#telefono").val();
                        obj.email = $("#email").val();

                        if(!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                    break;
                    case 4:
                        obj.rfc_id = $("#rfc_id").val();
                        obj.tcy_id = ' . $this->prm->tcy_id . ';
                        obj.nombre = $("#nombre").val();
                        obj.curp = $("#curp").val();
                        obj.paterno = $("#paterno").val();
                        obj.materno = $("#materno").val();
                        obj.referencia = $("#referencia").val();
                        obj.telefono = $("#telefono").val();
                        obj.email = $("#email").val();
                        if(!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                    break;
                }
            });
            $(".delete").click(function(){
                var elim= confirm("Estas seguro de Eliminar el RFC ' . ($rfc->getRfc_id()) . ' ");
                if(elim){
                    comunica("' . $this->pantalla . '",{
                    metodo:"abcProveedores",
                    abc:"b",
                    "rfc_id":"' . ($rfc->getRfc_id()) . '",
                    });
                    return false;
                }
            });
            $("#btnRegresarIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                comunica("scp/Catalogos/ControladorProveedores.php",{
                    metodo:"main",
                    tcy_id:' . $this->prm->tcy_id . '
                });
                return false;
            });
        ');

        $this->mixScript('
        $("#rfc_id").blur(function() {
            RFC($("#rfc_id").val());
        });

        function RFC(cual) {
            opcion = parseInt(' . $this->prm->tcy_id . ');
            switch (opcion) {
                case 1:
                    mensaje = "Debes poner un formato LLLL999999XXX";
                    if (cual.length != 13) {
                        notify("Debes poner un formato LLLL999999XXX", "warning");
                    } else {
                        var val = new Array();
                        val[0] = cual.substring(0, 4);
                        val[1] = cual.substring(4, 10);
                        val[2] = cual.substring(10, 13);
                        if (!validarRFC(val)) {
                            notify("Debes poner un formato LLLL999999XXX", "warning");
                        } 
                    }

                    break;
                case 2:
                    mensaje = "Debes poner un formato LLL999999XXX";
                    if (cual.length != 12) {
                        notify(mensaje);
                        $("#guardar").remove();
                    } else {
                        var val = new Array();
                        val[0] = cual.substring(0, 3);
                        val[1] = cual.substring(3, 9);
                        val[2] = cual.substring(9, 12);
                        if (!validarRFC(val)) {
                            $("#rfc_id").val("");
                            notify(mensaje);
                        } 
                    }

                    break;
                case 3:
                    mensaje = "Debes poner un formato con letras y numeros";
                    pat = /[a-z]|[A-Z]|[0-9]/;
                    if (cual.length > 8) {
                        if (!comp(cual, pat)) {
                            $("#rfc_id").val("");
                            notify(mensaje);
                        }
                    } else {
                        notify("Debes ingresar almenos 8 digitos", "warning");
                    }
                    break;
                case 4:
                    mensaje = "Debes poner un formato LLLL999999";
                    if (cual.length != 10) {
                        notify(mensaje);
                        $("#guardar").remove();
                    } else {
                        var val = new Array();
                        val[0] = cual.substring(0, 4);
                        val[1] = cual.substring(4, 10);
                        val[2] = cual.substring(11);
                        if (!validarRFC(val)) {
                            $("#rfc_id").val("");
                            notify(mensaje);
                        } else {
                            notify("RFC valido", "success");
                        }
                    }

                    break;
            }
        }

        function validarRFC(val) {
            pat = /[a-zñ]|[A-ZÑ]|[&]/;
            pat2 = /[a-zñ]|[A-ZÑ]|[0-9]/;
            if (!comp(val[0], pat)) {
                return false;
            }
            if (isNaN(val[1])) {
                return false;
            }
            if (!comp(val[2], pat2)) {
                return false;
            }
            return true;
        }

        function comp(cual, pa) {
            for (m = 0; m < cual.length; m++) {
                if (!pa.test(cual.charAt(m))) {
                    return false
                    break
                }
            }
            return true;
        }

        var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;

        var permirtirRFC = /^[a-zA-Z\u00f1\u00d1\d]/;
        $("#rfc_id").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permirtirRFC.test(String.fromCharCode(keynum));
        });
        $("#rfc_id").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#paterno").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
        });
        $("#paterno").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#materno").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
        });
        $("#materno").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#nombre").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;

            return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
        });
        $("#nombre").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#localidad").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;

            return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
        });

        $("#localidad").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#colonia").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
        });

        $("#colonia").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#calle").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
        });
        $("#calle").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#numero").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permirtirRFC.test(String.fromCharCode(keynum));
        });

        $("#numero").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });

        $("#referencia").keypress(function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                return true;
            return permirtirRFC.test(String.fromCharCode(keynum));
        });

        $("#referencia").keyup(function(e) {
            $(this).val($(this).val().toUpperCase());
        });
        ');
    }

    public function abcProveedores()
    {
        $sql = 'SELECT COUNT(*) AS Cuentas FROM vin_BancosUrProveedor INNER JOIN cat_BancosUr USING (bur_id) WHERE rfc_id = ? AND eba_id = 6';
        $this->setSql($sql);
        $this->conectar();
        $this->consultar([$this->prm->rfc_id]);
        $this->desconectar();
        if ($this->prm->email != '') {
            $this->prm->email;
        } else {
            $this->prm->email = ' ';
        }

        $this->prm->rfc_id = isset($this->prm->rfc_id) ? $this->prm->rfc_id : '';
        switch ($this->prm->abc) {
            case 'a':
                $this->conectar();
                $this->setSql('SELECT rfc_id from ' . BD_SINPRES . '.cat_Proveedores where rfc_id = ?; ');
                $this->consultar([$this->prm->rfc_id]);
                $this->desconectar();
                if ($this->getNumeroRegistros() != 0) {
                    $this->mixScript('notify("El RFC ' . strtoupper($this->prm->rfc_id) . ' ya Existe en la Base de Datos","error");');
                    $this->main();
                } else {
                    /*Los proveedores que se ingresen con sts_id = 1 es porque están verificados ante el sat pero solo aplica para las personas fisicas y morales  */
                    switch ($this->prm->tcy_id) {
                        case 1:
                        case 2:
                            $this->prm->color = '41A62A';
                            $this->prm->sts_id = 1;
                            // no break
                        default:
                            $this->prm->color = 'FF0000';
                            $this->prm->sts_id = 2;
                            break;
                    }

                    $this->prm->gpo_id = $this->ClasGpo;
                    $this->prm->ur_id = $_SESSION[_S]['ur_id'];

                    $this->prm->mun_id = (isset($this->prm->mun_id)) ? $this->prm->mun_id : '';
                    $this->prm->edo_id = (isset($this->prm->edo_id)) ? $this->prm->edo_id : '';


                    if ($this->prm->tcy_id == 1 || $this->prm->tcy_id == 3 || $this->prm->tcy_id == 4) {
                        $this->prm->soc_id = '0';
                    }

                    if ($this->prm->tcy_id != 4) {
                        $this->prm->curp = ' ';
                    }

                    if ($this->prm->tcy_id == 2 || $this->prm->tcy_id == 3) {
                        $this->prm->paterno = ' ';
                        $this->prm->materno = ' ';
                        $this->prm->exterior = ' ';
                        $this->prm->interior = ' ';
                    }

                    if ($this->prm->tcy_id == 3 || $this->prm->tcy_id == 4) {
                        $this->prm->edo_id = '0';
                        $this->prm->mun_id = '0';
                    }

                    if ($this->prm->tcy_id == 4) {
                        $this->prm->calle = ' ';
                        $this->prm->exterior = ' ';
                        $this->prm->interior = ' ';
                        $this->prm->colonia = ' ';
                        $this->prm->localidad = ' ';
                        $this->prm->cp = '0';
                    }

                    if ($this->prm->tcy_id == 3) {
                        if (strlen($this->prm->rfc_id) < 8) {
                            $this->mixScript('
                                notify("El beneficiario Extranjero debe tener almenos 8 digitos","warning");
                                comunica("' . $this->pantalla . '",{
                                    metodo:"main"
                                });
                            ');
                            break;
                        }
                    }

                    $this->prm->nombre = trim($this->prm->nombre);
                    if ($this->prm->tcy_id == 1) {
                        $this->prm->paterno = trim($this->prm->paterno);
                        $this->prm->materno = trim($this->prm->materno);
                    }
                    $this->prm->ejercicio = APP_EJERCICIO;

                    Proveedores::abc($this->prm);
                    $this->mixScript('
                        notify("El RFC ' . strtoupper($this->prm->rfc_id) . ' ha sido ingresado correctamente","success");
                        comunica("' . $this->pantalla . '",{
                            metodo:"main",
                            rfc_id:"' . $this->prm->rfc_id . '",
                            tcy_id:' . $this->prm->tcy_id . '
                        });
                    ');
                }
                break;

            case 'c':
                if (isset($this->prm->edo_id) && $this->prm->edo_id == '') {
                    $this->prm->edo_id = 0;
                }
                if (isset($this->prm->mun_id) && $this->prm->mun_id == '') {
                    $this->prm->mun_id = 0;
                }

                Proveedores::abc($this->prm);
                $this->mixScript('notify("El RFC ' . strtoupper($this->prm->rfc_id) . ' Se actualizó correctamente","success");');
                $this->main();
                break;

            case 'b':
                $bandera = false;
                if ($this->prm->rfc_id != '') {
                    $this->conectar();
                    $this->setSql('SELECT rfc_id, gpo_id, ur_id FROM ' . BD_SINPRES . '.cat_Proveedores WHERE rfc_id = ?');
                    $this->consultar([$this->prm->rfc_id]);
                    $this->desconectar();
                    if ($proveedor = $this->extraer()) {
                        if ($this->ClasGpo == $proveedor['gpo_id'] && $_SESSION[_S]['ur_id'] == $proveedor['ur_id']) {
                            $bandera = true;
                        } else {
                            $bandera = false;
                            $this->mixScript('
                                notify("No tienes permisos para eliminar el RFC ' . strtoupper($this->prm->rfc_id) . ' ", "error");
                            ');
                        }
                    }

                    if ($bandera) {
                        $this->conectar();
                        $this->setSql('SELECT rfc_id FROM scp_Contable WHERE rfc_id = ? LIMIT 1
                            UNION
                        SELECT rfc_id
                            FROM scp_Presupuestal
                        WHERE rfc_id = ?
                        LIMIT 1
                        ');
                        $params = [$this->prm->rfc_id, $this->prm->rfc_id];
                        $this->consultar($params);
                        $this->desconectar();

                        if ($this->getNumeroRegistros() != 0) {
                            $this->mixScript('
                                window.notify("No puedes eliminar el registro ya tiene numeros de control","error");
                            ');
                            $bandera = false;
                            $this->main();
                        } else {
                            $bandera = true;
                        }
                    }

                    if ($bandera == true) {
                        Proveedores::abc($this->prm);
                        $this->mixScript('
                            notify("Se eliminó el rfc: ' . strtoupper($this->prm->rfc_id) . '","error");
                            comunica("' . $this->pantalla . '",{
                                metodo:"main",
                            });
                        ');
                    }
                }
                break;
        }
    }

    public function cargaMunicipio()
    {
        $this->setContenedor('tr_municipio');

        $rfc = new Proveedores();
        if (!empty($this->prm->rfc_id)) {
            $rfc->setRfc_id($this->prm->rfc_id);
            $rfc->select();
        } else {
            $rfc->setRfc_id(0);
        }

        $mun_id = new stdClass();
        $mun_id->sql = 'SELECT mun_id,mun_nombre FROM ' . BD_SINPRES . '.cat_Municipios WHERE edo_id = ? and mun_id!=0';
        $mun_id->id = 'mun_id';
        $mun_id->width = '400px';
        $mun_id->label = 'Selecciona Municipio/Delegación';
        $mun_id->enRow = 1;
        $mun_id->selecciona = $rfc->getMun_id();
        $mun_id->params = [$this->prm->edo_id];
        $this->recargaCombo($mun_id);
    }

    public function habilitarRFC()
    {
        $user = $_SESSION[_S]['usr_id'];
        if (count($this->prm->rfc) > 0 && $this->prm->rfc != '') {
            foreach ($this->prm->rfc as $key => $val) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('SELECT * FROM cat_Proveedor WHERE rfc_id = ?');
                $conexion->consultar([$val->rfc_id]);
                $conexion->desconectar();
                if ($conexion->getNumeroRegistros() > 0) {
                    $this->mixScript(' notify("El RFC  ' . $val->rfc_id . ' ya ha sido habilitado ","error"); ');
                    return false;
                } else {
                    $this->conectar();
                    $this->setSql(' INSERT INTO cat_Proveedor (rfc_id,sts_id,rfc_cta_id,usr_id) VALUES (?, ?, ?, ?)');
                    $params = [$val->rfc_id, 1, 0, $user];
                    $this->consultar($params);
                    $this->desconectar();

                    $this->mixScript(' notify("El RFC  ' . $val->rfc_id . ' ha sido habilitado correctamente ","success"); ');

                    $this->prm->tcy_id = $val->tcy_id;
                    $this->prm->rfc_id = $val->rfc_id;
                    $this->llenaTablaProveedores();
                }
            }
        }
    }
    public function inhabilitarRFC()
    {
        $sql = '
            SELECT * from ' . BD_CTRL . '.cat_Proveedor cp 
            LEFT JOIN vin_BancosUrProveedor using (rfc_id)
            LEFT JOIN cat_BancoDocumentos using (rfc_id)
            LEFT JOIN scp_RectificacionPresupuestal using (rfc_id) 
            LEFT JOIN scp_Compromiso using (rfc_id)
            LEFT JOIN scp_Presupuestal using (rfc_id)
            LEFT JOIN scp_RectificacionContable using (rfc_id)
            LEFT JOIN scp_Contable using (rfc_id)
            WHERE cp.rfc_id = ? LIMIT 10';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->rfc_id]);
        $this->desconectar();

        if ($this->getNumeroRegistros() > 1) {
            $this->mixScript('notify("El RFC esta en uso, no es posible eliminar", "warning");');
        } else {
            $sql = 'DELETE FROM ' . BD_CTRL . '.cat_Proveedor where rfc_id = ?';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->prm->rfc_id]);
            $this->desconectar();
            $this->mixScript('notify("Se ha inhabilitado  el RFC ", "success");');
        }
    }
}

if (isset($_SESSION[_S]['prm']['S']['6']['1']) || isset($_SESSION[_S]['prm']['C']['2']['10']) || isset($_SESSION[_S]['prm']['T']['6']['5'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorProveedores();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
