<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/browser.php';

class ControladorLotes extends WebResponse
{
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        $this->pantalla = 'scp/afectaciones/ControladorEnvioCLC.php';
        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
            case 4:
            case 5:
                switch ($this->metodo) {
                    case 'comboRecurso':
                        $this->comboRecurso();
                        break;
                    case 'detalleLote':
                        $this->detalleLote();
                        break;
                    case 'llenaTablaCeres':
                        $this->llenaTablaCeres();
                        break;
                    case 'envia':
                        $this->envia();
                        break;
                    case 'printl':
                        $this->reporte();
                        break;
                    case 'detalle_reporte':
                        $this->detalle_reporte();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }
    /** ****************  FUNCION PRINCIPAL  ******************** */

    private function main()
    {
        $this->agregaTools('');

        $navegadores = ['Opera', 'Chrome', 'Firefox', 'Mozilla'];
        $browser = new Browser();
        $mensaje = '';
        if (!in_array($browser->getBrowser(), $navegadores)) {
            $mensaje = '<div class="alert alert-warning">EL navegador ' . $browser->getBrowser() . ' no cuenta con soporte completo para el sinpres</div>';
        }

        $cards = [];

        $c = new stdClass();
        $c->id = 'generalote';
        $c->titulo = 'ENVIO DE CLC';
        $c->img = 'img/img.php?img=clc_envio-aplicacion';
        $c->label = 'ENVÍO';
        $c->title = 'Envío de CLC';
        $cards[] = $c;

        $c = new stdClass();
        $c->id = 'print_l';
        $c->titulo = 'IMPRIMIR REPORTE';
        $c->img = 'img/img.php?img=clc_impresion';
        $c->label = 'REPORTE';
        $c->title = 'Imprimir reporte';
        $cards[] = $c;

        $this->mixHtml($mensaje . $this->ponCards($cards));
        $this->mixScript('
            $("#generalote").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"detalleLote"
                });
            });

            $("#print_l").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"printl"
                });
            });
        ');
    }

    /**************LISTAD DE LOS DETALLES DEL LOTE (CLCS) ******************* */
    private function detalleLote()
    {
        $maximoceres = 300;
        $che = new stdClass();
        $che->id = 'SelectAll';
        $che->label = 'Todas';
        $che->enDiv = false;
        $che->orientacion = 'H';
        $che->onClick = '
            var attr = $(this).attr("checked");
            if (typeof attr !== typeof undefined && attr !== false) {
                $(".addCere").prop( "checked", true );
            } else {
                $(".addCere").prop( "checked", false );
            }
        ';

        $cbo_filtro = new stdClass();
        $cbo_filtro->id = 'btn_filtro';
        $cbo_filtro->mensaje = 'FILTRAR CLC';
        $cbo_filtro->opciones = ['' => 'FILTRAR CLC', '1' => 'CLC ENVIADA', '2' => 'CLC PARA ENVIAR'];
        $cbo_filtro->required = true;

        $btn_selecciona = new stdClass();
        $btn_selecciona->id = 'btn_selecciona';
        $btn_selecciona->value = 'Buscar';
        $btn_selecciona->color = 'gris';
        $btn_selecciona->icon = 'search me-2';

        $btn_regresa = new stdClass();
        $btn_regresa->value = 'Regresar';
        $btn_regresa->enDiv = false;
        $btn_regresa->color = 'gris';
        $btn_regresa->icon = 'solid fa-circle-left me-2';
        $btn_regresa->id = 'btn_regresa';

        $input_fecha = new stdClass();
        $input_fecha->id = 'fecha_lotedetalle';
        $input_fecha->enDiv = false;
        $input_fecha->formato = 'fecha';

        $cbo_modo = new stdClass();
        $cbo_modo->id = 'mpc_iddetalle';
        $cbo_modo->name = 'control';
        $cbo_modo->required = true;
        $cbo_modo->enDiv = false;
        $cbo_modo->camposVal = 'mpc_id';
        $cbo_modo->agrupaPor = 'mpc_id';
        $cbo_modo->campoLabelAgrupacion = 'mpc_nombre';
        $cbo_modo->mensaje = 'Seleccione una opción';
        $cbo_modo->label = 'Modalidad de Pago';
        $cbo_modo->comoAtr = 'mpc_id';
        $cbo_modo->sql = 'SELECT mpc_id,mpc_nombre FROM cat_ModoPagoClc WHERE mpc_id != 4';

        $cbo_recurso = new stdClass();
        $cbo_recurso->id = 'ter_id';
        $cbo_recurso->name = 'control';
        $cbo_recurso->required = true;
        $cbo_recurso->enDiv = false;
        $cbo_recurso->camposVal = 'ter_id';
        $cbo_recurso->agrupaPor = 'ter_id';
        $cbo_recurso->campoLabelAgrupacion = 'mpc_nombre';
        $cbo_recurso->mensaje = 'Seleccione una opción';
        $cbo_recurso->label = 'Tipo de Recurso';
        $cbo_recurso->comoAtr = 'ter_id';

        $cbo_recurso = $this->templateCombo($cbo_recurso);
        if ($_SESSION[_S]['uni_id'] == 17) {
            $sql = '
                SELECT
                    dep_id,usr_nombre
                FROM cat_Usuario
                    INNER JOIN ctrlUsuario. cat_Usuario USING (usr_id)
                    INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                    INNER JOIN cat_Departamento USING (dep_id)
                WHERE
                    dep_id IN (44,45,46)
                    AND nvj_id = 4
                GROUP BY dep_id,usr_nombre
                ;';
        } else {
            $sql = '
                SELECT dep_id,usr_nombre
                FROM cat_Usuario
                    INNER JOIN ctrlUsuario. cat_Usuario USING (usr_id)
                    INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                    INNER JOIN cat_Departamento USING (dep_id)
                WHERE
                    dep_id IN (48,49)
                    AND nvj_id = 4
                GROUP BY dep_id,usr_nombre;
            ';
        }

        $cbo_firma = new stdClass();
        $cbo_firma->id = 'departamentoid';
        $cbo_firma->name = 'control';
        $cbo_firma->required = true;
        $cbo_firma->enDiv = false;
        $cbo_firma->camposVal = 'dep_id';
        $cbo_firma->campoLabelAgrupacion = 'usr_nombre';
        $cbo_firma->mensaje = 'Seleccione una opción';
        $cbo_firma->label = 'Departamento';
        $cbo_firma->sql = $sql;
        //$cbo_firma->selecciona = $numdep; // se comenta esta linea ya que la lic Dolores pidio que se vieran todos los registros en la carga inicial de la pagina o cuando no se seleccione un departamento fecha peticion en persona 13/03/2018
        $cbo_firma = $this->ponCombo($cbo_firma);

        $ur = new stdClass();
        $ur->id = 'ur_id';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->enDiv = false;
        $ur->placeholder = 'UR';

        $claveFinanciamiento = new stdClass();
        $claveFinanciamiento->id = 'cvefin';

        $claveFinanciamiento->maxlength = 9;
        $claveFinanciamiento->placeholder = 'CLAVEFIN';
        $claveFinanciamiento->enDiv = false;

        $this->agregaTools($this->ponBoton($btn_regresa));
        $botones = '
            <div class="row mb-3">
                <fieldset class="col-md-4 col-xs-12">
                    ' . $cbo_firma . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-12">
                    ' . $this->ponCombo($cbo_modo) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-12">
                    ' . $cbo_recurso . '
                </fieldset>
            </div>
            <div class="row mb-3">
                <fieldset class="col-md-3 col-xs-12">
                    ' . $this->ponInput($input_fecha) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-12">
                    ' . $this->ponInput($ur) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-12">
                    ' . $this->ponInput($claveFinanciamiento) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-12">
                    ' . $this->ponCombo($cbo_filtro) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-12">
                    ' . $this->ponBoton($btn_selecciona) . '
                </fieldset>
            </div>
        ';
        $this->mixHtml($botones);

        $html = '';
        $this->mixHtml($html);
        {
            $tabla = new stdClass();
            $tabla->id = 'tbl_ceres';
            $tabla->titulo = 'Listado de CLC';

            $tabla->comunica = new stdClass();
            $tabla->comunica->pantalla = $this->pantalla;
            $tabla->comunica->metodo = 'llenaTablaCeres';
            $tabla->comunica->loe_mes = '{$("#loe_mes").val()}';
            $tabla->comunica->loe_dia = '{$("#loe_dia").val()}';
            $tabla->comunica->loe_anio = '{$("#loe_anio").val()}';
            $tabla->comunica->loe_cereCo = '{$("#loe_cereCo").val()}';
            $tabla->comunica->dep_id = '{$("#departamentoid").val()}';
            $tabla->comunica->tcp_id = '{$("#tcp_id").val()}';
            $tabla->comunica->ur = '{$("#ur_id").val()}';

            $tabla->comunica->ter_id = '{$("#ter_id").val()}';
            $tabla->comunica->fecha = '{$("#fecha_lotedetalle").val()}';
            $tabla->comunica->ecf_clave = '{$("#cvefin").val()}';
            $tabla->comunica->mpc_id = '{$("#mpc_id").val()}';
            $tabla->comunica->maximo = $maximoceres;
            $tabla->filasAmostrar = [100, 150, 200, 300, 400];
            $tabla->numFilas = 50;
            $tr = [];

            $th = new stdClass();
            $th->texto = 'Enviar';
            $th->width = '5%';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Cancelar';
            $th->width = '5%';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'UR';
            $th->width = '7%';
            $th->align = 'center';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'CLC';
            $th->width = '7%';
            $th->align = 'center';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'CERE-CO';
            $th->width = '7%';
            $th->align = 'center';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Concepto';
            $th->width = '35%';
            $th->align = 'left';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Importe';
            $th->width = '10%';
            $th->align = 'center';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Fecha';
            $th->width = '10%';
            $th->align = 'center';
            array_push($tr, $th);

            $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'ecl_id';
            $td->casos->tipoCondicion = '=';
            $td->casos->valor = '8';
            $td->casos->ands = [];

            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->clc_id = 'clc_id';
            $td->casos->verdadero->attr->gpo_id = 'gpo_id';
            $td->casos->verdadero->attr->ur_id = 'ur_id';
            $td->casos->verdadero->attr->mpc_id = 'mpc_id';
            $td->casos->verdadero->class = 'enviarCLC';

            $td->casos->verdadero->name = 'clc_id';
            $td->casos->verdadero->icono = 'solid fa-paper-plane';
            $td->casos->verdadero->color = 'verde';
            $td->casos->verdadero->id = 'clc_idE  ';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'ecl_id';
            $td->casos->tipoCondicion = '=';
            $td->casos->valor = '10';
            $td->casos->ands = [];

            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->clc_id = 'clc_id';
            $td->casos->verdadero->attr->gpo_id = 'gpo_id';
            $td->casos->verdadero->attr->ur_id = 'ur_id';
            $td->casos->verdadero->attr->mpc_id = 'mpc_id';
            $td->casos->verdadero->class = 'cancelaEnvClc';
            $td->casos->verdadero->icono = 'fa-solid fa-ban';
            $td->casos->verdadero->color = 'rojo';

            $td->casos->verdadero->id = 'clc_idC';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'UR';
            $td->align = 'center';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'clc_id';
            $td->align = 'center';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'clc_cereCo';
            $td->align = 'center';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'clc_concepto';
            $td->align = 'left';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'Importe';
            $td->align = 'right';
            $td->formato = 'moneda';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'FechaCLC';
            $td->align = 'center';
            array_push($trbody, $td);

            $tabla->encabezado = [$tr];
            $tabla->cuerpo = $trbody;
        }
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript('
            $("#mpc_iddetalle").change(function(){
                if ($(this).find("option:selected").attr("mpc_id") != undefined) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"comboRecurso",
                        mpc_id:$(this).find("option:selected").attr("mpc_id"),
                        filtro:$("#btn_filtro").val()
                    });
                }
            });

            $("#btn_selecciona").click(function(){
                comunica("' . $this->pantalla . '",{
                    "metodo":"llenaTablaCeres",
                    "ur":$("#ur_id").val(),
                    "maximo":' . $maximoceres . ',
                    "fecha":$("#fecha_lotedetalle").val(),
                    dep_id:$("#departamentoid").val(),
                    mpc_id:$("#mpc_iddetalle").val(),
                    ter_id:$("#ter_id").val()  ,
                    ecf_clave:$("#cvefin").val(),
                    filtro:$("#btn_filtro").val()
                });
            });
            $("#btn_regresa").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"main"});
            });
        ');
        $this->mixScript($this->wrLlamaLlenaTabla('tbl_ceres'));
    }
    /******************** GENERA LISTADO DE TABLAS  ********************* */
    private function llenaTablaCeres()
    {
        $params = [];
        $sql = '
            SELECT
                gpo_id,ur_id,b.ecl_id,
                clc_id,C.mpc_id,
                CONCAT(gpo_id,ur_id) AS UR,
                (TRUNCATE(clc_percepcion,2)-TRUNCATE(clc_retencion,2)) AS Importe,
                clc_concepto,clc_cereCo,
                date_format(clc_generacionCereCo,"%d/%m/%Y") AS FechaCLC,
                clc_cereCo
            FROM scp_Clc C
                INNER JOIN scp_ClcAuxiliar b USING ( gpo_id,ur_id,clc_id )
                INNER JOIN enc_FiltroClc USING(gpo_id,ur_id,tcl_id,stc_id,mno_id)
                INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING ( cff_id,fte_id,sft_id,ori_id,sor_id)
            WHERE
                b.ecl_id IN(8,9,10)
                AND clc_id !=0
                AND SUBSTRING( C.tem_carga, 1, 5 ) != "116-A"
            ';

        if (isset($this->prm->filtro) && $this->prm->filtro == '1') {
            $sql .= ' AND b.ecl_id="10"';
        }
        if (isset($this->prm->filtro) && $this->prm->filtro == '2') {
            $sql .= ' AND b.ecl_id != "10"';
        }
        if (isset($this->prm->mpc_id) && $this->prm->mpc_id != '') {
            $sql .= ' AND mpc_id = :mpc_id';
            $params[':mpc_id'] = $this->prm->mpc_id;
        }
        if (isset($this->prm->ter_id) && $this->prm->ter_id != '') {
            $sql .= ' AND ter_id = :ter_id';
            $params[':ter_id'] = $this->prm->ter_id;
        }
        if (isset($this->prm->dep_id) && $this->prm->dep_id != '') {
            $sql .= ' AND dep_id = :dep_id';
            $params[':dep_id'] = $this->prm->dep_id;

            $sql .= ($_SESSION[_S]['uni_id'] == 18 && ($this->prm->dep_id == 48 || $this->prm->dep_id == 49) ? ' AND ecf_clave NOT IN ("AACAA0117","AAACA0118")' : ''); // Se excluywn las clc para la unidad 18
        } else {
            $this->prm->dep_id = '';
            if ($_SESSION[_S]['uni_id'] == 17) {
                $sql .= ' AND dep_id IN (44,45,46)';
            } else {
                $sql .= ' AND dep_id IN (48, 49)';
                $sql .= ($_SESSION[_S]['uni_id'] == 18 ? ' AND ecf_clave NOT IN ("AACAA0117","AAACA0118")' : ''); // se excluyen las clc para la unidad 18
            }
        }

        if (isset($this->prm->ur) && $this->prm->ur != '') {
            $gpo = substr($this->prm->ur, 0, 1);
            $ur = substr($this->prm->ur, 1, 2);
            $sql .= ' AND gpo_id = :gpo_id AND ur_id = :ur_id';
            $params[':gpo_id'] = $gpo;
            $params[':ur_id'] = $ur;
        }

        if (isset($this->prm->ecf_clave) && $this->prm->ecf_clave != '') {
            $sql .= ' AND ecf_clave = :ecf_clave';
            $params[':ecf_clave'] = $this->prm->ecf_clave;
        }

        $fecha = (isset($this->prm->fecha) && $this->prm->fecha != '' ? substr($this->prm->fecha, 6, 4) . '-' .
            substr($this->prm->fecha, 3, 2) . '-' . substr($this->prm->fecha, 0, 2) : '');

        $sql .= $fecha != '' ? PHP_EOL . ' AND DATE(clc_generacionCereCo) = DATE("' . $fecha . '") ' : '';

        $sql .= '
            GROUP BY gpo_id,ur_id,clc_id
            ORDER BY gpo_id, ur_id, clc_id ASC
        ';

        $this->setSql($sql);
        $this->setParams($params);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_ceres';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript("notify('No hay CLCs coincidentes para este lote','warning');");
        } else {
            $this->mixScript('
                $(".cancelaEnvClc").click(function(){
                    var btn = $(this);
                    alertify.confirm("Cancelar CLC","Se cancelara el envio de CLC \n desea proceder?",
                        function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"envia",accion:"cancela",
                            clc_id:btn.attr("clc_id"),
                            gpo_id:btn.attr("gpo_id"),
                            ur_id:btn.attr("ur_id"),
                            mpc_id:btn.attr("mpc_id"),
                            mpc_iddetalle:$("#mpc_iddetalle").val()
                            ' . (isset($this->prm->filtro) && $this->prm->filtro != '' ? ',filtro: "' . $this->prm->filtro . '"' : '') . '
                            ' . (isset($this->prm->ter_id) && $this->prm->ter_id != '' ? ',ter_id: "' . $this->prm->ter_id . '"' : '') . '
                            ' . (isset($this->prm->dep_id) && $this->prm->dep_id != '' ? ',dep_id: "' . $this->prm->dep_id . '"' : '') . '
                            ' . (isset($this->prm->ecf_clave) && $this->prm->ecf_clave != '' ? ',ecf_clave: "' . $this->prm->ecf_clave . '"' : '') . '
                            ' . (isset($this->prm->fecha) && $this->prm->fecha != '' ? ',fecha: "' . $this->prm->fecha . '"' : '') . '
                            ' . (isset($this->prm->ur) && $this->prm->ur != '' ? ',ur: "' . $this->prm->ur . '"' : '') . '
                            ' . (isset($this->prm->nav->np) ? ',nav:{np:"' . $this->prm->nav->np . '", nf:"' . $this->prm->nav->nf . '"}' : '') . '
                        });
                        },
                        undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Aceptar", cancel: "Cancelar"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                    }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                    if (cancela) {
                        cancela.classList.remove("btn-danger");
                        cancela.classList.add("btn-outline-danger");
                    }
                });

                $(".enviarCLC").click(function(){
                    var btn = $(this);
                    alertify.confirm("Enviar CLC","Se enviara la CLC a Tesoreria \n Desea proceder ?",
                        function(){
                        comunica("' . $this->pantalla . '",{
                        metodo:"envia",
                            accion:"envia",
                            clc_id:btn.attr("clc_id"),
                            gpo_id:btn.attr("gpo_id"),
                            ur_id:btn.attr("ur_id"),
                            mpc_id:btn.attr("mpc_id"),
                            mpc_iddetalle:$("#mpc_iddetalle").val()
                            ' . (isset($this->prm->filtro) && $this->prm->filtro != '' ? ',filtro: "' . $this->prm->filtro . '"' : '') . '
                            ' . (isset($this->prm->ter_id) && $this->prm->ter_id != '' ? ',ter_id: "' . $this->prm->ter_id . '"' : '') . '
                            ' . (isset($this->prm->dep_id) && $this->prm->dep_id != '' ? ',dep_id: "' . $this->prm->dep_id . '"' : '') . '
                            ' . (isset($this->prm->ecf_clave) && $this->prm->ecf_clave != '' ? ',ecf_clave: "' . $this->prm->ecf_clave . '"' : '') . '
                            ' . (isset($this->prm->fecha) && $this->prm->fecha != '' ? ',fecha: "' . $this->prm->fecha . '"' : '') . '
                            ' . (isset($this->prm->ur) && $this->prm->ur != '' ? ',ur: "' . $this->prm->ur . '"' : '') . '
                            ' . (isset($this->prm->nav->np) ? ',nav:{np:"' . $this->prm->nav->np . '", nf:"' . $this->prm->nav->nf . '"}' : '') . '
                        });
                        },
                        undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Aceptar", cancel: "Cancelar"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
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
    }
    /******************************  ENVIAR EL LOTE A INGRESOS ******************************** */
    private function envia()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
        ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = $_SESSION[_S]['usr_id'];
        $con = new AccesoDatos();

        $gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '' ? $this->prm->gpo_id : '');
        $ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '' ? $this->prm->ur_id : '');
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '' ? $this->prm->clc_id : '');

        $con->conectar();
        if ($this->prm->accion == 'cancela') {
            $con->conectar();
            $con->setSql('
                SET @usr_id = ?,
                @usr_ip =INET_ATON(?),
                @bit_observacion="REGRESA A INGRESOS / TESORERIA S 2 22";
            ');
            $con->consultar([$usr_id, $ip]);
            $sqlproce = '
                CALL sp_RegresaClc' . APP_EJERCICIO . '(?, ?, ?,INET_ATON(?), ?);
            ';
            $con->setSql($sqlproce);
            $con->consultar([$gpo_id, $ur_id, $clc_id, $ip, $usr_id]);
        } else {
            $con->conectar();
            $con->setSql('
                SET @usr_id = ?,
                @usr_ip =INET_ATON(?),
                @bit_observacion="ENVIA A INGRESOS / TESORERIA S 2 22";
            ');
            $con->consultar([$usr_id, $ip]);
            $sqlproce = '
                CALL sp_EnviaClcTesoreria' . APP_EJERCICIO . '(?, ?, ?,INET_ATON(?), ?);
            ';
            $con->setSql($sqlproce);
            $con->consultar([$gpo_id, $ur_id, $clc_id, $ip, $usr_id]);
        }
        $con->setSql('SELECT @banderaPresupuesto AS bandera,@mensajePresupuesto AS mensaje');
        $con->consultar();
        $mensaje = $con->extraer();
        if ($mensaje['bandera'] != 1) {
            $m = 'error';
        }
        $m = 'success';
        $con->desconectar();
        unset($con);

        $this->prm->mpc_id = $this->prm->mpc_iddetalle;
        $this->llenaTablaCeres();
        $this->mixScript(' notify("' . $mensaje['mensaje'] . '","' . $m . '")');
    }
    /* ************************RECARGAR COMBO DEL RECURSO ***************************** */
    private function comboRecurso()
    {
        $combo = new stdClass();
        switch ($this->prm->mpc_id) {
            case 1:
            case 2:
            case 5:
                $combo->sql = 'SELECT ter_id,ter_nombre FROM sinpres.cat_TipoRecurso';
                $combo->id = 'ter_id';
                $combo->mensaje = 'Selecciona';
                $this->recargaCombo($combo);
                break;
            case 4:
                $combo->sql = "SELECT ter_id,ter_nombre FROM sinpres.cat_TipoRecurso WHERE ter_id = 'A'";
                $combo->id = 'ter_id';
                $combo->mensaje = 'Selecciona';
                $this->recargaCombo($combo);
                break;
            case 3:
                $combo->sql = "SELECT 0 as ter_id, 'Recurso Estatal y Federal' as ter_nombre";
                $combo->id = 'ter_id';
                $combo->mensaje = 'Selecciona';
                $this->recargaCombo($combo);
                break;
        }
    }

    private function reporte()
    {
        $fecha_cere = new stdClass();
        $fecha_cere->id = 'f_inicial';
        $fecha_cere->name = 'fecha_cere';
        $fecha_cere->class = 'fecha';
        $fecha_cere->placeholder = 'FECHA';
        $fecha_cere->enDiv = true;
        $fecha_cere->formato = 'fecha';
        $fecha_cere->labelClass = '_32 txtL';
        $fecha_cere->required = true;
        $inicial = $this->ponInput($fecha_cere);

        $btn_regresa = new stdClass();
        $btn_regresa->value = 'Regresar';
        $btn_regresa->color = 'gris';
        $btn_regresa->class = 'me-1';
        $btn_regresa->icon = 'solid fa-circle-left me-2';
        $btn_regresa->id = 'btn_regresa';

        $btn_generar = new stdClass();
        $btn_generar->value = 'Generar';
        $btn_generar->color = 'verde';
        $btn_generar->class = 'mt-3';
        $btn_generar->icon = 'solid fa-circle-check me-2';
        $btn_generar->id = 'btn_generar';

        $cbo_modo = new stdClass();
        $cbo_modo->id = 'mpc_iddetalle';
        $cbo_modo->name = 'control';
        $cbo_modo->required = true;
        $cbo_modo->enDiv = false;
        $cbo_modo->camposVal = 'mpc_id';
        $cbo_modo->agrupaPor = 'mpc_id';
        $cbo_modo->campoLabelAgrupacion = 'mpc_nombre';
        $cbo_modo->mensaje = 'Seleccione una opción';
        $cbo_modo->label = 'Modalidad de Pago';
        $cbo_modo->comoAtr = 'mpc_id';
        $cbo_modo->sql = 'SELECT mpc_id, mpc_nombre FROM cat_ModoPagoClc WHERE mpc_id != 4';

        $cbo_recurso = new stdClass();
        $cbo_recurso->id = 'ter_id';
        $cbo_recurso->name = 'control';
        $cbo_recurso->required = true;
        $cbo_recurso->enDiv = false;
        $cbo_recurso->camposVal = 'ter_id';
        $cbo_recurso->agrupaPor = 'ter_id';
        $cbo_recurso->campoLabelAgrupacion = 'mpc_nombre';
        $cbo_recurso->mensaje = 'Seleccione una opción';
        $cbo_recurso->label = 'Tipo de Recurso';
        $cbo_recurso->comoAtr = 'ter_id';

        $cbo_recurso = $this->templateCombo($cbo_recurso);
        if ($_SESSION[_S]['uni_id'] == 17) {
            $sql = '
                SELECT
                dep_id,dep_titular as usr_nombre
                FROM cat_Usuario
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                INNER JOIN cat_Departamento USING (dep_id)
                WHERE dep_id IN (44,45,46)
                GROUP BY dep_id,dep_titular
            ';
        } else {
            $sql = '
                SELECT
                    dep_id,dep_titular as usr_nombre
                FROM cat_Usuario
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                INNER JOIN cat_Departamento USING (dep_id)
                WHERE dep_id IN (48,49)
                GROUP BY dep_id,dep_titular
            ';
        }
        $cbo_firma = new stdClass();
        $cbo_firma->id = 'departamentoid';
        $cbo_firma->name = 'control';
        $cbo_firma->required = true;
        $cbo_firma->enDiv = false;
        $cbo_firma->camposVal = 'dep_id';
        $cbo_firma->campoLabelAgrupacion = 'usr_nombre';
        $cbo_firma->mensaje = 'Seleccione una opción';
        $cbo_firma->label = 'Departamento';
        $cbo_firma->sql = $sql;

        $cbo_firma = $this->ponCombo($cbo_firma);

        $ur = new stdClass();
        $ur->id = 'ur_id';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->enDiv = false;
        $ur->placeholder = 'UR';

        $claveFinanciamiento = new stdClass();
        $claveFinanciamiento->id = 'cvefin';

        $claveFinanciamiento->maxlength = 9;
        $claveFinanciamiento->placeholder = 'CLAVEFIN';
        $claveFinanciamiento->enDiv = false;

        $cboTipo = new stdClass();
        $cboTipo->id = 'tipo';
        $cboTipo->name = 'control';
        $cboTipo->opciones = ['1' => 'CERE', '2' => 'CERECO'];
        $cboTipo->required = true;
        $cboTipo->mensaje = 'TIPO DE CLC';

        $this->agregaTools($this->ponBoton($btn_regresa));
        $filtros = '
            <legend>Listado de CLC´s Enviadas</legend>
            <div class="row mb-2">
                <fieldset class="col-md-4 col-xs-12">
                    ' . $cbo_firma . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-12">
                    ' . $this->ponCombo($cbo_modo) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-12">
                    ' . $cbo_recurso . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-4 col-xs-12">
                    <label class="control-label">UR</label>
                    ' . $this->ponInput($ur) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-12">
                    <label class="control-label">Clave de Financiamiento</label>
                    ' . $this->ponInput($claveFinanciamiento) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-12">
                    <label class="control-label">Día del reporte </label>
                    ' . $inicial . '
                </fieldset>
            </div>
            <div class="row mb-3">
                <fieldset class="col-md-3 col-xs-12">
                    <label for="bxClc" class="control-label">Hora Inicio </label>
                    <input id="horaInicio" class="form-control limpiar" type="time" name="horaI" placeholder="00:00:00" max="23:59:59" min="09:00:00" step="1"  novalidate="false">
                </fieldset>
                <fieldset class="col-md-3 col-xs-12">
                    <label for="bxClc" class="control-label">Hora Final </label>
                    <input id="horaFin" class="form-control limpiar" type="time" name="horaF" placeholder="00:00:00"  max="23:59:59" min="09:00:00" step="1"  novalidate="false">
                </fieldset>
                <fieldset class="col-md-4 col-xs-12">
                    <label for="tipo" class="control-label">Tipo de CLC </label>
                    ' . $this->ponCombo($cboTipo) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-12 d-flex justify-content-end">
                    ' . $this->ponBoton($btn_generar) . '
                </fieldset>
            </div>
            <div id="res_div"></div>
            <script type="javascript" src="" id="scri_pdf"/>
        ';
        $this->mixHtml($filtros);

        $html = '
            <div class="alert-info" style="padding:10px;">
                <fieldset style="display:table-cell; width:20%;vertical-align: middle;">

                </fieldset>
                <fieldset style="display:table-cell; width:15%;vertical-align: middle;">

                </fieldset>
                <fieldset style="display:table-cell; width:15%;vertical-align: middle;">

                </fieldset>
                <fieldset style="display:table-cell; width:15%;vertical-align: middle;">

                </fieldset>
            </div>
            <hr/>
        ';

        $this->mixHtml($html);

        $this->mixScript('
            $("#btn_regresa").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"main"});
            });

            $("#mpc_iddetalle").change(function(){
                if ($(this).find("option:selected").attr("mpc_id") != undefined) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"comboRecurso",
                        mpc_id:$(this).find("option:selected").attr("mpc_id")
                    });
                }
            });

            $("#btn_generar").click(function(){
                if ($("#f_inicial").val() =="" ) {
                    notify("Debe seleccionar una Fecha", "warning");
                } else {
                    comunica("' . $this->pantalla . '",{
                        metodo:"detalle_reporte",
                        ini:$("#f_inicial").val(),
                        "ur":$("#ur_id").val(),
                        dep_id:$("#departamentoid").val(),
                        mpc_id:$("#mpc_iddetalle").val(),
                        ter_id:$("#ter_id").val()  ,
                        ecf_clave:$("#cvefin").val(),
                        tipo:$("#tipo").val(),
                        horaInicio:$("#horaInicio").val(),
                        horaFin: $("#horaFin").val()
                    });
                }
            });
        ');
    }
    /****************************  OBTENER LOTES POR RANGO DE FECHAS ***************************** */
    private function detalle_reporte()
    {
        $this->setContenedor('res_div');
        $in = explode('/', $this->prm->ini);
        $inicio = $in[2] . '-' . $in[1] . '-' . $in[0];
        $dep = $_SESSION[_S]['uni_id'] == 17 ? '44,45,46' : '48,49';

        $pdf = new stdClass();
        $pdf->value = ' PDF ';
        $pdf->color = 'verde';
        $pdf->icon = 'print';
        $pdf->id = 'pdf';
        $pdf->click = '
            load_viewer(2017,"REPORTE DE CLC´s ENVIADAS","frame");
            $("#formpdf").submit();
        ';

        $xls = new stdClass();
        $xls->value = ' Excel ';
        $xls->color = 'verde';
        $xls->icon = 'file-excel th-large';
        $xls->id = 'xls';
        $xls->click = '$("#formxls").submit();';

        $params = [];

        $sql = '
            SELECT gpo_id, ur_id, bclc.ecl_id, clc_id, CONCAT( gpo_id, ur_id ) AS UR,
                ( TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2) ) AS Importe, clc_concepto, clc_cereCo,
                date_format( bclc.usr_fecha, "%e/%m/%Y %H:%i:%s" ) AS FechaCLC,
                clc_cereCo, mpc_nombre, tcl_nombre, ecf_clave, clc_percepcion, clc_retencion,
                clc_beneficiario
            FROM scp_Clc C
                INNER JOIN scp_ClcAuxiliar b USING ( gpo_id, ur_id, clc_id )
                INNER JOIN
                    (
                    SELECT MAX(bit_clc), gpo_id, ur_id, clc_id, ecl_id, usr_fecha
                    FROM ' . BD_BITACORA . '.bit_Clc
                    WHERE ecl_id IN (10)
                    AND DATE( usr_fecha ) = DATE(:inicio)
                    AND usr_id = :usr_id
                    GROUP BY gpo_id, ur_id, clc_id, ecl_id, usr_fecha
                    )    bclc USING (gpo_id, ur_id, clc_id)
                INNER JOIN cat_ModoPagoClc USING ( mpc_id )
                INNER JOIN cat_TipoClc USING ( tcl_id )
                INNER JOIN enc_FiltroClc USING ( gpo_id, ur_id, tcl_id, stc_id, mno_id )
                INNER JOIN enc_ClaveFinanciamiento USING ( ecf_id )
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING ( cff_id, fte_id, sft_id, ori_id, sor_id )
            WHERE C.clc_id != 0
                AND SUBSTRING( C.tem_carga, 1, 5 ) != "116-A"
        ';
        $params[':usr_id'] = $_SESSION[_S]['usr_id'];
        $params[':inicio'] = $inicio;

        if (isset($this->prm->tipo) && $this->prm->tipo != '') {
            $sql .= ' AND clc_tipoCereCo = :tipo';
            $params[':tipo'] = $this->prm->tipo;
        }
        if (isset($this->prm->mpc_id) && $this->prm->mpc_id != '') {
            $sql .= ' AND mpc_id = :mpc_id';
            $params[':mpc_id'] = $this->prm->mpc_id;
        }
        if (isset($this->prm->ter_id) && $this->prm->ter_id != '') {
            if ($this->prm->ter_id == '0') {
                $sql .= ' AND ter_id in ("A","B")';
            } else {
                $sql .= ' AND ter_id = :ter_id';
                $params[':ter_id'] = $this->prm->ter_id;
            }
        }

        if (isset($this->prm->dep_id) && $this->prm->dep_id != '') {
            $sql .= ' AND dep_id = :dep_id';
            $params[':dep_id'] = $this->prm->dep_id;
        } else {
            if ($_SESSION[_S]['uni_id'] == 17) {

                $sql .= ' AND dep_id   IN (' . $dep . ') ';
            } else {
                $sql .= ' AND dep_id   IN (' . $dep . ') ';
            }
        }

        if (isset($this->prm->ur) && $this->prm->ur != '') {
            $gpo = substr($this->prm->ur, 0, 1);
            $ur = substr($this->prm->ur, 1, 2);
            $sql .= ' AND gpo_id=' . $gpo . ' AND ur_id =' . $ur . ' ';
        }

        if (isset($this->prm->horaInicio) && $this->prm->horaInicio != '' && isset($this->prm->horaFin) && $this->prm->horaFin != '') {
            $sql .= ' AND TIME(bclc.usr_fecha) BETWEEN TIME(:horaInicio) AND TIME(:horaFin)';
            $params[':horaInicio'] = $this->prm->horaInicio;
            $params[':horaFin'] = $this->prm->horaFin;
        } elseif (isset($this->prm->horaInicio) && $this->prm->horaInicio != '') {
            $sql .= ' AND TIME(bclc.usr_fecha) > TIME(:horaInicio)';
            $params[':horaInicio'] = $this->prm->horaInicio;
        } elseif (isset($this->prm->horaFin) && $this->prm->horaFin != '') {
            $sql .= ' AND TIME(bclc.usr_fecha) < TIME(:horaFin)';
            $params[':horaFin'] = $this->prm->horaFin;
        }

        if (isset($this->prm->ecf_clave) && $this->prm->ecf_clave != '') {
            $sql .= ' AND ecf_clave = :ecf_clave';
            $params[':ecf_clave'] = $this->prm->ecf_clave;
        }

        $sql .= '
            GROUP BY gpo_id,ur_id,clc_id
            ORDER BY gpo_id,ur_id,clc_id
        ';

        $this->setSql($sql);
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);

        $html = '
            <table class="table table-bordered">
                <tr>
                    <th colspan="11" style="background-color:#C5E1A5;">RELACI&Oacute;N DE CLC´S ENVIADAS </th>
                    <th style="background-color:#C5E1A5;">
                        <form id="formxls" method="POST" action="rep/excel/scp/afectaciones/ReporteCLCenviadas.php" target="_blank">
                            <input type="hidden" name = "inicio" value="' . $this->prm->ini . '" />
                            <input type="hidden" name = "mpc_id" value="' . $this->prm->mpc_id . '" />
                            <input type="hidden" name = "ter_id" value="' . $this->prm->ter_id . '" />
                            <input type="hidden" name = "ecf_clave" value="' . $this->prm->ecf_clave . '" />
                            <input type="hidden" name = "dep_id" value="' . $this->prm->dep_id . '" />
                            <input type="hidden" name = "ur" value="' . $this->prm->ur . '" />
                            <input type="hidden" name = "tipo" value="' . $this->prm->tipo . '" />
                            <input type="hidden" name = "horaInicio" value="' . $this->prm->horaInicio . '" />
                            <input type="hidden" name = "horaFin" value="' . $this->prm->horaFin . '" />
                                ' . ($con->getNumeroRegistros() > 0 ? $this->ponBoton($xls) : '') . '
                    </th>
                </tr>
                <tr>
                    <th style="border:1px solid #333;" width="8%" class="warning">No. CLC</th>
                    <th style="border:1px solid #333;" width="8%" class="warning">CERE-CO</th>
                    <th style="border:1px solid #333;" width="8%" class="warning">UR</th>
                    <th style="border:1px solid #333;" width="10%" class="warning">MODALIDAD DE PAGO</th>
                    <th style="border:1px solid #333;" width="10%" class="warning">TIPO DE CLC</th>
                    <th style="border:1px solid #333;" width="8%" class="warning">CLAVE DE FINANCIAMIENTO</th>
                    <th style="border:1px solid #333;" width="20%" class="warning">CONCEPTO</th>
                    <th style="border:1px solid #333;" width="15%" class="warning">BENEFICIARIO</th>
                    <th style="border:1px solid #333;" width="10%" class="warning">PERCEPCIONES</th>
                    <th style="border:1px solid #333;" width="10%" class="warning">RETENCIONES</th>
                    <th style="border:1px solid #333;" width="12%" class="warning">IMPORTE</th>
                    <th style="border:1px solid #333;" width="10%" class="warning">FECHA Y HORA DE ENVI&Oacute;</th>
                </tr>
        ';
        while ($data = $con->extraer()) {
            $html .= '
                <tr>
                    <td>' . $data['clc_id'] . '</td>
                    <td>' . $data['clc_cereCo'] . '</td>
                    <td>' . $data['UR'] . '</td>
                    <td>' . $data['mpc_nombre'] . '</td>
                    <td>' . $data['tcl_nombre'] . '</td>
                    <td>' . $data['ecf_clave'] . '</td>
                    <td>' . $data['clc_concepto'] . '</td>
                    <td>' . $data['clc_beneficiario'] . '</td>
                    <td>' . $data['clc_percepcion'] . '</td>
                    <td>' . $data['clc_retencion'] . '</td>
                    <td align="right"><span style="float:left">
                        $</span>' . number_format($data['Importe'], 2) . '
                    </td>
                    <td align="center">' . $data['FechaCLC'] . '</td>
                </tr>
            ';
        }
        $html .= '</table>';
        $this->mixHtml($html);
    }
}
if (isset($_SESSION[_S]['prm']['S']['2']['22'])) { // permisos del usuario que puede ver este controlador
    if (!isset($this)) {
        new ControladorLotes();
    }
} else {
    WebResponse::sinPermisos();
}
