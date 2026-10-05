<?php

require_once dirname(__FILE__) . '/vistaScp.php';

class vistaCompromiso extends vistasScp
{
    public function vwToolsTablaCompromiso($prm)
    {
        $html = '
            <div class="mb-2"></div>
            <div class="row col-md-12 col-xs-12 mb-2">
                <fieldset class="col-md-2 col-xs-2" >
                ' . $prm['estado'] . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" >
                ' . $prm['mes'] . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1" >
                ' . $prm['cmp'] . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1" >
                ' . $prm['importe'] . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1" >
                ' . $prm['filtroRfc'] . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1" >
                ' . $prm['filtroOrden'] . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" >
                    ' . $prm['concepto'] . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" >
                <br>
                    ' . $prm['btnConsulta'] . '
                    ' . $prm['clean'] . '
                </fieldset>
            </div>';
        return $html;
    }

    public function getScriptcomboDoc($tipo)
    {
        $script = '';
        switch ($tipo) {
            case 'F':
                $script = '
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre") == "CONTRATO DE OBRA") {
                        $("#otro").hide();
                        $("#contratos").show();
                        $("#encProg").attr("disabled","disabled");
                        $("#encCveFin").attr("disabled","disabled");
                        $("#ipu_id").prop("required",true);
                        comunica("scp/afectaciones/ControladorCompromiso.php",{
                            metodo: "Contratos"
                        });
                    } else {
                        $("#contratos").hide();
                        $("#encProg").removeAttr("disabled");
                        $("#encCveFin").removeAttr("disabled");
                        $("#ipu_id").prop("required",false);
                    }
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre")=="OTRO") {
                        document.getElementById("otro").style.display="block";
                        $("#otroDoc").attr("required","1");
                        $("#encProg").removeAttr("disabled");
                        $("#encCveFin").removeAttr("disabled");
                        $("#ipu_id").prop("required",false);
                    } else {
                        document.getElementById("otro").style.display="none";
                        $("#otroDoc").removeAttr("required");
                        $("#encProg").attr("disabled","disabled");
                        $("#encCveFin").attr("disabled","disabled");
                        $("#ipu_id").prop("required",true);
                    }';
                break;
            case 'G':
                $script = '
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre") == "CONTRATO") {
                        $("#otro").hide();
                        $("#contratos").show();
                        $("#encProg").attr("disabled","disabled");
                        $("#encCveFin").attr("disabled","disabled");
                        $("#ipu_id").prop("required",true);
                        comunica("scp/afectaciones/ControladorCompromiso.php",{
                            metodo: "Contratos"
                        });
                    } else {
                        $("#contratos").hide();
                        $("#encProg").removeAttr("disabled");
                        $("#encCveFin").removeAttr("disabled");
                        $("#ipu_id").prop("required",false);

                    }
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre") == "OTRO") {
                        document.getElementById("otro").style.display="block";
                        $("#otroDoc").attr("required","1");
                        $("#encProg").removeAttr("disabled");
                        $("#encCveFin").removeAttr("disabled");
                        $("#ipu_id").prop("required",false);
                    } else if ($("#tpd_id").find("option:selected").attr("tpd_nombre")!="OTRO" && $("#tpd_id").find("option:selected").attr("tpd_nombre")!="CONTRATO") {
                        document.getElementById("otro").style.display = "none";
                        $("#otroDoc").removeAttr("required");
                        $("#encProg").removeAttr("disabled");
                        $("#encCveFin").removeAttr("disabled");
                        $("#ipu_id").prop("required",false);
                    }';
                break;
            default:
                $script = '
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre")=="OTRO") {
                            document.getElementById("otro").style.display="block";
                            $("#otroDoc").attr("required","1");
                    } else {
                        document.getElementById("otro").style.display="none";
                        $("#otroDoc").removeAttr("required");
                    }';
                break;
        }
        return $script;
    }

    public function getComboContrato($cmp)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $contrato = new stdClass();
        $contrato->id = 'ipu_id';
        $contrato->name = 'control';
        $contrato->required = true;
        $contrato->detonar = false;
        $contrato->enDiv = true;
        $contrato->class = 'habilitar';
        $contrato->mensaje = 'Selecciona  un tipo de nomina';
        $contrato->label = 'Número de Contrato';
        $contrato->comoAtr = 'ipu_numeroContrato';
        $contrato->change = '
            if( $(this).val() != "" ){
                $("#encProg").removeAttr("disabled");
                $("#encCveFin").removeAttr("disabled");
                comunica("scp/afectaciones/ControladorCompromiso.php",{
                    metodo: "DatosObra",
                    "ipu_id":$("#ipu_id").val(),
                    "ipu_numeroContrato":$(this).find("option:selected").attr("ipu_numeroContrato")
                });
            }';
        $contrato->search = true;
        if ($cmp->getTpd_id() != '' && $cmp->getNumeroContrato() != '') {
            $contrato->sql = 'SELECT ipu_id,ipu_numeroContrato FROM sinpres.cat_InversionPublica
                WHERE gpo=' . $gpo_id . ' AND ur=' . $ur_id . ' AND eip_id = 2
            ';
            $contrato->selecciona = $cmp->getIpu_id();
        }
        return $contrato;
    }

    public function getComboVarios($n)
    {
        switch ($n) {
            case 'otroDoc':
                $otroDoc = new StdClass();
                $otroDoc->id = 'otroDoc';
                $otroDoc->label = 'Capturar otro Documento';
                $otroDoc->class = 'habilitar';
                $otroDoc->enDiv = true;
                return $otroDoc;
                break;
            case 'concepto':
                $concepto = new stdClass();
                $concepto->id = 'addConcepto';
                $concepto->label = 'Concepto';
                $concepto->placeholder = 'Concepto ';
                $concepto->enDiv = true;
                $concepto->required = true;
                $concepto->width = '400px';
                $concepto->class = 'anulaenter habilitar mb-2';
                $concepto->height = '150px';
                $concepto->maxlength = '600';
                return $concepto;
                break;
            case 'importe':
                $importe = new StdClass();
                $importe->id = 'addImporte';
                $importe->class = 'habilitar mb-2';
                $importe->label = 'Importe';
                $importe->required = true;
                $importe->enDiv = true;
                $importe->formato = 'moneda';
                return $importe;
                break;
            case 'rfc':
                $rfc = new stdClass();
                $rfc->id = 'rfc_id';
                $rfc->camposText = 'rfc_nombre';
                $rfc->required = true;
                $rfc->tabla = 'cat_Proveedor';
                $rfc->required = true;
                $rfc->tablas = ' INNER JOIN sinpres.cat_Proveedores using (rfc_id)';
                $rfc->name = 'control';
                $rfc->autocomplete = true;
                $rfc->class = 'habilitar';
                $rfc->autocomplete = true;
                $rfc->placeholder = 'R.F.C. del Proveedor, Beneficiario o Institución';
                $rfc->campoVal = 'rfc_id';
                $rfc->label = 'R.F.C. del Proveedor, Beneficiario o Instittución';
                $rfc->desdecaracter = 4;
                $rfc->maxlength = 13;
                $rfc->enDiv = true;
                return $rfc;
                break;
            case 'rfc_nombre':
                $rfc_nombre = new stdClass();
                $rfc_nombre->id = 'rfc_nombre';
                $rfc_nombre->name = 'control';
                $rfc_nombre->required = true;
                $rfc_nombre->placeholder = 'Nombre del proveedor, beneficiario o instittución';
                $rfc_nombre->label = 'Nombre del proveedor, Beneficiario o Instittución';
                $rfc_nombre->enDiv = true;
                $rfc_nombre->lectura = true;
                $rfc_nombre->readonly = 'readonly';
                return $rfc_nombre;
                break;
            case 'finFecha':
                $finFecha = new stdClass();
                $finFecha->id = 'finFecha';
                $finFecha->name = 'finFecha';
                $finFecha->label = 'Periodo Final';
                $finFecha->class = 'datepicker habilitar';
                $finFecha->enDiv = true;
                $finFecha->required = true;
                $finFecha->formato = 'fecha';
                $finFecha->placeholder = date('Y-d-m');
                return $finFecha;
                break;
            case 'FechaOrden':
                $FechaOrden = new stdClass();
                $FechaOrden->id = 'FechaOrden';
                $FechaOrden->name = 'FechaOrden';
                $FechaOrden->label = 'Fecha del documento soporte';
                $FechaOrden->class = 'datepicker habilitar mb-2';
                $FechaOrden->enDiv = true;
                $FechaOrden->formato = 'fecha';
                $FechaOrden->required = true;
                $FechaOrden->placeholder = date('Y-d-m');
                return $FechaOrden;
                break;
            case 'ordenPedido':
                $ordenPedido = new StdClass();
                $ordenPedido->id = 'ordenPedido';
                $ordenPedido->label = 'Número de documento soporte';
                $ordenPedido->enDiv = true;
                $ordenPedido->required = true;
                $ordenPedido->class = 'habilitar mb-2';
                return $ordenPedido;
                break;
            case 'inicioFecha':
                $inicioFecha = new stdClass();
                $inicioFecha->id = 'inicioFecha';
                $inicioFecha->name = 'inicioFecha';
                $inicioFecha->label = 'Periodo Inicial';
                $inicioFecha->class = 'datepicker habilitar';
                $inicioFecha->enDiv = true;
                $inicioFecha->required = true;
                $inicioFecha->formato = 'fecha';
                $inicioFecha->placeholder = date('Y-d-m');
                return $inicioFecha;
                break;
            case 'submod':
                $submod = new stdClass();
                $submod->id = 'smn_id';
                $submod->name = 'control';
                $submod->enDiv = true;
                $submod->required = true;
                $submod->class = 'habilitar';
                $submod->camposVal = 'smn_id';
                $submod->agrupaPor = 'smn_id';
                $submod->campoLabelAgrupacion = 'smn_nombre';
                $submod->mensaje = 'Selecciona';
                $submod->label = 'Tipo de Haberes';
                $submod->comoAtr = 'smn_id';
                $submod->sql = 'SELECT smn_id,smn_nombre FROM
                        cat_SubmodalidadNomina WHERE smn_id !=0 ';
                return $submod;
                break;
            case 'tipoNom':
                $tipoNom = new stdClass();
                $tipoNom->id = 'tno_id';
                $tipoNom->name = 'control';
                $tipoNom->required = true;
                $tipoNom->enDiv = true;
                $tipoNom->class = 'habilitar';
                $tipoNom->sql = 'SELECT tno_id,tno_nombre FROM cat_TipoNomina ';
                $tipoNom->mensaje = 'Selecciona  un tipo de nomina';
                $tipoNom->label = 'Tipo de Nomina';
                $tipoNom->search = true;
                return $tipoNom;
                break;
        }
    }

    public function getComboDoc($tipo, $cmp)
    {
        $comboDoc = new stdClass();
        $comboDoc->id = 'tpd_id';
        $comboDoc->name = 'tpd';
        $comboDoc->mensaje = 'Selecciona';
        $comboDoc->required = true;
        $comboDoc->label = 'Tipo de Documento Soporte';
        $comboDoc->class = 'habilitar mb-2';
        $comboDoc->comoAtr = 'tpd_nombre';
        $comboDoc->enDiv = true;
        $comboDoc->detonar = false;
        $comboDoc->search = true;
        $comboDoc->selecciona = $cmp->getTpd_id();

        $FiltroDoc = '';
        switch ($tipo) {
            case 'F':
            case 'G':
                $FiltroDoc = 'WHERE tcl_id="' . $tipo . '"  AND stc_id = 0 AND tpd_momento="C" ';
                break;
            default:
                $FiltroDoc = ' WHERE tcl_id="' . $cmp->getTcl_id() . '" AND stc_id=' . $cmp->getStc_id() . ' AND tpd_momento="C"';
                break;
        }
        $ur_clave = '';
        if ($cmp->getTpd_id() != '' || $tipo == 'F' || $tipo == 'G' || $tipo == 'I') {
            $comboDoc->sql = 'SELECT tpd_id,tpd_nombre FROM cat_TipoDocumento ' . $FiltroDoc
                . ' AND uni_id = ' . (in_array($ur_clave, $this->getPrincesas()) ? '18' : '17');
        }
        return $comboDoc;
    }

    public function getControlesBuscar($n)
    {
        /*  SON LOS CONTROLES QUE SE PONEN EN LA TABLA PRINCIPAL
        DE COMPROMISOS PARA SU BUSQUEDA  O FILTRADO */
        switch ($n) {
            case 'cmp':
                $cmp = new stdClass();
                $cmp->id = 'cmp';
                $cmp->name = 'cmp';
                $cmp->label = 'Compromiso';
                $cmp->class = 'limpiar';
                $cmp->placeholder = 'N°';
                $cmp->enDiv = true;
                return $cmp;
                break;
            case 'clean':
                $clean = new stdClass();
                $clean->class = 'btn btn-outline-warning me-1';
                $clean->id = 'clean';
                $clean->value = 'Limpiar';
                $clean->Title = 'Limpiar';
                $clean->text = 'Limpiar';
                $clean->icon = 'trash me-1';
                $clean->click = '
                    $(".limpiar").val("");
                    $("#estado").change();
                    $("#estado").val("");
                    comunica("scp/afectaciones/ControladorCompromiso.php",{
                        metodo:"ListasGeneradas",
                    });
                ';
                return $clean;
                break;
            case 'btnConsulta':
                $btnConsulta = new stdClass();
                $btnConsulta->id = 'btnConsulta';
                $btnConsulta->class = 'btn btn-outline-secondary me-1';
                $btnConsulta->value = 'Buscar';
                $btnConsulta->Title = 'Buscar';
                $btnConsulta->text = 'Buscar';
                $btnConsulta->icon = 'search me-1';
                return $btnConsulta;
                break;
            case 'importe':
                $importe = new stdClass();
                $importe->id = 'importe';
                $importe->name = 'importe';
                $importe->label = 'Importe';
                $importe->class = 'limpiar';
                $importe->formato = 'numero';
                $importe->placeholder = 'Importe';
                $importe->enDiv = true;
                return $importe;
                break;
                // Marcos: 08/marzo/2018 adicionamos filtros
            case 'filtroRfc':
                $filtroRfc = new stdClass();
                $filtroRfc->id = 'filtroRfc';
                $filtroRfc->name = 'filtroRfc';
                $filtroRfc->label = 'RFC';
                $filtroRfc->class = 'limpiar';
                $filtroRfc->placeholder = 'RFC';
                $filtroRfc->enDiv = true;
                return $filtroRfc;
                break;
            case 'filtroOrden':
                $filtroOrden = new stdClass();
                $filtroOrden->id = 'filtroOrden';
                $filtroOrden->name = 'filtroOrden';
                $filtroOrden->label = '#orden';
                $filtroOrden->class = 'limpiar';
                $filtroOrden->placeholder = '#Orden';
                $filtroOrden->enDiv = true;
                return $filtroOrden;
                break;
            case 'concepto':
                $concepto = new stdClass();
                $concepto->id = 'concepto';
                $concepto->name = 'concepto';
                $concepto->label = 'Concepto del Compromiso';
                $concepto->class = 'limpiar';
                $concepto->placeholder = 'Concepto';
                $concepto->enDiv = true;
                return $concepto;
                break;
            case 'mes':
                $mes = new stdClass();
                $mes->id = 'mes';
                $mes->class = 'limpiar';
                $mes->form = 'enviaPost';
                $mes->sql = 'SELECT 0 AS msc_id , "TODOS" AS msc_nombre
                    UNION ALL SELECT  msc_id,msc_nombre
                    FROM  cat_MesContable
                    WHERE msc_id between 01 AND  12;
                ';
                $mes->label = 'Mes del Compromiso';
                $mes->selecciona = date('m');
                $mes->enRow = 1;
                $mes->search = true;
                return $mes;
                break;
            case 'estado':
                $estado = new stdClass();
                $estado->id = 'estado';
                $estado->class = 'limpiar';
                $estado->form = 'enviaPost';
                $estado->sql = 'SELECT ecm_id,ecm_nombre
                    FROM cat_EstadoCompromiso;
                ';
                $estado->label = 'Estado del Compromiso';
                $estado->enRow = 1;
                $estado->search = true;
                return $estado;
                break;
        }
    }

    public function getBotonesVarios($n, $prm = [])
    {
        switch ($n) {
            case 'btnEliminar':
                $btnEliminar = new stdClass();
                $btnEliminar->id = 'delete';
                $btnEliminar->class = 'delete me-1';
                $btnEliminar->color = 'rojo';
                $btnEliminar->text = 'Eliminar';
                $btnEliminar->value = 'Eliminar';
                $btnEliminar->icon = 'solid fa-trash me-2';
                $btnEliminar->click = '
                    alertify.confirm("ELIMINAR COMPROMISO", "¿ Confirma que desea eliminar el Compromiso ' . $prm['cmp'] . ' ?", 
                        function(){ 
                            comunica("scp/afectaciones/ControladorCompromiso.php",{
                                metodo: "abcCompromiso",
                                abc:"b",
                                "cmp_id":"' . $prm['cmp'] . '",
                                "tipo":"' . $prm['tipo'] . '"
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
                ';
                return $btnEliminar;
                break;
            case 'btnCancelar':
                $btnCancelar = new stdClass();
                $btnCancelar->id = 'btnCancelar';
                $btnCancelar->class = 'btn btn-outline-danger me-1 ';
                $btnCancelar->text = 'Cancelar';
                $btnCancelar->value = 'Cancelar';
                $btnCancelar->icon = 'times me-2';
                $btnCancelar->click = '
                    alertify.confirm("CANCELAR COMPROMISO", "¿ Confirma que desea Cancelar  el Compromiso ' . ($prm['cmp']) . '?", 
                        function(){ 
                            comunica("scp/afectaciones/ControladorCompromiso.php",{
                                metodo: "CancelarCompromiso",
                                "cmp_id":"' . $prm['cmp'] . '",
                                "tipo":"' . $prm['tipo'] . '"
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
                ';
                return $btnCancelar;
                break;
            case 'btnAjuste':
                $btnAjuste = new stdClass();
                $btnAjuste->id = 'btnAjuste';
                $btnAjuste->class = 'btn btn-outline-primary me-1';
                $btnAjuste->text = 'Ajustar Compromiso';
                $btnAjuste->color = 'azul';
                $btnAjuste->value = 'Ajustar Compromiso';
                $btnAjuste->icon = 'solid fa-floppy-disk-circle-arrow-right me-2';
                $btnAjuste->click = '
                    alertify.confirm("AJUSTAR  COMPROMISO", "¿ Confirma que desea Ajustar  el Compromiso ' . $prm['cmp'] . '?", 
                        function(){ 
                            comunica("scp/afectaciones/ControladorCompromiso.php",{
                                metodo: "AjusteCompromiso",
                                "cmp_id":"' . $prm['cmp'] . '"
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
                    }';
                return $btnAjuste;
                break;
            case 'btnRegresar':
                $btnRegresar = new stdClass();
                $btnRegresar->id = 'regresar';
                $btnRegresar->name = 'regresar';
                $btnRegresar->class = 'btn btn-outline-secondary me-1';
                $btnRegresar->text = 'Regresar';
                $btnRegresar->value = 'Regresar';
                $btnRegresar->icon = 'solid fa-circle-left me-2';
                return $btnRegresar;
                break;
        }
    }

    public function getComboSubtipoClc($prm)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        //TIPO = TIPO DE CLC O TCL_ID
        //CMP_ID = NUMERO DE COMPROMISO SI ES QUE ES EDICION
        //IMPORTE AUN NO SE PARA QUE LO USA
        $filtro = '';
        $grupo = '';
        if (in_array($gpo_id . $ur_id, $this->getPrincesas())) {
            switch ($prm['tipo']) {
                case 'A': //S.P.
                    $filtro = 'AND stc_id in(3,5,6,8,10,11,15)';
                    break;
                case 'C': //Pagos a terceros
                    $filtro = 'AND stc_id IN (1,2,3,4)';
                    break;
                case 'M':
                    $filtro = 'AND stc_id BETWEEN 1 AND 11';
                    break;
                default:
                    $filtro = 'AND stc_id IN (1,2)';
                    break;
            }
            $grupo = 2;
        } else {
            $grupo = 1;
            switch ($prm['tipo']) {
                case 'A':
                    $filtro = ' AND stc_id != 17';
                    break;
                case 'I':
                    if ($gpo_id . $ur_id == '902') {
                        $filtro = ' AND stc_id = "04"';
                    } else {
                        $filtro = ' AND stc_id IN (1,4)';
                    }

                    break;
                case 'J':
                case 'K':
                    $filtro = ' AND stc_id = 1';
                    break;
            }
        }

        $subtipo = new stdClass();
        $subtipo->id = 'stc_id';
        $subtipo->name = 'control';
        $subtipo->required = true;
        $subtipo->enDiv = true;
        $subtipo->class = 'mb-2';
        $subtipo->campoLabelAgrupacion = 'stc_nombre';
        $subtipo->mensaje = 'Selecciona';
        $subtipo->label = 'Subtipo de CLC';
        $subtipo->search = true;
        $subtipo->comoAtr = 'stc_id';

        if ($grupo == 1) {
            switch ($prm['tipo']) {
                case 'A':
                    $subtipo->detonar = false;
                    $subtipo->change = '
                        comunica("scp/afectaciones/ControladorCompromiso.php",{metodo:"comboNomina",
                            stc_id:$("#stc_id").val(),
                            id:2,
                            tipo:"' . $prm['tipo'] . '",
                            combo:"mno_id"});';
                    break;
                case 'I':
                case 'L':
                    $subtipo->change = '
                        comunica("scp/afectaciones/ControladorCompromiso.php",{
                            metodo:"ObjetoGasto",
                            tipo:"' . $prm['tipo'] . '",
                            filtro:"ModNomPartida",
                            ' . ($prm['cmp_id'] != '' ? 'cmp_id:' . $prm['cmp_id'] . ',' : '') . '
                            ' . ($prm['cmp_importe'] != '' ? 'cmp_importe:' . $prm['cmp_importe'] . ',' : '') . '
                            stc_id:$("#stc_id").val()
                        });';
                    $subtipo->detonar = false;
                    break;
            }
        } else {
            $subtipo->change = '
                comunica("scp/afectaciones/ControladorCompromiso.php",{
                    metodo:"ObjetoGasto",
                    tipo:"' . $prm['tipo'] . '",
                    filtro:"ModNomPartida",
                    mno_id:0,
                    ' . ($prm['cmp_id'] != '' ? 'cmp_id:' . $prm['cmp_id'] . ',' : '') . '
                    ' . ($prm['cmp_importe'] != '' ? 'cmp_importe:' . $prm['cmp_importe'] . ',' : '') . '
                    stc_id:$("#stc_id").val(),
                });';
            if ($prm['cmp_id'] > 0) {
                $subtipo->detonar = true;
            } else {
                $subtipo->detonar = false;
            }
        }
        $subtipo->sql = 'SELECT stc_id,stc_nombre FROM cat_SubtipoClc WHERE  tcl_id = "' . $prm['tipo'] . '" ' . $filtro;
        return $subtipo;
    }

    public function getBotonGuardar($prm)
    {
        $cmp_id = $prm['cmp_id'] > 0 ? ' obj.cmp_id = ' . $prm['cmp_id'] : '';

        $btnGuardar = new stdClass();
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->class = 'me-1';
        $btnGuardar->color = 'verde';
        $btnGuardar->id = 'addGuardar';
        if ($prm['abc'] == 'a') {
            $btnGuardar->value = 'Guardar';
        } else {
            $btnGuardar->value = 'Actualizar';
        }

        $btnGuardar->click = '
            var tipo ="' . $prm['tipo'] . '";

            var capturado  = parseFloat(unformatNumber($("#addImporte").val()).toFixed(2));
            if(capturado==0){
                notify("No puedes generar un compromiso con $ 0.00 de presupuesto","error");
                return false;
            }

            var obj = {"metodo":"abcCompromiso"};
            obj.abc = "' . $prm['abc'] . '";
            ' . $cmp_id . PHP_EOL;

        switch ($prm['tipo']) {
            case 'A':
                $smn_id = '';
                $mno_id = '';

                if ($prm['smn_id'] > 0) {
                    $smn_id = 'obj.smn_id = ' . $prm['smn_id'] . ';';
                }

                if ($prm['grupo'] == 1) {
                    $mno_id = '
                        if( $("#mno_id").length){
                            obj.mno_id = $("#mno_id").val();
                        } else {
                            obj.mno_id = 0;
                        }' . PHP_EOL;
                } else {
                    $mno_id = 'obj.mno_id = 0;' . PHP_EOL;
                }

                $btnGuardar->click .= '
                    if (!validarFechas($("#inicioFecha").val(), $("#finFecha").val())) {
                        notify("La fecha de termino no puede ser menor a la fecha de inicio","error");
                        return false;
                    }

                    obj.pg_id = $("#pg_id").val();
                    obj.pe_id = $("#pe_id").val();
                    obj.tcp_id ="D";
                    obj.tcl_id ="A";
                    obj.stc_id = $("#stc_id").val();
                    obj.tno_id = $("#tno_id").val();

                    ' . $smn_id . '
                    ' . $mno_id . '
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre")=="OTRO" ) {
                        obj.otroDocumento = (($("#otroDoc").length) ? $("#otroDoc").val():"");
                    }
                    obj.tpd_id = $("#tpd_id").val();
                    obj.rfc_id = $("#rfc_id").val();
                    obj.fechaInicio = $("#inicioFecha").val();
                    obj.fechaFin = $("#finFecha").val();
                    obj.concepto = $("#addConcepto").val();
                    obj.importe =parseFloat(unformatNumber($("#addImporte").val()).toFixed(2));
                    if(!comunica("scp/afectaciones/ControladorCompromiso.php", obj,true ))
                    $(this).removeAttr("disabled");
                    ' . PHP_EOL;
                break;
            case 'B':
                $btnGuardar->click .= '
                    obj.pg_id = $("#pg_id").val();
                    obj.pe_id = $("#pe_id").val();
                    obj.tcp_id = (($("#TipoCompra").length) ? $("#TipoCompra").val(): "D" );
                    obj.tcl_id ="' . $prm['tipo'] . '";
                    obj.rfc_id = $("#rfc_id").val();
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre")=="OTRO") {
                        obj.otroDocumento = (($("#otroDoc").length) ? $("#otroDoc").val():"");
                    }
                    obj.stc_id ="0";
                    obj.numOrden = $("#ordenPedido").val();
                    obj.fechaOrden = $("#FechaOrden").val();
                    obj.concepto = $("#addConcepto").val();
                    obj.importe =parseFloat(unformatNumber($("#addImporte").val()).toFixed(2));
                    obj.tpd_id = $("#tpd_id").val();
                    if(!comunica("scp/afectaciones/ControladorCompromiso.php", obj,true ))
                        $(this).removeAttr("disabled");' . PHP_EOL;
                break;
            case 'G':
            case 'F':
                $btnGuardar->click .= '
                    obj.pg_id = $("#pg_id").val();
                    obj.pe_id = $("#pe_id").val();
                    obj.tcp_id ="D";
                    obj.tcl_id ="' . $prm['tipo'] . '";
                    obj.rfc_id = $("#rfc_id").val();
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre")=="OTRO") {
                        obj.otroDocumento = (($("#otroDoc").length) ? $("#otroDoc").val():"");
                        if (tipo=="F") {
                            obj.stc_id=02;
                        } else {
                            obj.stc_id=00;
                        }
                    }

                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre") == "CONTRATO DE OBRA" || $("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO") {
                        obj.stc_id= (($("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO DE OBRA") ? 0:1 );
                        obj.ipu_id = $("#ipu_id").val();
                        obj.numeroContrato = $("#ipu_id").find("option:selected").attr("ipu_numeroContrato");
                    }
                    if (tipo == "G") {
                        if ($("#tpd_id").find("option:selected").attr("tpd_nombre") !="CONTRATO") {
                            obj.stc_id = 00 ;
                        }
                    }
                    obj.numOrden = $("#ordenPedido").val();
                    obj.fechaOrden = $("#FechaOrden").val();
                    obj.concepto = $("#addConcepto").val();
                    obj.importe = parseFloat(unformatNumber($("#addImporte").val()).toFixed(2));
                    obj.tpd_id = $("#tpd_id").val();
                    if (!comunica("scp/afectaciones/ControladorCompromiso.php", obj,true ))
                        $(this).removeAttr("disabled");' . PHP_EOL;
                break;
            case 'I':
            case 'J':
            case 'K':
            case 'L':
                // Marcos: 22/ene/2018
                // Estaba con = y se puso a .= igual que en los otros CASEs
                $btnGuardar->click .= '
                    obj.pg_id = $("#pg_id").val();
                    obj.pe_id = $("#pe_id").val();
                    obj.stc_id = $("#stc_id").val();
                    obj.tcp_id ="D";
                    obj.tcl_id ="' . $prm['tipo'] . '";
                    obj.rfc_id = $("#rfc_id").val();
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre")=="OTRO") {
                        obj.otroDocumento = (($("#otroDoc").length) ? $("#otroDoc").val():"");
                    }
                    obj.concepto = $("#addConcepto").val();
                    obj.importe =parseFloat(unformatNumber($("#addImporte").val()).toFixed(2));
                    obj.tpd_id = $("#tpd_id").val();
                    if(!comunica("scp/afectaciones/ControladorCompromiso.php", obj,true ))
                    $(this).removeAttr("disabled");' . PHP_EOL;
                break;
            default:
                print_r('mega error');
                break;
        }
        return $btnGuardar;
    }

    public function getComboModalidad($prm)
    {
        $modalidad = new stdClass();
        $modalidad->id = 'mno_id';
        $modalidad->name = 'control';
        $modalidad->required = true;
        $modalidad->enDiv = true;
        $modalidad->detonar = false;
        $modalidad->class = 'habilitar';
        $modalidad->mensaje = 'Selecciona una Modalidad';
        $modalidad->label = 'Modalidad';
        $modalidad->comoAtr = 'mno_id';
        $modalidad->search = true;
        $modalidad->change = '
            if ($(this).find("option:selected").val() ==6) {
                $("#submod").show();
                $("#smn_id").attr("required","1");
            } else {
                $("#submod").hide();
                $("#smn_id").removeAttr("required");
                $("#smn_id").val(0);
            }
            if ($(this).val() != "") {
                comunica("scp/afectaciones/ControladorCompromiso.php",{
                    metodo:"ObjetoGasto",
                    tipo:"' . $prm['tipo'] . '",
                    filtro:"ModNomPartida",
                    stc_id:$("#stc_id").val(),
                    ' . ($prm['cmp_id'] != '' ? 'cmp_id:' . $prm['cmp_id'] . ',' : '') . '
                    ' . ($prm['cmp_importe'] != '' ? 'cmp_importe:' . $prm['cmp_importe'] . ',' : '') . '
                    mno_id:$("#mno_id").val()
                });
            }
        ';
        return $modalidad;
    }

    public function getClasificacionAdministrativa($tipo, $abc, $ex)
    {

        $cveAdmitiva = new stdClass();
        $cveAdmitiva->filtro = 'poa';
        $cveAdmitiva->required = true;

        if ($tipo == 'F' || $tipo == 'G') {
            $cveAdmitiva->toa = 2;
            $cveAdmitiva->change = '
                if($("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO DE OBRA" || $("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO"){
                    comunica("scp/afectaciones/ControladorCompromiso.php",{
                        metodo: "DatosObra",
                        "ipu_id":$("#ipu_id").val(),
                        "ipu_numeroContrato":$("#ipu_id").find("option:selected").attr("ipu_numeroContrato")
                    });
                }
            ';
        }
        if ($abc == 'c') {
            $cveAdmitiva->prg_id = $ex['prg_id'];
            $cveAdmitiva->spr_id = $ex['spr_id'];
            $cveAdmitiva->py_id = $ex['py_id'];
            $cveAdmitiva->oa_id = $ex['oa_id'];
            $cveAdmitiva->lectura = true;
        }
        return $cveAdmitiva;
    }

    public function vwGraficaBarra($presupuesto, $capturado)
    {
        $scrip = "
            $('#grafica').highcharts({
                chart: {
                    type: 'column'
                },
                colors:[
                    {linearGradient: [0, 100, 0, 500],stops: [[0, 'rgb( 0 ,  76,  153) '],[1, 'rgb( 0, 25, 51)']]},
                    {linearGradient: [0, 100, 0, 500],stops: [[0, 'rgb( 32,  32,  32) '],[1, 'rgb( 0, 0, 0)']]},
                    {linearGradient: [0, 100, 0, 500],stops: [[0, 'rgb( 174, 139,  28) '],[1, 'rgb( 250, 250, 250)']]},
                    {linearGradient: [0, 100, 0, 500],stops: [[0, 'rgb( 232, 185,  38) '],[1, 'rgb( 250, 250, 250)']]},
                    {linearGradient: [0, 100, 0, 500],stops: [[0, 'rgb( 159, 153, 215) '],[1, 'rgb( 250, 250, 250)']]}
                ],
                title: {
                    text: 'Presupuesto'
                },
                xAxis: {
                    categories: ['Presupuesto']
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Presupuesto Modificado'
                    },
                    stackLabels: {
                        enabled: false,
                        style: {
                            fontWeight: 'bold',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                        }
                    }
                },
                legend: {
                    align: 'right',
                    x: -30,
                    verticalAlign: 'top',
                    y: 25,
                    floating: true,
                    backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
                    borderColor: '#CCC',
                    borderWidth: 1,
                    shadow: false
                },
                tooltip: {
                    formatter: function () {
                        return '<b>' + this.x + '</b><br/>' +
                            this.series.name + ': ' + this.y + '<br/>' +
                            'Total: ' + this.point.stackTotal;
                    }
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: true,
                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                            style: {
                                textShadow: '0 0 3px black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Modificado',
                    data: [" . round($presupuesto['vigente'], 2) . "]
                }, {
                    name: 'Capturado',
                    data: [" . round($capturado, 2) . ']
                }]
            });
        ';
        return $scrip;
    }

    public function vwGraficaPastel($presupuesto, $modificaciones)
    {
        $grafica2 = "
            Highcharts.chart('grafica2', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Presupuesto Anual Disponible'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'XXX',
                    colorByPoint: true,
                    data: [{
                        name: 'Disponible',
                        y: " . round($presupuesto['disponible']) . ",
                        sliced: true,
                        selected: true
                    }, {
                        name: 'Modificaciones Presupuestales',
                        y: " . round($modificaciones['dmo_importe'], 2) . "
                    }, {
                        name: 'Comprometido',
                        y: " . round($presupuesto['comprometido'], 2) . '
                    }]
                }]
            });';
        return $grafica2;
    }

    public function vwGraficaLinear($presupuesto)
    {

        $grafica3 = "
            Highcharts.chart('grafica3', {
                chart: {
                    type: 'line',
                },
                title: {
                    text: 'Comprometido por Mes'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {

                    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
                },
                yAxis: {
                    title: {
                        text: 'Importe'
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true
                        },
                        enableMouseTracking: false
                    }
                },
                series: [{
                    name: 'Vigente',
                    color:'#990000',
                    data: [
                        " . round($presupuesto['vig_ene'], 2) . ',
                        ' . round($presupuesto['vig_feb'], 2) . ',
                        ' . round($presupuesto['vig_mar'], 2) . ',
                        ' . round($presupuesto['vig_abr'], 2) . ',
                        ' . round($presupuesto['vig_may'], 2) . ',
                        ' . round($presupuesto['vig_jun'], 2) . ',
                        ' . round($presupuesto['vig_jul'], 2) . ',
                        ' . round($presupuesto['vig_ago'], 2) . ',
                        ' . round($presupuesto['vig_sep'], 2) . ',
                        ' . round($presupuesto['vig_oct'], 2) . ',
                        ' . round($presupuesto['vig_nov'], 2) . ',
                        ' . round($presupuesto['vig_dic'], 2) . "
                    ]
                },{
                    name: 'Comprometido',
                    data: [
                        " . round($presupuesto['com_ene'], 2) . ',
                        ' . round($presupuesto['com_feb'], 2) . ',
                        ' . round($presupuesto['com_mar'], 2) . ',
                        ' . round($presupuesto['com_abr'], 2) . ',
                        ' . round($presupuesto['com_may'], 2) . ',
                        ' . round($presupuesto['com_jun'], 2) . ',
                        ' . round($presupuesto['com_jul'], 2) . ',
                        ' . round($presupuesto['com_ago'], 2) . ',
                        ' . round($presupuesto['com_sep'], 2) . ',
                        ' . round($presupuesto['com_oct'], 2) . ',
                        ' . round($presupuesto['com_nov'], 2) . ',
                        ' . round($presupuesto['com_dic'], 2) . '
                    ]
                }]
            });
        ';
        return $grafica3;
    }

    public function vwPintaContenedorGrafica($prm)
    {
        /* DEVUELVE LOS CONTENENDORES PARA LOS JS DE LAS GRAFICAS  */
        return '
            <span class="cerrarGrafica" id="cmp_hideGraph" style="cursor:pointer;">Ocultar Gr&aacute;fica
                <span class="fa-solid fa-eye-slash mb-2"></span>
            </span>
            <span class="cerrarGrafica mb-2" id="cmp_ShowGraph" style="display:none;cursor:pointer;">
                Mostrar Gr&aacute;fica
                <span class="fa-solid fa-eye mb-2"></span>
            </span>
            <div class="" id="cmp_graph" style="overflow:hidden; text-align:center">
                <div class="cmp_graph" id="grafica" style="display:inline-block;"></div>
                <div class="cmp_graph" id="grafica2" style="display:inline-block"></div>
                <div class="cmp_graph" id="grafica3" style="display:inline-block"></div>
            </div>
            <table style="width:100%">
                <tr>
                    <td>
                        <div class="row success">
                            <fieldset class="col-md-3 col-xs-3">
                                <strong> Modificado: ' . number_format($prm['vigente'], 2) . ' </strong>
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                <strong> Capturado: ' . round($prm['capturado'], 2) . ' % </strong>
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                <strong > Disponible: <label id="disponible">' . number_format($prm['disponible'], 2) . '</label> </strong>
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                <strong> Comprometido: ' . number_format($prm['comprometido'], 2) . ' </strong>
                            </fieldset>
                        </div>
                    </td>
                </tr>
            </table>
        ';
    }
}
