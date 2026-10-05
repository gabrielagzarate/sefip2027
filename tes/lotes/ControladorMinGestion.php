<?php
/**
 * Description of ControladorMinGestion
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Update: 12-11-2019
 * Comment: -----
 * Objective: Crear lotes federales
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de lectura, escritura y validación
 **/

/*
 * En esta clase se manejarán las propuestas generadas, se podrán autorizar o cancelar
 * segun se requiera, además se podra ver su detalle con la posibilidad de editar
 * la propuesta.
 */

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_GenerarLoteFederal.php';
require_once dirname(__FILE__) . '/../../ent/tes/propuesta/temPropuestaClc.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/Lote.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDetalle.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDocumento.php';
require_once dirname(__FILE__) . '/../../ent/tes/linea/LineaCaptura.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/LoteClcLineacaptura.php';

class ControladorMinGestion extends WebResponse
{

    public $usr_id;
    public $ip;
    public $escritura;
    public $validacion;

    public function __construct()
    {

        parent::__construct();
        $this->pantalla = "tes/lotes/ControladorMinGestion.php";
        //Variables de sesión
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        //Permisos
        $this->escritura = isset($_SESSION[_S]['prm']['T']['4']['2']['2']);
        $this->validacion = isset($_SESSION[_S]['prm']['T']['4']['2']['3']);

        switch ($this->metodo) {
            case 'buscaFolios':
                $this->buscaFolios();
                break;
            case 'pintaTablaFederales':
                $this->pintaTablaFederales();
                break;
            case 'llenaTablaFederales':
                $this->llenaTablaFederales();
                break;
            case 'quitarClc':
                $this->quitarClc();
                break;
            case 'limpiarTodo':
                $this->limpiarTodo();
                break;
            case 'verificarSaldo':
                $this->verificarSaldo();
                break;
            case 'limpiar':
                $this->limpiar();
                break;
            case 'validarPropuestaFederal':
                $this->validarPropuestaFederal();
                break;
            case 'actualizarLineaCaptura':
                $this->actualizarLineaCaptura();
                break;
            case 'guardarLinea':
                $this->guardarLinea();
                break;
            case 'verificarImportes':
                $this->verificarImportes();
                break;
            case 'asignarLineaAVacios':
                $this->asignarLineaAVacios();
                break;
            case 'main':
                $this->main();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    /* Se declaran los botones y tablas que contendrá la vista/*/
    private function main()
    {
        //al cargar la pantalla limpiamos los datos
        $this->limpiar();
        //Crea el boton de regreso

        $this->mixScript('
            $("#toolsPantalla").html("");
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $ponBotonBuscar = "";
        //Dibuja el boton
        $inputAnio = new StdClass();
        $anio = array();
        array_push($anio, '2026');
        array_push($anio, '2025');
        array_push($anio, '2024');
        array_push($anio, '2023');
        array_push($anio, '2022');
        array_push($anio, '2021');
        if ($this->serverProduccion) {
            array_push($anio, '2020');
            array_push($anio, '2019');
            array_push($anio, '2018');
            array_push($anio, '2017');
        }
        $inputAnio->opciones = $anio;
        $inputAnio->id = 'anio';
        $inputAnio->name = 'anio';
        $inputAnio->search = true;
        $inputAnio->required = true;
        $inputAnio->mensaje = 'SELECCIONE EL EJERCICIO';
        $inputAnio->label = 'EJERCICIO';

        $inputModo = new StdClass();
        $modoPago = array();
        array_push($modoPago, 'TRANSFERENCIA');
        array_push($modoPago, 'CHEQUE');
        array_push($modoPago, 'LINEA DE CAPTURA');
        $inputModo->opciones = $modoPago;
        $inputModo->id = 'modo';
        $inputModo->search = true;
        $inputModo->name = 'modo';
        $inputModo->required = true;
        $inputModo->mensaje = 'SELECCIONE EL MODO DE PAGO';
        $inputModo->label = 'MODO DE PAGO';

        $inputDocumentos = new StdClass();
        $documento = array();
        array_push($documento, 'OTB-CERE');
        array_push($documento, 'OP-CERECO');
        $inputDocumentos->opciones = $documento;
        $inputDocumentos->id = 'documento';
        $inputDocumentos->name = 'documento';
        $inputDocumentos->required = true;
        $inputDocumentos->search = true;
        $inputDocumentos->mensaje = 'SELECCIONE EL TIPO';
        $inputDocumentos->label = 'TIPO';

        $inputEspecial = new StdClass();
        $esp = array();
        array_push($esp, 'NORMAL');
        $inputEspecial->opciones = $esp;
        $inputEspecial->id = 'especial';
        $inputEspecial->name = 'especial';
        $inputEspecial->required = true;
        $inputEspecial->mensaje = 'SELECCIONE';
        $inputEspecial->label = '';

        $comboEspecial = $this->ponCombo($inputEspecial);
        $comboEspecial = '';
        //pendiente analisis de cuenta pagadoras federales
        $inputPagadora = new StdClass();
        $esp = array();
        array_push($esp, 'HSBC');
        array_push($esp, 'Banamex');
        $inputPagadora->opciones = $esp;
        $inputPagadora->id = 'pagadora';
        $inputPagadora->name = 'pagadora';
        $inputPagadora->required = true;
        $inputPagadora->mensaje = 'SELECCIONE';
        $inputPagadora->label = 'PAGADORA';

        $inputBuscar = new StdClass();
        $esp = array();
        array_push($esp, 'FOLIOS - ORDENES');
        $inputBuscar->opciones = $esp;
        $inputBuscar->id = 'buscar';
        $inputBuscar->name = 'buscar';
        $inputBuscar->required = true;
        $inputBuscar->mensaje = 'SELECCIONE';
        $inputBuscar->label = 'BUSCAR POR';

        $inputFecha = new StdClass();
        $inputFecha->id = 'fechaPago';
        $inputFecha->name = 'fechaPago';
        $inputFecha->required = true;
        $inputFecha->enDiv = true;
        $inputFecha->label = 'FECHA DE PAGO';
        $inputFecha->formato = 'fecha';

        $inputFolios = new StdClass();
        $inputFolios->id = 'folios';
        $inputFolios->name = 'folios';
        $inputFolios->required = true;
        $inputFolios->enDiv = true;
        $inputFolios->label = 'FOLIOS'; //FOLIOS/LOTES

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscar";
        $btnBuscar->name = "btnBuscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->icon = "search me-2";
        $btnBuscar->class = "btn btn-outline-secondary me-1";
        $btnBuscar->click = '
            if ($("#buscar").val() == 0) {
                strFolios = $("#folios").val();
                strFolios = strFolios.replace(",", "");
                if (!/^([0-9, ])*$/.test(strFolios)) {
                    notify("Para la busqueda por folios solo debe capturar numeros.", "warning");
                    $("#folios").focus();
                    return;
                }
            }
            comunica("' . $this->pantalla . '",{
                metodo:"buscaFolios",
                anio:$("#anio option:selected").text().split(" ")[2].trim(),
                modo:$("#modo option:selected").text().split(" ")[0].trim(),
                documento:$("#documento option:selected").text().split(" ")[0].trim(),
                especial:$("#especial option:selected").text().split(" ")[0].trim(),
                pagadora:$("#pagadora option:selected").text().split(" ")[0].trim(),
                buscar:$("#buscar").val(),
                folios:$("#folios").val()
            });
        ';
        if ($this->escritura) {
            $ponBotonBuscar = $this->ponBoton($btnBuscar);
        }
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = "btnLimpiar";
        $btnLimpiar->name = "btnLimpiar";
        $btnLimpiar->class = "btn btn-outline-info me-1";
        $btnLimpiar->value = "Limpiar";
        $btnLimpiar->color = "amarillo";
        $btnLimpiar->icon = "trash me-2";
        $btnLimpiar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"limpiarTodo"
            });
        ';
        $this->mixHtml('
            <div class="row mb-2">
                <div class="col-md-3">' . $this->ponCombo($inputAnio) . '</div>
                <div class="col-md-3">' . $this->ponCombo($inputDocumentos) . '</div>
                <div class="col-md-3">' . $this->ponCombo($inputModo) . '</div>
                <div class="col-md-3">' . $comboEspecial . '</div>
            </div>
            <div class="row mb-2">

                <div class="col-md-3">' . $this->ponCombo($inputBuscar) . '</div>
                <div class="col-md-3">' . $this->ponInput($inputFolios) . '</div>
                <div class="col-md-3">' . $this->ponInput($inputFecha) . '</div>
            </div>

            <div class="row">
                <div class="col-md-3">' . $ponBotonBuscar . '</div>
                <div class="col-md-3">' . $this->ponBoton($btnLimpiar) . '</div>
            </div>
            <div class="row">
                <fieldset id="divTablaFederal" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
            <div id="divModalLineaCaptura"></div>
        ');
    }

    private function validarPropuestaFederal()
    {

        $mensaje = "";
        //verificamos que no vayan valores vacios
        $modo = isset($this->prm->modo) ? $this->prm->modo : "";
        $fechaPago = isset($this->prm->fecha) ? $this->prm->fecha : "";
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        $documento = isset($this->prm->documento) ? $this->prm->documento : "";

        if ($modo == "" || $fechaPago == "") {
            $this->mixScript('notify("Faltan datos por capturar","warning");');
        } else {
            //Reordenar el formato de fecha  año/mes/dia
            $arrFecha = explode("/", $fechaPago);
            $fechaPago = $arrFecha[2] . "-" . $arrFecha[1] . "-" . $arrFecha[0];

            $generaLote = new sp_GenerarLoteFederal();
            $generaLote->setModo_pago($modo);
            $generaLote->setFecha_pago($fechaPago);
            $generaLote->setPro_ejercicio($ejercicio);
            $generaLote->setDocumento($documento);
            $generaLote->setSis_id('TES');
            $generaLote->setEjercicio(APP_EJERCICIO);
            $generaLote->setBit_observacion('LOTE FEDERAL GENERADO DESDE PANTALLA T_4_2_1');
            $generaLote->execute();

            if (!$generaLote->getBandera()) {
                $mensaje = $generaLote->getMensaje();
                $this->mixScript('
                    notify(" ' . $mensaje . '", "error",
                    );
                ');
            } else {
                $mensaje = $generaLote->getMensaje();
                //Muestra mensaje
                $this->mixScript('
                    notify(" ' . $mensaje . '", "success",
                    );
                ');
            }
            $this->main();
        }
    }

    private function verificarSaldo()
    {

        $parametros = [
            'usr_id' => $this->usr_id,
        ];
        if ($this->prm->modo == 2) {
            $errores = '';
            $this->conectar();
            $this->setSql("
                SELECT DISTINCT pro_lineaCaptura FROM ".BD_TESORERITEM.".tem_PropuestaClc
                WHERE usr_id = :usr_id;
            ");
            $this->consultar($parametros);
            while ($row = $this->extraer()) {
                $saldoLinea = $this->getSaldoLinea($row['pro_lineaCaptura']);
                if ($saldoLinea < 0) {
                    $errores .= "El importe de la Linea de Captura " . $row['pro_lineaCaptura'] . " es menor al (los) importe(s) de la(s) CLC(s).";
                }
            }
            $this->desconectar();
            if ($errores != '') {
                $this->mixScript('
                    notify("' . $errores . '", "error");
                ');
                return;
            }
        }

        $anio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";

        switch ($anio) {
            case 2017:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id, BN.bsf_id as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2017.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2017.".enc_BancosSefinCveFin BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2018:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2018.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2019:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2019.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2020:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2020.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2021:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2021.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2022:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2022.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2023:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2023.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2024:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2024.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2025:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL_2025.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
            case 2026:
                $sql = "
                    SELECT gpo_id, ur_id, clc_id , BN.cbs_id  as cta, pro_importeClc
                    FROM ".BD_TESORERITEM.".tem_PropuestaClc  p
                    INNER JOIN ".BD_CTRL.".scp_Clc C USING (gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    WHERE p.usr_id= :usr_id";
                break;
        }

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $cuentas = array();
        while ($row = $this->extraer()) {
            $cta = $row['cta'];
            $cuentas[$cta] = $row['pro_importeClc'];
        }
        $ejercicio = date("Y");
        $mes = date("n");
        $dia = date("j");
        $errores = "";

        //verificamos que las cuentas tengan saldo
        foreach ($cuentas as $key => $value) {
            $paramCuenta = [
                'cbs_id' => $key,
                'pfi_ejercicio' => $ejercicio,
                'msc_id' => $mes,
                'pfi_dia' => $dia,
            ];
            //1.- verificamos que tenga saldo
            $sql = "
                SELECT (pfi_capital+pfi_productoFinanciero-pfi_egreso) AS saldo FROM ".BD_TESORERIA.".ban_ProductoFinanciero
                WHERE cbs_id= :cbs_id AND pfi_ejercicio= :pfi_ejercicio
                AND msc_id= :msc_id
                AND pfi_dia= :pfi_dia order by pfi_id desc limit 1
            ";
            $this->setSql($sql);
            $this->conectar();
            $this->consultar($paramCuenta);
            if ($this->getNumeroRegistros() > 0) {
                $row = $this->extraer();
                $dif = $row['saldo'] - $cuentas[$key];
                if ($dif >= 0) {
                    //la cuenta tiene suficiente saldo
                } else {
                    $errores .= "Cuenta " . $key . " SALDO $" . $row['saldo'] . " Requiere $" . $cuentas[$key] . " Diferencia $" . $dif . " .";
                }
            } else {
                //no encontro la cuenta en la tabla de saldos
                $errores .= "No se encontro Cuenta " . $key . ". ";
            }

        }

        if ($errores == "") {
            //si no encontro errores mostramos el boton de Validar
            $btnValidarPropuesta = new stdClass();
            $btnValidarPropuesta->id = 'btnValidarPropuesta';
            $btnValidarPropuesta->name = 'btnValidarPropuesta';
            $btnValidarPropuesta->text = 'Regresar';
            $btnValidarPropuesta->value = 'Validar';
            $btnValidarPropuesta->type = 'button';
            $btnValidarPropuesta->color = 'verde';
            $btnValidarPropuesta->icon = 'solid fa-check me-2';
            $btnValidarPropuesta->class = 'btn btn-outline-success me-1';           
            $btnValidarPropuesta->click = '
                alertify.confirm("REGISTRAR PROPUESTA", "¿Desea registrar la propuesta?",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                        metodo:"validarPropuestaFederal",
                                        ejercicio:$("#anio option:selected").text().split(" ")[2].trim(),
                                        modo:$("#modo option:selected").text().split(" ")[0].trim(),
                                        pagadora:$("#pagadora option:selected").text().split(" ")[0].trim(),
                                        fecha:$("#fechaPago").val(),
                                        documento:$("#documento option:selected").text().split(" ")[0].trim()
                                    });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            ';
            //si tiene los permisos de validacion se pinta el boton
            if ($this->validacion) {
                $this->mixScript('notify("SALDO VERIFICADO");');
                $this->agregaTools($this->ponBoton($btnValidarPropuesta));
            }
        } else {
            $this->mixScript('notify("' . $errores . '", "error");');
        }
    }

    private function limpiarTodo()
    {
        $parametros =[
            'usr_id' =>  $this->usr_id,
        ];
        $sql = "
            DELETE FROM ".BD_TESORERITEM.".tem_PropuestaClc WHERE usr_id= :usr_id";
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->mixScript('$("#toolsPantalla").html("");');
        $this->main();
    }

    private function limpiar()
    {
        $parametros = [
            'usr_id' =>  $this->usr_id,
        ];
        $sql = "
            DELETE FROM ".BD_TESORERITEM.".tem_PropuestaClc WHERE usr_id= :usr_id";
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->mixScript('$("#toolsPantalla").html("");');
    }

    private function buscaFolios()
    {
        $anio = isset($this->prm->anio) ? $this->prm->anio : "";
        $modo = isset($this->prm->modo) ? $this->prm->modo : "";
        $documento = isset($this->prm->documento) ? $this->prm->documento : "";
        $buscar = isset($this->prm->buscar) ? $this->prm->buscar : "";
        $folios = isset($this->prm->folios) ? $this->prm->folios : "";
        //limpiamos las busquedas de folios:

        $condLineaCaptura = '';
        if ($modo == 2) {
            $condLineaCaptura = ' AND CD.mpc_id = 2';
        }

        $sql = "";
        //buscamos los clc
        if ($folios != "") {
            $parametro = [
                'usr_id' =>$this->usr_id,
            ];
            switch ($anio) {
                case 2017:
                    //buscar 0 -> clc 1 -> lote egreso extraemos los folios
                    if ($buscar == 1) {
                        //separamos los folios
                        $tok = strtok($folios, ", \n\t");
                        $cadena = "";
                        while ($tok !== false) {
                            $cadena .= "'" . $tok . "',";
                            $tok = strtok(", \n\t");
                        }
                        $folios = substr($cadena, 0, -1);
                        $sql = "
                                SELECT CDX.clc_cereCo FOLIO
                                FROM ".BD_CTRL_2017.".scp_ClcAuxiliar CDX
                                INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                                LEFT JOIN ".BD_CTRL_2017.".scp_DetalleLoteEgresos DLE USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN ".BD_CTRL_2017.".scp_LoteEgresos LE ON DLE.loe_dia = LE.loe_dia AND DLE.loe_mes = LE.loe_mes AND DLE.loe_anio = LE.loe_anio AND DLE.loe_cereCo = LE.loe_cereCo AND DLE.loe_id = LE.loe_id
                                LEFT JOIN ".BD_CTRL_2017.".tes_ClcLoteTesoreria CLT USING(gpo_id, ur_id, clc_id)
                                WHERE ant.ecl_id = 10 AND CLT.lot_id IS NULL AND LE.loe_clave IN (" . $folios . ") ";
                        //extraemos los folios
                        $this->conectar();
                        $this->setSql($sql);
                        $this->consultar();
                        $cadena = "";
                        while ($row = $this->extraer()) {
                            $cadena .= $row['FOLIO'] . ",";
                        }
                        $folios = substr($cadena, 0, -1);
                    }
                    //arma la consulta clc 2017
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    //si es modo 2 -> op 1 -> otb

                    $condicion_sql = " WHERE TMP.clc_id is null AND CLA.clc_cereCo IN (" . $folios . ") AND CD.clc_tipoCereCo = " . $cereCo . "
                    AND CLT.lot_id IS NULL AND ant.ecl_id = 10 AND ant.ejr_id_clc=2017 AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0 $condLineaCaptura ORDER BY BN.bsf_id,(CD.clc_percepcion-CD.clc_retencion)";

                    $sql = "
                            SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                            CD.clc_id CLC ,
                            CD.gpo_id GPO,
                            CD.ur_id UR,
                            (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                            CD.bur_id DESTINO
                            FROM ".BD_CTRL_2017.".scp_Clc CD
                            LEFT JOIN (
                                SELECT gpo_id, ur_id, clc_id
                                FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                                WHERE  T.usr_id = :usr_id
                            ) AS TMP  USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                            LEFT JOIN ".BD_CTRL_2017.".tes_ClcLoteTesoreria AS CLT ON CD.gpo_id = CLT.gpo_id AND CD.ur_id = CLT.ur_id AND CD.clc_id = CLT.clc_id
                            INNER JOIN ".BD_CTRL_2017.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                            LEFT JOIN ".BD_CTRL_2017.".scp_DetalleLoteEgresos DLE ON CD.gpo_id = DLE.gpo_id AND CD.ur_id = DLE.ur_id AND CD.clc_id = DLE.clc_id
                            LEFT JOIN ".BD_CTRL_2017.".scp_LoteEgresos LE ON DLE.loe_dia = LE.loe_dia AND DLE.loe_mes = LE.loe_mes AND DLE.loe_anio = LE.loe_anio AND DLE.loe_cereCo = LE.loe_cereCo AND DLE.loe_id = LE.loe_id
                            INNER JOIN ".BD_CTRL_2017.".enc_ClaveFinanciamiento CV using(ecf_id)
                            INNER JOIN ".BD_SINPRES.".cat_ClasificacionSuborigen CS using( fte_id, sft_id, ori_id, sor_id, cso_id )
                            INNER JOIN ".BD_CTRL_2017.".enc_BancosSefinCveFin BN USING (ecf_id)
                            INNER JOIN ".BD_CTRL_2017.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id)) " . $condicion_sql;
                    break;
                case 2018:
                    if ($buscar == 1) {
                        //separamos los folios
                        $tok = strtok($folios, ", \n\t");
                        $cadena = "";
                        while ($tok !== false) {
                            $cadena .= "'" . $tok . "',";
                            $tok = strtok(", \n\t");
                        }
                        $folios = substr($cadena, 0, -1);
                        $sql = "
                            SELECT CDX.clc_cereCo FOLIO
                            FROM ".BD_CTRL_2018.".scp_ClcAuxiliar CDX
                            INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                            LEFT JOIN ".BD_CTRL_2018.".scp_DetalleLoteEgresos DLE USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN ".BD_CTRL_2018.".scp_LoteEgresos LE ON DLE.loe_dia = LE.loe_dia AND DLE.loe_mes = LE.loe_mes AND DLE.loe_anio = LE.loe_anio AND DLE.loe_cereCo = LE.loe_cereCo AND DLE.loe_id = LE.loe_id
                            WHERE ant.ecl_id = 10  AND LE.loe_clave IN (" . $folios . ")
                        ";
                        //extraemos los folios
                        $this->conectar();
                        $this->setSql($sql);
                        $this->consultar();
                        $cadena = "";
                        while ($row = $this->extraer()) {
                            $cadena .= $row['FOLIO'] . ",";
                        }
                        $folios = substr($cadena, 0, -1);
                    }
                    //arma la consulta clc 2018
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND  ant.ecl_id = 10
                        AND ant.ejr_id_clc=2018
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2018.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2018.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        LEFT JOIN ".BD_CTRL_2018.".scp_DetalleLoteEgresos DLE ON CD.gpo_id = DLE.gpo_id AND CD.ur_id = DLE.ur_id AND CD.clc_id = DLE.clc_id
                        LEFT JOIN ".BD_CTRL_2018.".scp_LoteEgresos LE ON DLE.loe_dia = LE.loe_dia AND DLE.loe_mes = LE.loe_mes AND DLE.loe_anio = LE.loe_anio AND DLE.loe_cereCo = LE.loe_cereCo AND DLE.loe_id = LE.loe_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;

                    break;
                case 2019:
                    //arma la consulta clc 2019
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND ant.ecl_id = 10
                        AND ant.ejr_id_clc=2019
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2019.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id =  :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2019.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
                case 2020:
                    //arma la consulta clc 2020
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                            WHERE TMP.clc_id is null
                            AND CLA.clc_cereCo IN (" . $folios . ")
                            AND CD.clc_tipoCereCo = " . $cereCo . "
                            AND ant.ecl_id = 10
                            AND ant.ejr_id_clc=2020
                            AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                            AND CB.ter_id='B' $condLineaCaptura
                            ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2020.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2020.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
                case 2021:
                    //arma la consulta clc 2021
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND ant.ecl_id = 10
                        AND ant.ejr_id_clc=2021
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2021.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2021.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
                case 2022:
                    //arma la consulta clc 2022
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND ant.ecl_id = 10
                        AND ant.ejr_id_clc=2022
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2022.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2022.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
                case 2023:
                    //arma la consulta clc 2023
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND ant.ecl_id = 10
                        AND ant.ejr_id_clc=2023
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2023.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2023.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
                case 2024:
                    //arma la consulta clc 2024
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND ant.ecl_id = 10
                        AND ant.ejr_id_clc=2024
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2024.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2024.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
                case 2025:
                    //arma la consulta clc 2025
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND ant.ecl_id = 10
                        AND ant.ejr_id_clc=2025
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL_2025.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2025.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
                case 2026:
                    //arma la consulta clc 2026
                    $condicion_sql = '';
                    $cereCo = $documento == 0 ? 1 : 2;
                    $condicion_sql = "
                        WHERE TMP.clc_id is null
                        AND CLA.clc_cereCo IN (" . $folios . ")
                        AND CD.clc_tipoCereCo = " . $cereCo . "
                        AND  CD.ecl_id = 10
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) <> 0
                        AND CB.ter_id='B' $condLineaCaptura
                        ORDER BY CB.cbs_id,(CD.clc_percepcion-CD.clc_retencion)";
                    $sql = "
                        SELECT CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        CD.bur_id DESTINO
                        FROM ".BD_CTRL.".scp_Clc CD
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM ".BD_TESORERITEM.".tem_PropuestaClc as T
                            WHERE  T.usr_id = :usr_id
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ".BD_CTRL.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CV USING (ecf_id)
                        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB using(cbs_id) " . $condicion_sql;
                    break;
            }
            //consultamos los datos e insertamos en el temporal
            if (strlen($folios) == 0) {
                $this->mixScript('
                    notify("No existen folios asociados al lote de egresos", "warning");
                ');
                return;
            }
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($parametro);
            $j = 0;
            while ($row = $this->extraer()) {
                //Valida que la CLC no este ocupada por otro usuario
                $conexion = new AccesoDatos();
                $paramClc =[
                    'gpo_id' => $row['GPO'],
                    'ur_id' => $row['UR'],
                    'clc_id' => $row['CLC'],
                    'ejercicio' => $anio,
                ];
                $sql = "
                    SELECT t.pro_ejercicio,t.gpo_id,t.ur_id,t.clc_id,t.usr_id,u.usr_usuario
                        FROM ".BD_TESORERITEM.".tem_PropuestaClc t
                        INNER JOIN ".BD_CTRL.".cat_Usuario u USING(usr_id)
                    WHERE t.gpo_id= :gpo_id
                    AND t.ur_id= :ur_id
                    AND t.clc_id= :clc_id
                    AND t.pro_ejercicio= :ejercicio";
                $conexion->setSql($sql);
                $conexion->conectar();
                $conexion->consultar($paramClc);
                if ($conexion->getNumeroRegistros() > 0) {
                    while ($inf = $conexion->extraer()) {
                        if ($inf['usr_id'] != $this->usr_id) {
                            $this->mixScript('
                                notify("La CLC esta ocupada por el usuario ' . $inf['usr_usuario'] . '", "warning");
                            ');
                        }
                    }
                } else {
                    //agregamos al temporal las clc que no tenga
                    $this->prm->pro_id = $this->usr_id . date("mdhis") . $j++;
                    $this->prm->ejercicio = $anio;
                    $this->prm->gpo_id = $row['GPO'];
                    $this->prm->ur_id = $row['UR'];
                    $this->prm->clc_id = $row['CLC'];
                    $this->prm->importeClc = $row['IMPORTE'];
                    $this->prm->lineaCaptura = '0';
                    $this->prm->bur_id = $row['DESTINO'];
                    $this->prm->abc = 'a';
                    TemporalPropuestaClc::abc($this->prm);
                }
            }
        }
        //si hay datos en la tabla temporal se agrega el boton validar
        $sql =
            "SELECT
                FORMAT(sum(p.pro_importeClc),2) importe, count(p.clc_id) contador#, c.mpc_id
            FROM
                ".BD_TESORERITEM.".tem_PropuestaClc p ";
        switch ($anio) {
            case 2017:
                $sql .= "INNER JOIN ".BD_CTRL_2017.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2018:
                $sql .= "INNER JOIN ".BD_CTRL_2018.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2019:
                $sql .= "INNER JOIN ".BD_CTRL_2019.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2020:
                $sql .= "INNER JOIN ".BD_CTRL_2020.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2021:
                $sql .= "INNER JOIN ".BD_CTRL_2021.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2022:
                $sql .= "INNER JOIN ".BD_CTRL_2022.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2023:
                $sql .= "INNER JOIN ".BD_CTRL_2023.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2024:
                $sql .= "INNER JOIN ".BD_CTRL_2024.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2025:
                $sql .= "INNER JOIN ".BD_CTRL_2025.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
            case 2026:
                $sql .= "INNER JOIN ".BD_CTRL.".scp_Clc c using (gpo_id,ur_id,clc_id) ";
                break;
        }

        $paramValidar =[
            'pro_ejercicio' => $anio,
            'usr_id' => $this->usr_id,
        ];
        $sql .=
        "WHERE pro_ejercicio= :pro_ejercicio AND p.usr_id= :usr_id
            #GROUP BY pro_ejercicio,p.usr_id,c.mpc_id";

        $this->setSql($sql);
        $this->conectar();
        $this->consultar($paramValidar);
        $row = $this->extraer();
        $importe = $row['importe'];
        $jsLineaCaptura = '
            textVacio = null;
            $(".textLinea").each(function() {
                if ($(this).val().trim() == "") {
                    textVacio = $(this);
                    return false;
                }
            });

            if (textVacio != null) {
                notify("Debe registrar todas las lineas de captura.", "warning");
                textVacio.focus();
                return;
            }
        ';

        $btnVerficaSaldo = new stdClass();
        $btnVerficaSaldo->id = 'btnVerficaSaldo';
        $btnVerficaSaldo->name = 'btnVerficaSaldo';
        $btnVerficaSaldo->text = 'Regresar';
        $btnVerficaSaldo->value = 'Verificar Saldo $' . $importe;
        $btnVerficaSaldo->type = 'button';
        $btnVerficaSaldo->color = 'verde';
        $btnVerficaSaldo->icon = 'solid fa-check me-2';
        $btnVerficaSaldo->class = 'btn btn-outline-success me-1';   
        $btnVerficaSaldo->click = ($this->prm->modo == 2 ? $jsLineaCaptura : '') . '
            comunica("' . $this->pantalla . '",{
                metodo: "verificarSaldo",
                ejercicio: $("#anio option:selected").text().split(" ")[2].trim(),
                modo: $("#modo option:selected").text().split(" ")[0].trim()
            })';

        if ($row['contador'] > 0 && $this->escritura) {
            $this->agregaTools($this->ponBoton($btnVerficaSaldo));
        }

        $this->pintaTablaFederales();
        $this->llenaTablaFederales();
    }

    private function pintaTablaFederales()
    {

        $this->setContenedor('divTablaFederal');
        $tabla = new stdClass();
        $tabla->id = 'tbl-federal';
        $tabla->titulo = 'Listado de CLCs ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaFederales';
        $tabla->comunica->anio = '{$("#anio option:selected").text().split(" ")[2].trim()}';
        $tabla->comunica->modo = '{$("#modo option:selected").text().split(" ")[0].trim()}';
        $tabla->comunica->documento = '{$("#documento option:selected").text().split(" ")[0].trim()}';
        $tabla->comunica->especial = '{$("#especial option:selected").text().split(" ")[0].trim()}';
        $tabla->comunica->pagadora = '{$("#pagadora option:selected").text().split(" ")[0].trim()}';
        $tabla->comunica->buscar = '{$("#buscar option:selected").text().split(" ")[0].trim()}';
        $tabla->comunica->folios = '{$("#folios").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ORDEN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR RESPONSABLE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA NOMBRE';
        array_push($tr, $th);

        $th = new stdClass();
        if ($this->prm->modo == 2) {
            $th->texto = 'LINEA DE CAPTURA';
        } else {
            $th->texto = 'CTA DESTINO';
        }
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CVE FIN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIM';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'GPO_UR';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CLC';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'FOLIO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'RESPONSABLE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'BENEFICIARIO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'IMPORTE';
        $td->formato = "moneda";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'BANCO';
        $td->casos->cases = array();

        $caso = new stdClass();
        $caso->valor = '002';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/banamex.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '012';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/bancomer.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '014';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/santander.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '021';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/hsbc.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '037';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/interacciones.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '044';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/scotia.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '072';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/banorte.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '132';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/multiva.png';
        array_push($td->casos->cases, $caso);

        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CARGO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NOMBRE';
        array_push($trbody, $td);

        $td = new stdClass();
        if ($this->prm->modo == 2) {
            $td->tipo = 'text';
            $td->class = 'textLinea';
            $td->attr = new stdClass();
            $td->attr->value = "LINEA";
            $td->attr->UR = "UR";
            $td->attr->GPO = "GPO";
            $td->attr->CLC = "CLC";
            $td->attr->EJERCICIO = "EJERCICIO";
            $td->attr->IMPORTE = "IMPORTE";
            $td->attr->maxlength = "20";
        } else {
            $td->campoTxt = 'DESTINO';
        }
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CVE_FIN';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = "trash";
        $td->color = "rojo";
        $td->class = "quitarClc";
        $td->name = "IDCLC";
        $td->id = "IDCLC";
        $td->attr = new stdClass();
        $td->attr->UR = "UR";
        $td->attr->GPO = "GPO";
        $td->attr->CLC = "CLC";
        $td->attr->EJERCICIO = "EJERCICIO";

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixHtml('
            <div><label id="lblNumRegistros"></label></div>
        ');

        $this->mixScript("
            $(document).off('focus', '.textLinea');
            $(document).on('focus', '.textLinea', function() {
                $(this).attr('anterior', $(this).val());
            });
            $(document).off('blur', '.textLinea');
            $(document).on('blur', '.textLinea', function() {
                if ($(this).attr('anterior') != $(this).val()) {
                    comunica('$this->pantalla', {
                        metodo: 'actualizarLineaCaptura',
                        ur: $(this).attr('UR'),
                        gpo: $(this).attr('GPO'),
                        clc: $(this).attr('CLC'),
                        ejercicio: $(this).attr('EJERCICIO'),
                        importe: $(this).attr('IMPORTE'),
                        linea: $(this).val()
                    });
                }
            });
        ");
    }

    private function llenaTablaFederales()
    {

        $sql = "";
        $parametro =[
            'usr_id' => $this->usr_id,
        ];

        switch ($this->prm->anio) {
            case 2017:
                $sql = "
                    SELECT
                        '2017' as EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        LE.loe_clave    LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto     CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.bsf_id CARGO,
                        SUBSTR(BN.bsf_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                        CD.bur_id DESTINO,
                        concat(CV.sft_id,CV.ori_id,CV.sor_id,CV.cso_id,CV.cfi_id,CV.cfi_anio)   CVE_FIN,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2017.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    LEFT JOIN ".BD_CTRL_2017.".tes_ClcLoteTesoreria AS CLT ON CD.gpo_id = CLT.gpo_id AND CD.ur_id = CLT.ur_id AND CD.clc_id = CLT.clc_id
                    INNER JOIN ".BD_CTRL_2017.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    LEFT JOIN ".BD_CTRL_2017.".scp_DetalleLoteEgresos DLE ON CD.gpo_id = DLE.gpo_id AND CD.ur_id = DLE.ur_id AND CD.clc_id = DLE.clc_id
                    LEFT JOIN ".BD_CTRL_2017.".scp_LoteEgresos LE ON DLE.loe_dia = LE.loe_dia AND DLE.loe_mes = LE.loe_mes AND DLE.loe_anio = LE.loe_anio AND DLE.loe_cereCo = LE.loe_cereCo AND DLE.loe_id = LE.loe_id
                    INNER JOIN ".BD_CTRL_2017.".enc_BancosSefinCveFin BN USING (ecf_id)
                    INNER JOIN ".BD_CTRL_2017.".enc_ClaveFinanciamiento CV using(ecf_id)
                    INNER JOIN ".BD_CTRL_2017.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS ON BN.bsf_id = BS.cbs_id
                    WHERE T.usr_id= :usr_id";
                break;
            case 2018:
                $sql = "
                    SELECT
                        '2018' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        LE.loe_clave    LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                        #IB.iba_abreviatura BANCO
                        #concat(CV.sft_id,CV.ori_id,CV.sor_id,CV.cso_id,CV.cfi_id,CV.cfi_anio)   CVE_FIN,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2018.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2018.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    LEFT JOIN ".BD_CTRL_2018.".scp_DetalleLoteEgresos DLE ON CD.gpo_id = DLE.gpo_id AND CD.ur_id = DLE.ur_id AND CD.clc_id = DLE.clc_id
                    LEFT JOIN ".BD_CTRL_2018.".scp_LoteEgresos LE ON DLE.loe_dia = LE.loe_dia AND DLE.loe_mes = LE.loe_mes AND DLE.loe_anio = LE.loe_anio AND DLE.loe_cereCo = LE.loe_cereCo AND DLE.loe_id = LE.loe_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    #INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria IB USING(iba_id)
                    #INNER JOIN ".BD_CTRL_2018.".enc_ClaveFinanciamiento CV using(ecf_id)
                    INNER JOIN ".BD_CTRL_2018.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                break;
            case 2019:
                $sql = "
                    SELECT
                        '2019' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto     CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                        #IB.iba_abreviatura BANCO
                        #concat(CV.sft_id,CV.ori_id,CV.sor_id,CV.cso_id,CV.cfi_id,CV.cfi_anio)   CVE_FIN,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2019.".scp_Clc CD
                    inner join ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2019.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    #LEFT JOIN ".BD_CTRL_2019.".scp_DetalleLoteEgresos DLE ON CD.gpo_id = DLE.gpo_id AND CD.ur_id = DLE.ur_id AND CD.clc_id = DLE.clc_id
                    #LEFT JOIN ".BD_CTRL_2019.".scp_LoteEgresos LE ON DLE.loe_dia = LE.loe_dia AND DLE.loe_mes = LE.loe_mes AND DLE.loe_anio = LE.loe_anio AND DLE.loe_cereCo = LE.loe_cereCo AND DLE.loe_id = LE.loe_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    #INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria IB USING(iba_id)
                    #INNER JOIN ".BD_CTRL_2019.".enc_ClaveFinanciamiento CV using(ecf_id)
                    INNER JOIN ".BD_CTRL_2019.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                break;
            case 2020:
                $sql = "
                    SELECT
                        '2020' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2020.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2020.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    INNER JOIN ".BD_CTRL_2020.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                break;
            case 2021:
                $sql = "
                    SELECT
                        '2021' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2021.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2021.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    INNER JOIN ".BD_CTRL_2021.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                break;
            case 2022:
                $sql = "
                    SELECT
                        '2022' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2022.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2022.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    INNER JOIN ".BD_CTRL_2022.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                break;
            case 2023:
                $sql = "
                    SELECT
                        '2023' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2023.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2023.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    INNER JOIN ".BD_CTRL_2023.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                break;
            case 2024:
                $sql = "
                    SELECT
                        '2024' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2024.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2024.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    INNER JOIN ".BD_CTRL_2024.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                    break;
            case 2025:
                $sql = "
                    SELECT
                        '2025' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL_2025.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL_2025.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    INNER JOIN ".BD_CTRL_2025.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                    break;
            case 2026:
                $sql = "
                    SELECT
                        '2026' AS EJERCICIO,
                        CONCAT(CD.gpo_id,CD.ur_id)  GPO_UR,
                        ' ' AS LOTE_EGRESO,
                        CD.clc_id CLC ,
                        CD.gpo_id GPO,
                        CD.ur_id UR,
                        CLA.clc_cereCo FOLIO ,
                        UR.ur_nombre RESPONSABLE,
                        CD.clc_beneficiario BENEFICIARIO,
                        CD.clc_concepto CONCEPTO,
                        (CD.clc_percepcion-CD.clc_retencion) IMPORTE,
                        BN.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        SUBSTR(BN.cbs_id, 1, 3) AS BANCO,
                        BS.cbs_nombre AS NOMBRE,
                    IF(T.pro_lineaCaptura = '0', '', T.pro_lineaCaptura) AS LINEA
                    FROM ".BD_CTRL.".scp_Clc CD
                    INNER JOIN ".BD_TESORERITEM.".tem_PropuestaClc as T using(gpo_id,ur_id,clc_id)
                    INNER JOIN ".BD_CTRL.".scp_ClcAuxiliar AS CLA ON CD.gpo_id = CLA.gpo_id AND CD.ur_id = CLA.ur_id AND CD.clc_id = CLA.clc_id
                    INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento BN USING (ecf_id)
                    INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using (cbs_id)
                    INNER JOIN ".BD_CTRL.".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
                    WHERE T.usr_id= :usr_id";
                break;
        }

        $this->setSql($sql);
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametro);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $nav->idTabla = "tbl-federal";
        $numRegs = $this->wrLlenaTabla($nav);

        $this->mixScript('
            $("#tbl-federal .tabla-footer").hide();
            $("#lblNumRegistros").html("' . $numRegs . ' registro' . ($numRegs == 1 ? '' : 's') . '");
            $(".quitarClc").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "quitarClc",
                    gpo_id: $(this).attr("GPO"),
                    ur_id: $(this).attr("UR"),
                    clc_id: $(this).attr("CLC"),
                    ejercicio: $(this).attr("EJERCICIO"),
                    anio: $("#anio option:selected").text().split(" ")[2].trim(),
                    modo: $("#modo option:selected").text().split(" ")[0].trim(),
                    documento: $("#documento option:selected").text().split(" ")[0].trim(),
                    especial: $("#especial option:selected").text().split(" ")[0].trim(),
                    pagadora: $("#pagadora option:selected").text().split(" ")[0].trim(),
                    buscar: $("#buscar option:selected").text().split(" ")[0].trim(),
                    folios: $("#folios").val()
                });
            });
        ');
    }

    private function quitarClc()
    {
        $this->prm->abc = "b";
        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : "";
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : "";
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : "";
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        TemporalPropuestaClc::abc($this->prm);

        $parametro = [
            'usr_id' => $this->usr_id,
        ];
        $sql = "SELECT FORMAT(sum(pro_importeClc),2) importe,count(clc_id) contador
        FROM ".BD_TESORERITEM.".tem_PropuestaClc where usr_id= :usr_id";
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametro);
        $row = $conexion->extraer();
        $importe = $row['importe'];
        $contador = $row['contador'];

        if ($contador == 0) {
            $this->main();
        } else {

            $btnVerficaSaldo = new stdClass();
            $btnVerficaSaldo->id = 'btnVerficaSaldo';
            $btnVerficaSaldo->name = 'btnVerficaSaldo';
            $btnVerficaSaldo->text = 'Regresar';
            $btnVerficaSaldo->value = 'Verificar Saldo $' . $importe;
            $btnVerficaSaldo->type = 'button';
            $btnVerficaSaldo->color = 'verde';
            $btnVerficaSaldo->icon = 'solid fa-check me-2';
            $btnVerficaSaldo->class = 'btn btn-outline-success me-1';  
            $btnVerficaSaldo->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"verificarSaldo",
                    ejercicio:$("#anio option:selected").text().split(" ")[2].trim(),
                    modo: $("#modo option:selected").text().split(" ")[0].trim()
                })';

            if ($row['contador'] > 0 && $this->escritura) {
                $this->agregaTools($this->ponBoton($btnVerficaSaldo));
            }

            $this->llenaTablaFederales();
        }
    }

    private function actualizarLineaCaptura()
    {
        $ur = isset($this->prm->ur) ? $this->prm->ur : '';
        $gpo = isset($this->prm->gpo) ? $this->prm->gpo : '';
        $clc = isset($this->prm->clc) ? $this->prm->clc : '';
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $linea = isset($this->prm->linea) ? $this->prm->linea : '';

        if (trim($linea) != '') {
            $parametro = [
                'linea' => $linea,
            ];
            $this->conectar();
            $this->setSQL("
                SELECT elc_id FROM ".BD_TESORERIA.".lca_LineaCaptura WHERE lca_LineaCaptura = :linea
            ");
            $this->consultar($parametro);
            $row = $this->extraer();

            if (!$row) {
                $txtLineaCaptura = new StdClass();
                $txtLineaCaptura->id = 'txtLineaCaptura';
                $txtLineaCaptura->name = 'txtLineaCaptura';
                $txtLineaCaptura->required = true;
                $txtLineaCaptura->enDiv = true;
                $txtLineaCaptura->label = 'Linea de Captura:';
                $txtLineaCaptura->value = $linea;
                $txtLineaCaptura->maxlength = 20;

                $dpFechaEmision = new StdClass();
                $dpFechaEmision->id = 'dpFechaEmision';
                $dpFechaEmision->name = 'dpFechaEmision';
                $dpFechaEmision->required = true;
                $dpFechaEmision->enDiv = true;
                $dpFechaEmision->label = 'Fecha de Emision:';
                $dpFechaEmision->formato = 'fecha';

                $dpFechaVencimiento = new StdClass();
                $dpFechaVencimiento->id = 'dpFechaVencimiento';
                $dpFechaVencimiento->name = 'dpFechaVencimiento';
                $dpFechaVencimiento->required = true;
                $dpFechaVencimiento->enDiv = true;
                $dpFechaVencimiento->label = 'Fecha de Vencimiento:';
                $dpFechaVencimiento->formato = 'fecha';

                $txtImporteLinea = new StdClass();
                $txtImporteLinea->id = 'txtImporteLinea';
                $txtImporteLinea->name = 'txtImporteLinea';
                $txtImporteLinea->required = true;
                $txtImporteLinea->enDiv = true;
                $txtImporteLinea->label = 'Importe:';
                $txtImporteLinea->formato = 'moneda';

                $this->setContenedor('divModalLineaCaptura');
                $this->mixHtml('
                    <div id="modalLineaCaptura" class="modal fade in"  role="dialog" data-backdrop="false">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close btn btn-outline-secondary btnCancelarCapLinea">&times;</button>
                                    <h4 class="modal-title">Datos de la Linea de Captura</h4>
                                </div>
                                <div class="modal-body">
                                    ' . $this->ponInput($txtLineaCaptura) . '
                                    <div class="row">
                                        <div class="col-md-4"> ' .
                    $this->ponInput($txtImporteLinea) . '
                                        </div>
                                    </div>
                                    ' . $this->ponInput($dpFechaEmision) .
                    $this->ponInput($dpFechaVencimiento) . '
                                </div>
                                <div class="modal-footer">
                                    <button id="btnGuardarLinea" type="button" class="btn btn-success">Aceptar</button>
                                    <button type="button" class="btn btn-outline-danger btnCancelarCapLinea">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                ');

                $this->mixScript('
                    $(".btnCancelarCapLinea").click(function () {
                        $("#modalLineaCaptura").modal("hide");
                    });

                    $("#btnGuardarLinea").click(function () {
                        hoy = new Date();
                        emision = $("#date-dpFechaEmision").datepicker("getDate");
                        vencimiento = $("#date-dpFechaVencimiento").datepicker("getDate");

                        if (emision > hoy) {
                            notify("La fecha de emision no puede ser posterior a la fecha actual.", "error");
                            return false;
                        }

                        if (emision > vencimiento) {
                            notify("La fecha de vencimiento no puede ser anterior a la fecha de emision.", "error");
                            return false;
                        }

                        importeLinea = unformatNumber($("#txtImporteLinea").val());

                        if (importeLinea >= ' . $this->prm->importe . ') {
                            comunica("' . $this->pantalla . '", {
                                metodo: "guardarLinea",
                                linea: $("#txtLineaCaptura").val(),
                                importe: importeLinea,
                                emision: $("#dpFechaEmision").val(),
                                vencimiento: $("#dpFechaVencimiento").val(),
                                ur: ' . $this->prm->ur . ',
                                gpo: "' . $this->prm->gpo . '",
                                clc: ' . $this->prm->clc . ',
                                ejercicio: ' . $this->prm->ejercicio . ',
                                importeClc: ' . $this->prm->importe . '
                            }, "modalLineaCaptura");
                        }
                        else {
                            notify("El importe de la CLC es mayor al de la Linea de Captura", "warning");
                        }
                    });

                    $("#modalLineaCaptura").modal("show");
                    $("#txtImporteLinea").focus();
                ');
            } else if ($row['elc_id'] == 1) {
                $saldoLinea = $this->getSaldoLinea($linea);
                $tmpPro = new stdClass();
                $tmpPro->gpo_id = $gpo;
                $tmpPro->ur_id = $ur;
                $tmpPro->clc_id = $clc;
                $tmpPro->ejercicio = $ejercicio;
                $tmpPro->lineaCaptura = $linea;
                $tmpPro->abc = 'c';
                TemporalPropuestaClc::abc($tmpPro);

                if ($saldoLinea < $this->prm->importe) {
                    $this->mixScript('
                        notify("La suma de los importes de las CLCs es mayor al importe de la Linea de Captura.", "error");
                    ');
                }
            } else {
                $this->mixScript('
                    notify("La linea de captura ya ha sido utilizada.", "error");
                ');
            }
        } else {
            $tmpPro = new stdClass();
            $tmpPro->gpo_id = $gpo;
            $tmpPro->ur_id = $ur;
            $tmpPro->clc_id = $clc;
            $tmpPro->ejercicio = $ejercicio;
            $tmpPro->lineaCaptura = '0';
            $tmpPro->abc = 'c';
            TemporalPropuestaClc::abc($tmpPro);
        }
    }

    private function guardarLinea()
    {
        $lc = new stdClass();
        $lc->lca_folio = 'FEDERAL';
        $lc->lineaCaptura = $this->prm->linea;
        $lc->fechaGeneracion = $this->date_to_mysqldate($this->prm->emision);
        $lc->fechaVencimiento = $this->date_to_mysqldate($this->prm->vencimiento);
        $lc->importe = $this->prm->importe;
        $lc->cuentaAbono = '0';
        $lc->ejr_id = APP_EJERCICIO; //Verificar que valor debe tomar
        $lc->tlc_id = 2;
        $lc->elc_id = 1; //Verificar que valor debe tomar
        $lc->idConvenioBanco = "0";
        $lc->abc = 'a';
        LineaCaptura::abc($lc);

        $tmpPro = new stdClass();
        $tmpPro->gpo_id = $this->prm->gpo;
        $tmpPro->ur_id = $this->prm->ur;
        $tmpPro->clc_id = $this->prm->clc;
        $tmpPro->ejercicio = $this->prm->ejercicio;
        $tmpPro->lineaCaptura = $this->prm->linea;
        $tmpPro->abc = 'c';
        TemporalPropuestaClc::abc($tmpPro);

        $this->mixScript('
            $("#modalLineaCaptura").modal("hide");

            hayLineasSinCapturar = false;
            $(".textLinea").each(function (){
                if ($(this).val().trim() == "") {
                    hayLineasSinCapturar = true;
                    return false;
                }
            })

            if (hayLineasSinCapturar) {
                alertify.confirm("", "Linea de Captura agregada exitosamente, ¿Desea asignarla a las CLCs de la tabla que no tengan registrada Linea de Captura?",
                        function(){
                            comunica("' . $this->pantalla . '", {
                                metodo: "verificarImportes",
                                anio: ' . $this->prm->ejercicio . ',
                                importe: ' . ($this->prm->importe - $this->prm->importeClc) . ',
                                linea: "' . $this->prm->linea . '"
                            });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Si", cancel:"No"});
            }
        ');
    }

    private function verificarImportes()
    {
        $this->conectar();
        $parametro = [
            'usr_id' => $this->usr_id,
        ];
        $this->setSQL('
            SELECT SUM(pro_importeClc) AS total FROM '.BD_TESORERITEM.'.tem_PropuestaClc
            WHERE pro_lineaCaptura = "0" AND usr_id = :usr_id;
        ');
        $this->consultar($parametro);
        $row = $this->extraer();
        $this->desconectar();
        if ($row['total'] != $this->prm->importe) {
            $this->mixScript('
                alertify.confirm("", "La suma de los importes de las CLCs es diferente al importe de la Linea de Captura, ¿Desea Continuar?",
                        function(){
                            comunica("' . $this->pantalla . '", {
                                metodo : "asignarLineaAVacios",
                                anio: ' . $this->prm->anio . ',
                                linea : "' . $this->prm->linea . '"
                            });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Si", cancel:"No"});
            ');
        } else {
            $this->asignarLineaAVacios();
        }
    }

    private function asignarLineaAVacios()
    {
        $this->conectar();
        $parametros = [
            'linea' => $this->prm->linea,
            'usr_id' => $this->usr_id, 
        ];
        $this->setSQL('
            UPDATE '.BD_TESORERITEM.'.tem_PropuestaClc SET pro_lineaCaptura = :linea
            WHERE pro_lineaCaptura = "0" AND usr_id = :usr_id;
        ');
        $this->consultar($parametros);
        $this->desconectar();
        $this->llenaTablaFederales();
    }

    private function getSaldoLinea($linea)
    {
        $ad = new AccesoDatos();
        $parametrodAd =[
            'linea' => $linea,
        ];
        $ad->conectar();
        $ad->setSQL('
            SELECT lca_importe FROM '.BD_TESORERIA.'.lca_LineaCaptura
            WHERE lca_lineaCaptura = :linea;
        ');
        $ad->consultar($parametrodAd);
        $row = $ad->extraer();

        $importeLinea = $row['lca_importe'];

        $parametroAd2 = [
            'linea' => $linea,
            'usr_id' => $this->usr_id,
        ];
        $ad->setSQL('
            SELECT SUM(pro_importeClc) AS importes FROM '.BD_TESORERITEM.'.tem_PropuestaClc
            WHERE pro_lineaCaptura = :linea AND usr_id = :usr_id;
        ');
        $ad->consultar($parametroAd2);
        $row = $ad->extraer();
        $importes = $row['importes'];
        $ad->desconectar();

        return ($importeLinea - $importes);
    }
}

if (isset($_SESSION[_S]['prm']['T']['4']['2']['1'])) {
    new ControladorMinGestion();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
