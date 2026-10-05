<?php
$desdeMenu = false;
require_once dirname(__FILE__) . "/../../lib/constantes.php";
if (!isset($_SESSION[_S])) {
    session_start();
    $desdeMenu = true;
}

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/Ped.php';
require_once dirname(__FILE__) . '/../../srv/PesEstrategia.php';
require_once dirname(__FILE__) . '/../../srv/ProgramaPresupuestario.php';
require_once dirname(__FILE__) . '/../../srv/CatFuncional.php';
require_once dirname(__FILE__) . '/../../srv/UbicacionGeografica.php';
require_once dirname(__FILE__) . '/../../ent/ObraActividad.php';
require_once dirname(__FILE__) . '/../../ent/HistorialMensajes.php';
require_once dirname(__FILE__) . '/../../ent/BitObraActividad.php';
require_once dirname(__FILE__) . '/../../srv/CatPep.php';
require_once dirname(__FILE__) . '/../../srv/Pin.php';
require_once dirname(__FILE__) . '/../../srv/PoliticasTransversales.php';

class ControladorActividades extends WebResponse
{
    public $clasAdm_gpo;
    public $clasAdm_ur;
    public $usr_id;
    public $pantalla;
    public $enPoa;

    public function __construct()
    {
        parent::__construct();
        $this->clasAdm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : null;
        $this->clasAdm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : null;
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->pantalla = 'mga/complementos/ControladorActividades.php';
        $this->enPoa = APP_EJERCICIO_ANTERIOR.'-12-31 23:59:59';

        $this->metodo = (isset($this->prm->metodoCtrlAct)) ? $this->prm->metodoCtrlAct : '';
        switch ($this->metodo) {
            case 'addModActividad':
                $this->addModActividad();
                break;
            case 'abcActividad':
                $this->abcActividad();
                break;
            case 'agregaCveFin':
                $this->agregaCveFin();
                break;
            case 'muestraCveFin':
                $this->muestraCveFin();
                break;
            case 'eliminaCveFin':
                $this->eliminaCveFin();
                break;
            case 'observacion':
                $this->mensaje();
                break;
            case 'verHistorial':
                $this->verHistorial();
                break;
            case 'muestraPedTransversal':
                $this->muestraPedTransversal();
                break;
            case 'agregaPolitica':
                $this->agregaPolitica();
                break;
            case 'eliminaPolitica':
                $this->eliminaPolitica();
                break;
            case 'muestraPlanInstitucional':
                $this->muestraPlanInstitucional();
                break;
        }
        $this->returnJson();
    }

    private function addModActividad()
    {
        //MARCOS: Si viene de la pantalla de previsiones, guardamos lo que venga en las variables de sesión
        $pp = (isset($this->prm->pp) ? 1 : 0);

        if ($pp == 1) {
            $_SESSION[_S]['clasAdm-prg'] = $this->prm->comboprg;
            $_SESSION[_S]['clasAdm-spr'] = $this->prm->combosp;
            $_SESSION[_S]['clasAdm-py'] = $this->prm->combopy;
            $_SESSION[_S]['clasAdm-oa'] = null;
            $_SESSION[_S]['clasAdm-epg'] = null;
        }

        $form = new stdClass();
        $form->id = 'datosGenerales';
        $form->controlador = "mga/complementos/ControladorActividades.php";
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'a';
        $form->datosExtra->cvesFin = '"{cveFinArray}"';
        $form->datosExtra->PoliticasT = '"{objetoEpt()}"';
        $form->datosExtra->metas = '"{objetoMetas()}"';
        $form->datosExtra->arrayPI = '"{ArraysObjetivosPi}"';
        $form->datosExtra->metodoCtrlAct = 'abcActividad';
        $form->datosExtra->pp = (string) $pp;
        $guardar = new stdClass();
        $guardar->value = 'Guardar';
        $guardar->form = 'datosGenerales';
        $guardar->id = 'guardaActividad';
        $guardar->type = 'submit';
        $guardar->color = 'azul';
        $oa = new ObraActividad();
        $oa->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $oa->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $oa->setPrg_id($_SESSION[_S]['clasAdm-prg']);
        $oa->setSpr_id($_SESSION[_S]['clasAdm-spr']);
        $oa->setPy_id($_SESSION[_S]['clasAdm-py']);
        if (!empty($this->prm->oa_id)) {
            $oa->setOa_id($this->prm->oa_id);
            $oa->select();
            $form->datosExtra->abc = 'c';
            $form->datosExtra->oa_id = $this->prm->oa_id;
            $form->datosExtra->eoa_id = $oa->getEoa_id();
            $form->datosExtra->epg_id = $oa->getEpg();
        }

        $form = new form($form);
        $prm = new stdClass();
        $prm->pp = $pp;
        $prm->comboprg = $this->prm->comboprg;
        $prm->combosp = $this->prm->combosp;
        //$pp indica si es un programa de previsión
        $oaResponse = $this->datosActividad($oa, $prm);

        $form->mixHtml($oaResponse->getHtml());
        $oaResponse->setContenedor('toolsPantalla.tools');
        $this->agregaTools($oaResponse->getHtml());
        $this->mixHtml($form->ponForm());
        $this->mixScript($oaResponse->getScript() . '
            bloquearClasAdmin();

            function objetoEpt(){
                var stringObj = "[";
                for(var index in ept) {
                    if(index>0)
                        stringObj += ",";
                    stringObj += "{";
                    var obj = ept[index];
                    c = 0;
                    for(var i in obj) {
                        if( c > 0 )
                            stringObj += ",";
                        stringObj += \'"\' + i + \'"\' + \':"\' + obj[i] + \'"\';
                        c++;
                    }
                        console.log();
                    stringObj += "}";
                }
                stringObj += "]";
                return stringObj;
            }

            function objetoMetas(){
                var stringMetas = "[";
                for(var index in metasArray) {
                    if(index>0)
                        stringMetas += ",";
                    stringMetas += "{";
                    var m = metasArray[index];
                    a = 0;
                    for(var i in m) {
                        if( a > 0 )
                            stringMetas += ",";
                        stringMetas += \'"\' + i + \'"\' + \':"\' + m[i] + \'"\';
                        a++;
                    }
                        console.log();
                    stringMetas += "}";
                }
                stringMetas += "]";
                return stringMetas;
            }

            document.getElementById("datosGenerales").getElementsByTagName("div")[5].setAttribute("id","cbue");
            $("#cbue").removeClass("col-md-9");
            $("#cbue").addClass("col-md-12");

        ');
    }

    private function abcActividad()
    {
        if ($this->prm->abc == 'a') {
            if ($this->prm->PoliticasT == '[]') {
                $this->mixScript('notify("Agrega una política transversal", "warning");');
                return false;
            } else {
                if (is_string($this->prm->PoliticasT)) {
                    $PoliticasT = json_decode($this->prm->PoliticasT);
                }

                $sumaPorcentaje = 0;
                foreach ($PoliticasT as $ept_id) {
                    $sumaPorcentaje += $ept_id->ept_porcentaje;
                }
                $diferencia = abs($sumaPorcentaje - 100);
                if ($sumaPorcentaje == 0) {
                    $this->mixScript('notify("El porcentaje debe ser mayor 0.00%", "warning");');
                    return false;
                } else if ($diferencia != 0) {
                    $this->mixScript('notify("Te hace falta ' . $diferencia . '%, debes de agregar otra política transversal.", "warning");');
                    return false;
                }
            }
        }

        $this->prm->eug_id = UbicacionGeografica::getEug_id();
        $this->prm->epe_id = $_SESSION[_S]['ped']->epe;
        $this->prm->epods_id = $_SESSION[_S]['ped']->epods_id;
        $this->prm->eps_id = $_SESSION[_S]['pes']->eps;
        $this->prm->efn_id = $_SESSION[_S]['catfuncional']->efn;
        $this->prm->epp_id = $_SESSION[_S]['pp']->epp;
        $this->prm->epc_id = $_SESSION[_S]['pep']->epc;
        $fechaActual = strtotime(date("d-m-Y H:i:00", time()));
        $fechaPoa = strtotime($this->enPoa);

        $this->selectEncProgramatico();
        switch ($this->prm->abc) {
            case 'a':
                $this->prm->nombre = preg_replace("/\r\n+|\r+|\n+|\t+/i", "", $this->prm->nombre);
                $mensaje = ' guardada exitosamente';
                $observacion = ' CAPTURADA CON PED:' . $this->prm->epe_id . ',ODS:' . $this->prm->epods_id . ',PES:' . $this->prm->eps_id . ',FUNCIONAL:' . $this->prm->efn_id . ',SECTORIAL:' . $this->prm->epc_id . ',PROGRAMA PRESUPUESTARIO:' . $this->prm->epp_id . ',';
                $this->prm->eoa_id = 1;
                if ($_SESSION[_S]['clasAdm-prg'] >= 100 and $_SESSION[_S]['clasAdm-prg'] <= 899) {
                    $this->prm->eop_id = ($fechaActual <= $fechaPoa) ? 1 : 5;
                } else {
                    $this->prm->eop_id = 5;
                }
                break;
            case 'c':
                $this->prm->nombre = preg_replace("/\r\n+|\r+|\n+|\t+/i", "", $this->prm->nombre);
                $observacion = ' MODIFICADO CON PED:' . $this->prm->epe_id . ',ODS:' . $this->prm->epods_id . ',PES:' . $this->prm->eps_id . ',FUNCIONAL:' . $this->prm->efn_id . ',SECTORIAL:' . $this->prm->epc_id . ',PROGRAMA PRESUPUESTARIO:' . $this->prm->epp_id . ',';
                $mensaje = ' modificada exitosamente';
                $this->prm->eoa_id = $this->prm->eoa_id;
                break;
            case 'b':
                $mensaje = ' eliminada exitosamente';
                $observacion = ' ELIMINADA DESDE PANTALLA M_4_2';
                break;
        }

        if ($mensaje != '') {
            $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'];
            $this->prm->mensaje = $observacion;
            $oa_id = ObraActividad::abc($this->prm);
            $this->prm->oa_id = $oa_id;

            $this->prm->oa_id = ($this->prm->abc == 'a') ? $oa_id : $this->prm->oa_id;
            $tipo = ((isset($this->prm->toa_id) && $this->prm->toa_id == 1) ? 'Actividad ' : "Obra ");
            $this->mixScript('notify("' . $tipo . $this->prm->oa_id . $mensaje . '", "success");');
            $this->mixScript('
                comunica("mga/complementos/ControladorPrevisiones.php",{
                    metodo:"ponTablaActividades",
                    prg_id:' . $_SESSION[_S]['clasAdm-prg'] . ',
                    spr_id:' . $_SESSION[_S]['clasAdm-spr'] . '
                });
            ');
        }
    }

    private function agregaCveFin()
    {
        $and = "";
        $parametros [':ecf'] = $this->prm->ecf_id;
        if (date('Y') == APP_EJERCICIO_ANTERIOR) {
            $and = "AND cfi_anio = :anio";
            $parametros[':anio'] = substr(APP_EJERCICIO, -2);
        }
        $a = new AccesoDatos();
        $a->conectar();
        $a->setSql('
            SELECT DISTINCT cfi_clave, cfi_nombre, ecf_id
            FROM sinpres.cat_ClaveFinanciamiento'.APP_EJERCICIO.'
            JOIN enc_ClaveFinanciamiento e USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE ecf_id = :ecf
            '.$and.'
        ');
        $a->consultar($parametros);
        $a->desconectar();
        $ecvefin = $a->extraer();
        unset($this->prm->ecf_id);

        $this->prm->ecf_id = $ecvefin['ecf_id'];
        $boton = new stdClass();
        $boton->title = 'Eliminar clave';
        $boton->id = 'cve_' . $this->prm->ecf_id;
        $boton->color = 'rojo';
        $boton->icon = 'trash-can';
        $fila = '
            $("#filasCvesFin").append(
                $("<tr>").attr("id","cvefinFila-' . $this->prm->ecf_id . '")
                    .html("<td>' . $ecvefin['cfi_clave'] . '</td>")
                    .append("<td>' . $ecvefin['cfi_nombre'] . '</td>")
                    .append("<td>' . addslashes($this->ponBoton($boton)) . '</td>")
            );
        ';
        $this->mixScript('$("#myModal").modal().remove();');
        if (!empty($this->prm->epg_id)) {
            $this->conectar();
            $this->setSql('
                SELECT  ecf_id FROM poa_OrigenRecurso
                WHERE ecf_id= ? 
                AND epg_id= ?;
            ');
            $this->consultar([$this->prm->ecf_id, $this->prm->epg_id]);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                $this->mixScript('notify("La clave ya esta agregada","success");');
            } else {
                $params = [
                    ':usr' => $_SESSION[_S]['usr_id'],
                    ':observacion' => "Registrar desde la aplicación MGA-Complementos-Alta de previsiones"
                ];
                $this->conectar();
                $this->setSql('SET @bit_observacion = :observacion, @usr_ip=' . BD_SINPRES . '.fn_get_ip(), @usr_id= :usr');
                $this->consultar($params);

                $this->setSql('
                    INSERT INTO poa_OrigenRecurso(gpo_id, ur_id, epg_id, ecf_id,ore_folioModif,ore_folioLib,ore_oficioLib,ore_oficioAut,ore_idObra,usr_id)
                    VALUES(:gpo_id, :ur_id, :epg_id, :ecf_id, 0, 0, "", "", "", :usr_id);
                ');
                $params = [
                    ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur_id' => $_SESSION[_S]['clasAdm-ur'],
                    ':epg_id' => $this->prm->epg_id,
                    ':ecf_id' => $this->prm->ecf_id,
                    ':usr_id' => $_SESSION[_S]['usr_id']
                ];
                $this->consultar($params);
                $this->desconectar();
                $this->mixScript('$("#myModal").modal().remove();');

                $datosOa = new ObraActividad($this->prm);

                $this->selectEncProgramatico();
                $this->prm->nombre = $datosOa->getNombre();
                $this->prm->observacion = $datosOa->getObservacion();
                $this->prm->ue_id = $datosOa->getUe_id();
                $this->prm->anticipo = $datosOa->getAnticipo();
                $this->prm->anticipo2025 = $datosOa->getAnticipo2025();
                $this->prm->amortizacion = $datosOa->getAmortizacion();
                $this->prm->eug_id = $datosOa->getEug_id();
                $this->prm->eoa_id = $datosOa->getEoa_id();
                $this->prm->toa_id = $datosOa->getToa_id();
                $this->prm->pbf_id = $datosOa->getPbf_id();
                $this->prm->sts_id = $datosOa->getSts_id();
                $this->registrarBitacora(': CveFin ' . $this->prm->ecf_id . ' Agregada');
                $this->mixScript('
                    ' . $fila . '
                    $("#cve_' . $this->prm->ecf_id . '").click(function(){
                        alertify.confirm("ELIMINAR CLAVE DE FINANCIAMIENTO", "¿Confirma que desea eliminar la clave de financiamiento?", function(){
                            $("#cvefinFila-' . $this->prm->ecf_id . '").remove();
                            comunica("poa/ControladorActividades.php",{
                                metodoCtrlAct:"eliminaCveFin",
                                ecf_id:"' . $this->prm->ecf_id . '",
                                epg_id:' . $this->prm->epg_id . '
                            });
                        }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    });
                ');
            }
        } else {
            $this->mixScript('
                if(cveFinArray.indexOf("' . $this->prm->ecf_id . '") < 0){
                    cveFinArray.push("' . $this->prm->ecf_id . '");
                    ' . $fila . '
                    $("#cve_' . $this->prm->ecf_id . '").click(function(){
                        $("#cvefinFila-' . $this->prm->ecf_id . '").remove();
                        cveFinArray.splice( cveFinArray.indexOf("' . $this->prm->ecf_id . '"),1);
                    });
                }else notify("La clave ya esta agregada","success");
            ');
        }
    }

    private function muestraCveFin()
    {
        //Se agrego porque marcaba que un objeto se estaba creando vacio
        if (!isset($_SESSION[_S]['cvefin'])) {
            $_SESSION[_S]['cvefin'] = new stdClass();
        }
        $epg_id = (isset($this->prm->epg_id)) ? ',epg_id:' . $this->prm->epg_id . '' : "";
        $this->setContenedor('divContenedorGeneralModal');
        $texto = (!isset($this->prm->lectura)) ? 'Clave de Financiamiento' : 'Clave de Financiamiento';

        $and = "";
        $parametros = array();        
        if (date('Y') == APP_EJERCICIO_ANTERIOR) {
            $and = "AND cfi_anio = :anio";
            $parametros[':anio'] = substr(APP_EJERCICIO, -2);
        }

        $comboCF = new stdClass();
        $comboCF->label = 'Clave de Financiamiento ';
        $comboCF->id = 'ecf_id';
        $comboCF->class = 'cmbClaveFinanciamiento';
        $comboCF->camposVal = 'ecf_id';
        $comboCF->required = true;
        $comboCF->sql = '
            SELECT DISTINCT cfi_clave, cfi_nombre, ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            JOIN enc_ClaveFinanciamiento e USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE e.sts_id =  1 ' . $and . '
            AND CASE WHEN year(now())<' . APP_EJERCICIO . ' THEN
                ecf_poa = 1
                ELSE
                ecf_poa = ecf_poa
            END;
        ';
        if ($and != "") {
            $comboCF->params = $parametros;
        }
        $comboCF->search = true;
        $comboCF->comoAtr = 'ecf_id';
        $this->recargaCombo($comboCF);

        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">' . $texto . '</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formCveFin">
                        ' . $this->ponCombo($comboCF) . '
                        </form>
                    </div>
                    <div class="modal-footer">';
        if (!isset($this->prm->lectura)) {
            $modal .= '<button type="submit" class="btn btn-outline-success" id="btnAceptarCvfFin" form="">Aceptar</button>';
        }

        $modal .= '<button type="button" class="btn btn-outline-danger" id="cancelaPt" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);

        $this->mixScript('
            $("#myModal").modal().show();
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            })
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#cancelaPt").click(function(){ $("#myModal").modal().remove(); });


            $("#btnAceptarCvfFin").click(function(){
                valido = ( $("#ecf_id").val() != "null" );
                ok = true;

                $("#formCveFin").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                });
                if(valido && ok){
                    callbackComunica = function(){
                        $("#modalClaveFinanciamiento").empty();
                    };
                    comunica("mga/complementos/ControladorActividades.php",{
                        metodoCtrlAct: "agregaCveFin",
                        ecf_id:$("#ecf_id").val()
                        ' . $epg_id . '
                    });
                    ' . (!isset($this->prm->epg_id) ? '$("#myModal").modal().remove();' : "") . '
                }
            });
        ');
    }

    private function eliminaCveFin()
    {
        $_SESSION[_S]['clasAdm-oa'] = null;
        $this->setSql('
            SELECT DISTINCT ecf_id 
            FROM poa_OrigenRecurso 
            WHERE epg_id = ? ;
        ');
        $this->conectar();
        $this->consultar([$this->prm->epg_id]);
        $this->desconectar();

        if ($this->getNumeroRegistros() > 1) {
            $this->conectar();
            $this->setSql('
                SELECT '.BD_SINPRES.'.fn_tableExists("presupuesto","'.BD_CTRL.'") AS existe,'.BD_SINPRES.'.fn_tableExists("poa_Alimentacion","'.BD_CTRL.'") AS existe2,'.BD_SINPRES.'.fn_tableExists("poa_OtrasPartidas","'.BD_CTRL.'") AS existe3,'.BD_SINPRES.'.fn_tableExists("poa_VehiculoMaquina","'.BD_CTRL.'") AS existe4;
            ');
            $this->consultar();
            $tabla = $this->extraer();

            if ($tabla['existe'] == 1 && $tabla['existe2'] == 1 && $tabla['existe3'] == 1) {
                $vehiculoMaquina = "";
                if ($tabla['existe4'] == 1) {
                    $vehiculoMaquina = "
                    UNION SELECT count(gpo_id) AS registros FROM poa_VehiculoMaquina
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id ";
                }
                $this->setSql('
                    SELECT count(gpo_id) AS registros FROM poa_Alimentacion
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    UNION SELECT count(gpo_id) AS registros FROM poa_Arrendamiento
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    UNION SELECT count(gpo_id) AS registros FROM poa_OtrasPartidas
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    UNION SELECT count(gpo_id) AS registros FROM poa_SeguroFianza
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    UNION SELECT  count(gpo_id) AS registros FROM poa_ServiciosPersonales
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    UNION SELECT count(gpo_id) AS registros FROM poa_ServicioVigilancia
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    UNION SELECT count(gpo_id) AS registros FROM poa_TelefoniaCelular
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    ' . $vehiculoMaquina . '
                    UNION SELECT count(gpo_id) AS registros FROM presupuesto
                    WHERE epg_id = :epg_id AND ecf_id = :ecf_id
                    ORDER BY registros DESC
                    ;
                ');
                $params = [
                    ':epg_id' => $this->prm->epg_id, 
                    ':ecf_id' => $this->prm->ecf_id
                ];
                $this->consultar($params);
                $this->desconectar();
                $totalRegistros = $this->extraer();
                $numRegistros = $totalRegistros['registros'];
                if ($numRegistros == 0) {
                    $this->mixScript(' $("#cvefinFila-' . $this->prm->ecf_id . '").remove(); ');
                    $this->conectar();
                    $this->setSql('
                        SET
                        @usr_ip='.BD_SINPRES.'.fn_get_ip(),
                        @bit_observacion=?,
                        @usr_id=? 
                    ');
                    $params = ["CLAVE DE FINANANCIAMIENTO ELIMINADA DESDE PANTALLA M_4_2",$_SESSION[_S]['usr_id']];
                    $this->consultar($params);
                    $this->setSql('
                        DELETE FROM poa_OrigenRecurso 
                        WHERE epg_id = ?
                        AND ecf_id= ?;
                    ');

                    $this->consultar([$this->prm->epg_id, $this->prm->ecf_id]);
                    $this->desconectar();
                    $datosOa = new ObraActividad($this->prm);
                    $this->selectEncProgramatico();
                    $this->prm->nombre = $datosOa->getNombre();
                    $this->prm->observacion = $datosOa->getObservacion();
                    $this->prm->ue_id = $datosOa->getUe_id();
                    $this->prm->anticipo = $datosOa->getAnticipo();
                    $this->prm->anticipo2025 = $datosOa->getAnticipo2025();
                    $this->prm->amortizacion = $datosOa->getAmortizacion();
                    $this->prm->eug_id = $datosOa->getEug_id();
                    $this->prm->eoa_id = $datosOa->getEoa_id();
                    $this->prm->toa_id = $datosOa->getToa_id();
                    $this->prm->pbf_id = $datosOa->getPbf_id();
                    $this->prm->sts_id = $datosOa->getSts_id();
                    $this->registrarBitacora(': CveFin ' . $this->prm->ecf_id . ' Eliminada');
                } else {
                    $this->mixScript('notify("No se puede eliminar la clave de financiamiento ya que tiene cedulas capturadas","warning");');
                }
            } else {
                $this->setSql('
                    SET 
                    @usr_ip = '.BD_SINPRES.'.fn_get_ip(),
                    @bit_observacion = "CLAVE DE FINANANCIAMIENTO ELIMINADA DESDE PANTALLA M_4_2",
                    @usr_id = ?;
                ');
                $this->consultar([$_SESSION[_S]['usr_id']]);
                $this->mixScript(' $("#cvefinFila-' . $this->prm->ecf_id . '").remove(); ');
                $this->setSql('
                    DELETE FROM poa_OrigenRecurso
                    WHERE epg_id = ?
                    AND ecf_id = ?;
                ');
                $this->consultar([$this->prm->epg_id, $this->prm->ecf_id]);
                $this->desconectar();
                $datosOa = new ObraActividad($this->prm);
                $this->selectEncProgramatico();
                $this->prm->nombre = $datosOa->getNombre();
                $this->prm->observacion = $datosOa->getObservacion();
                $this->prm->ue_id = $datosOa->getUe_id();
                $this->prm->anticipo = $datosOa->getAnticipo();
                $this->prm->anticipo2025 = $datosOa->getAnticipo2025();
                $this->prm->amortizacion = $datosOa->getAmortizacion();
                $this->prm->eug_id = $datosOa->getEug_id();
                $this->prm->eoa_id = $datosOa->getEoa_id();
                $this->prm->toa_id = $datosOa->getToa_id();
                $this->prm->pbf_id = $datosOa->getPbf_id();
                $this->prm->sts_id = $datosOa->getSts_id();
                $this->registrarBitacora(': CveFin ' . $this->prm->ecf_id . ' Eliminada');
            }
        } else {
            $this->mixScript('notify("No se puede quedar sin Clave de Financiamiento","warning");');
        }
    }

    private function selectEncProgramatico()
    {
        if (!empty($this->prm->epg_id)) {
            $this->setSql('
                SELECT  gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,eoa_id
                FROM enc_Programatico
                INNER JOIN cat_ObraActividad  USING ( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                WHERE  epg_id = ?;
            ');
            $this->conectar();
            $this->consultar([$this->prm->epg_id]);
            $this->desconectar();
            $datos = $this->extraer();
            if ($this->getNumeroRegistros() > 0) {
                $this->prm->gpo_id = $datos['gpo_id'];
                $this->prm->ur_id = $datos['ur_id'];
                $this->prm->prg_id = $datos['prg_id'];
                $this->prm->spr_id = $datos['spr_id'];
                $this->prm->py_id = $datos['py_id'];
                $this->prm->oa_id = $datos['oa_id'];
                $this->prm->eoa_id = $datos['eoa_id'];
            }
        } else {
            $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
            $this->prm->prg_id = $_SESSION[_S]['clasAdm-prg'];
            $this->prm->spr_id = $_SESSION[_S]['clasAdm-spr'];
            $this->prm->py_id = $_SESSION[_S]['clasAdm-py'];
            $this->prm->eoa_id = 1;

        }
    }

    private function registrarBitacora($observacion)
    {
        $this->prm->observacion = 'Actividad ' . $observacion;
        $this->prm->abc = 'a';
        BitObraActividad::abc($this->prm);
    }

    public static function datosActividad($actividad, $parametros)
    {
        //Definimos a qué pantalla regresaremos, dependiendo de cómo llegamos aquí
        $pantallaRetorno = "mga/complementos/ControladorActividades";
        $pp = $parametros->pp;
        $comboprg = (isset($parametros->comboprg) ? $parametros->comboprg : "");
        $combosp = (isset($parametros->combosp) ? $parametros->combosp : "");
        if (isset($parametros->pp)) {
            if ($parametros->pp == 1) {
                $pantallaRetorno = "mga/complementos/ControladorPrevisiones.php";
            }
        }

        $prm = (func_num_args() > 1) ? func_get_arg(1) : new stdClass();

        $filasCveFin = '';
        $prmNewCveFin = '';
        $guardar = '';
        $regresar = '';
        $botonDel = '';
        $botonDelete = '';
        $botonClickValidarPes = '';
        $botonClickValidaEgresos = '';
        $botonClicHistorial = '';
        $botonHistorial = '';
        $botonClicHistorial2 = '';
        $botonHistorial2 = '';
        $botonClicGuardaObs = '';
        $botonGuardarObservacion = '';
        $validarEgresos = '';
        $guardarPes = '';
        $validaPes = '';
        $epg = $actividad->getEpg();
        $ug = new stdClass();
        $catFun = new stdClass();
        $da = new WebResponse();
        $nombre = new stdClass();
        $actividadMIR = new stdClass();

        $tipo = new stdClass();
        $tipo->sql = '';
        $actEsp = new stdClass();
        $actEsp->sql = '';
        $hombres = new stdClass();
        $mujeres = new stdClass();
        $total = new stdClass();
        $ue = new stdClass();
        $ue->horizontal = 3;
        $ue->ue_id = $actividad->getUe_id();
        $lectura = (
            (($actividad->getToa_id() != "" && $actividad->getToa_id() != 1) || $_SESSION[_S]['nvj_id'] > 2) ||
            ((!is_null($_SESSION[_S]['clasAdm-py']) && !empty($_SESSION[_S]['clasAdm-py']) && !isset($_SESSION[_S]['prm']['A']['2']['2'])) ||
                (!isset($_SESSION[_S]['prm']['A']['2']['1']) && is_null($_SESSION[_S]['clasAdm-py']))
            ) || ($_SESSION[_S]['ssc_id'] == 2) || $_SESSION[_S]['nvj_id'] >= 3 ||
            (
                ($actividad->getEoa_id() != 0 &&
                    $actividad->getEoa_id() != 1 &&
                    $actividad->getEoa_id() != 3
                )
            )
        );

        if ($pp == 1) {
            $lectura = false;
        }

        $params = [':epg' => $epg];
        $campoImporte = $_SESSION[_S]['nvj_id'] >= 5 && $_SESSION[_S]['ssc_id'] == 2 && $_SESSION[_S]['dir_id'] == 4;
        if ($epg * 1 != 0) {

            $da->setSql('
                SELECT ' . BD_SINPRES . '.fn_tableExists("poa_Alimentacion","' . BD_CTRL . '") AS existe, ' . BD_SINPRES . '.fn_tableExists("poa_OtrasPartidas","' . BD_CTRL . '") AS existe3, ' . BD_SINPRES . '.fn_tableExists("poa_VehiculoMaquina","' . BD_CTRL . '") AS existe4;
            ');
            $da->conectar();
            $da->consultar();
            $d = $da->extraer();

            if ($d['existe'] == 1 && $d['existe3'] == 1) {
                $vehiculoMaquina = "";
                if ($d['existe4'] == 1) {
                    $vehiculoMaquina = "
                    UNION SELECT count(gpo_id) as registros FROM poa_VehiculoMaquina WHERE epg_id = :epg ";
                }
                $da->setSql("
                    SELECT count(gpo_id) as registros FROM poa_Alimentacion WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_Arrendamiento WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_OtrasPartidas WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_SeguroFianza WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_ServiciosPersonales WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_ServicioVigilancia WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_TelefoniaCelular WHERE epg_id = :epg
                    " . $vehiculoMaquina . "
                    UNION SELECT count(gpo_id) as registros FROM presupuesto WHERE epg_id = :epg
                    ORDER BY registros DESC;
                ");
                $da->conectar();
                $da->consultar($params);
                $totalRegistros = $da->extraer();
                $numeroRegistro = $totalRegistros['registros'];
                $da->desconectar();

                $con = new AccesoDatos();
                $con->setSql('
                    SELECT ' . BD_SINPRES . '.fn_tableExists("poa_Alimentacion","' . BD_CTRL . '") AS existe, ' . BD_SINPRES . '.fn_tableExists("poa_OtrasPartidas","' . BD_CTRL . '") AS existe3;
                ');
                $con->conectar();
                $con->consultar();
                $d = $con->extraer();
                if ($d['existe'] == 1 && $d['existe3'] == 1) {
                    $eliminarSi = (($numeroRegistro == 0) ? true : false);
                } else {
                    $eliminarSi = true;
                }

                $con->desconectar();
            } else {
                $eliminarSi = true;
            }
        }

        $da->setSql('
            SELECT c.ecf_id, CONCAT(d.cff_id, d.fte_id, d.sft_id, d.ori_id, d.sor_id, d.cfi_id, d.cfi_anio) cveFin ,
            d.cfi_nombre, b.ore_idObra, b.ore_oficioAut
            FROM poa_OrigenRecurso AS b
            JOIN enc_ClaveFinanciamiento AS c USING (ecf_id)
            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' as d USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE b.epg_id = :epg
            GROUP BY b.epg_id, c.ecf_id,d.cfi_nombre,b.ore_idObra,b.ore_oficioAut;
        ');

        $da->conectar();
        $da->consultar($params);
        $da->desconectar();

        while ($r = $da->extraer()) {
            $boton = new stdClass();
            $boton->title = 'Eliminar clave';
            $boton->id = 'cve_' . $r['ecf_id'];
            $boton->color = 'rojo';
            $boton->icon = 'trash-can';
            $boton->click = '
                alertify.confirm(
                    "<h5> ELIMINAR CLAVE DE FINANCIAMIENTO </h5>",
                    "¿Confirma que desea eliminar la clave de financiamiento?",
                    function(){
                        comunica("mga/complementos/ControladorActividades.php",{
                            metodoCtrlAct:"eliminaCveFin",
                            epg_id:' . ($epg * 1) . ',
                            ecf_id:' . ($r['ecf_id'] * 1) . '
                            });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            ';
            $boton = ($lectura) ? '' : $da->ponBoton($boton);
            if ($campoImporte) {
                $boton = '$ ' . number_format($r['ecf_id'], 2);
            }
            $filasCveFin .= '<tr id="cvefinFila-' . ($r['ecf_id'] * 1) . '"><td>' . $r['cveFin'] . '</td><td>' . $r['cfi_nombre'] . '</td>' . (($actividad->getToa_id() == 2) ? '<td>' . $r['ore_oficioAut'] . '</td><td>' . $r['ore_idObra'] . '</td>' : '') . '<td>' . $boton . '</td>';
        }

        $botonClickValidar = $botonClickRechazar = $clickEliminarAct = $clickRegresarAct = '';

        if ($lectura) {
            $catFun->lectura = true;
            $tipo->sql = "WHERE toa_id = :toa;";
            $tipo->params = [':toa' => $actividad->getToa_id()];
            $actEsp->sql = "WHERE ae_id = :ae_id;";
            $actEsp->params = [':ae_id' => $actividad->getAe_id()];
            $actividadMIR->readonly = true;
            $hombres->readonly = true;
            $mujeres->readonly = true;
            $total->readonly = true;
            $botonCveFin = '';
            $ug->lectura = true;
            $ue->lectura = true;
        } else {
            $botonCveFin = new stdClass();
            $botonCveFin->id = 'cveFinPlus';
            $botonCveFin->color = 'azul';
            $botonCveFin->icon = 'plus';
            $botonCveFin->title = 'Agregar clave';
            $botonCveFin->class = 'me-2';
            $botonCveFin->click = 'comunica("mga/complementos/ControladorActividades.php",{metodoCtrlAct:"muestraCveFin"' . $prmNewCveFin . ',epg_id:' . ($epg * 1) . '});';
            $botonCveFin = $da->ponBoton($botonCveFin);

            if ($epg * 1 != 0) {
                $botonDel = new stdClass();
                $botonDel->value = ' Eliminar';
                $botonDel->id = 'eliminaActividad';
                $botonDel->color = 'rojo';
                $botonDel->icon = 'trash';
                $botonDel->class = 'me-2';
                $callbackComunica = (empty($prm->regresar)) ? 'console.info("Actividad Eliminada");' : $prm->regresar;

                $queEliminaActividad = '¿Confirma que desar eliminar la actividad ' . $actividad->getOa_id() . '?';
                $clickEliminarAct = '
                    alertify.confirm(
                        "<h5> ELIMINAR ACTIVIDAD </h5>",
                        "' . $queEliminaActividad . '",
                        function(){
                            comunica("mga/complementos/ControladorActividades.php",{
                                    metodoCtrlAct:"abcActividad",
                                    pp: ' . $pp . ',
                                    epg_id:' . ($epg * 1) . ',
                                    abc:"b" ' . ((empty($prm->regresar)) ? '' : ',noMain:true') . '
                            });
                                ' . $callbackComunica . '
                        },
                        function(){},
                    ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});

                ';
                $botonDelete = (($eliminarSi) ? '<button id="eliminaActividad" type="button" class="btn btn-outline-danger btn-sm me-1" title="Eliminar"><i class="fa-solid fa-trash me-2"></i>Eliminar</button>' : "");
            }

            $guardar = '<button id="guardaActividad" type="submit" form="datosGenerales" class="btn btn-outline-success me-1" title="Guardar"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>';
        }

        $boton = new stdClass();
        $boton->value = ' Regresar';
        $boton->icon = 'circle-left';
        $boton->id = 'regresarAct';

        //FUNCIONALIDAD PARA PANTALLA DE PREVISIONES
        if ($pp == 1) {
            $clickRegresarAct = 'comunica("' . $pantallaRetorno . '",{metodo:"ponTablaActividades", prg_id:' . $comboprg . ',spr_id:' . $combosp . ' });';
        } else {
            $clickRegresarAct = (empty($prm->regresar)) ? 'comunica("mga/complementos/ControladorActividades.php",{metodoCtrlAct:"mainBack"});' : $prm->regresar;
        }
        $regresar = '<button id="regresarAct" type="button" class="btn btn-outline-secondary me-1" title="Regresar"><i class="fa fa-circle-left me-2"></i>Regresar</button> ';

        //Si se esta agregando/modificando desde el Modulo de Alta de Previsiones debe permitir editar el nombre de la Actividad
        if ($pp != 1) {
            //Verifica si es usuario de presupuesto o planeacion y/o el estado de la obra actividad, para bloquear campos
            if ((!empty($_SESSION[_S]['ssc_id']) && ($_SESSION[_S]['ssc_id']) == 2 || ($_SESSION[_S]['ssc_id']) == 3) ||
                ($actividad->getEoa_id() == 4 || $actividad->getEoa_id() == 5 || $actividad->getEoa_id() == 2 || $actividad->getEoa_id() == 2)
            ) {
                $nombre->disabled = true;
                $hombres->disabled = true;
                $mujeres->disabled = true;
            }
        }

        $nombre->id = 'nombre';
        $nombre->label = 'Nombre';
        $nombre->maxlength = 600;
        $nombre->required = true;
        $nombre->placeholder = 'Escriba la descripcion';
        $nombre->text = $actividad->getNombre();
        $nombre = $da->ponTextarea($nombre);

        $seleccion = '';

        $tipo->id = 'toa_id';
        $tipo->name = 'toa_id';

        //SI ES PROYECTO, ENTONCES ES DE PLANEACIÓN SINO DE EGRESOS
        if (!is_null($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] != 00) {
            $tipo->sql = 'SELECT  toa_id, toa_nombre
                FROM(
                    SELECT 1 toa_id,"SUB. EGRESOS" toa_nombre
                    UNION
                    SELECT 2 toa_id,"SUB. PLANEACIÓN" toa_nombre
                )noTipo ' . $tipo->sql;
            $seleccion = 2;
            $tipo->disabled = true;
        } else if (!is_null($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] == 00) {
            $tipo->sql = 'SELECT  toa_id, toa_nombre
                FROM(
                    SELECT 1 toa_id,"SUB. PRESUPUESTO" toa_nombre
                    ' . (($pp == 1) ? ' UNION SELECT 2 toa_id,"SUB. PLANEACIÓN" toa_nombre ' : "") . '
                )noTipo ' . $tipo->sql;
        }

        $seleccion = ($pp == 1) ? 1 : $seleccion;

        #TIPO DE PROYECTO O ACTIVIDAD 1 - GASTOS OPERACIÓN, 2 INVERSIÓN
        $tipo->required = true;
        $tipo->camposText = 'toa_nombre';
        $tipo->selecciona = (($actividad->getToa_id() != '') ? $actividad->getToa_id() : $seleccion);
        $tipo->enDiv = true;
        $tipo = $da->ponRadio($tipo);

        $hombres->id = 'hombres';
        $hombres->formato = 'entero';
        $hombres->required = true;
        $hombres->label = 'Hombres';
        $hombres->maxlength = 10;
        $hombres->enDiv = true;
        $hombres->value = $actividad->getHombres() * 1;
        $hombres->blur = '
            $("#total").val( ($("#mujeres").val() *1 ) + ( $(this).val() * 1) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $hombres = $da->ponInput($hombres);
        $mujeres->id = 'mujeres';
        $mujeres->formato = 'entero';
        $mujeres->required = true;
        $mujeres->label = 'Mujeres';
        $mujeres->maxlength = 10;
        $mujeres->enDiv = true;
        $mujeres->value = $actividad->getMujeres() * 1;
        $mujeres->blur = '
            $("#total").val( ( $("#hombres").val() *1 ) + ( $(this).val() * 1 ) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $mujeres = $da->ponInput($mujeres);
        $total->id = 'total';
        $total->formato = 'entero';
        $total->required = true;
        $total->label = 'Total';
        $total->enDiv = true;
        $total->horizontal = 3;
        $total->value = $actividad->getTotal() * 1;
        if ($total->value == 0) {
            $total->value = ($actividad->getMujeres() * 1) + ($actividad->getHombres() * 1);
        }

        $total->blur = '
            if( $(this).val() * 1 == 0 ){
                $("#mujeres,#hombres").removeAttr("readonly");
            }else if($("#mujeres").val()!=0 || $("#hombres").val()!=0){
                if($("#total").val()!= ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1)){
                    $("#mujeres").val(0);
                    $("#hombres").val(0);
                    $("#mujeres,#hombres").attr("readonly",true);
                }else{
                    $("#total").val( ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1) );
                    $("#total").attr("readonly",true);
                    $("#mujeres,#hombres").removeAttr("readonly");
                }
            }else{
                $("#mujeres").val(0);
                $("#hombres").val(0);
                $("#mujeres,#hombres").attr("readonly",true);
            }
        ';
        $total = $da->ponInput($total);
        $catFun->fin_id = $actividad->getFin_id();
        $catFun->fun_id = $actividad->getFun_id();
        $catFun->sfu_id = $actividad->getSfu_id();

        #LA UBICACIÓN GEOGRAFICA PREDETERMINADA PARA LAS ACTIVIDADES DE PREVISIÓN ES : ESTATAL
        if ($pp == 1) {
            $da->setSql('
                SELECT eug_id
                FROM enc_UbicacionGeografica 
                WHERE reg_id = 9;
            ');
            $da->conectar();
            $da->consultar();
            $ubica = $da->extraer();
            $da->desconectar();
            $ug->eug_id = $ubica['eug_id'];
            $ug->lectura = true;
        } else {
            $ug->eug_id = $actividad->getEug_id();
        }

        $datoEpg_id = (($epg * 1 != 0) ? ',epg_id:' . ($epg * 1) . '' : "");
        $filasPoliticasT = '';

        #POLITICAS TRANSVERSALES
        $botonPedt = new stdClass();
        $botonPedt->id = 'botonPedPlus';
        $botonPedt->color = 'azul';
        $botonPedt->icon = 'plus';
        $botonPedt->title = 'Agregar';
        $botonPedt->class = 'me-2';
        $botonPedt->click = '
            comunica("mga/complementos/ControladorActividades.php",{
                metodoCtrlAct:"muestraPedTransversal"' . $datoEpg_id . ' 
            });
        ';

        $botonPedTrans = '';

        #PLAN INSTITUCIONAL
        $botonPI = new stdClass();
        $botonPI->id = 'botonPIPlus';
        $botonPI->color = 'azul';
        $botonPI->icon = 'plus';
        $botonPI->title = 'Agregar';
        $botonPI->class = 'me-2';
        $botonPI->click = '
            comunica("mga/complementos/ControladorActividades.php",{
                metodoCtrlAct:"muestraPlanInstitucional"' . $datoEpg_id . '
            });
        ';

        $filasPlanI = '';
        $botonPlanInsticional = '';

        #PES
        $pes = new stdClass();
        #PED
        $ped = new stdClass();
        $ped->cargaEncuadres = true;
        #PROGRAMA PRESUPUESTARIO
        $prpr = new stdClass();
        #PLAN ESPECIAL
        $pep = new stdClass();
        $informativo = false;
        $botonEliminar = '';
        if ($_SESSION[_S]['clasAdm-prg'] >= 100 and $_SESSION[_S]['clasAdm-prg'] <= 899) { //100-899 Programas orientados a resultados
            #Se mostrará de manera informativa los encuadres vinculados a nivel programa, y unicamente permitirá complementar información adicional.
            $params = [
                ':programa' => $_SESSION[_S]['clasAdm-prg'],
                ':supPrograma' => $_SESSION[_S]['clasAdm-spr'],
            ];
            $da->setSql('
                SELECT epo_id,spe_id,efn_id,epp_id,epc_id,epods_id
                FROM vin_Programa
                INNER JOIN cat_Subprogramas USING (prgs_id)
                WHERE prgs_id = :programa
                AND sprs_id= :supPrograma
            ');

            $da->getSql();
            $da->conectar();
            $da->consultar($params);
            $datos = $da->extraer();
            $da->desconectar();
            $pd = new WebResponse();

            if ($da->getNumeroRegistros() > 0) {
                $ped->epods_id = $datos['epods_id'];
                $pes->spe_id = $datos['spe_id'];
                $pes->obp_id = $datos['obp_id'];
                $pes->sectorDisabled = true;
                $pes->objetivoDisabled = true;
                $pep->ple_id = $datos['ple_id'];
                $pep->ope_id = $datos['ope_id'];
                $pep->planDisabled = true;
                $pep->objetivoDisabled = true;
                $catFun->efn_id = $datos['efn_id'];
                $prpr->epp_id = $datos['epp_id'];
                $pd->setSql('
                    SELECT eje_id, tem_id, obj_id FROM enc_PedObjetivo
                    WHERE epo_id = ?;
                ');
                $pd->conectar();
                $pd->consultar([$datos['epo_id']]);
                $regs = $pd->extraer();
                if ($pd->getNumeroRegistros() > 0) {
                    $ped->eje_id = $regs['eje_id'];
                    $ped->tem_id = $regs['tem_id'];
                    $ped->obj_id = $regs['obj_id'];
                }

                $prpr->lectura = $catFun->lectura = $ped->lecturapr = true;
                $pes->lectura = $pep->lectura = false;
            }

            $eps_id = "";
            if (!empty($epg)) {
                $params = [':epg' => $epg];
                $pd->setSql("
                    SELECT * FROM vin_Programatico
                    WHERE epg_id = :epg ;
                ");
                $pd->conectar();
                $pd->consultar($params);
                $regs = $pd->extraer();
                if ($pd->getNumeroRegistros() > 0) {
                    $ped->epe_id = $regs['epe_id'];
                    $pep->rspe_id = $regs['rspe_id'];
                    $eps_id = $regs['eps_id'];
                    $pes->eps_id = $eps_id;
                    $params = [':eps' => $regs['eps_id']];
                    $pd->setSql('
                        SELECT spe_id, obp_id FROM enc_Pes
                        WHERE eps_id = :eps ;
                    ');
                    $pd->conectar();
                    $pd->consultar($params);
                    $regs = $pd->extraer();
                    if ($pd->getNumeroRegistros() > 0) {
                        $pes->obp_id = $regs['obp_id'];
                    }
                }
                //Verifica el ssc_id de la session si es planeacion o presupuesto los combos solo serán de lectura
                if (!empty($_SESSION[_S]['ssc_id']) && ($_SESSION[_S]['ssc_id']) == 2 || ($_SESSION[_S]['ssc_id']) == 3) {
                    $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = true;
                } else if ($actividad->getEoa_id() == 4 || $actividad->getEoa_id() == 5 || $actividad->getEoa_id() == 2) {
                    $ped->lectura = true;
                    $pes->eps_id = $eps_id;
                    $pes->lectura = true;
                }

                $params = [
                    ':grupo' => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur' => $_SESSION[_S]['clasAdm-ur'],
                    ':programa' => $_SESSION[_S]['clasAdm-prg'],
                    ':supPrograma' => $_SESSION[_S]['clasAdm-spr'],
                    ':proyecto' => $_SESSION[_S]['clasAdm-py'],
                    ':oa' => $actividad->getOa_id(),
                ];
                $da->setSql('
                    SELECT elt.etla_id,lat.lact_id,lat.lact_Clave,vp.ept_porcentaje
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vp using(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc elt using(etla_id)
                    INNER JOIN cat_LineaAccionTransversal lat using(lact_id)
                    WHERE ep.gpo_id = :grupo
                    AND ep.ur_id = :ur
                    AND ep.prg_id = :programa
                    AND ep.spr_id = :supPrograma
                    AND ep.py_id = :proyecto
                    AND ep.oa_id = :oa;
                ');

                $da->conectar();
                $da->consultar($params);

                $da->desconectar();
                while ($r = $da->extraer()) {
                    $boton = new stdClass();
                    $boton->title = 'Eliminar política transversal';
                    $boton->id = 'ept_' . $r['etla_id'];
                    $boton->color = 'rojo';
                    $boton->icon = 'trash-can';
                    $boton->click = '
                        comunica("mga/complementos/ControladorActividades.php",{
                            metodoCtrlAct:"eliminaPolitica",
                            etla_id:' . $r['etla_id'] . ',
                            epg_id:"' . $epg . '"
                        });
                    ';
                    $botonEliminar = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura) && $actividad->getToa_id() != 2) ? $da->ponBoton($boton) : "");
                    $filasPoliticasT .= '<tr class="registro" id="filasPoliticasT-' . ($r['etla_id']) . '"><td>' . $r['lact_Clave'] . '</td><td>' . $r['ept_porcentaje'] . '</td><td>' . $botonEliminar . '</td>';
                }
            }
            $botonPedTrans = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura)) ? $da->ponBoton($botonPedt) : "");

            //Obtiene el Plan Institucional del Programa para mostrarlo
            $da->setSql('
                    SELECT epi_id,pi_nombre, Descripcion
                    FROM enc_PlanInstitucional epi JOIN  cat_ObjetivoPI  USING (pi_id, objpi_id)
                    JOIN cat_PlanInstitucional USING (pi_id)
                    JOIN vin_ProgramaPI USING (epi_id)
                    WHERE prgs_id = ?;
                ');
            $params = [$_SESSION[_S]['clasAdm-prg']];
            $da->conectar();
            $da->consultar($params);
            $da->desconectar();

            while ($r = $da->extraer()) {
                $filasPlanI .= '<tr class="registro" id="filasPin-' . $r['epi_id'] . '"><td>' . $r['pi_nombre'] . '</td><td>' . $r['Descripcion'] . '</td><td></td>';
            }
            $botonPlanInsticional = "";
        } else { #900-999 Gastos no asignables a programas.
            $hombres = $mujeres = $total = '';
            // Si es de programas de actividades centrales de gestión o gastos no asignables a programas no hereda la vinvulación del programa a la actividad.

            if (!empty($epg)) {
                $params = [
                    ':grupo' => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur' => $_SESSION[_S]['clasAdm-ur'],
                    ':programa' => $_SESSION[_S]['clasAdm-prg'],
                    ':subPrograma' => $_SESSION[_S]['clasAdm-spr'],
                    ':proyecto' => $_SESSION[_S]['clasAdm-py'],
                    ':oa' => $actividad->getOa_id(),
                ];
                $da->setSql('
                    SELECT * FROM enc_Programatico
                    INNER JOIN vin_Programatico using(epg_id)
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND prg_id = :programa
                    AND spr_id = :subPrograma
                    AND py_id = :proyecto
                    AND oa_id = :oa ;
                ');

                $da->conectar();
                $da->consultar($params);
                $datos = $da->extraer();
                if ($da->getNumeroRegistros() > 0) {
                    //Verifica el s22ssc de la session si es planeacion o presupuesto los combos solo serán de lectura
                    if (!empty($_SESSION[_S]['ssc_id']) && ($_SESSION[_S]['ssc_id']) == 2 || ($_SESSION[_S]['ssc_id']) == 3) {
                        $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = true;
                    } else if ($actividad->getEoa_id() == 4 || $actividad->getEoa_id() == 5 || $actividad->getEoa_id() == 2 || $actividad->getEoa_id() == 2) {
                        $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = true;
                    }

                    //Si se esta agregando/modificando desde el Modulo de Alta de Previsiones debe permitir cambiar los combos
                    if ($pp == 1) {
                        $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = false;
                    }

                    $ped->epe_id = $datos['epe_id'];
                    $ped->epods_id = $datos['epods_id'];
                    $pes->eps_id = $datos['eps_id'];
                    $pep->epc_id = $datos['epc_id'];
                    $catFun->efn_id = $datos['efn_id'];
                    $prpr->epp_id = $datos['epp_id'];
                }

                $da->setSql('
                    SELECT elt.etla_id,lat.lact_id,lat.lact_Clave,vp.ept_porcentaje
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vp using(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc elt using(etla_id)
                    INNER JOIN cat_LineaAccionTransversal lat using(lact_id)
                    WHERE ep.gpo_id = :grupo
                    AND ep.ur_id = :ur
                    AND ep.prg_id = :programa
                    AND ep.spr_id = :subPrograma
                    AND ep.py_id = :proyecto
                    AND ep.oa_id = :oa ;
                ');
                $da->conectar();
                $da->consultar($params);

                $da->desconectar();
                while ($r = $da->extraer()) {
                    $boton = new stdClass();
                    $boton->title = 'Eliminar política transversal';
                    $boton->id = 'ept_' . $r['etla_id'];
                    $boton->color = 'rojo';
                    $boton->icon = 'trash-can';
                    $boton->click = '
                        comunica("mga/complementos/ControladorActividades.php",{
                            metodoCtrlAct:"eliminaPolitica",
                            etla_id:' . $r['etla_id'] . ',
                            epg_id:"' . $epg . '"});
                        ';
                    $botonEliminar = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura) && $actividad->getToa_id() != 2) ? $da->ponBoton($boton) : "");
                    $filasPoliticasT .= '<tr class="registro" id="filasPoliticasT-' . ($r['etla_id']) . '"><td>' . $r['lact_Clave'] . '</td><td>' . $r['ept_porcentaje'] . '</td><td>' . $botonEliminar . '</td>';
                }

                //Obtiene el Plan Institucional de la Actividad para mostrarlo
                $da->setSql('
                        SELECT epi_id,pi_nombre, Descripcion
                        FROM enc_PlanInstitucional epi JOIN  cat_ObjetivoPI  USING (pi_id, objpi_id)
                        JOIN cat_PlanInstitucional USING (pi_id)
                        JOIN vin_ProgramaticoInstitucional USING (epi_id)
                        WHERE epg_id = ?;
                    ');
                $da->conectar();
                $da->consultar([$epg]);
                $da->desconectar();

                while ($r = $da->extraer()) {
                    $botonEliminaPI = new stdClass();
                    $botonEliminaPI->title = 'Eliminar Plan  Institucional';
                    $botonEliminaPI->id = 'epin_' . $r['epi_id'];
                    $botonEliminaPI->color = 'rojo';
                    $botonEliminaPI->icon = 'trash-can';
                    $botonEliminaPI->click = '
                        comunica("mga/complementos/ControladorActividades.php",{
                            metodoCtrlAct:"eliminaPi",
                            epg_id:"' . $epg . '",
                            epi_id:"' . $r['epi_id'] . '"
                        });
                    ';
                    $botonDeletePI = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura) && $actividad->getToa_id() != 2) ? $da->ponBoton($botonEliminaPI) : "");
                    $filasPlanI .= '<tr class="registro" id="filasPin-' . $r['epi_id'] . '"><td>' . $r['pi_nombre'] . '</td><td>' . $r['Descripcion'] . '</td><td>' . $botonDeletePI . '</td>';
                }

            }

            $botonPedTrans = ((($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2) && (!$lectura)) || ($prm->pp == 1) ? $da->ponBoton($botonPedt) : "");
            $botonPlanInsticional = ((($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2) && (!$lectura)) || ($prm->pp == 1) ? $da->ponBoton($botonPI) : "");
        }

        $planInsticional = '<div class="row col-md-9">
            <legend>' . $botonPlanInsticional . 'Plan Institucional</legend>
            <div id="modalPI"></div>
                <table class="table table-striped"><thead><tr><th>Plan</th><th>Objetivo PI</th>' . ($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2 ? '<th>Eliminar</th>' : "") . '</thead><tbody id="filasPlanI">' . $filasPlanI . '</tbody>
                </table>
            </div>';

        $titulo = (isset($prm->es) ? 'Tipo de proyecto' : "Tipo de Actividad");
        $da->mixHtml('
            <div class="row col-md-12">
                <div class="row col-md-12">
                    <div class="col-md-6 col-xs-6 mb-4">
                        <legend align="left">Datos Generales</legend>
                        ' . $titulo
            . $tipo
            . ClasificacionAdministrativa::clasAdminUe($ue)
            . $nombre . '
                    </div>
                    </div>
                    <div class="col-md-6 col-xs-6">
                    ' . $planInsticional . '
                </div>
            </div>
            <div class="row mb-4">
            <!--aqui iria la vinculación-->
            <div class="col-md-6 col-xs-6">
                <div class="col-md-' . (($campoImporte) ? '7' : '9') . ' col-xs-12 inline-block">
                    <legend>' . $botonCveFin . 'Claves de Financiamiento</legend>
                    <div id="modalClaveFinanciamiento"></div>
                    <table class="table table-striped"><thead><tr><th>Clave</th><th>Nombre</th>' .
            (($actividad->getToa_id() == 2) ? '<th>Oficio de Autorizacion</th><th>IDOBRA</th>' : '') .
            (($campoImporte) ? '<th>Importe' : '<th>Eliminar') .
            '</th></thead><tbody id="filasCvesFin">' . $filasCveFin . '</tbody></table>
                </div>
            </div>
            <div class="col-md-6 col-xs-6">
            ' . UbicacionGeografica::ponUbicacionGeografica($ug) . '
            </div>
            </div>
            <div class="row" ' .
            // Si es una actividad de previsiones, existirá pero no verán la Ubicación Geográfica
            // ( la dejamos porque es un campo obligatorio )
            (($pp == 1) ? ' style="display:none" ' : '')
            . '>
            </div>
            <button type="submit" name="guardaActividad2" id="guardaActividad2" form hidden="hidden"></button>
        ');

        $da->mixHtml(
            '<div class="row">' .
            '<div class="col-md-6">' .
            Ped::ponPed($ped) .
            '</div>
                <div class="col-md-6">' .
            Pep::ponPep($pep) .
            '</div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">' .
            Pes::ponPes($pes) .
            '</div>
                <div class="col-md-6">' .
            CatalogoFuncional::ponCatalogoFuncional($catFun) .
            '</div>
            </div>
            <div class="row">
                <div class="col-md-6">' .
            ProgramaPresupuestario::ponProgramaPres($prpr) .
            '</div>
                <div class="col-md-6 col-xs-6">
                    <div class="row col-md-9">
                    <legend>' . $botonPedTrans . 'Políticas Transversales</legend>
                    <div id="modalPedTransversal"></div>
                        <table class="table table-striped"><thead><tr><th>Línea de Acción</th><th>%</th>' . ($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2 ? '<th>Eliminar</th>' : "") . '</thead><tbody id="filasPoliticasT">' . $filasPoliticasT . '</tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                </div>
                <div class="col-md-6 col-xs-6">
                    
                </div>
            </div>'
        );
        $mensajeRechazo = $observacion = '';
        $validar = '';
        $rechazar = $validarEgresos = '';

        //SECCIÓN DE VALIDACIÓN
        if ($pp != 1) {
            if ($_SESSION[_S]['nvj_id'] >= 3) {
                $mensaje = new HistorialMensajes();
                $mensaje->setMje_id($actividad->getObservacion());
                $mensaje->select();
                $observacion = new stdClass();
                $observacion->id = 'observaciones';
                $observacion->enDiv = true;
                $observacion->maxlength = 1000;
                $observacion->disabled = true;
                $observacion->texto = $mensaje->getMje_descripcion();
                $saveObservacion = '';
                if ($actividad->getEoa_id() != '' && ($actividad->getEoa_id() == 2 || $actividad->getEoa_id() == 5)) {
                    if (
                        $actividad->getToa_id() == 1 && $_SESSION[_S]['ssc_id'] == 3 && $actividad->getOa_id() != 0 &&
                        isset($_SESSION[_S]['prm']['A']['2']['2']['3'])
                    ) {
                        $boton = new stdClass();
                        $boton->id = 'validando';
                        $boton->color = 'verde';
                        $boton->icon = 'ok';
                        $boton->value = ' Validar Obra/Actividad ';
                        $botonClickValidar = '
                            comunica("mga/complementos/ControladorActividades.php",{
                                metodoCtrlAct:"validarOa",
                                epg_id:' . ($epg * 1) . ' ' . ((empty($prm->regresar)) ? '' : ",
                                regresar:'" . $prm->regresar . "'") . ' ' . (($actividad->getToa_id() == 1) ? ',
                                eoa_id:4' : (($actividad->getToa_id() == 2) ? ",
                                eoa_id:'5'" : "")) . '
                            });
                        ';

                        $validar = '
                            <button id="validando" type="button" class="btn btn-outline-success me-1" title=" Validar Obra/Actividad">
                                <i class="fa fa-check me-2"></i>Validar Obra/Actividad
                            </button>';

                        $boton = new stdClass();
                        $boton->id = 'rechazar';
                        $boton->color = 'rojo';
                        $boton->icon = 'remove';
                        $boton->value = ' Rechazar Obra/Actividad';
                        $botonClickRechazar = '
                        $("#divContenedorGeneralModal").html(
                            $("<div/>").css("display","block").attr("tabindex","-1").addClass("modal").attr("id","myModal").attr("data-bs-backdrop","static").attr("data-bs-keyboard","false").attr("aria-labelledby","staticBackdropLabel").attr("aria-hidden","false")
                                .html(
                                    $("<div/>").addClass("modal-dialog")
                                        .html(
                                            $("<div/>").addClass("modal-content")
                                                .html(
                                                    $("<div/>").addClass("modal-header")
                                                        .html( $("<h4/>").addClass("modal-title").text("Agregar motivo de rechazo") )
                                                        .append( $("<button/>").addClass("close cerrar btn btn-xs btn-danger").attr("float","right").attr("type","button").append( $("<i/>").addClass("fa fa-xmark")).click(function(){ $("#myModal").modal().remove(); return false;}) )
                                                    ).append(
                                                        $("<div/>").addClass("modal-body")
                                                            .html( $("<div/>").addClass("from-group")
                                                                .html( $("<label/>").attr("for","rP").text("Motivo de rechazo:") )
                                                                .append( $("<textarea/>").attr("name","rP").attr("id","rP").attr("maxlength","1000").attr("required","1").addClass("form-control") )
                                                        )
                                                    ).append( $("<div/>").addClass("modal-footer")
                                                    .html( $("<button/>").attr("id","btnGuardarObs").addClass("btn btn-outline-success").text("Aceptar")
                                                    .click(function(){
                                                        callbackComunica = function(){
                                                            comunica("mga/complementos/ControladorActividades.php",{
                                                                metodoCtrlAct:"mainBack"
                                                            });
                                                        };
                                                        comunica("mga/complementos/ControladorActividades.php",{
                                                            metodoCtrlAct:"validarOa",
                                                            mensaje:$("#rP").val(),
                                                            epg_id:' . ($epg * 1) . ',
                                                            eoa_id:3 ' . ((empty($prm->regresar)) ? '' : ",
                                                            regresar:'" . $prm->regresar . "'") . 
                                                        '});
                                                        $("#myModal").modal().remove();
                                                        return false;
                                                    })
                                                    )
                                                    .append( $("<button/>").addClass("btn btn-outline-danger").text("Cancelar").click(function(){ $("#myModal").modal().remove(); return false;}) )
                                                )
                                        )
                                )
                        );
                        $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
                        ';
                        $rechazar = '
                            <button id="rechazar" type="button" class="btn btn-outline-danger me-1" title=" Rechazar' . (isset($prm->es) ? ' Proyecto ' : " Obra/Actividad ") . '">
                                <i class="fa fa-remove me-2"></i>Rechazar' . (isset($prm->es) ? ' Proyecto ' : " Obra/Actividad ") . '
                            </button>
                        ';

                        $observacion->readonly = true;
                        $observacion->disabled = true;
                    } else {
                        //SI NO ESTA ENVIADA A VALIDACIÓN NO SE PUEDE CAPTURAR OBSERVACIÓN
                        if ($actividad->getEoa_id() != 2 && $actividad->getEoa_id() != 5) {
                            $observacion->readonly = true;
                        }
                    }
                }

                if ($actividad->getEoa_id() != 0 && ($actividad->getEoa_id() == 6)) {
                    if ($actividad->getToa_id() == 2 && $_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['nvj_id'] >= 3 && $actividad->getOa_id() != 0 && isset($_SESSION[_S]['prm']['A']['2']['2']['3'])) {
                        $boton = new stdClass();
                        $boton->id = 'validandoEgresos';
                        $boton->color = 'verde';
                        $boton->icon = 'ok';
                        $boton->value = ' Validar Obra/Actividad ';
                        $botonClickValidaEgresos = '
                            comunica("mga/complementos/ControladorActividades.php",{
                                metodoCtrlAct:"validarOa",
                                epg_id:' . ($epg * 1) . ',
                                eoa_id:4 ' . ((empty($prm->regresar)) ? '' : ",
                                regresar:'" . $prm->regresar . "'") . '
                            });
                        ';
                        $validarEgresos = '
                            <button id="validandoEgresos" type="button" class="btn btn-sm me-1" title="Validar Obra/Actividad">
                                <i class="fa fa-ok me-2"></i>Validar Obra/Actividad
                            </button>
                        ';

                        $saveObservacion = new stdClass();
                        $saveObservacion->value = ' Enviar';
                        $saveObservacion->icon = 'comment';
                        $saveObservacion->color = 'azul';
                        $saveObservacion->id = 'saveO';
                        $botonClicGuardaObs = '
                            comunica("mga/complementos/ControladorActividades.php",{
                                metodoCtrlAct:"observacion",
                                mensaje:$("#observaciones").val(),
                                epg_id:' . ($epg * 1) . ' ' . ((empty($prm->regresar)) ? '' : ",
                                regresar:'" . $prm->regresar . "'") . '
                            });
                        ';
                        $botonGuardarObservacion = '
                            <button id="saveO" type="button" class="btn btn-success btn-sm me-1" title=" Enviar">
                                <i class="fa fa-comment me-2"></i>Enviar
                            </button>
                        ';
                    }
                }

                $boton = new stdClass();
                $boton->value = 'Historial Observaciones';
                $boton->icon = 'list-alt';
                $boton->id = 'verHistorial';
                $botonClicHistorial = '
                    comunica("mga/complementos/ControladorActividades.php",{
                        metodoCtrlAct:"verHistorial",
                        tipo:"observacion",
                        mje_id:' . ($actividad->getObservacion()) . '
                    });
                ';
                $observacion = '
                <div class="col-md-6 col-xs-12">
                <fieldset class="col-md-9 col-xs-12 inline-block"><legend>Observación:</legend>' . $da->ponTextArea($observacion) . '</div></fieldset>';
                $botonHistorial = '<button id="verHistorial" type="button" class="btn btn-outline-secondary me-1" title=" Historial Observaciones"><i class="fa fa-list-alt me-2"></i>Historial Observaciones</button>';
            }

            if ($actividad->getDescripcion() != 0) {
                $mensaje = new HistorialMensajes();
                $mensaje->setMje_id($actividad->getDescripcion());
                $mensaje->select();
                $mensajeRechazo = new stdClass();
                $mensajeRechazo->id = 'rechazo';
                $mensajeRechazo->enDiv = true;
                $mensajeRechazo->maxlength = 1000;
                $mensajeRechazo->readOnly = 100;
                $mensajeRechazo->texto = $mensaje->getMje_descripcion();
                $boton = new stdClass();
                $boton->value = ' Historial de Rechazo';
                $boton->icon = 'list-alt';
                $boton->id = 'verHistorial2';
                $botonClicHistorial2 = '
                    comunica("mga/complementos/ControladorActividades.php",{
                        metodoCtrlAct:"verHistorial",
                        tipo:"rechazo",
                        mje_id:' . ($actividad->getDescripcion()) . '
                    });
                ';
                $botonHistorial2 = '<button id="verHistorial2" type="button" class="btn btn-outline-secondary me-1" title=" Historial de Rechazo"><i class="fa fa-list-alt me-2"></i>Historial de Rechazo</button>';

                $mensajeRechazo = '
                <div class="col-md-6 col-xs-12">
                    <fieldset class="col-md-9 col-xs-12 inline-block">
                        <legend>Motivo de rechazo</legend>
                        ' . $da->ponTextArea($mensajeRechazo) . '
                    </fieldset>
                </div>
            ';
            }
        }

        $da->agregaTools($regresar . $guardar . $botonDelete . $validar . $validarEgresos . $rechazar . $guardarPes . $validaPes . $botonHistorial . $botonHistorial2 . $botonGuardarObservacion . '<div id="divContenedorGeneralModal"></div>');
        $da->mixHtml('<div class="row">' . $observacion . $mensajeRechazo . '</div>');

        $da->mixScript('
            var cveFinArray = [];
            var metasArray = [];
            var politicasArray = [];
            var ept = [];
            ArraysObjetivosPi = [];
            var encPI = [];
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#regresarAct").click(); e.stopPropagation(); });


            $("#eliminaActividad").click(function(){
                ' . $clickEliminarAct . '
            });
            $("#regresarAct").click(function(){
                ' . $clickRegresarAct . '
            });

            $("#validando").click(function(){
                ' . $botonClickValidar . '
            });

            $("#rechazar").click(function(){
                ' . $botonClickRechazar . '
            });

            $("#Validar").click(function(){
                ' . $botonClickValidarPes . '
            });

            $("#validandoEgresos").click(function(){
                ' . $botonClickValidaEgresos . '
            });

            $("#verHistorial").click(function(){
                ' . $botonClicHistorial . '
            });

            $("#verHistorial2").click(function(){
                ' . $botonClicHistorial2 . '
            });
            $("#saveO").click(function(){
                ' . $botonClicGuardaObs . '
            });
        ');
        return $da;
    }

    private function muestraPlanInstitucional()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Plan Institucional</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="PlanIstitucional">
                        <div class="row">
                        ' . Pin::ponPin() . '
                        </form>
                    </div>
                    <div class="modal-footer">';

        if (!isset($this->prm->lectura)) {
            $modal .= '<button type="submit" class="btn btn-outline-success" id="btnGuardarPlanI" form="">Aceptar</button>';
        }

        $modal .= '<button type="button" class="btn btn-outline-danger" id="cancelaPI" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';
        $this->mixHtml($modal);

        $epg_id = (isset($this->prm->epg_id)) ? ',epg_id:' . $this->prm->epg_id . '' : "";

        $this->mixScript('
            $("#myModal").modal().show();
            $("#myModal").on("hidden.bs.modal", function (e) {$("#myModal").modal().remove();});
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#cancelaPI").click(function(){ $("#myModal").modal().remove(); });

            $("#btnGuardarPlanI").click(function(){
                $("#modalPI").empty();
                if ($("#objpi_id").val() != "") {
                    comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct: "agregaPi"' . $epg_id . '});
                    $("#myModal").modal().remove();
                }
            });
        ');
    }

    private function getEjePED()
    {
        $eje_id = 0;
        $params = [
            ':programa' => $_SESSION[_S]['clasAdm-prg'],
            ':supPrograma' => $_SESSION[_S]['clasAdm-spr'],
        ];
        $this->conectar();
        $this->setSql('
                SELECT epo_id
                FROM vin_Programa
                INNER JOIN cat_Subprogramas USING (prgs_id)
                WHERE prgs_id = :programa
                AND sprs_id = :supPrograma ;
            ');
        $this->consultar($params);
        $datos = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            $this->conectar();
            $params = [':epo' => $datos['epo_id']];
            $this->setSql('
                SELECT eje_id, tem_id, obj_id FROM enc_PedObjetivo
                WHERE epo_id = :epo ;
            ');
            $this->consultar($params);
            $regs = $this->extraer();
            if ($this->getNumeroRegistros() > 0) {
                $eje_id = $regs['eje_id'];
            }
        }
        return $eje_id;
    }

    //Valida si la Unidad Ejecutora tiene Ejes Transversales Asignados
    private function getEjeT()
    {
        $tieneEjesTransversales = false;
        if (!empty($this->ue_id)) {
            $this->conectar();
            $params = [
                ':grupo' => $this->gpo_id,
                ':ur' => $this->ur_id,
                ':ue' => $this->ue_id,
            ];
            $this->setSql('
                    SELECT *
                    FROM enc_EjeTransSupueDos
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND ue_id = :ue ;
                ');
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $tieneEjesTransversales = true;
            }
        }
        return $tieneEjesTransversales;
    }

    //Valida si la Unidad Ejecutora tiene Estrategias Transversales Asignadas
    private function getEtraT()
    {
        $tieneEstrategiasTransversales = false;
        if (!empty($this->ue_id)) {
            $this->conectar();
            $params = [
                ':grupo' => $this->gpo_id,
                ':ur' => $this->ur_id,
                ':ue' => $this->ue_id,
            ];
            $this->setSql('
                    SELECT *
                    FROM enc_EstrategiaTransversalSupDos
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND ue_id = :ue ;
                ');
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $tieneEstrategiasTransversales = true;
            }
        }
        return $tieneEstrategiasTransversales;
    }

    //Valida si la Unidad Ejecutora tiene Lineas de Acción Asignadas
    private function getLinAccT()
    {
        $tieneLineasAccionTransversales = false;
        if (!empty($this->ue_id)) {
            $this->conectar();
            $params = [
                ':grupo' => $this->gpo_id,
                ':ur' => $this->ur_id,
                ':ue' => $this->ue_id,
            ];
            $this->setSql('
                    SELECT *
                    FROM enc_EjeTransLineaAcc
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND ue_id = :ue
                ');
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $tieneLineasAccionTransversales = true;
            }
        }
        return $tieneLineasAccionTransversales;
    }

    private function muestraPedTransversal()
    {
        $eje_id = $this->getEjePED();
        $PoliticasT = new stdClass();
        $PoliticasT->eje_id = $eje_id;
        $PoliticasT->lectura = true;
        $PoliticasT->ueEjeT = $this->getEjeT();
        $PoliticasT->ueEtraT = $this->getEtraT();
        $PoliticasT->ueLinAccT = $this->getLinAccT();

        $this->setContenedor('divContenedorGeneralModal');

        $texto = (!isset($this->prm->lectura)) ? 'Agregar Política transversal' : 'Política transversal';

        $inputPorcentaje = new stdClass();
        $inputPorcentaje->id = 'ept_porcentaje';
        $inputPorcentaje->label = 'Ponderación porcentual';
        $inputPorcentaje->maxlength = 4;
        $inputPorcentaje->formato = 'numero';
        $inputPorcentaje->size = 4;
        $inputPorcentaje->enDiv = true;

        if (isset($this->prm->ept_porcentaje) && $this->prm->ept_porcentaje != '') {
            $inputPorcentaje->value = $this->prm->ept_porcentaje;
            $inputPorcentaje->disabled = true;
        }
        $inputPorcentaje->required = true;
        $ept_porcentaje = $this->ponInput($inputPorcentaje);
        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">' . $texto . '</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="PoliticaTransversal">
                        <div class="row">
                        ' . PoliticasT::ponPoliticasT($PoliticasT) . $ept_porcentaje . '
                        </form>
                    </div>
                    <div class="modal-footer">';
        if (!isset($this->prm->lectura)) {
            $modal .= '<button type="submit" class="btn btn-outline-success" id="btnGuardarPolitica" form="">Aceptar</button>';
        }

        $modal .= '<button type="button" class="btn btn-outline-danger" id="cancelaPt" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';
        $this->mixHtml($modal);

        $epg_id = (isset($this->prm->epg_id)) ? ',epg_id:' . $this->prm->epg_id . '' : "";

        $this->mixScript('
            $("#myModal").modal().show();
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            })
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#cancelaPt").click(function(){ $("#myModal").modal().remove(); });

            $("#btnGuardarPolitica").click(function(){
                valido = ( $("#lact_id").val() != "null" );
                ok = true;
                var r=0;

                var diferencia=0;
                for(var q=0;q<ept.length;q++)
                {
                    if(typeof(ept[q]["ept_porcentaje"])=="number") r+=ept[q]["ept_porcentaje"];
                }
                diferencia = (100 - r);

                if($("#ept_porcentaje").val().replace(",","").substring(0,1) == "-"){
                    ok = false;
                    alert("El valor de la ponderación no puede ser negativo");
                } else if($("#ept_porcentaje").val().replace(",","") == 0.00){
                    ok = false;
                    alert("El porcentaje debe se mayor 0.00%");
                } else if($("#ept_porcentaje").val().replace(",","") > diferencia && $("#lact_id").val()!=5){
                    ok = false;
                    if(diferencia==0)
                        alert("La suma de las Líneas de Acción ya tiene 100%");
                    else
                        alert("Solo puedes poner maximo: "+diferencia);
                }

                $("#PoliticaTransversal").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                    if(!valido){
                        if ($("#ejt_id").val() == "" || $("#etra_id").val() == "" || $("#lact_id").val() == "") {
                            if (!valido) {
                            }
                        }
                    }
                });
                if(valido && ok){
                    callbackComunica = function(){
                        $("#modalPedTransversal").empty();
                    };
                    comunica("'.$this->pantalla.'",{
                        metodoCtrlAct: "agregaPolitica",
                        ejt_id:$("#ejt_id").val(),
                        eje_id:$("#eje_id").val(),
                        etra_id:$("#etra_id").val(),
                        lact_id:$("#lact_id").val(),
                        ept_porcentaje:$("#ept_porcentaje").val()
                        ' . $epg_id . '
                    });
                    ' . (!isset($this->prm->epg_id) ? '$("#myModal").modal().remove();' : '') . '
                }
            });
        ');
    }

    private function agregaPolitica()
    { 
        $this->prm->etla_id = $_SESSION[_S]['politicasT']->etla;
        $a = new AccesoDatos();
        $a->conectar();
        $params = [':etla' => $this->prm->etla_id];
        $a->setSql('
            SELECT lact_Clave
            FROM enc_EjeTransLineaAcc
            INNER JOIN cat_LineaAccionTransversal USING(lact_id)
            WHERE etla_id = :etla ;
        ');
        $a->consultar($params);
        $a->desconectar();
        $ept = $a->extraer();
        
        $boton = new stdClass();
        $boton->title = 'Eliminar Política Transversal';
        $boton->id = 'ept_' . $this->prm->etla_id;
        $boton->color = 'rojo';
        $boton->icon = 'trash-can';
        $fila = '
            $("#filasPoliticasT").append(
                $("<tr>").attr("id","filasPoliticasT-' . $this->prm->etla_id . '")
                .attr("class","registro")
                .append("<td>' . $ept['lact_Clave'] . '</td>")
                .append("<td>' . $this->prm->ept_porcentaje . '</td>")
                .append("<td>' . addslashes($this->ponBoton($boton)) . '</td>")
            );
        ';
        if (isset($this->prm->epg_id) && $this->prm->epg_id != '') {
            $b = new AccesoDatos();
            $b->conectar();
            $params = [
                ':grupo' => $this->clasAdm_gpo,
                ':ur' => $this->clasAdm_ur,
                ':epg' => $this->prm->epg_id,
                ':etla' => $this->prm->etla_id,
            ];
            $b->setSql("
                SELECT etl.etla_id
                FROM enc_Programatico ep
                INNER JOIN vin_ProgramaticoTransversal vpt USING(epg_id)
                INNER JOIN enc_EjeTransLineaAcc etl USING(etla_id)
                INNER JOIN cat_LineaAccionTransversal lat USING(lact_id)
                WHERE ep.gpo_id = :grupo
                AND ep.ur_id = :ur
                AND ep.epg_id = :epg
                AND vpt.etla_id = :etla;
            ");
            $b->consultar($params);
            $b->desconectar();
            if ($b->getNumeroRegistros() > 0) {
                $this->mixScript('
                    alertify.alert("", "<h5>El eje ya está agregado</h5>");
                ');
            } else {
                $c = new AccesoDatos();
                $c->conectar();
                $c->setSql('
                    SELECT etl.etla_id
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vpt USING(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc etl USING(etla_id)
                    INNER JOIN cat_LineaAccionTransversal USING(lact_id)
                    WHERE ep.gpo_id = :gpo_id
                    and ep.ur_id = :ur_id
                    and ep.epg_id =:epg_id;
                ');
                $params = [
                    ':gpo_id' => $this->clasAdm_gpo,
                    ':ur_id' => $this->clasAdm_ur,
                    ':epg_id' => $this->prm->epg_id
                ]; 
                $c->consultar($params);
                $c->desconectar();

                if (($this->prm->etla_id == 5 && $c->getNumeroRegistros() > 0)) {
                    $cped = new AccesoDatos();
                    $cped->conectar();
                    $cped->setSql('
                        SELECT etl.etla_id, vpt.ept_porcentaje
                        FROM enc_Programatico ep
                        INNER JOIN vin_ProgramaticoTransversal vpt USING(epg_id)
                        INNER JOIN enc_EjeTransLineaAcc etl USING(etla_id)
                        INNER JOIN cat_LineaAccionTransversal lat USING(lact_id)
                        WHERE ep.gpo_id = :gpo_id
                        and ep.ur_id = :ur_id
                        and ep.epg_id=:epg_id;
                    ');
                    $params = [
                        ':gpo_id' => $this->clasAdm_gpo,
                        ':ur_id' => $this->clasAdm_ur,
                        ':epg_id' => $this->prm->epg_id
                    ];
                    $cped->consultar($params);
                    $cped->desconectar();
                    $row = $cped->extraer();
                    $eptids = '';
                    do {
                        $eptids .= $row['etla_id'] . '- porcentaje' . $row['ept_porcentaje'] . ',';
                    } while ($row = $cped->extraer());

                    //Eimina eje transversal
                    $d = new AccesoDatos();
                    $d->conectar();
                    $params = [':epg' => $this->prm->epg_id];
                    $d->conectar();
                    $d->setSql("DELETE FROM vin_ProgramaticoTransversal WHERE epg_id = :epg;");
                    $d->consultar($params);
                    $d->desconectar();

                    $datosOa = new ObraActividad($this->prm);

                    //Agrega eje transversal
                    $epgT = new AccesoDatos();
                    $epgT->conectar();
                    $params = [
                        ':epg' => $this->prm->epg_id,
                        ':etla' => $this->prm->etla_id,
                        ':porcentaje' => number_format($this->prm->ept_porcentaje, 2),
                        ':usr' => $this->usr_id,
                    ];
                    $epgT->setSql("INSERT INTO vin_ProgramaticoTransversal (epg_id,etla_id,epp_porcentaje,usr_id)
                                VALUES(:epg, :etla, :porcentaje, :usr)");
                    $epgT->consultar($params);
                    $epgT->desconectar();

                    $this->prm->gpo_id = $datosOa->getGpo_id();
                    $this->prm->ur_id = $datosOa->getUr_id();
                    $this->prm->prg_id = $datosOa->getPrg_id();
                    $this->prm->spr_id = $datosOa->getSpr_id();
                    $this->prm->py_id = $datosOa->getPy_id();
                    $this->prm->oa_id = $datosOa->getOa_id();
                    $this->prm->eoa_id = $datosOa->getEoa_id();

                    $this->prm->nombre = $datosOa->getNombre();
                    $this->prm->observacion = $datosOa->getObservacion();
                    $this->prm->ue_id = $datosOa->getUe_id();
                    $this->prm->anticipo = $datosOa->getAnticipo();
                    $this->prm->anticipo2025 = $datosOa->getAnticipo2025();
                    $this->prm->amortizacion = $datosOa->getAmortizacion();
                    $this->prm->eug_id = $datosOa->getEug_id();
                    $this->prm->eoa_id = $datosOa->getEoa_id();
                    $this->prm->toa_id = $datosOa->getToa_id();
                    $this->prm->pbf_id = $datosOa->getPbf_id();
                    $this->prm->sts_id = $datosOa->getSts_id();
                    $this->registrarBitacora('Ejes transversales tenia: ' . $eptids . ' y agrego ' . $this->prm->etla_id . ' -porcentaje' . $this->prm->ept_porcentaje . '');

                    $remove = '';
                    if (!empty($this->prm->etla_id)) {
                        if ($this->prm->etla_id == 5) {
                            $remove .= '$(".registro").remove();';
                        } else {
                            $remove .= '$("#filasPoliticasT-5").remove();';
                        }

                    }
                    
                    $this->mixScript('
                        '.$remove.'
                        ' . $fila . '
                        $("#ept_' . $this->prm->etla_id . '").click(function(){
                            if(politicasArray.length > 1)
                                $("#filasPoliticasT-' . $this->prm->etla_id . '").remove();
                            comunica("' . $this->pantalla . '",{
                                metodoCtrlAct:"eliminaPolitica",
                                epg_id:"' . $this->prm->epg_id . '",
                                etla_id:' . $this->prm->etla_id . '
                            });
                        });
                    ');
                } else {
                    $e = new AccesoDatos();
                    $e->conectar();
                    $e->setSql('
                        SELECT etl.etla_id, vpt.ept_porcentaje
                        FROM enc_Programatico ep
                        INNER JOIN vin_ProgramaticoTransversal vpt using(epg_id)
                        INNER JOIN enc_EjeTransLineaAcc etl using(etla_id) 
                        INNER JOIN cat_LineaAccionTransversal lat using(lact_id) 
                        WHERE ep.gpo_id = :gpo_id
                        and ep.ur_id = :ur_id
                        and ep.epg_id = :epg_id;
                    ');
                    $params = [
                        ':gpo_id' => $this->clasAdm_gpo,
                        ':ur_id' => $this->clasAdm_ur,
                        ':epg_id' => $this->prm->epg_id
                    ];
                    $e->consultar($params);
                    $e->desconectar();
                    $row = $e->extraer();

                    $eptids = '';
                    do {
                        $eptids .= $row['etla_id'] . ' -porcentaje' . $row['ept_porcentaje'] . ',';
                    } while ($row = $e->extraer());

                    $datosOa = new ObraActividad($this->prm);

                    $porcent = new AccesoDatos();
                    $porcent->conectar();
                    $porcent->setSql('
                        SELECT SUM(ept_porcentaje) as sumaPorcentaje
                        FROM vin_ProgramaticoTransversal
                        WHERE epg_id = ?;
                    ');
                    $porcent->consultar([$this->prm->epg_id]);
                    $porcent->desconectar();
                    $totalPorcentaje = $porcent->extraer();
                    $ban = true;
                    $diferencia = 0;
                    $diferencia = (100.00 - (float) number_format(($totalPorcentaje['sumaPorcentaje']), 2, '.', ''));
                    $diferencia = number_format($diferencia, 2, '.', '');
                    if (number_format($this->prm->ept_porcentaje, 2) == 0.00) {
                        $ban = false;
                        $this->mixScript('alert("El porcentaje debe ser mayor 0.00%");');
                    } else if ($totalPorcentaje['sumaPorcentaje'] == 100) {
                        $ban = false;
                        $this->mixScript('alert("La suma de las lineas de acción ya tiene 100%");');
                    } else if ($this->prm->ept_porcentaje > $diferencia) {
                        $ban = false;
                        $this->mixScript('alert("Solo puedes poner maximo ' . $diferencia . '");');
                    }

                    if ($ban) {
                        $epgT = new AccesoDatos();
                        $epgT->conectar();
                        $params = [
                            ':epg' => $this->prm->epg_id,
                            ':etla' => $this->prm->etla_id,
                            ':porcentaje' => number_format($this->prm->ept_porcentaje, 2),
                            ':usr' => $this->usr_id,
                        ];
                        $epgT->setSql("INSERT INTO vin_ProgramaticoTransversal (epg_id,etla_id,ept_porcentaje,usr_id)
                                VALUES(:epg, :etla, :porcentaje, :usr)");
                        $epgT->consultar($params);
                        $epgT->desconectar();

                        $this->prm->gpo_id = $datosOa->getGpo_id();
                        $this->prm->ur_id = $datosOa->getUr_id();
                        $this->prm->prg_id = $datosOa->getPrg_id();
                        $this->prm->spr_id = $datosOa->getSpr_id();
                        $this->prm->py_id = $datosOa->getPy_id();
                        $this->prm->oa_id = $datosOa->getOa_id();
                        $this->prm->eoa_id = $datosOa->getEoa_id();

                        $this->prm->nombre = $datosOa->getNombre();
                        $this->prm->observacion = $datosOa->getObservacion();
                        $this->prm->ue_id = $datosOa->getUe_id();
                        $this->prm->anticipo = $datosOa->getAnticipo();
                        $this->prm->anticipo2025 = $datosOa->getAnticipo2025();
                        $this->prm->amortizacion = $datosOa->getAmortizacion();
                        $this->prm->eug_id = $datosOa->getEug_id();
                        $this->prm->eoa_id = $datosOa->getEoa_id();
                        $this->prm->toa_id = $datosOa->getToa_id();
                        $this->prm->pbf_id = $datosOa->getPbf_id();
                        $this->prm->sts_id = $datosOa->getSts_id();

                        $this->registrarBitacora('Ejes transversales tenia: ' . $eptids . ' y agrego ' . $this->prm->etla_id . ' - porcentaje ' . $this->prm->ept_porcentaje . '');

                        $remove = '';
                        if (!empty($this->prm->ejt_id)) {
                            if ($this->prm->ejt_id == 5) {
                                $remove .= '$(".registro").remove();';
                            } else {
                                $remove .= '$("#filasPoliticasT-5").remove();';
                            }

                        }

                        $this->mixScript('
                            ' . $remove . '
                            ' . $fila . '
                            $("#botonPedPlus").attr("disabled",false);
                            $("#ept_' . $this->prm->etla_id . '").click(function(){
                                comunica("' . $this->pantalla . '",{
                                    metodoCtrlAct:"eliminaPolitica",
                                    epg_id:"' . $this->prm->epg_id . '",
                                    etla_id:' . $this->prm->etla_id . '
                                });
                            });
                            $("#myModal").modal().remove();
                        ');
                    }
                }
            }
                $this->mixScript('
                $("#e_' . $this->prm->etla_id . '").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodoCtrlAct:"muestraPedTransversal",
                        etla_id:' . $this->prm->etla_id . ',
                        ept_porcentaje:' . $this->prm->ept_porcentaje . ',
                        lectura:"true"
                    });
                });
            ');

        } else {
            $this->mixScript('
                if(politicasArray.indexOf("05") < 0){
                    if(($("#filasPoliticasT-01").length ==1 || $("#filasPoliticasT-02").length ==1 || $("#filasPoliticasT-03").length ==1 || $("#filasPoliticasT-04").length ==1) && ' . $this->prm->ejt_id . ' == "05"){
                        $("#botonPedPlus").attr("disabled","disabled");
                        for(var i = politicasArray.length - 1; i >= 0; i--) {
                            $(".registro").remove();
                            politicasArray.splice(i, 1);
                            ept.splice(i, 1);
                        }
                        politicasArray.push("' . $this->prm->etla_id . '");
                        ept.push({"etla_id":"' . $this->prm->etla_id . '","ept_porcentaje":"' . $this->prm->ept_porcentaje . '"});
                        ' . $fila . '

                        $("#ept_05").click(function(){
                            $("#filasPoliticasT-05").remove();
                            politicasArray.splice( politicasArray.indexOf("05"),1);
                            ept.splice( ept.indexOf("' . $this->prm->etla_id . '"),1);
                            $("#botonPedPlus").attr("disabled",false);
                        });

                        $("#e_' . $this->prm->etla_id . '").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodoCtrlAct:"muestraPedTransversal",
                                etla_id:"' . $this->prm->etla_id . '",
                                ept_porcentaje:' . $this->prm->ept_porcentaje . ',
                                lectura:"true"
                            });
                        });
                    }else{
                        if(politicasArray.indexOf("' . $this->prm->etla_id . '") < 0 ){
                            if(' . $this->prm->etla_id . ' == "05")
                                $("#botonPedPlus").attr("disabled","disabled");
                                politicasArray.push("' . $this->prm->etla_id . '");

                                ept.push({"etla_id":"' . $this->prm->etla_id . '","ept_porcentaje":' . $this->prm->ept_porcentaje . '});
                                    ' . $fila . '
                                $("#ept_' . $this->prm->etla_id . '").click(function(){
                                    $("#filasPoliticasT-' . $this->prm->etla_id . '").remove();
                                    ept.splice( politicasArray.indexOf("' . $this->prm->etla_id . '"),1);
                                    politicasArray.splice( politicasArray.indexOf("' . $this->prm->etla_id . '"),1);
                                });

                                $("#ept_05").click(function(){
                                    $("#filasPoliticasT-05").remove();
                                    politicasArray.splice( politicasArray.indexOf(05),1);
                                    ept.splice( ept.indexOf(' . $this->prm->etla_id . '),1);
                                    $("#botonPedPlus").attr("disabled",false);
                                });

                                $("#e_' . $this->prm->etla_id . '").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodoCtrlAct:"muestraPedTransversal",
                                        etla_id:' . $this->prm->etla_id . ',
                                        ept_porcentaje:' . $this->prm->ept_porcentaje . ',
                                        lectura:"true"
                                    });
                                });
                        }else
                            alert("La línea de acción ya está agregada");
                    }
                }else $("#botonPedPlus").attr("disabled","disabled");
            ');
        }
    }

    private function eliminaPolitica()
    {
        $params = [
            ':epg' => $this->prm->epg_id,
            ':etla' => $this->prm->etla_id,
        ];
        $this->setSql('DELETE FROM vin_ProgramaticoTransversal WHERE epg_id = :epg and etla_id = :etla;');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();

        $this->mixScript('
            $("#filasPoliticasT-' . $this->prm->etla_id . '").remove();
        ');

        $datosOa = new ObraActividad($this->prm);

        $this->prm->gpo_id = $datosOa->getGpo_id();
        $this->prm->ur_id = $datosOa->getUr_id();
        $this->prm->prg_id = $datosOa->getPrg_id();
        $this->prm->spr_id = $datosOa->getSpr_id();
        $this->prm->py_id = $datosOa->getPy_id();
        $this->prm->oa_id = $datosOa->getOa_id();
        $this->prm->eoa_id = $datosOa->getEoa_id();

        $this->prm->nombre = $datosOa->getNombre();
        $this->prm->observacion = $datosOa->getObservacion();
        $this->prm->ue_id = $datosOa->getUe_id();
        $this->prm->anticipo = $datosOa->getAnticipo();
        $this->prm->anticipo2025 = $datosOa->getAnticipo2025();
        $this->prm->amortizacion = $datosOa->getAmortizacion();
        $this->prm->eug_id = $datosOa->getEug_id();
        $this->prm->eoa_id = $datosOa->getEoa_id();
        $this->prm->toa_id = $datosOa->getToa_id();
        $this->prm->pbf_id = $datosOa->getPbf_id();
        $this->prm->sts_id = $datosOa->getSts_id();

        $this->registrarBitacora('Eliminó Política transversal: ' . $this->prm->lact_id . '');
    }
}

if (isset($_POST['metodoCtrlAct']) || $desdeMenu) {
    new ControladorActividades();
}
