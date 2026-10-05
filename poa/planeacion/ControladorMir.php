<?php

ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');


//INICIO DE SESIÓN Y LLAMADAS A LIBRERIAS
require_once dirname(__FILE__) . "/../../lib/constantes.php";
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/Indicador.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaIndicadorPed.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaProposito.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaComponente.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/sp_RegistroMetaActividad.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MensajeMir.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoModificacionMetaComponente.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoModificacionMetaFin.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoModificacionMetaProposito.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaActividadMir.php';
require_once dirname(__FILE__) . '/../../lib/poa/mir/AyudaContextualMir.php';

set_time_limit(0);

class ControladorMir extends WebResponse
{

    private $mir_id; #ID DE LA MIR
    private $vpm_id; #VINCULACION PROGRAMA MIR UR RESPONSABLE (vin_ProgramasMir)
    private $validador; #INDICA SI EL USUARIO ES VALIDADOR(TRUE O FALSE)
    private $urResponsable; #INDICA SI ES LA UR RESPONSABLE(TRUE O FALSE)
    private $estadoMir; #ESTADO DE LA MIR
    private $lectura = false; #INDICA SI PUEDE EDITAR O NO
    private $trimestreCierre;
    private $activo; #INDICA SI ESTA ACTIVO EL REGISTRO DE DATOS
    private $Trimestre;
    private $usrIp;
    private $usu;
    private $cargaUR;
    private $cargaPlaneacion;

    public function __construct()
    {

        parent::__construct();

        // EL MÓDULO ESTA ACTIVO SOLO ANTES DE INICIAR EL EJERCICIO
        $this->activo = date("Y-m-d H:i:s") <= APP_EJERCICIO_ANTERIOR.'-12-31 23:59:00' ? true : false;

        // VERIFICA SI ES USUARIO VALIDADOR Y SE ASIGNA A LA VARIABLE
        $this->validador = (isset($_SESSION[_S]['prm']['A']['12']['2']['60']) || #VALIDACIÓN GUBERNATURA
            isset($_SESSION[_S]['prm']['A']['12']['2']['61']) || #VALIDACIÓN EGRESOS
            isset($_SESSION[_S]['prm']['A']['12']['2']['62']) #VALIDACIÓN PLANEACIÓN
        );
        $this->lectura = $this->validador;
        $this->usu = $_SESSION[_S]['usu'];

        // CARGA DE ÁRBOL DEL PROBLEMA, ÁRBOL DE OBJETIVOS, SELECCIÓN DE ALTERNATIVAS Y DIAGNÓSTICO
        // ESTA ACTIVO HASTA EL 29 DE DICIEMBRE ANTES DE INICIAR EL EJERCICIO
        $this->cargaUR = date("Y-m-d H:i:s") <= APP_EJERCICIO_ANTERIOR.'-12-29 23:59:00' ? true : false;

        //VERIFICA SI EL USUARIO DE PLANEACION PUEDE REALIZAR LA CARGA 
        // CARGA DE ÁRBOL DEL PROBLEMA, ÁRBOL DE OBJETIVOS, SELECCIÓN DE ALTERNATIVAS Y DIAGNÓSTICO
        $this->cargaPlaneacion = ($this->usu == 'LGARCIAC' || $this->usu == 'MDIONICIOM' || $this->usu == 'ADIAZL' || $this->usu == 'NMARTINEZC') ? true : false;

        $parametros = array();

        // SE OBTIENE EL ESTADO DE LA MIR Y LA BANDERA DE UR RESPONSABLE
        if (!empty($_SESSION[_S]['clasAdm-prg'])) {
            $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];
            $validador = $this->validador;
            $gpo_id = $_SESSION[_S]['gpo_id'];
            $ur_id = $_SESSION[_S]['ur_id'];
            $nvj_id = $_SESSION[_S]['nvj_id'];

            $this->conectar();
            $this->setSql('
                SELECT  mir_id,  emi_id,vpm_id, 1 eup_mir
                FROM cat_Mir a
                INNER JOIN  vin_ProgramasMir USING(mir_id)
                WHERE prgs_id= :prg_id ' .
                (($validador || $nvj_id >= 3) ? '' : ' AND gpo_id = :gpo_id AND ur_id = :ur_id') . '
                UNION ALL
                SELECT  mir_id,  b.emi_id,vpm_id, 0 eup_mir
                FROM cat_Mir a
                INNER JOIN  vin_ProgramasMir USING(mir_id)
                INNER JOIN vin_UrProgramaMir b USING(vpm_id)
                WHERE b.prgs_id= :prg_id ' .
                (($validador || $nvj_id >= 3) ? '' : ' AND b.gpo_id = :gpo_id AND b.ur_id = :ur_id') . '
                ORDER BY eup_mir DESC;'
            );

            $parametros = ['prg_id' => $prg_id];
            if (!$validador && $nvj_id < 3) {
                $parametros['gpo_id'] = $gpo_id;
                $parametros['ur_id'] = $ur_id;
            }

            $this->consultar($parametros);
            $datos = $this->extraer();

            $this->estadoMir = $datos['emi_id'] != '' ? $datos['emi_id'] : 0;
            $this->estadoMir = $this->estadoMir * 1;

            $this->lectura = $this->estadoMir == 2 || $this->estadoMir == 4;

            $this->mir_id = $datos['mir_id'];
            $this->vpm_id = $datos['vpm_id'];
            $this->urResponsable = ($this->validador) ? false : ($datos['eup_mir'] == 1 ? true : false);
            $this->desconectar();
        }
        // ASIGNAR EL TRIMESTRE CORRIENTE

        $this->trimestreCierre = 0;

        switch ($this->metodo) {
            case 'pintaPantalla':
                $this->pintaPantalla();
                break;
            case 'listaIndicador':
                $this->verificarIndicadores();
                break;
            case 'modalMediosVerificacion':
                switch ($this->prm->nivel) {
                    case 1:
                    case 2:
                        $this->modalListadoMedioSupuesto('MedioVerificacion');
                        break;
                    case 3:
                        $this->modalListadoMedioSupuestoComponente('MedioVerificacion');
                        break;
                    case 4:
                        $this->modalListadoMedioSupuestoActividad('MedioVerificacion');
                }
                break;
            case 'abcMedioVerificacion':
                switch ($this->prm->nivel) {
                    case 1:
                    case 2:
                        $this->abcMedioSupuesto('MedioVerificacion');
                        break;
                    case 3:
                        $this->abcMedioSupuestoComponente('MedioVerificacion');
                        break;
                    case 4:
                        $this->abcMedioSupuestoActividad('MedioVerificacion');
                }
                break;
            case 'modalSupuesto':
                switch ($this->prm->nivel) {
                    case 1:
                    case 2:
                        $this->modalListadoMedioSupuesto();
                        break;
                    case 3:
                        $this->modalListadoMedioSupuestoComponente();
                        break;
                    case 4:
                        $this->modalListadoMedioSupuestoActividad();
                }
                break;
            case 'abcSupuesto':
                switch ($this->prm->nivel) {
                    case 1:
                    case 2:
                        $this->abcMedioSupuesto();
                        break;
                    case 3:
                        $this->abcMedioSupuestoComponente();
                        break;
                    case 4:
                        $this->abcMedioSupuestoActividad();
                }
                break;
            case 'addmodIndicador':
                $this->addmodIndicador();
                break;
            case 'listaIndNivel':
                $this->listaIndNivel();
                break;
            case 'llenaTablaIndicador':
                $this->llenaTablaIndicador();
                break;
            case 'addModMetaFin':
                $this->addModMetaFin();
                break;
            case 'abcMetaPed':
                $this->abcMetaPed();
                break;
            case 'addModMetaComponente':
                $this->addModMetaComponente();
                break;
            case 'abcMetaComponente':
                $this->abcMetaComponente();
                break;
            case 'addModMetaActividad':
                $this->addModMetaActividad();
                break;
            case 'editaMetaActividad':
                $this->editaMetaActividad();
                break;
            case 'guardarOrden':
                $this->guardarOrden();
                break;
            case 'addModMetaProposito':
                $this->addModMetaProposito();
                break;
            case 'abcMetaPes':
                $this->abcMetaPes();
                break;
            case 'abcMetaActividadMir':
                $this->abcMetaActividadMir();
                break;
            case 'enviarURParticipante':
                $this->enviarURParticipante();
                break;
            case 'validaUrParticipante':
                $this->validaUrParticipante();
                break;
            case 'enviar':
                $this->enviar();
                break;
            case 'addMensajeRechazo':
                $this->addMensajeRechazo();
                break;
            case 'rechazarMir':
                $this->rechazarMir();
                break;
            case 'validarMir':
                $this->validarMir();
                break;
            case 'uploadProblemas':
                $this->uploadArchivo('Arbol');
                break;
            case 'uploadObjetivo':
                $this->uploadArchivo('Objetivo');
                break;
            case 'uploadAlternativas':
                $this->uploadArchivo('Alternativas');
                break;
            case 'uploadDiagnostico':
                $this->uploadArchivo('Diagnostico');
                break;
            case 'abcResumenNarrativo':
                $this->abcResumenNarrativo();
                break;
            case 'verHistorial':
                $this->verHistorial();
                break;
            case 'verHistorialCambios':
                $this->verHistorialCambios();
                break;
            case 'llenaHistorialCambios':
                $this->llenaHistorialCambios();
            case 'download':
                $this->download();
                break;
            case 'actualizaMir':
                $this->actualizaMir();
                break;
            case 'modalMedioVer':
                $this->modalMedioVer();
                break;
            case 'main':
                $this->main();
                break;
            case 'existenMedios':
                $this->existenMedios();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {

        $clasAdmin = new stdClass();

        $clasAdmin->required = true;
        $clasAdmin->comboUe = false;
        $clasAdmin->comboSpr = false;
        $clasAdmin->comboPy = false;
        $clasAdmin->comboOa = false;
        $clasAdmin->change = '
            if($("#comboprg").val()!= ""){
                if($("#comboprg").val() * 1 >= 100 && $("#comboprg").val() <= 899 ){
                    comunica("' . $this->pantalla . '", {
                        metodo:"pintaPantalla"
                    });
                }
                if( $("#comboprg").val() * 1 >= 0 && $("#comboprg").val()<= 99  ) {
                    comunica("' . $this->pantalla . '", {
                        metodo:"listaIndicador",
                        nivel:0
                    });
                }
                if($("#comboprg").val()* 1 >= 900 && $("#comboprg").val() <= 999 ){
                    comunica("' . $this->pantalla . '", {
                        metodo:"listaIndicador",
                        nivel:0
                    });
                }
                $("#clasAdminSeccionCombos").hide();
            }$("#contenedor").html("<p class=\'text-danger\'>Seleccionar un Programa</p>");
            $("#techos").show();
        ';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = "circle-left me-1";
        $btnregresarindex->click = '$("nav").menusinpres("clickMenu");$("#clasAdminSeccionCombos").hide();';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $this->mixScript('$("#clasAdminSeccionCombos").show();'
            . ' $("#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();' . ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
    }

    private function pintaPantalla()
    {

        $parametros = array();
        $gpo_id = $_SESSION[_S]['gpo_id'];
        $ur_id = $_SESSION[_S]['ur_id'];

        //ALFONSO 2020-11-08: Se debe actualizar el estado de la MIR cada que refresquemos la pantalla.
        if ($this->validador || $this->urResponsable) {
            $sql = 'SELECT emi_id FROM cat_Mir WHERE mir_id = ?;';
            $parametros = [$this->mir_id];
        } else {
            $sql = 'SELECT emi_id FROM vin_UrProgramaMir WHERE vpm_id = ?
                AND gpo_id = ? AND ur_id = ?;';
            $parametros = [
                $this->vpm_id,
                $gpo_id,
                $ur_id,
            ];
        }
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        if ($row = $this->extraer()) {
            $this->estadoMir = $row['emi_id'];
        }
        $this->desconectar();

        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = "circle-left me-1";
        $btnregresarindex->click = 'comunica("' . $this->pantalla . '",{ metodo:"main"});';
        $this->agregaTools($this->ponBoton($btnregresarindex));
        $this->conectar();
        $this->setSql('SELECT mjm_id FROM cat_MensajeMir WHERE mir_id=?;');

        (isset($this->mir_id) ? $this->consultar([$this->mir_id]) : '');

        $btn = new stdClass();
        $btn->id = 'btnImprimir';
        $btn->type = 'button';
        $btn->title = 'Imprimir MIR Revisión';
        $btn->value = ' Revisión';
        $btn->class = "me-1";
        $btn->icon = "file-pdf";
        $btn->color = "rojo";

        $btnMI = new stdClass();
        $btnMI->id = 'btnImprimirMir';
        $btnMI->type = 'button';
        $btnMI->title = 'Imprimir MIR Inicial';
        $btnMI->value = ' Inicial';
        $btnMI->class = "me-1";
        $btnMI->icon = "file-pdf";
        $btnMI->color = "rojo";

        $btnMR2 = new stdClass();
        $btnMR2->id = 'btnImpRevision2';
        $btnMR2->type = 'button';
        $btnMR2->title = 'Imprimir MIRRevision 2';
        $btnMR2->value = ' Revision 2';
        $btnMR2->class = "me-1";
        $btnMR2->icon = "file-pdf";
        $btnMR2->color = "rojo";

        $btnP = new stdClass();
        $btnP->id = 'btnReportePrevio';
        $btnP->type = 'button';
        $btnP->title = 'Imprimir MIR Revisión Previo';
        $btnP->class = "me-1";
        $btnP->icon = "file-pdf";
        $btnP->color = "rojo";

        $botonI = '';
        $btnV = new stdClass();
        $btnV->id = 'btnImprimirValidacion';
        $btnV->type = 'button';
        $btnV->title = 'Imprimir MIR Validación';
        $btnV->value = ' Validación';
        $btnV->class = "me-1";
        $btnV->icon = "file-pdf";
        $btnV->color = "rojo";

        $btnE = new stdClass();
        $btnE->id = 'btnImprimirEjecutivo';
        $btnE->type = 'button';
        $btnE->title = 'Imprimir MIR Ejecutivo';
        $btnE->value = ' Ejecutivo';
        $btnE->class = "me-1";
        $btnE->icon = "file-pdf";
        $btnE->color = "rojo";

        $btnCE = new stdClass();
        $btnCE->id = 'btnImprimirCongreso';
        $btnCE->type = 'button';
        $btnCE->title = 'Imprimir MIR Congreso';
        $btnCE->value = ' Congreso';
        $btnCE->title = ' Congreso';
        $btnCE->class = "me-1";
        $btnCE->icon = "file-pdf";
        $btnCE->color = "rojo";

        $btnLeg = new stdClass();
        $btnLeg->id = 'btnImprimirLegislativo';
        $btnLeg->type = 'button';
        $btnLeg->title = 'Imprimir MIR Legislativo';
        $btnLeg->value = ' Legislativo';
        $btnLeg->class = "me-1";
        $btnLeg->icon = "file-pdf";
        $btnLeg->color = "rojo";

        $btnPeriodico = new stdClass();
        $btnPeriodico->id = 'btnImprimirPeriodico';
        $btnPeriodico->type = 'button';
        $btnPeriodico->title = 'Imprimir Periódico Oficial';
        $btnPeriodico->value = ' Periódico O.';
        $btnPeriodico->class = "me-1";
        $btnPeriodico->icon = "file-pdf";
        $btnPeriodico->color = "rojo";

        $btnFicha = new stdClass();
        $btnFicha->id = 'btnImprimirFicha';
        $btnFicha->type = 'button';
        $btnFicha->title = 'Imprimir MIR ';
        $btnFicha->value = ' Ficha I.';
        $btnFicha->class = "me-1";
        $btnFicha->icon = "file-pdf";
        $btnFicha->color = "rojo";

            $btn->click = '
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/mir/rep_MirRevision.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';

            $btnMI->click = '
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/mir/rep_MIRInicialN.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';

            $btnMR2->click = '
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/mir/rep_MIRRevision2.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';

            //Reporte LOGO GOBIERNO
            $btnE->click = '
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/mir/rep_MirValidacion.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                    .append($("<input>").attr("type", "hidden").attr("name", "logo").attr("value", 1 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';
            //Reporte LOGO CONGRESO
            $btnCE->click = '
                var form = $("<form>")
                .attr("action", "rep/doc/poa/mir/rep_MirValidacion3.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                    .append($("<input>").attr("type", "hidden").attr("name", "logo").attr("value", 2 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';

            $btnV->click = '
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/mir/rep_MirValidacion.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';

            $btnLeg->click = '
                var form = $("<form>")
                .attr("action", "rep/doc/poa/mir/rep_MirValidacion3.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "logo").attr("value", 3 ))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';

            $btnPeriodico->click = '
                var form = $("<form>")
                .attr("action", "rep/doc/poa/mir/rep_MirValidacion3.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "").attr("value", ""))
                    .append($("<input>").attr("type", "hidden").attr("name", "logo").attr("value", 4 ))
                    .append($("<input>").attr("type", "hidden").attr("name", "eri_trimestre").attr("value", 0 ))
                $("body").append(form);
                form.submit();
                form.remove();
            ';

            $btnFicha->click = '
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/mir/reporteFichaIndicador.php")
                    .attr("method", "post")
                    .attr("name", "Mir")
                    .attr("id", "Mir")
                    .attr("target", "_blank")
                $("body").append(form);
                form.submit();
                form.remove();
            ';

        $botonLogoEjecutivo = $botonLogoCongreso = $botonLogoLegislativo = $botonLogoPeriodico = $botonFichaIndicador = $botonMR = $botonMI = '';

        // Botones para los reportes con logos y Ficha Indicador
        if (isset($_SESSION[_S]['prm']['A']['12']['2']['62'])) {
            $botonMI = $this->ponBoton($btnMI);
            $botonLogoCongreso = $this->ponBoton($btnCE);
            $botonLogoLegislativo = $this->ponBoton($btnLeg);
            $botonLogoPeriodico = $this->ponBoton($btnPeriodico);
            $botonFichaIndicador = $this->ponBoton($btnFicha);
            $botonMR = $this->ponBoton($btnMR2);
        } else if (isset($_SESSION[_S]['prm']['A']['12']['2']['60']) || isset($_SESSION[_S]['prm']['A']['12']['2']['62']) || $this->usu == 'EROBLESC' || $this->usu == 'RHERNANDEZC') {
            $botonMR = $this->ponBoton($btnMR2);
        } else if (!($this->validador) && isset($_SESSION[_S]['prm']['A']['12']['2']['1'])) {
            $botonMI = $this->ponBoton($btnMI);
            $botonMR = $this->ponBoton($btnMR2);
        }
        if (isset($_SESSION[_S]['prm']['A']['12']['2']['1']) && ($this->usu == 'ALCRUZV' || $this->usu == 'ADIAZL'
            || $this->usu == 'NMARTINEZC' || $this->usu == 'JPEREZA' || $this->usu == 'DLOPEZV')) {
            # code...
            $botonLogoCongreso = $this->ponBoton($btnCE);
            $botonLogoLegislativo = $this->ponBoton($btnLeg);
            $botonLogoPeriodico = $this->ponBoton($btnPeriodico);
            $botonFichaIndicador = $this->ponBoton($btnFicha);
        }

        //if ($this->estadoMir == 9) {
            $botonI = $this->ponBoton($btnV);
        //} 

        $btnOrden = '';
        $btnOrdena = $btnActualiza = $botonHistorialC = '';
        if (isset($_SESSION[_S]['prm']['A']['12']['2']['62'])) {

            $btnO = new stdClass();
            $btnO->id = 'btnOrdenar';
            $btnO->type = 'button';
            $btnO->title = 'Ordenar actividades para imprimir';
            $btnO->class = "me-1";
            $btnO->value = 'Ordenar Actividades';
            $btnO->color = "azul";
            $btnOrden = $this->ponBoton($btnO);

            $btnO2 = new stdClass();
            $btnO2->id = 'btnOrdenar2';
            $btnO2->type = 'button';
            $btnO2->title = 'Ordenar Componentes para imprimir';
            $btnO2->class = "me-1";
            $btnO2->value = 'Ordenar Componentes';
            $btnO2->color = "azul";
            $btnOrdena = $this->ponBoton($btnO2);

            $botonHistorialC ='<button id="verHistorialCambios" type="button" class="btn btn-outline-secondary me-1" title="Ver historial de cambios"><i class="fa-solid fa-clock"></i></button>';

            $this->mixScript('$("#verHistorialCambios").click(function(){
                comunica( "' . $this->pantalla . '", { metodo:"verHistorialCambios" } );
                });');
        }
        $botonReportePrevio = '';
        

        //se comentan reportes mientras se modifican
        //$this->agregaTools($btnOrden . ' ' . $btnOrdena . ' ' .$botonMI . ' ' . $botonI . ' ' . $botonMR .' ' .  $botonReportePrevio . ' ' . $botonLogoCongreso . ' ' . $botonLogoLegislativo . ' ' . $botonLogoPeriodico . ' ' . $botonFichaIndicador . ' ' .  $botonHistorialC);
        $this->agregaTools($btnOrden . ' ' . $btnOrdena . ' ' .$botonMI . ' ' . $botonI . ' ' . $botonHistorialC );

        // BOTÓN PARA VALIDAR LOS DATOS DE UR PARTICIPANTE
        if ($this->urResponsable) {
            $btn = new stdClass();
            $btn->id = 'btnValidarUr';
            $btn->style = '';
            $btn->type = 'button';
            $btn->title = 'Validar datos de la UR participante';
            $btn->class = 'btn btn-outline-success me-1';
            $btn->icon = "solid fa-circle-check";
            $btn->click = 'comunica("' . $this->pantalla . '",{metodo:"validaUrParticipante"})';
            isset($_SESSION[_S]['prm']['A']['12']['2']['2']) ? $this->agregaTools($this->ponBoton($btn)) : '';
        }

        if ($this->activo && ($this->usu == 'LGARCIAC' || $this->usu == 'MDIONICIOM' || $this->usu == 'ALCRUZV' || $this->usu == 'ADIAZL' 
            ||  $this->usu == 'JHERNANDEZR' || $this->usu == 'NMARTINEZC')) {
            # code...
            $btnActualiza = new stdClass();
            $btnActualiza->id = 'btnActualizaM';
            if ($this->estadoMir == 9) {
                $btnActualiza->title = 'Actualizar MIR';
                $btnActualiza->icon = "fa fa-refresh me-2";
                $btnActualiza->value = "Actualizar";
                $btnActualiza->color = "gris";
            } else if ($this->activo && ($this->usu == 'LGARCIAC' || $this->usu == 'MDIONICIOM' || $this->usu == 'ALCRUZV' || $this->usu == 'ADIAZL' 
            ||  $this->usu == 'JHERNANDEZR' || $this->usu == 'NMARTINEZC') && $this->estadoMir == 10) {
                $btnActualiza->title = 'Validar Actualización';
                $btnActualiza->icon = "fa fa-check me-2";
                $btnActualiza->value = "Validar";
                $btnActualiza->color = "verde";
            }
            $btnActualiza->click = '
                alertify.confirm("ACTUALIZAR DATOS", "¿Confirma que desea actualizar la MIR del programa <h5 style=\"display:inline\">' . $_SESSION[_S]['clasAdm-prg'] . '</h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '", {
                            metodo: "actualizaMir",
                            nivel: "VALIDADOR",
                            prg_id:' . $_SESSION[_S]['clasAdm-prg'] . '
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            ';
        }

        // BOTON GUARDAR RESUMEN NARRATIVO PROPOSITO
        $addResumen = new stdClass();
        $addResumen->id = 'GuardaResumen';
        $addResumen->type = 'button';
        $addResumen->color = 'verde';
        $addResumen->value = 'Guardar';
        $addResumen->click = 'comunica("' . $this->pantalla . '",{
            metodo:"abcResumenNarrativo",
            nivel:"2",
            resumen:$("#narrativo").val()
            });';

        $botonResumen = $this->ponBoton($addResumen);

        // BOTON GUARDAR RESUMEN NARRATIVO FIN
        $addResumenFin = new stdClass();
        $addResumenFin->id = 'GuardaResumenFin';
        $addResumenFin->type = 'button';
        $addResumenFin->color = 'verde';
        $addResumenFin->value = 'Guardar';
        $addResumenFin->click = 'comunica("' . $this->pantalla . '",{
            metodo:"abcResumenNarrativo",
            nivel:"1",
            resumen:$("#narrativoFin").val()
            });';

        $botonResumenFin = $this->ponBoton($addResumenFin);
        // CAPTURAR RESUMEN NARRATIVO FIN
        $resumenFin = new stdClass();
        $resumenFin->id = 'narrativoFin';
        $resumenFin->required = true;
        $resumenFin->enDiv = true;
        $resumenFin->maxlength = 500;
        $resumenFin->width = '300px';
        $resumenFin->height = '100px';
        if ($_SESSION[_S]['ssc_id'] != 2 || ($this->estadoMir > 6 &&  $this->estadoMir != 11) || !($this->activo)) {
            $resumenFin->readonly = 'true';
            $botonResumenFin = "";
        }
        // CAPTURAR RESUMEN NARRATIVO PROPOSITO
        $resumen = new stdClass();
        $resumen->id = 'narrativo';
        $resumen->required = true;
        $resumen->enDiv = true;
        $resumen->maxlength = 600;
        $resumen->width = '300px';
        $resumen->height = '100px';
        if (!$this->urResponsable || $this->estadoMir > 5 || !$this->activo) {
            $resumen->readonly = 'true';
            $botonResumen = "";
        }

        if ($_SESSION[_S]['nvj_id'] <= 2 && !$this->validador && (
            ($this->urResponsable && $this->estadoMir == 1 || $this->estadoMir == 5) || (!$this->urResponsable && $this->estadoMir == 1 || $this->estadoMir == 3))) {

            $btn = new stdClass();
            $btn->id = 'btnEnviarR';
            $btn->type = 'button';
            $btn->title = "Enviar a Revisión de UR responsable";
            $btn->class = "btn btn-outline-info me-1 ";
            $btn->icon = "solid fa-paper-plane fa-lg";
            #Si no es validador y no es la UR responsable
            $URRespon = 0;
            if ($this->urResponsable) {
                $btn->title = 'Enviar a Planeacion';
                $URRespon = 1;
            }
            $btn->click = 'comunica("' . $this->pantalla . '",{
                metodo:"enviarURParticipante",
                metaFin:$(this).attr("metaFin"),
                metaProp:$(this).attr("metaProp"),
                supuestoPro:$(this).attr("supProp"),
                mediosVerPro:$(this).attr("medvProp"),
                urRespon:' . $URRespon . '});';

            isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $this->activo ? $this->agregaTools($this->ponBoton($btn)) : '';
        } elseif ($this->validador) {

            $btn = new stdClass();
            $btn->id = 'btnValidarMir';
            $btn->type = 'button';
            $btn->title = 'Enviar a revisión';
            $btn->class = "btn btn-outline-success me-1 ";
            $btn->icon = "fa fa-check me-2";
            $btn->value = "Validar";
            $btn->click = 'comunica("' . $this->pantalla . '",{metodo:"enviar"})';
            $btn2 = new stdClass();
            $btn2->id = 'btnRechazarMir';
            $btn2->type = 'button';
            $btn2->title = 'rechazar';
            $btn2->class = "me-1 ";
            $btn2->icon = 'solid fa-trash me-2';
            $btn2->value = ' Rechazar';
            $btn2->color = "rojo";
            $btn2->click = '
                alertify.confirm("RECHAZAR DATOS", "¿Confirma que desea rechazar la MIR del programa <h5 style=\"display:inline\">' . $_SESSION[_S]['clasAdm-prg'] . '</h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '", {
                            metodo: "addMensajeRechazo",
                            nivel: "VALIDADOR"
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            ';

            switch ($this->estadoMir) {
                case 6:
                case 11:
                    if (isset($_SESSION[_S]['prm']['A']['12']['2']['62']) && $this->activo) { //validación planeación
                        $this->agregaTools($this->ponBoton($btn));
                        $this->agregaTools($this->ponBoton($btn2));
                    }
                    break;
                case 7:
                    if (isset($_SESSION[_S]['prm']['A']['12']['2']['60']) && $this->activo) { //validación para gobernatura
                        $this->agregaTools($this->ponBoton($btn));
                        $this->agregaTools($this->ponBoton($btn2));
                    }
                    break;
                case 9:
                case 10:
                    if ($this->activo && ($this->usu == 'LGARCIAC' || $this->usu == 'MDIONICIOM')) { //validación para gobernatura
                        $this->agregaTools($this->ponBoton($btnActualiza));
                    }
                    break;
            }
        }

        if ($this->estadoMir == 9 || $this->estadoMir == 10) {
            if ($this->activo && ($this->usu == 'ALCRUZV' || $this->usu == 'ADIAZL' 
                ||  $this->usu == 'JHERNANDEZR' || $this->usu == 'NMARTINEZC')) {
                $this->agregaTools($this->ponBoton($btnActualiza));
            }
        }

        $this->mixHtml(AyudaContextualMir::css());
        $this->mixHtml('<fieldset class="col-md-12 col-xs-12">');

        if ($this->Trimestre == 1) {
            $this->mixHtml('<div class="alert alert-info alert-dismissable" align="center"><strong>MIR VALIDADA<br/> EJERCICIO FISCAL ' . APP_EJERCICIO . '</strong>');
        } else {
            switch ($this->estadoMir) {
                case 2:
                    if (!$this->urResponsable) {
                        $this->mixHtml('<div class="alert alert-info alert-dismissable" align="center"><strong>Enviado a validacion de UR responsable</strong>');
                    }

                    break;
                case 3:
                    $this->mixHtml('<div class="alert alert-danger alert-dismissable" align="center"><strong>Rechazado por Ur responsable</strong>');
                    break;
                case 4:
                    $this->mixHtml('<div class="alert alert-success alert-dismissable" align="center"><strong>Validado por Ur responsable</strong>');
                    break;
                case 6:
                    $this->mixHtml('<div class="alert alert-info alert-dismissable" align="center"><strong>Enviado a validacion de Planeación</strong>');
                    break;
                case 7: //VoBo PLANEACIÓN
                    $this->mixHtml('<div class="alert alert-info alert-dismissable" align="center"><strong>VoBo POR PLANEACIÓN<br/> EJERCICIO FISCAL ' . APP_EJERCICIO . '</strong>');
                    break;
                case 9: // MIR VALIDADA
                    $this->mixHtml('<div class="alert alert-info alert-dismissable" align="center"><strong>MIR VALIDADA<br/> EJERCICIO FISCAL ' . APP_EJERCICIO . '</strong>');
                    break;
                case 5: // MIR VALIDADA
                    $this->mixHtml('<div class="alert alert-danger alert-dismissable" align="center"><strong>MIR RECHAZADA<br/> EJERCICIO FISCAL ' . APP_EJERCICIO . '</strong>');
                    break;
                case 10: // MIR RECHAZADA GUBERNATURA
                    $this->mixHtml('<div class="alert alert-danger alert-dismissable" align="center"><strong>MIR EN ACTUALIZACIÓN<br/> EJERCICIO FISCAL ' . APP_EJERCICIO . '</strong>');
                    break;
                case 11: // MIR RECHAZADA GUBERNATURA
                    $this->mixHtml('<div class="alert alert-danger alert-dismissable" align="center"><strong>MIR RECHAZADA ENVIADA A PLANEACIÓN<br/> EJERCICIO FISCAL ' . APP_EJERCICIO . '</strong>');
                    break;
            }
        }

        $this->mixHtml('</div></fieldset>');

        $this->conectar();
        $this->setSql('
            SELECT   pm.gpo_id, pm.ur_id,mir_archivoArbol,  mir_archivoObjetivo, mir_archivoAlternativas, mir_archivoDiagnostico, epg_id,epo_id,
                count(distinct ind_fin) indicadoresFin,  IF(ISNULL(MAX(metaFin)),0,MAX(metaFin)) metaFin,obj_nombre fin,mir_resumenNarrativo,
                IF(ISNULL(MAX(mediosFin)),0,MAX(mediosFin)) mediosFin, IF(ISNULL(MAX(SupuestosFin)),0,MAX(SupuestosFin)) SupuestosFin
            FROM cat_Mir M
            INNER JOIN vin_ProgramasMir USING (mir_id)
            INNER JOIN vin_UrProgramaMir pm USING (vpm_id)
            INNER JOIN enc_UrSubprogramas eur ON eur.gpo_id=pm.gpo_id AND eur.ur_id=pm.ur_id AND eur.prgs_id=pm.prgs_id AND eur.sprs_id=pm.sprs_id
            INNER JOIN (
                SELECT epg_id, gpo_id, ur_id, prg_id as prgs_id, p.epo_id, obj_nombre
                FROM enc_Programatico e
                INNER JOIN vin_Programa v ON e.prg_id=v.prgs_id
                INNER JOIN enc_PedObjetivo p ON p.epo_id=v.epo_id
                INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
                WHERE gpo_id=:gpo_id AND ur_id=:ur_id
                AND prg_id=:prg_id AND spr_id=:valor AND py_id=:valor AND oa_id=:valor2
            )e ON e.gpo_id= eur.gpo_id AND e.ur_id=eur.ur_id AND e.prgs_id=eur.prgs_id
            LEFT JOIN (
                SELECT vpm_id, count(mtf_id) metaFin, nim_id, ind_id
                FROM vin_ProgramaMirIndicador
                INNER JOIN cat_MetaFin M USING (pmi_id)
                WHERE trim_0=:valor3 AND mtf_trimestreCierre=:trimestreCierre
                GROUP BY  ind_id,mtf_id, vpm_id,nim_id
            )m USING(vpm_id)
            LEFT JOIN (
                SELECT vpm_id,concat(nim_id,ind_id) ind_fin, nim_id, ind_id
                FROM vin_ProgramaMirIndicador v
                INNER JOIN cat_Indicador
                USING(nim_id,ind_id)
                WHERE nim_id=:valor3 AND v.trim_0=:valor3
            )s USING(vpm_id)
            LEFT JOIN (
                SELECT count(spm_id) SupuestosFin ,vpm_id
                FROM cat_SupuestoProgramaMir
                WHERE nim_id = :valor3 AND spm_trimestreCierre=:trimestreCierre
                AND spm_origen=:spm
                GROUP BY vpm_id
            )SupFin USING(vpm_id)
            LEFT JOIN (
                SELECT count(mvpm_id) mediosFin ,vpm_id
                FROM cat_MedioVerificacionProgramaMir
                WHERE nim_id = :valor3 AND mvpm_trimestreCierre=:trimestreCierre
                AND mvpm_origen=:spm
                GROUP BY vpm_id
            ) MevFin USING(vpm_id)
            WHERE pm.gpo_id=:gpo_id AND pm.ur_id=:ur_id
            AND pm.prgs_id=:prg_id
            GROUP BY  pm.gpo_id, pm.ur_id, pm.prgs_id,M.mir_id;
        ');

        $parametros = [
            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id' => $_SESSION[_S]['clasAdm-ur'],
            ':prg_id' => $prg_id,
            ':trimestreCierre' => $this->trimestreCierre,
            ':valor' => 00,
            ':valor2' => 000,
            ':valor3' => 1,
            ':spm' => "P",
        ];

        $this->consultar($parametros);
        $this->desconectar();
        $objetivo = $this->extraer();

        $this->conectar();
        $this->setSql('
            SELECT pm.gpo_id, pm.ur_id, mir_archivoArbol,   mir_archivoObjetivo, mir_archivoAlternativas, mir_archivoDiagnostico, vpm_resumenNarrativo proposito,
            count(distinct ind_pro) indicadoresPro, IF(ISNULL(MAX(metaProp)),0,MAX(metaProp)) metaProp,
            IF(ISNULL(mediosPro),0,mediosPro) mediosPro, IF(ISNULL(MAX(SupuestosPro)),0,MAX(SupuestosPro)) SupuestosPro
            FROM cat_Mir M
            INNER JOIN vin_ProgramasMir USING (mir_id)
            INNER JOIN vin_UrProgramaMir pm USING (vpm_id)
            INNER JOIN enc_UrSubprogramas eur ON eur.gpo_id=pm.gpo_id AND eur.ur_id=pm.ur_id AND eur.prgs_id=pm.prgs_id AND eur.sprs_id=pm.sprs_id
            LEFT JOIN (
                SELECT vpm_id, count(mpr_id) metaProp, nim_id, ind_id
                FROM vin_ProgramaMirIndicador
                INNER JOIN cat_MetaProposito USING(pmi_id)
                WHERE trim_0=:valor AND mpr_trimestreCierre=:trimestreCierre
                GROUP BY ind_id, mpr_id, vpm_id,nim_id
            )m USING(vpm_id)
            LEFT JOIN (
                SELECT vpm_id,concat(nim_id,ind_id) ind_pro, nim_id, ind_id
                FROM vin_ProgramaMirIndicador v
                INNER JOIN cat_Indicador
                USING(nim_id,ind_id)
                WHERE nim_id=:nim AND v.trim_0=:valor
            )s USING(vpm_id)
            LEFT JOIN (
                SELECT count(spm_id) SupuestosPro ,vpm_id
                FROM cat_SupuestoProgramaMir
                WHERE nim_id =:nim AND spm_trimestreCierre=:trimestreCierre
                AND spm_origen=:mvpm
                GROUP BY vpm_id
            )SupProp USING(vpm_id)
            LEFT JOIN (
                SELECT count(mvpm_id) mediosPro ,vpm_id
                FROM cat_MedioVerificacionProgramaMir
                WHERE nim_id =:nim AND mvpm_trimestreCierre=:trimestreCierre
                AND mvpm_origen=:mvpm
                GROUP BY vpm_id
            ) MevProp USING(vpm_id)
            WHERE pm.gpo_id=:gpo_id AND pm.ur_id=:ur_id
            AND pm.prgs_id=:prg_id
            GROUP BY  pm.gpo_id, pm.ur_id, pm.prgs_id,M.mir_id,vpm_id;
        ');
        $parametros = [
            ':trimestreCierre' => $this->trimestreCierre,
            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id' => $_SESSION[_S]['clasAdm-ur'],
            ':prg_id' => $prg_id,
            ':valor' => 1,
            ':nim' => 2,
            ':mvpm' => "P",
        ];
        $this->consultar($parametros);
        $this->desconectar();
        $proposito = $this->extraer();

        // CARGAR RESUMEN NARRATIVO
        $resumen->value = strtoupper($proposito['proposito']);
        $resumenFin->value = strtoupper($objetivo['mir_resumenNarrativo']);

        $html = '';
        //div para cargar los archivos en PDF -- (Árbol de problema, Árbol de objetivos, Selección de alternativas)
        $nombre = str_pad($_SESSION[_S]['clasAdm-prg'], 3, "0", STR_PAD_LEFT);

        $html = '
            <div class="row" id="archivos" >

            <div class="col-md-3 col-xs-3">
                <label>Árbol de problema: </label><a class="lnk_ext" id="nombreArchProb" tabindex=-1 lnk="PLANEACION/' . $prg_id . '/arb_problemas/' . $nombre . '_arbolArbol.pdf?npi='
        . rand(1, 99999) . '" target="_blank">' . $objetivo['mir_archivoArbol'] . '</a><br />

                ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '<input type="file" name ="ArbolFile" id="nombreArchProblema"
                                data-url="poa/planeacion/ControladorMir.php" data-form-data=\'{"metodo":"uploadProblemas", "opcion":"' . $this->prm->opcion->claveOpcion . '"}\'/>
                ' : '') . '
                <br/>
                ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '
                <div id="progressPrb" class="progress progress-striped">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        <span class="sr-only">100% Complete</span>
                    </div>
                </div>
                ' : '') . '
            </div>
            <div class="col-md-3 col-xs-3">
                <label>Árbol de objetivos: </label><a class="lnk_ext" id="nombreArchObjetivo" tabindex=-1  lnk="PLANEACION/' . $prg_id . '/arb_objetivos/' . $nombre . '_arbolObjetivo.pdf" target="_blank">' . $objetivo['mir_archivoObjetivo'] . '</a><br />
                    ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '<input type="file" name ="ObjetivoFile" id="nombreArchObj"
                            data-url="poa/planeacion/ControladorMir.php" data-form-data=\'{"metodo":"uploadObjetivo", "opcion":"' . $this->prm->opcion->claveOpcion . '"}\' >
                    ' : '') . '
                    <br/>
                    ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '
                    <div id="progressObj" class="progress progress-striped">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                    ' : '') . '
                </div>
                <div class="col-md-3 col-xs-3">
                <label>Selección de alternativas: </label><a class="lnk_ext" id="nombreArchAlternativas" tabindex=-1  lnk="PLANEACION/' . $prg_id . '/sel_alternativas/' . $nombre . '_arbolAlternativas.pdf" target="_blank">' . $objetivo['mir_archivoAlternativas'] . '</a><br />
                    ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '<input type="file" name ="AlternativasFile" id="nombreArchAlterna"
                        data-url="poa/planeacion/ControladorMir.php" data-form-data=\'{"metodo":"uploadAlternativas", "opcion":"' . $this->prm->opcion->claveOpcion . '"}\'/>
                    ' : '') . '
                    <br/>
                    ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '
                    <div id="progressAlt" class="progress progress-striped">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                    ' : '') . '
                </div>
                <div class="col-md-3 col-xs-3">
                <label>Diagnóstico: </label><a class="lnk_ext" id="nombreArchDiagnostico" tabindex=-1  lnk="PLANEACION/' . $prg_id . '/diagnostico/' . $nombre . '_arbolDiagnostico.pdf" target="_blank">' . $objetivo['mir_archivoDiagnostico'] . '</a><br />
                    ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '<input type="file" name ="DiagnosticoFile" id="nombreArchDiagnos"
                        data-url="poa/planeacion/ControladorMir.php" data-form-data=\'{"metodo":"uploadDiagnostico", "opcion":"' . $this->prm->opcion->claveOpcion . '"}\'/>
                    ' : '') . '
                    <br/>
                    ' . (($this->urResponsable && $this->cargaUR && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) || $this->cargaPlaneacion) ? '
                    <div id="progressDiag" class="progress progress-striped">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                    ' : '') . '
                </div>

            </div>
            <br>
            <div class="panel panel-info">
                <div class="panel-heading "> <h3 class="panel-title" style="color:#000;">FIN</h3> </div>
                    <div class="panel-body">
                        <table class="table table-hover">
                            <tr>
                                <th style="vertical-align: middle;">Resumen Narrativo</th>
                                <th style="text-align:center; width:5px" >Indicador</th>
                                <th style="text-align:center; width:5px" >Med. Verificación</th>
                                <th style="text-align:center; width:5px" >Supuesto</th>
                            </tr>
                            <tr>
                                <td style="color:#000; vertical-align: middle;"> ' . $this->ponInput($resumenFin) . $botonResumenFin . '</td>
                                <td style="text-align:center; width:5px" ><button type="button" id="indicadorFin" class=" btn btn-outline-secondary indicador " nivel=1 title="">' . $objetivo['indicadoresFin'] . '</td>
                                <td style="text-align:center; width:5px" ><button type="button" id="medioVeriFin" class=" btn btn-outline-secondary medioV " nivel=1 epo_id=' . $objetivo['epo_id'] . ' eps_id=0 pdf_id=0 pdi_id=0 title="">' . $objetivo['mediosFin'] . '</td>
                                <td style="text-align:center; width:5px" ><button type="button" id="supuestosFin" class=" btn btn-outline-secondary supuesto " nivel=1 epo_id=' . $objetivo['epo_id'] . ' eps_id=0 pdf_id=0 pdi_id=0 title="">' . $objetivo['SupuestosFin'] . '</td>
                            <tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel-heading "> <h3 class="panel-title" style="color:#000;">PROPÓSITO</h3> </div>
                <div class="panel-body">
                    <table class="table table-hover">
                        <tr>
                            <th style="vertical-align: middle;">Resumen Narrativo</th>
                            <th style="text-align:center; width:5px" >Indicador</th>
                            <th style="text-align:center; width:5px" >Med. Verificación</th>
                            <th style="text-align:center; width:5px" >Supuesto</th>
                        </tr>
                        <tr>
                            <td style="color:#000; vertical-align: middle;"> ' . $this->ponInput($resumen) . $botonResumen . '</td>
                            <td style="text-align:center; width:5px" ><button type="button" id="indicadorPro" class=" btn btn-outline-secondary indicador " nivel=2 title="">' . $proposito['indicadoresPro'] . '</td>
                            <td style="text-align:center; width:5px" ><button type="button" id="medioVeriPro" class=" btn btn-outline-secondary medioV " nivel=2 eps_id=0 epe_id=0 pdf_id=0 pdi_id=0 title="">' . $proposito['mediosPro'] . '</td>
                            <td style="text-align:center; width:5px" ><button type="button" id="supuestosPro" class=" btn btn-outline-secondary supuesto " nivel=2 eps_id=0 epe_id=0 pdf_id=0 pdi_id=0 title="">' . $proposito['SupuestosPro'] . '</td>
                        <tr>
                    </table>
                </div>
            </div>
        ';

        $this->mixHtml($html);

        $this->mixScript(
            '
            // para poder visualizar los PDF
            $(".lnk_ext").click(function(){
                lnk = $(this).attr("lnk");
                tit = $(this).html();

                if (tit != "Sin archivo") {
                    load_viewer("vis1",tit,"F");
                    comunica("' . $this->pantalla . '",{metodo:"download",lnk:lnk},false,true);
                }
            });

            $("#medioVeriFin,#supuestosFin").prop("nivel",1);
            $("#medioVeriPro,#supuestosPro").prop("nivel",2);
            $("#indicadorFin").prop("nivel",1);
            $("#indicadorPro").prop("nivel",2);
            $(".indicador").click(function(){
                var p = {
                    metodo:"listaIndicador",
                    nivel: $(this).prop("nivel")
                };
                if($(this).prop("nivel")> 2)p.pdf_id = $(this).prop("pdf_id");
                if($(this).prop("nivel")==4)p.pdi_id = $(this).prop("pdi_id");
                if($(this).prop("nivel")==4)p.com_id = $(this).attr("com_id");
                comunica("' . $this->pantalla . '",p);
            });
            $(".medioV").click(function(){
                var p = {
                    metodo:"modalMediosVerificacion",
                    nivel: $(this).prop("nivel"),
                    epe_id: $(this).attr("epe_id"),
                    eps_id: $(this).attr("eps_id"),
                    pdf_id: $(this).attr("pdf_id"),
                    pdi_id: $(this).attr("pdi_id")
                };
                if($(this).prop("nivel")>2)p.pdf_id = $(this).prop("pdf_id");
                if($(this).prop("nivel")==4)p.pdi_id = $(this).prop("pdi_id");
                comunica("' . $this->pantalla . '",p);
                });
            $(".supuesto").click(function(){
                var p = {
                    metodo:"modalSupuesto",
                    nivel: $(this).prop("nivel"),
                    epe_id: $(this).attr("epe_id"),
                    eps_id: $(this).attr("eps_id"),
                    pdf_id: $(this).attr("pdf_id"),
                    pdi_id: $(this).attr("pdi_id")
                };
                if($(this).prop("nivel")>2)p.pdf_id = $(this).prop("pdf_id");
                if($(this).prop("nivel")==4)p.pdi_id = $(this).prop("pdi_id");
                comunica("' . $this->pantalla . '",p);
            });
            '
        );

        $this->conectar();
        $sql = '
            SELECT sprs_id com_id, pdf_id, pdf_mir componente,
                IF(ISNULL (MAX(ind_com)),0,MAX(ind_com)) AS indicadoresCom,
                IF(ISNULL(MediosCom),0,MediosCom) MediosCom,
                IF(ISNULL(SupuestosCom),0,SupuestosCom) SupuestosCom,
                COALESCE(m.epfi_id, i.epfi_id) AS epfi_id, i.epfi_orden
            FROM cat_Subprogramas
            INNER JOIN enc_UrSubprogramas USING(prgs_id, sprs_id)
            INNER JOIN cat_ProductoFinal USING(pdf_id)
            LEFT JOIN(
                SELECT pdf_id, epfi_id, COUNT(*) metaComponente, nim_id, ind_id, e.usr_fecha, trim_0
                FROM enc_ProductoFinalIndicador e
                INNER JOIN cat_MetaComponente USING(epfi_id)
                WHERE trim_0=:valor AND mco_trimestreCierre=:trimestreCierre
                GROUP BY pdf_id,epfi_id,nim_id,ind_id
            )m USING(pdf_id)
            LEFT JOIN(
                SELECT pdf_id, epfi_id, COUNT(ind_id) AS ind_com, nim_id, MAX(ind_id), epfi_orden, trim_0
                FROM enc_ProductoFinalIndicador e
                INNER JOIN cat_Indicador USING(nim_id,ind_id)
                WHERE e.trim_0=:valor
                GROUP BY pdf_id,nim_id
            )i USING(pdf_id)
            LEFT JOIN (
                SELECT pdf_id, COUNT(mvcm_id) MediosCom
                FROM cat_MedioVerificacionComponente
                INNER JOIN cat_Subprogramas USING (prgs_id, sprs_id)
                WHERE mvcm_trimestreCierre=:trimestreCierre
                AND mvcm_origen="P"
                GROUP BY pdf_id
            )Mev USING(pdf_id)
            LEFT JOIN (
                SELECT pdf_id, COUNT(spc_id) SupuestosCom
                FROM cat_SupuestoComponente
                INNER JOIN cat_Subprogramas USING (prgs_id, sprs_id)
                WHERE spc_trimestreCierre=:trimestreCierre AND spc_origen="P"
                GROUP BY pdf_id
            )Sup USING(pdf_id)
            LEFT JOIN cat_Subprograma USING(gpo_id,ur_id, pdf_id)
            WHERE ' . (((!$this->urResponsable && $_SESSION[_S]['nvj_id'] <= 2) && !$this->validador) ? '
            gpo_id =:gpo_id AND ur_id =:ur_id AND
            ' : '') . '
            prgs_id =:prg_id AND (esr_id=:esr_id OR sprs_id=:sprs_id) AND pdf_id!=:valor2 AND spr_status=1
            ' . (((!$this->urResponsable && $_SESSION[_S]['nvj_id'] <= 2) && !$this->validador) ? '
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id, epfi_orden;
            ' : '
            GROUP BY prgs_id, sprs_id, epfi_orden
            ORDER BY epfi_orden;
            ');
        $parametros = [
            ':trimestreCierre' => $this->trimestreCierre,
            ':prg_id' => $prg_id,
            ':valor' => 1,
            ':valor2' => 0,
            ':esr_id' => 4,
            ':sprs_id' => 0,
        ];
        if ((!$this->urResponsable && $_SESSION[_S]['nvj_id'] <= 2) && !$this->validador) {
            $parametros[':gpo_id'] = $_SESSION[_S]['clasAdm-gpo'];
            $parametros[':ur_id'] = $_SESSION[_S]['clasAdm-ur'];
        }
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();
        $componente = '';
        $htmlCA = '';
        $colspanComp = (isset($_SESSION[_S]['prm']['A']['12']['2']['62']) ? 5 : 4);
        $conActividad = new AccesoDatos();
        $conActividad->conectar();

        AyudaContextualMir::habilitarEnTodosLosComponentes(); //para activar la ayuda contextual en todos los componentes.

        while ($row = $this->extraer()) {
            if ($row['com_id'] != $componente) {
                $componenteM = $row['com_id'];
                $collapseId = 'mirCompCollapse-' . $row['com_id'];
                $htmlCA .= '
                    <div class="panel panel-default mir-comp-panel">
                        <div class="panel-heading mir-comp-toggle" role="button" tabindex="0"
                            data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '"
                            title="Clic para desplegar u ocultar el contenido del componente"
                            style="cursor:pointer; border-bottom:2px solid #8B0000; padding:10px 15px;">
                            <div style="display:flex; align-items:center; width:100%; gap:1.25rem;">
                                <h3 class="panel-title" style="color:#000; margin:0; flex:0 0 auto;">COMPONENTE ' . $row['com_id'] . '</h3>
                                <span class="mir-comp-indicador" style="display:flex; align-items:center; gap:8px; color:#555; margin-left:auto; padding-left:1.25rem; flex:0 0 auto;">
                                    <small class="mir-comp-hint" style="margin-left:0.5rem;">Clic para desplegar</small>
                                    <span class="glyphicon glyphicon-chevron-down mir-comp-chevron" aria-hidden="true"></span>
                                </span>
                            </div>
                        </div>
                        <div id="' . $collapseId . '" class="collapse mir-comp-collapse">
                            <div class="panel-body">
                                <table class="table table-hover mir-comp-table">
                                    <tr>
                                        ' . (isset($_SESSION[_S]['prm']['A']['12']['2']['62']) ? '<th style="vertical-align: middle;">Orden Impresión</th>' : '') . '
                                        <th style="vertical-align: middle;">ID - Resumen Narrativo</th>
                                        ' . AyudaContextualMir::th('componente', 'indicador', 'Indicador', 'text-align:center; width:5px', $row['com_id']) . '
                                        ' . AyudaContextualMir::th('componente', 'medio_verificacion', 'Med. Verificación', 'text-align:center; width:5px', $row['com_id']) . '
                                        ' . AyudaContextualMir::th('componente', 'supuesto', 'Supuesto', 'text-align:center; width:5px', $row['com_id']) . '
                                    </tr>
                                    <tr>
                                        ' . (isset($_SESSION[_S]['prm']['A']['12']['2']['62']) ? '<td style="color:#000; vertical-align: middle;"><input type="text" name="" id="filasImprimir-' . $row['epfi_id'] . '" class="filasImprimir"' . '" pdf="' . $row['pdf_id'] . '" epfi_id="' . $row['epfi_id'] . '" value="' . $row['epfi_orden'] . '"/></td>' : '') . '
                                        <td style="color:#000; vertical-align: middle;">' . $row['pdf_id'] . ' - ' . $row['componente'] . '</td>
                                        <td style="text-align:center; width:5px" ><button type="button" id="indicadorCom' . $row['pdf_id'] . '" class=" btn btn-outline-secondary indicador com" nivel=3 title="">' . $row['indicadoresCom'] . '</td>
                                        <td style="text-align:center; width:5px" ><button type="button" id="medioVeriCom' . $row['pdf_id'] . '" epe_id=0 eps_id=0 pdf_id="' . $row['pdf_id'] . '" pdi_id=0 class=" btn btn-outline-secondary medioV com" nivel=3 title="">' . $row['MediosCom'] . '</td>
                                        <td style="text-align:center; width:5px" ><button type="button" id="supuestosCom' . $row['pdf_id'] . '" epe_id=0 eps_id=0 pdf_id="' . $row['pdf_id'] . '" pdi_id=0 class=" btn btn-outline-secondary supuesto com" nivel=3 title="">' . $row['SupuestosCom'] . '</td>
                                    </tr>
                                    <tr>
                                        <td colspan="' . $colspanComp . '">
                                            <div class="mir-actividades-block warning" style="background: rgb(255, 234, 168); padding: 8px; margin-top: 8px;">
                                                <h4 class="panel-title warning" style="color:#000;">ACTIVIDADES</h4>
                                                <table class="table table-hover">
                                                    <tr>
                                                ' . (isset($_SESSION[_S]['prm']['A']['12']['2']['62']) ? '<th style="vertical-align: middle;">Orden Impresión</th>' : '') . '
                                                <th style="vertical-align: middle;">ID</th>
                                                <th style="vertical-align: middle;">Resumen Narrativo</th>
                                                <th style="text-align:center; width:5px" >Indicador</th>
                                                <th style="text-align:center; width:5px" >Med. Verificación</th>
                                                <th style="text-align:center; width:5px" >Supuesto</th>
                                            </tr>
                ';
                $conActividad->setSql('
                    SELECT  pdf_id, pdi_id, pdi_mir AS pdi_nombre,
                        COUNT(DISTINCT pii_id) IndAct,
                        IF(ISNULL(SupuestosAct),0,SupuestosAct) SupuestosAct,
                        IF(ISNULL(MedioVerAct),0,MedioVerAct) MedioVerAct, MAX(pii_id) pii_id, pii_orden
                    FROM vin_ProductoIntermedioIndicador vpii
                    JOIN cat_ProductoIntermedio USING(pdi_id)
                    INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING(pdf_id, pdi_id)
                    LEFT JOIN(
                    SELECT pdf_id, pdi_id, COUNT(spa_id) SupuestosAct
                    FROM cat_SupuestoActividad
                    WHERE spa_trimestreCierre=:trimestreCierre
                    AND spa_origen=:smac
                    GROUP BY pdf_id, pdi_id
                    )actSup USING(pdf_id, pdi_id)
                    LEFT JOIN(
                    SELECT pdf_id, pdi_id, COUNT(mvac_id) MedioVerAct
                    FROM cat_MedioVerificacionActividad
                    WHERE mvac_trimestreCierre=:trimestreCierre
                    AND mvac_origen=:smac
                    GROUP BY pdf_id, pdi_id
                    )actMev USING(pdf_id, pdi_id)
                    WHERE pdf_id =:pdf_id AND  pdi_id != :valor2 AND vpii.trim_0 = :valor
                    AND vpfi.sts_id=:valor
                    GROUP BY pdi_id, pdi_nombre,pii_orden
                    ORDER BY pii_orden;
                ');

                $parametros = [
                    ':trimestreCierre' => $this->trimestreCierre,
                    ':pdf_id' => $row['pdf_id'],
                    ':smac' => "P",
                    'valor' => 1,
                    'valor2' => 0,
                ];

                $conActividad->consultar($parametros);
                while ($act = $conActividad->extraer()) {
                    $htmlCA .= '
                        <tr>
                            ' . (isset($_SESSION[_S]['prm']['A']['12']['2']['62']) ? '<td style="color:#000; vertical-align: middle;"><input type="text" name="" id="filasImprimir-' . $act['pdi_id'] . '" class="filasImprimir" act="' . $act['pdi_id'] . '" nom="' . $act['pdi_nombre'] . '" pdf="' . $row['pdf_id'] . '" value="' . $act['pii_orden'] . '"/></td>' : '') . '
                            <td style="color:#000; vertical-align: middle;">' . $act['pdi_id'] . '</td>
                            <td style="color:#000; vertical-align: middle;">' . $act['pdi_nombre'] . '</td>
                            <td style="text-align:center; width:5px" ><button type="button" id="indicadorAct' . $act['pdi_id'] . '" pdf_id="' . $row['pdf_id'] . '" com_id="' . $componenteM . '" class=" btn btn-outline-secondary indicador act" nivel=4 title="">' . $act['IndAct'] . '</td>
                            <td style="text-align:center; width:5px" ><button type="button" id="medioVeriAct' . $act['pdi_id'] . '" pdf_id="' . $row['pdf_id'] . '" epe_id=0 eps_id=0 pdf_id="' . $row['pdf_id'] . '" pdi_id="' . $act['pdi_id'] . '" class=" btn btn-outline-secondary medioV act" nivel=4 title="">' . $act['MedioVerAct'] . '</td>
                            <td style="text-align:center; width:5px" ><button type="button" id="supuestosAct' . $act['pdi_id'] . '" pdf_id="' . $row['pdf_id'] . '" epe_id=0 eps_id=0 pdf_id="' . $row['pdf_id'] . '" pdi_id="' . $act['pdi_id'] . '" class=" btn btn-outline-secondary supuesto act" nivel=4 title="">' . $act['SupuestosAct'] . '</td>
                        </tr>

                    ';
                }
            }
            $componente = $row['com_id'] * 1;

            $htmlCA .= '
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                <div id="tablaCompAct"></div>';
        }

        $this->mixHtml($htmlCA);

        $this->mixScript(AyudaContextualMir::js());

        $this->mixScript('

            $(".mir-comp-panel .panel-body button, .mir-comp-panel .panel-body input").click(function(e) {
                e.stopPropagation();
            });

            $(".mir-comp-toggle").click(function(e) {
                if ($(e.target).closest("button, input, a, .btn").length) {
                    return;
                }
                var target = $(this).attr("data-bs-target");
                if (target && typeof bootstrap !== "undefined") {
                    var collapseEl = document.querySelector(target);
                    if (collapseEl) {
                        bootstrap.Collapse.getOrCreateInstance(collapseEl).toggle();
                    }
                }
            });

            $(".mir-comp-collapse").on("show.bs.collapse", function() {
                var $panel = $(this).closest(".mir-comp-panel");
                $panel.find(".mir-comp-chevron")
                    .removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
                $panel.find(".mir-comp-hint").text("Clic para ocultar");
                $panel.find(".mir-comp-toggle").attr("aria-expanded", "true");
                if (typeof initMirAyudaContextual === "function") {
                    initMirAyudaContextual(this);
                }
            }).on("hide.bs.collapse", function() {
                var $panel = $(this).closest(".mir-comp-panel");
                $panel.find(".mir-comp-chevron")
                    .removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down");
                $panel.find(".mir-comp-hint").text("Clic para desplegar");
                $panel.find(".mir-comp-toggle").attr("aria-expanded", "false");
            });

            $(".indicador.com,.medioV.com,.supuesto.com").each(function(){
                var c = $(this).prop("id");
                $(this).prop("pdf_id",c.substring(12,c.length)).prop("nivel",3);
            });
            $(".indicador.act,.medioV.act,.supuesto.act").each(function(){
                var c = $(this).prop("id");
                $(this).prop("pdi_id",c.substring(12,c.length)).prop("nivel",4);
                $(this).prop("pdf_id", $(this).attr("pdf_id"));
                
            });

            var orden = new Array();

            $("input[id=ckecfilasImprimir]").click(function(){
                if($(this).is(":checked")){
                    $("#filasImprimir-"+$(this).attr("com")).append(
                        $("<tr>").attr("id","filasImprimir-"+$(this).attr("act"))
                            .attr("class","registro")
                            .append("<td>"+$(this).attr("act")+"</td>")
                            .append("<td>"+$(this).attr("nom")+"</td>")
                            .append($("<input>").attr("type", "hidden").attr("name", "ckecfilasImprimir2[]").attr("value", "{\"pdf_id\":\""+$(this).attr("com")+"\",\"pdi_id\":\""+$(this).attr("act")+"\"}").attr("form","Mir"))
                            .css("color","#000")
                    );
                    orden.push($(this).attr("act"));
                }else{
                    $("#filasImprimir-"+$(this).attr("act")).remove();
                    orden.splice(orden.indexOf($(this).attr("act")),1);
                }

                console.log("ord-"+ orden + " or-"+orden.indexOf($(this).attr("act")));
            });

            $(".filasImprimir").blur(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"guardarOrden",
                    eri_id:$(this).attr("eri_id"),
                    orden:$(this).val(),
                    pdi_id:$(this).attr("act"),
                    ipf_id:$(this).attr("epfi_id"),
                    pdf_id:$(this).attr("pdf")
                });
            });

            $("#btnOrdenar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaPantalla"
                });
            });

            $("#btnOrdenar2").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaPantalla"
                });
            });


            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnregresarindex").click(); e.stopPropagation(); });

            $("#nombreArchObj").click(function(){
                $("#progress .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
                });

            $("#nombreArchAlterna").click(function(){
                $("#progress .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
                });

            $("#nombreArchProblema").click(function(){
                $("#progress .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
                });

            $("#nombreArchObj").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#nombreArchObjetivo").text(validacion.nombre);
                    alert(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressObj .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });

            $("#nombreArchAlterna").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#nombreArchAlternativas").text(validacion.nombre);
                    alert(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressAlt .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });

            $("#nombreArchProblema").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#nombreArchProb").text(validacion.nombre);
                    alert(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressPrb .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });

            $("#nombreArchDiagnos").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#nombreArchDiagnostico").text(validacion.nombre);
                    alert(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressDiag .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });

            $("#btnEnvValMir").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaciones",
                    estadoM: 1
                });
            });

            $("#btnVoBo").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaciones",
                    estadoM:2
                });
            });

            $("#btnRecibir").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaciones",
                    estadoM:3
                });
            });
        ');

        $this->mixScript('
            $(".indicador2").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"listaIndicador",
                    nivel: $(this).attr("nivel"),
                    pro_id:$(this).attr("pro_id"),
                    com_id:$(this).attr("com"),
                    act_id:$(this).attr("act_id")
                });
            });

            $(".supuesto2").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"modalSupuesto",
                    nivel: $(this).attr("nivel"),
                    pro_id:$(this).attr("pro_id"),
                    com_id:$(this).attr("com"),
                    act_id:$(this).attr("act_id")
                });
            });

            $("#btnEnviarR").attr("metaFin","' . $objetivo['metaFin'] . '");
            $("#btnEnviarR").attr("supProp","' . $proposito['SupuestosPro'] . '");
            $("#btnEnviarR").attr("metaProp","' . $proposito['metaProp'] . '");
            $("#btnEnviarR").attr("medvProp","' . $proposito['mediosPro'] . '");

        ');
    }

    private function verificarIndicadores()
    {

        $parametros = array();

        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->icon = 'circle-left me-2';
        $regresar->color = 'gris';
        $regresar->value = 'Regresar';
        $regresar->click = '
            $("nav").menusinpres("clickOpcion");
        ';

        switch ($this->prm->nivel) {
            case 0:
                $this->agregaTools($this->ponBoton($regresar));
                $this->mixHtml('
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                            <strong>ESTE PROGRAMA NO TIENE MATRÍZ DE INDICADORES PARA RESULTADOS</strong>
                        </div>
                    </fieldset>
                ');
                break;
            case 1:
                $this->setSql('
                    SELECT  COUNT(distinct nim_id,ind_id) cuantos,MAX(ind_id) AS ind_id,MAX(pmi_id) as eri_id, MAX(umi_id) AS umi_id
                    FROM vin_ProgramaMirIndicador v
                    INNER JOIN vin_ProgramasMir USING (vpm_id)
                    INNER JOIN cat_Indicador USING(ind_id,nim_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING (umi_id)
                    WHERE prgs_id=:prg_id
                    AND nim_id = :valor AND v.trim_0=:valor
                    HAVING cuantos != 0;
                ');
                $parametros = [
                    ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                    ':valor' => 1,
                ];
                break;
            case 2:
                $this->setSql('
                    SELECT COUNT(distinct nim_id,ind_id) cuantos,MAX(ind_id) AS ind_id, MAX(pmi_id) AS eri_id, MAX(umi_id) AS umi_id
                    FROM vin_ProgramaMirIndicador vp
                    INNER JOIN (
                        SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id
                        FROM vin_ProgramasMir
                        UNION
                        SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id
                        FROM vin_UrProgramaMir ur
                        INNER JOIN vin_ProgramasMir USING(vpm_id)
                    )v USING (vpm_id)
                    INNER JOIN cat_Indicador USING(ind_id,nim_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    WHERE gpo_id =? AND ur_id =?
                    AND prgs_id =?
                    AND nim_id = ? AND vp.trim_0=?
                    HAVING cuantos != 0;
                ');
                $parametros = [
                    $_SESSION[_S]['clasAdm-gpo'],
                    $_SESSION[_S]['clasAdm-ur'],
                    $_SESSION[_S]['clasAdm-prg'],
                    2,
                    1,
                ];
                break;
            case 3:
                $this->setSql('
                    SELECT COUNT(DISTINCT nim_id,ind_id) cuantos, MAX(ind_id) AS ind_id, MAX(epfi_id) AS eri_id,MAX(umi_id) AS umi_id
                    FROM enc_ProductoFinalIndicador enc
                    JOIN cat_Subprograma USING(pdf_id)
                    INNER JOIN cat_Indicador USING(ind_id,nim_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    WHERE enc.trim_0 = :sts AND nim_id = :nim AND pdf_id =:pdf_id
                    AND prg_id =:prg_id' .
                    ($this->urResponsable || $this->validador ? '' : '
                    AND gpo_id =:gpo_id
                    AND ur_id =:ur_id') . '
                    HAVING cuantos != 0;
                ');
                $parametros = [
                    ':sts' => 1,
                    'nim' => 3,
                    ':pdf_id' => $this->prm->pdf_id,
                    ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                ];
                if (!$this->urResponsable && !$this->validador) {
                    $parametros[':gpo_id'] = $_SESSION[_S]['clasAdm-gpo'];
                    $parametros[':ur_id'] = $_SESSION[_S]['clasAdm-ur'];
                }
                break;
            case 4:
                $this->setSql('
                    SELECT COUNT(DISTINCT nim_id,ind_id) cuantos, MAX(ind_id) AS ind_id , MAX(pii_id) AS eri_id
                    FROM vin_ProductoIntermedioIndicador
                    WHERE trim_0 = ? AND nim_id = ? AND pdf_id =?
                    AND pdi_id =?
                    HAVING cuantos != 0;
                ');
                $parametros = [
                    1,
                    4,
                    $this->prm->pdf_id,
                    $this->prm->pdi_id,
                ];
                break;
        }
        $this->conectar();
        $this->consultar($parametros);

        $this->desconectar();
        if ($this->prm->nivel != 0) {
            if ($this->getNumeroRegistros() > 0) {

                $cuantos = $this->extraer();
                $ind_id = $cuantos['ind_id'];
                $eri_id = $cuantos['eri_id'];
                (isset($cuantos['umi_id']) ? $umi_id = $cuantos['umi_id'] : '');
                $cuantos = $cuantos['cuantos'] * 1;
                if ($cuantos > 1) {
                    $this->listaIndNivel();
                } else {
                    $this->prm->eri_id = $eri_id;
                    $this->prm->ind_id = $ind_id;
                    (isset($umi_id) ? $this->prm->umi_id = $umi_id : '');
                    $this->addmodIndicador(true);
                }
            } else {
                $regresar->click = '
                    $("#toolsPantalla").html("");
                    $("nav").menusinpres("quitaBreadcrumbs");
                    comunica("' . $this->pantalla . '",{
                        metodo:"pintaPantalla",
                        regresa:"true"
                    });
                    desbloquearClasAdmin();
                    return false;
                ';

                $this->agregaTools($this->ponBoton($regresar));
                $this->mixHtml('
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert" align="center">
                            <strong>NO CUENTA CON NINGUN INDICADOR</strong>
                        </div>
                    </fieldset>
                ');
            };
        }
    }

    private function modalListadoMedioSupuesto($tabla = 'Supuesto')
    {
        $parametros = array();
        $mayusculas = $nomenclatura = $nivel = '';

        $this->setContenedor('divContenedorGeneralModal');
        if (!isset($this->prm->regresa)) {
            $this->titulo("Supuestos");
        }
        $prmComunica = '';
        $puedeEditar = $this->urResponsable;
        switch ($tabla) {
            case 'MedioVerificacion':
                $nomenclatura = 'mvpm_';
                $mayusculas = '$("#nuevoNombre").attr("style","text-transform:none");';
                break;
            case 'Supuesto':
                $nomenclatura = 'spm_';
                $mayusculas = '';
                break;
        }
        $consulta = new AccesoDatos();
        $consulta->conectar();
        $consulta->setSql('
            SELECT concat(gpo_id,ur_id) AS ur
            FROM vin_ProgramasMir
            WHERE vpm_id=?
        ');
        $consulta->consultar([$this->vpm_id]);

        $consulta2 = new AccesoDatos();
        $consulta2->conectar();
        $consulta2->setSql('
            SELECT pdf_id, prg_id
            FROM cat_Subprograma
            WHERE gpo_id=? and ur_id=?
            AND prg_id=? and pdf_id=?;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $_SESSION[_S]['clasAdm-prg'],
            $this->prm->pdf_id,
        ];
        $consulta2->consultar($parametros);

        switch ($this->prm->nivel) {
            case 1:
                $nivel = 'FIN';
                $puedeEditar = ($_SESSION[_S]['ssc_id'] == 2 && isset($_SESSION[_S]['prm']['A']['12']['2']['2'])) ? 1 : 0;
                break;
            case 2:
                $nivel = 'PROPOSITO';
                break;
        }

        $botonNuevo = '';
        if (($this->estadoMir < 2 || $this->estadoMir == 3 || $this->estadoMir == 5 || $this->estadoMir == 6 || $this->estadoMir == 11 || $this->estadoMir == 10) && $puedeEditar && $this->activo && $tabla == 'Supuesto') {
            $btnNuevoSupuesto = new stdClass();
            $btnNuevoSupuesto->id = 'addSupuesto';
            $btnNuevoSupuesto->icon = 'plus me-1';
            $btnNuevoSupuesto->color = 'azul';
            $btnNuevoSupuesto->value = ' Nuevo';
            $btnNuevoSupuesto->title = 'Agregar ' . $tabla;
            $btnNuevoSupuesto->click = '
                if($("#nuevoSupuesto").length == 0){
                    $("#listaSupuestos").append(
                        $("<li/>").addClass("list-group-item active d-flex justify-content-between").attr("id","nuevoSupuesto").html(
                            $("<div />").addClass("col-md-11").html(
                                $("<input/>").addClass("form-control").attr("id","nuevoNombre").attr("type","text")
                            )
                        ).append(
                        $("<button/>").addClass("btn btn-test").html($("<span/>").addClass("fa-solid fa-floppy-disk fa-2x")).click(function(){
                            if($("#nuevoNombre").val()!=""){
                                comunica("' . $this->pantalla . '",{
                                    metodo: "abc' . $tabla . '",
                                    abc:"a",
                                    nombre:$("#nuevoNombre").val(),
                                    epo_id:"' . (isset($this->prm->epe_id) ? $this->prm->epe_id : '') . '",
                                    eps_id:"' . $this->prm->eps_id . '",
                                    pdf_id_id:"' . $this->prm->pdf_id . '",
                                    pdi_id:"' . $this->prm->pdi_id . '",
                                    nivel:' . $this->prm->nivel . $prmComunica . ',
                                });
                            }
                        })
                        )
                    );
                    ' . $mayusculas . '
                }else{
                    $("#nuevoNombre").focus();
                }
            ';
            $botonNuevo = $this->ponBoton($btnNuevoSupuesto);
        }
        $this->conectar();
        $this->setSql('
            SELECT ' . $nomenclatura . 'id,' . $nomenclatura . 'nombre,gpo_id,ur_id,
            if((gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id=' . $_SESSION[_S]['ur_id'] . ') AND nim_id>1 ,1,0) editar
            FROM cat_' . $tabla . 'ProgramaMir
            JOIN vin_ProgramasMir USING(vpm_id)
            WHERE prgs_id =? AND nim_id =?
            AND ' . $nomenclatura . 'trimestreCierre=?
            AND ' . $nomenclatura . 'origen=?;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-prg'],
            $this->prm->nivel,
            $this->trimestreCierre,
            "P",
        ];
        $this->consultar($parametros);
        $this->desconectar();

        $this->mixHtml('
            <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog modal-lg" style="width: 80% !IMPORTANT;">
                    <br /><br /><br /><br />
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">' . $tabla . ' nivel <b>' . $nivel . '</b></h4>
                                <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                            </div>
                        <div class="modal-body">
                <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                <ul class="list-group" id="listaSupuestos" >
        ');
        while ($supuestos = $this->extraer()) {
            $this->mixHtml('<li class="list-group-item d-flex justify-content-between " id="' . $supuestos[$nomenclatura . 'id'] . '">' . $supuestos[$nomenclatura . 'nombre'] . '<div>');
            #si el estado de la MIR del programa es 2 0 3 y puede editar en el nivel y ademas es un registro realizado por la UR se le permite eliminar
            if (($this->estadoMir < 2 || $this->estadoMir == 3 || $this->estadoMir == 5) && $supuestos['editar'] == 1 && $_SESSION[_S]['nvj_id'] <= 2 && $this->activo && $tabla == 'Supuesto') {
                $this->mixHtml('<button type="button" class="btn btn-outline-danger btn-sm deleteSup me-2" title="Eliminar" style="float:right"><span class="fa fa-solid fa-trash-can"></span></button>');
            } else {
                if (isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && ($this->estadoMir == 5 || $this->estadoMir == 1 || $this->estadoMir == 3 || $this->estadoMir == 6 || $this->estadoMir == 11) && $puedeEditar && $this->activo && $tabla == 'Supuesto') {
                    $this->mixHtml('<button type="button" class="btn btn-outline-danger btn-sm deleteSup me-2" title="Eliminar" style="float:right"><span class="fa fa-solid fa-trash-can"></span></button>');
                }
                $this->mixHtml('<span class="badge bg-info" style="float:right; margin-right: 8px;">UR: ' . $supuestos['gpo_id'] . $supuestos['ur_id'] . '</span>');
            }
            $this->mixHtml('</div></li>');
        }
        $this->mixHtml('
            </ul>
            </fieldset>
            </div>
            </div>
            <div class="modal-footer">
                ' . (isset($_SESSION[_S]['prm']['A']['12']['2']['2']) ? $botonNuevo : '') . '
                <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </div>
            </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
                $("nav").menusinpres("quitaBreadcrumbs");
            });
            $(".deleteSup").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"abc' . $tabla . '",
                    abc:"b",
                    ' . $nomenclatura . 'id: $(this).parent().parent().attr("id"),
                    nivel:' . $this->prm->nivel . $prmComunica . '
                })
            });
            $("#close").click(function(){ $("#myModal").hide(); });
            $("#cerrar").click(function(){ $("#myModal").remove(); });
            $("#myModal").modal().show();
        ');
    }

    private function abcMedioSupuesto($tabla = 'Supuesto')
    {
        $parametros = array();
        $prmComunica = $idNum = $insert = $nombre = $nomenclatura = $mensaje = '';
        switch ($tabla) {
            case 'MedioVerificacion':
                $insert = 'INSERT INTO cat_MedioVerificacionProgramaMir (nim_id,vpm_id,pmi_id,mvpm_origen,mvpm_nombre,mvpm_trimestreCierre,usr_id)';
                $nomenclatura = 'mvpm_';
                $id = (isset($this->prm->mvpm_id)) ? $this->prm->mvpm_id : '';
                $idNum = 'medioVeri';
                $nombre = trim(strtoupper((isset($this->prm->nombre)) ? strtoupper($this->prm->nombre) : ''));
                $mensaje = 'Medio de verificación';
                break;
            case 'Supuesto':
                $nomenclatura = 'spm_';
                $insert = 'INSERT INTO cat_SupuestoProgramaMir (nim_id, vpm_id,spm_origen,spm_nombre, spm_trimestreCierre,usr_id)';
                $id = (isset($this->prm->spm_id)) ? $this->prm->spm_id : '';
                $idNum = 'supuestos';
                $nombre = trim(strtoupper((isset($this->prm->nombre)) ? strtoupper($this->prm->nombre) : ''));
                break;
        }
        $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->prm->prg_id = $_SESSION[_S]['clasAdm-prg'];
        
        switch ($this->prm->nivel) {
            case 1:
                $idNum = $idNum . 'Fin';
                break;
            case 2:
                $idNum = $idNum . 'Pro';
                break;
        }
        $this->conectar();
        switch ($this->prm->abc) {
            case 'a': //agregar supuesto o medio de verificación
                /* PARA EL CAMPO $nomenclatura_origen se envia el valor "P" lo que indica que el registro
                 * fue dado de alta desde el POA, este campo se usa como restricción para mostrar en el módulo
                 * MIR INICIAL unicamente la información registrada durante el POA.
                 */                
                if ($tabla == 'MedioVerificacion') {
                    # code...
                    switch ($this->prm->nivel) {
                        case 1:
                            $mensaje = $mensaje . 'fin registrado';
                            break;
                        case 2:
                            $mensaje = $mensaje . 'proposito registrado';
                            break;
                    }
                    $this->setSql($insert . ' VALUES (:nivel,:vpm_id,:pmi_id,:valor,:nombre,:trimestreCierre,:usr_id);');
                    $parametros = [
                        ':nivel' => $this->prm->nivel,
                        ':vpm_id' => $this->prm->vpm_id,
                        ':pmi_id' => $this->prm->pmi_id,
                        ':valor' => "P",
                        ':nombre' => $nombre,
                        ':trimestreCierre' => $this->trimestreCierre,
                        ':usr_id' => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($parametros);
                    $id = $this->getUltimoId();
                } else {
                    $this->setSql($insert . ' VALUES (:nivel,:vpm_id,:valor,:nombre,:trimestreCierre,:usr_id);');
                    $parametros = [
                        ':nivel' => $this->prm->nivel,
                        ':vpm_id' => $this->vpm_id,
                        ':valor' => "P",
                        ':nombre' => $nombre,
                        ':trimestreCierre' => $this->trimestreCierre,
                        ':usr_id' => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($parametros);
                    $id = $this->getUltimoId();
                }
                $this->mixScript('
                    $("#nuevoSupuesto").html("' . $nombre . '")
                        .append(
                            $("<button/>").attr("type","button").addClass("btn btn-outline-danger btn-sm me-2").attr("title","Eliminar").html($("<span/>").addClass("fa fa-solid fa-trash-can"))
                            .click(function(){comunica("' . $this->pantalla . '",{metodo:"abc' . $tabla . '",abc:"b",' . $nomenclatura . 'id:' . $id . ',nivel:' . $this->prm->nivel . $prmComunica . '})})
                        ).removeClass("active").attr("id",' . $id . ');
                ');
                break;
            case 'b': //eliminar
                switch ($this->prm->nivel) {
                    case 1:
                        $mensaje = $mensaje . 'fin eliminado';
                        break;
                    case 2:
                        $mensaje = $mensaje . 'proposito eliminado';
                        break;
                }
                $this->setSql('DELETE FROM cat_' . $tabla . 'ProgramaMir WHERE ' . $nomenclatura . 'id = ?');
                $this->consultar([$id]);
                $this->mixScript(' $("#' . $id . '").remove();');
                break;
        }
        $this->setSql('
            SELECT COUNT(' . $nomenclatura . 'id)cuantos
            FROM cat_' . $tabla . 'ProgramaMir
            JOIN vin_ProgramasMir USING(vpm_id)
            WHERE prgs_id =:prg_id AND nim_id =:nim_id
            AND ' . $nomenclatura . 'trimestreCierre=:trimestreCierre AND ' . $nomenclatura . 'origen=:valor;
        ');
        $parametros = [
            ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
            ':nim_id' => $this->prm->nivel,
            ':trimestreCierre' => $this->trimestreCierre,
            ':valor' => "P",
        ];
        $this->consultar($parametros);
        $cuantos = $this->extraer();
        $cuantos = $cuantos['cuantos'];

        if ($tabla == 'MedioVerificacion') {
            $this->setSql('
                INSERT INTO ' . BD_BITACORA . '.bit_MedioVerificacionProgramaMir (bit_observacion, nim_id, vpm_id, pmi_id, mvpm_nombre, mvpm_trimestreCierre, usr_id)
                VALUES (:observacion, :nim_id,:vpm_id, :pmi_id,:nombre,:trimestreCierre,:usr_id)
            ');
            $parametros = [
                ':observacion' => $mensaje,
                ':nim_id' => $this->prm->nivel,
                ':vpm_id' => $this->prm->vpm_id,
                ':pmi_id' => $this->prm->pmi_id,
                ':nombre' => $this->prm->nombre,
                ':trimestreCierre' => $this->trimestreCierre,
                ':usr_id' => $_SESSION[_S]['usr_id']
            ];
            $this->consultar($parametros);
        }

        $this->desconectar();
        $this->mixScript('
            $("#myModal").modal("hide");
            $("#' . $idNum . '").html("' . $cuantos . '");
            if("'.$tabla.'" == "MedioVerificacion") {
                notify("'.$mensaje.'");
                comunica("' . $this->pantalla . '",{
                    metodo:"listaIndicador",
                    vpm_id: ' . $this->vpm_id . ',
                    ' .(isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                    nivel: ' . $this->prm->nivel . '
                });
            }
        ');
        $this->Bitacora($sql = '');
    }

    public function modalMedioVer() {
        $this->setContenedor('divContenedorGeneralModal');

        switch ($this->prm->nivel) {
            case 1: case 2:
                # code...
                $this->setSql('
                    SELECT pmi_id 
                    FROM vin_ProgramaMirIndicador 
                    WHERE vpm_id = ? AND nim_id = ? AND ind_id = ?;
                ');
                $this->conectar();
                $this->consultar([$this->prm->vpm_id, $this->prm->nivel, $this->prm->ind_id]);
                $datos = $this->extraer();
                $this->desconectar();

                $this->prm->pmi_id = $datos['pmi_id'];
                break;
            case 3:
                # code...
                $this->setSql('
                    SELECT epfi_id 
                    FROM enc_ProductoFinalIndicador 
                    WHERE pdf_id = ? AND ind_id = ?;
                ');
                $this->conectar();
                $this->consultar([$this->prm->pdf_id, $this->prm->ind_id]);
                $datos = $this->extraer();
                $this->desconectar();

                $this->prm->epfi_id = $datos['epfi_id'];
                break;
            case 4:
                # code...
                $this->setSql('
                    SELECT pii_id 
                    FROM vin_ProductoIntermedioIndicador 
                    WHERE pdf_id = ? AND pdi_id = ? AND ind_id = ?;
                ');
                $this->conectar();
                $this->consultar([$this->prm->pdf_id, $this->prm->pdi_id, $this->prm->ind_id]);
                $datos = $this->extraer();
                $this->desconectar();

                $this->prm->pii_id = $datos['pii_id'];
                break;
        }
        
        $this->mixHtml('
            <div id="myModal" class="modal ">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Registro de Medio de Verificación nivel <b>' . $this->prm->nivel . '</b></h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row"></div>
                            <div class="form-group">
                                <input type="text" class="form-control" id="mvpm_nombre" placeholder="Escribe el medio de verificación...">
                            </div>
                            <div class="modal-footer">
                                <button id="btnGuardarMedio" type="button" class="btn btn-outline-primary">Guardar</button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->mixScript('
            $("#myModal").modal("show");
            $("#btnGuardarMedio").click(function(){
                var texto = $("#mvpm_nombre").val();
                var nivelM = '. $this->prm->nivel .';
                if(texto == ""){
                    notify("El campo no puede estar vacío", "error");
                    return;
                }
                if(nivelM == 1 || nivelM == 2) {
                    comunica("'. $this->pantalla .'", {
                        metodo: "abcMedioVerificacion",
                        abc:"a",
                        nombre: texto,
                        pmi_id: '. (isset($this->prm->pmi_id) ? $this->prm->pmi_id : 0) .',
                        vpm_id: '. (isset($this->prm->vpm_id) ? $this->prm->vpm_id : 0) .',
                        nivel: ' . $this->prm->nivel . ',
                        tabla:"MedioVerificacion"
                    });
                } else if(nivelM == 3) {
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcMedioVerificacion",
                        abc:"a",
                        nombre:texto,
                        pdf_id:"' . (isset($this->prm->pdf_id) ? $this->prm->pdf_id : 0) . '",
                        pdf_id:"' . (isset($this->prm->pdf_id) ? $this->prm->pdf_id : 0) . '",
                        epfi_id:"' . (isset($this->prm->epfi_id) ? $this->prm->epfi_id : 0) . '",
                        nivel:' . $this->prm->nivel . ',
                        tabla:"MedioVerificacion"
                    });
                } else if(nivelM == 4) {
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcMedioVerificacion",
                        abc:"a",
                        nombre:texto,
                        pdf_id:"' . (isset($this->prm->pdf_id) ? $this->prm->pdf_id : 0) . '",
                        pdi_id:"' . (isset($this->prm->pdi_id) ? $this->prm->pdi_id : 0) . '",
                        pii_id:"' . (isset($this->prm->pii_id) ? $this->prm->pii_id : 0) . '",
                        nivel:' . $this->prm->nivel . ',
                        ' .(isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                        tabla:"MedioVerificacion"
                    });
                }
            });
        ');
    }

    private function Bitacora($sql)
    {
        /*             * ***************** LOG DE LA MIR*********************************** */
        $rutalog = "log/mir-PRG-" . $_SESSION[_S]['clasAdm-prg'] . "-UR-" . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . $_SESSION[_S]['usr_id'] . ".txt";
        $file = fopen($rutalog, "a");
        $tempdata = '';
        $hoy = date("Y-m-d H-i-s");

        foreach ($this->prm as $field => $value) {
            if (is_string($value)) {
                $tempdata .= '|' . $field . "=" . $value;
            }
        }

        $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
        $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];
        if ($sql != "") {
            $tempdata .= '|CONSULTA=' . $sql . '';
        }
        fwrite($file, "Registro de  " . $hoy . ":" . $tempdata . PHP_EOL);
        fclose($file);

        $peso = filesize($rutalog);

        $peso = ($peso / 1024) / 1024;

        if ($peso > 8) {
            $rutamodificada = "log/mir-PRG-" . $_SESSION[_S]['clasAdm-prg'] . "-UR-" . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . $_SESSION[_S]['usr_id'] . $hoy . ".txt";
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, "a");
            fwrite($file, "Creado:" . $hoy . PHP_EOL);
            fclose($file);
        }
    }

    private function addmodIndicador($buscaInd = false)
    {
        $metodo = "";
        $parametros = '';
        $parametros2 = array();
        if (isset($this->prm->ban) && $this->prm->ban != '') {
            $buscaInd = true;
        }

        if (isset($this->prm->regresaInd) && $this->prm->regresaInd != '') {
            $metodo = 'listaIndNivel';
            $parametros = ', nivel: ' . $this->prm->nivel . '
                ' . (isset($this->prm->com_id) ? ', com_id:' . $this->prm->com_id : "") . '
                ' . (isset($this->prm->pdf_id) ? ', pdf_id:' . $this->prm->pdf_id . ',' : "") . '
                ' . (isset($this->prm->pdi_id) ? 'pdi_id:' . $this->prm->pdi_id . '' : "") . '
            ';
        } else {
            $metodo = 'pintaPantalla';
        }
        $this->prm->nim_id = (empty($this->prm->nim_id)) ? $this->prm->nivel : $this->prm->nim_id;
        $alineacion = $this->muestraAlineacion();
        $verificacion = $this->muestraVerificacion();
        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->icon = 'circle-left me-2';
        $regresar->color = 'gris';
        $regresar->class = "me-2";
        $regresar->value = ' Regresar';
        $regresar->click = 'comunica("' . $this->pantalla . '",{ metodo:"listaIndicador", nivel: ' . $this->prm->nivel . ' });';
        #si buscaInd = true se busca el indicador unico del nivel FIN o PROPOSITO segun sea el caso del programa selecionado en $_SESSION[_S]['clasAdm-prg']
        if ($buscaInd && !empty($_SESSION[_S]['clasAdm-prg'])) {
            $this->conectar();
            switch ($this->prm->nivel) {
                case 1:
                case 2:
                    $this->setSql('
                        SELECT distinct ind_id,pmi_id
                        FROM cat_Indicador
                        INNER JOIN vin_ProgramaMirIndicador USING(nim_id,ind_id)
                        INNER JOIN (SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id
                            FROM vin_ProgramasMir
                            UNION
                            SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id
                            FROM vin_UrProgramaMir ur
                            INNER JOIN vin_ProgramasMir USING(vpm_id)
                        )v USING (vpm_id)
                        WHERE gpo_id =:gpo_id AND ur_id =:ur_id
                        AND prgs_id =:prg_id
                        AND nim_id =:nim_id AND ind_id=:ind_id;
                    ');

                    $parametros2 = [
                        ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
                        ':ur_id' => $_SESSION[_S]['clasAdm-ur'],
                        ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                        ':nim_id' => $this->prm->nivel,
                        ':ind_id' => $this->prm->ind_id,
                    ];

                    $this->consultar($parametros2);
                    $ind = $this->extraer();
                    $this->prm->ind_id = $ind['ind_id'];
                    $this->prm->eri_id = $ind['pmi_id'];
                    $this->lectura = true;
                    break;
                case 3:
                case 4:
                    $this->lectura = true;
                    break;
            }
            $this->desconectar();

            $alineacion = $this->muestraAlineacion();

            $regresar->click = 'comunica("' . $this->pantalla . '",{metodo:"' . $metodo . '"' . $parametros . '});';
        }
        $this->agregaTools($this->ponBoton($regresar));

        $abc = (isset($this->prm->ind_id)) ? 'c' : 'a';
        $ind = new Indicador($this->prm);
        $indNombre = new stdClass();
        $indNombre->id = 'nombre';
        $indNombre->required = true;
        $indNombre->enDiv = true;
        $indNombre->placeholder = 'Nombre del Indicador';

        $indObjetivo = new stdClass();
        $indObjetivo->id = 'definicion';
        $indObjetivo->required = true;
        $indObjetivo->maxlength = 500;
        $indObjetivo->placeholder = 'Definicion del indicador';

        $dimencion = new stdClass();
        $dimencion->required = true;
        $dimencion->id = 'dim_id';
        $dimencion->class = 'mb-4';
        $dimencion->label = 'Dimensión';
        $dimencion->sql = 'SELECT dim_id,dim_nombre FROM cat_Dimension ';
        $frecuencia = new stdClass();
        $frecuencia->required = true;
        $frecuencia->label = 'Frecuencia de Medición';
        $frecuencia->id = 'frm_id';
        $frecuencia->class = 'mb-4';
        $frecuencia->sql = 'SELECT frm_id,frm_nombre FROM cat_FrecuenciaMedicion ';
        if ($this->prm->nivel == 3) {
            $frecuencia->sql = 'SELECT frm_id,frm_nombre FROM cat_FrecuenciaMedicion where frm_id=2';
        }
        $formula = new stdClass();
        $formula->required = true;
        $formula->id = 'formulaCalculo';
        $formula->maxlength = 500;
        $formula->placeholder = 'Fórmula de Cálculo';

        $numerador = new stdClass();
        $numerador->required = true;
        $numerador->id = 'numerador';
        $numerador->label = 'Numerador';
        $numerador->enDiv = true;
        $denominador = new stdClass();
        $denominador->required = true;
        $denominador->id = 'denominador';
        $denominador->label = 'Denominador';
        $denominador->enDiv = true;
        $tipo = new stdClass();
        $tipo->required = true;
        $tipo->label = 'Tipo';
        $tipo->id = 'tpi_id';
        $tipo->class = 'mb-4';
        $tipo->sql = 'SELECT tpi_id,tpi_nombre FROM cat_TipoIndicador ';

        if ((($_SESSION[_S]['clasAdm-prg'] >= 0 && $_SESSION[_S]['clasAdm-prg'] <= 9) || ($_SESSION[_S]['clasAdm-prg'] >= 900 && $_SESSION[_S]['clasAdm-prg'] <= 999))) {
            $tipo->sql .= 'WHERE tpi_id = 2 ';
        }
        $sentido = new stdClass();
        $sentido->required = true;
        $sentido->label = 'Sentido Esperado';
        $sentido->id = 'ses_id';
        $sentido->class = 'mb-4';
        $sentido->sql = 'SELECT ses_id,ses_nombre FROM cat_SentidoEsperado ';
        $unidad = new stdClass();
        $unidad->required = true;
        $unidad->label = 'Forma de Cálculo del Indicador';
        $unidad->id = 'umi_id';
        $unidad->class = 'mb-4';
        $unidad->sql = 'SELECT umi_id,umi_nombre FROM cat_UnidadMedidaIndicador ';

        $tipoValor = new stdClass();
        $tipoValor->label = 'Tipo de Valor';
        $tipoValor->required = true;
        $tipoValor->id = 'tiv_id';
        $tipoValor->class = 'mb-4';
        $tipoValor->sql = 'SELECT tiv_id,tiv_nombre FROM cat_TipoValor ';

        $indNombre->value = $ind->getNombreMIR();
        $indObjetivo->texto = $ind->getDefinicionMIR();
        $dimencion->selecciona = $ind->getDim_id();
        $frecuencia->selecciona = $ind->getFrm_id();
        $formula->texto = $ind->getFormulaCalculo();
        $tipo->selecciona = $ind->getTpi_id();
        $sentido->selecciona = $ind->getSes_id();
        $unidad->selecciona = $ind->getUmi_id();

        $tipoValor->selecciona = $ind->getTiv_id();
        if ($this->lectura) {
            $indNombre->readonly = true;
            $indObjetivo->readonly = true;
            $dimencion->sql = 'SELECT dim_id,dim_nombre FROM cat_Dimension WHERE dim_id = ' . $ind->getDim_id();
            $frecuencia->sql = 'SELECT frm_id,frm_nombre FROM cat_FrecuenciaMedicion WHERE frm_id = ' . $ind->getFrm_id();
            if ($this->prm->nivel == 3) {
                $frecuencia->sql = 'SELECT frm_id,frm_nombre FROM cat_FrecuenciaMedicion WHERE frm_id = 2';
            }
            $formula->readonly = true;
            $tipo->sql = 'SELECT tpi_id,tpi_nombre FROM cat_TipoIndicador WHERE tpi_id = ' . $ind->getTpi_id();
            $sentido->sql = 'SELECT ses_id,ses_nombre FROM cat_SentidoEsperado WHERE ses_id = ' . $ind->getSes_id();
            $unidad->sql = 'SELECT umi_id,umi_nombre FROM cat_UnidadMedidaIndicador WHERE umi_id = ' . $ind->getUmi_id();

            $tipoValor->sql = 'SELECT tiv_id,tiv_nombre FROM cat_TipoValor WHERE tiv_id = ' . $ind->getTiv_id();
        }

        $form = new stdClass();
        $form->id = 'newIndicador';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcIndicador';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->nivel = $this->prm->nivel;

        $form = new form($form);
        $form->mixHtml('
            <div class="row mb-3">
                <label for="ind_nombre" class="col-sm-12 control-label">Datos del Indicador de Nivel: ' . $this->prm->nivel . ' Numero: ' . $this->prm->ind_id . ':</label>
            </div>
            <div class="row mb-4"><!--<div class="form-group">-->
                <label for="ind_nombre" class="col-sm-3 control-label">Nombre del Indicador:</label>
                <div class="col-sm-9">' . $this->ponInput($indNombre) . '</div>
            </div>

            <div class="row mb-3">
                <label for="ind_nombre" class="col-sm-3 control-label">Definición del Indicador MIR:</label>
                <div class="col-sm-9">' . $this->ponTextArea($indObjetivo) . '</div>
            </div>
            <div class="row mb-4">
                <label for="ind_nombre" class="col-sm-3 control-label">Método de cálculo o fórmula Indicador:</label>
                <div class="col-sm-9">' . $this->ponTextArea($formula) . '</div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4 col-xs-4">' . $this->ponCombo($tipo) . '</div>
                <div class="col-md-4 col-xs-4">' . $this->ponCombo($unidad) . '</div>
                <div class="col-md-4 col-xs-4">' . $this->ponCombo($dimencion) . '</div>

                <div class="col-md-4 col-xs-4" id="datoFrecuencia" class="form-group">' . $this->ponCombo($frecuencia) . '</div>
                <div class="col-md-4 col-xs-4">' . $this->ponCombo($sentido) . '</div>
                <div class="col-md-4 col-xs-4">' . $this->ponCombo($tipoValor) . '</div>
            </div>

        ');

        if ($abc == 'a') {
            $btnNuevoMedio = new stdClass();
            $btnNuevoMedio->id = 'btnNuevoMedio';
            $btnNuevoMedio->icon = 'plus me-2';
            $btnNuevoMedio->color = "azul";
            $btnNuevoMedio->type = 'button';
            $btnNuevoMedio->value = ' Agregar';
            $btnNuevoMedio->click = '
                $tr = $("<tr/>").html(
                    $("<td/>").html(
                        $("<input />").addClass("mediosVerificacion form-control").attr("name","medios[]")
                    )
                ).append(
                    $("<td/>").html(
                        $("<button />").addClass("btn btn-outline-danger btn-xs")
                        .html( $("<i/>").addClass("trash") )
                        .click( function(){ $(this).parent().parent().remove(); })
                    )
                );
                $("#registrosMedios").append( $tr );
            ';
            $form->mixHtml('
                <div class="col-md-6 col-xs-6">
                    <table class="table tbl-mv">
                        <thead>
                            <tr class="warning">
                                <th>Medios de verificación</th>
                                <th width="5px">' . $this->ponBoton($btnNuevoMedio) . '</th>
                            </tr>
                        </thead>
                        <tbody id="registrosMedios">
                            <tr>
                                <td class="form-group"><input name = "medios[]" class="mediosVerificacion form-control" required /></td>
                                <td style="width:5px;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ');
        }
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
                $this->mixScript('
                    $("#btnNuevoMedio").remove();
                ');
                break;
            default:
                $this->mixScript('
                    $("#btnNuevoMedio").remove();
                ');
                break;
        }
        $this->mixHtml('
            ' . $alineacion . '
            <div class="panel panel-default">
                <a class="btn btn-block success" role="button" data-bs-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            ESTRUCTURA DEL INDICADOR ' . $this->prm->ind_id . ' NIVEL ' . $this->prm->nivel . '
                        </h4>
                    </div>
                </a>
                <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                        <div class="row">' . $form->ponForm() . '</div><br>
        ');
        if ($abc == 'c') {
            $this->mixHtml($this->listaMetas($ind->getInd_id()));
            $this->llenaTablaMetas();
        }
        $this->mixHtml('
                    </div>
                </div>
            </div>
            '. $verificacion . '
        ');
    }

    private function listaIndNivel()
    {

        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->icon = 'circle-left me-2';
        $regresar->color = 'gris';
        $regresar->value = 'Regresar';

        $this->agregaTools($this->ponBoton($regresar));
        $alineacion = ($this->prm->nivel != 5) ? $this->muestraAlineacion() : '';

        //filtro por tipo
        $tpi_id = new stdClass();
        $tpi_id->label = 'Filtro (Tipo Indicador)';
        $tpi_id->id = 'tpi_id';
        $tpi_id->class = 'mb-4';
        $tpi_id->sql = 'SELECT tpi_id,tpi_nombre from cat_TipoIndicador';

        $filtro = $this->templateCombo($tpi_id);

        $tabla = new stdClass();
        $tabla->titulo = 'Indicadores';
        $tabla->id = 'tbl-Indicador';
        $tabla->tools = $filtro;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaIndicador';
        $tabla->comunica->nivel = $this->prm->nivel;
        $tabla->comunica->lectura = $this->lectura;
        $tabla->comunica->pdf_id = (isset($this->prm->pdf_id) ? $this->prm->pdf_id : "");
        $tabla->comunica->pdi_id = (isset($this->prm->pdi_id) ? $this->prm->pdi_id : "");
        $tabla->comunica->pro_id = (isset($this->prm->pro_id) ? $this->prm->pro_id : "");
        $tabla->comunica->com_id = (isset($this->prm->com_id) ? $this->prm->com_id : "");
        $tabla->comunica->act_id = (isset($this->prm->act_id) ? $this->prm->act_id : "");

        $tr = array();
        //ver
        $th = new stdClass();
        $th->texto = 'Ver/Editar';
        $th->width = '5px';
        array_push($tr, $th);

        //Id
        $th = new stdClass();
        $th->texto = 'Id';
        $th->width = '60px';
        array_push($tr, $th);

        //nombre indicador
        $th = new stdClass();
        $th->texto = 'Indicador';
        array_push($tr, $th);

        //Tipo
        $th = new stdClass();
        $th->texto = 'Tipo';
        array_push($tr, $th);

        //Unidad de medida
        $th = new stdClass();
        $th->texto = 'Forma de Cálculo del Indicador';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->tipo = "boton";
        $td->icono = "eye";
        $td->class = "verIndicador";
        $td->attr = new stdClass();
        $td->attr->pro_id = "pro_id";
        $td->attr->com_id = "com_id";
        $td->attr->act_id = "act_id";
        $td->attr->ind_id = "ind_id";
        $td->attr->eri_id = "eri_id";
        $td->attr->umi_id = "umi_id";

        array_push($trbody, $td);

        //Id
        $td = new stdClass();
        $td->campoTxt = 'ind_id';
        array_push($trbody, $td);

        //Indicador
        $td = new stdClass();
        $td->campoTxt = 'ind_nombreMIR';
        array_push($trbody, $td);

        //Tipo
        $td = new stdClass();
        $td->campoTxt = 'tpi_nombre';
        array_push($trbody, $td);

        //Unidad de medida
        $td = new stdClass();
        $td->campoTxt = 'umi_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($alineacion . '
            <div id="tablaIndicadores" class="row">
                ' . $this->wrPonTabla($tabla) . '
            </div>
            <div class="row">
                <fieldset id="mediosVerificacion" class="col-md-6 col-xs-12">

                </fieldset>
            </div>
            <div class="row">
                <fieldset id="supuesto" class="col-md-6 col-xs-12">

                </fieldset>
            </div>
        ');

        $this->recargaCombo($tpi_id);

        $this->llenaTablaIndicador();

        $this->mixScript('
            $("#addIndicador").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo : "addmodIndicador",
                    nivel: ' . $this->prm->nivel . ',
                    pro_id:' . (isset($this->prm->pro_id) ? $this->prm->pro_id : 0) . ',
                    com_id:' . (isset($this->prm->com_id) ? $this->prm->com_id : 0) . ',
                    act_id:' . (isset($this->prm->act_id) ? $this->prm->act_id : 0) . ',
                    lectura:false
                });
                return false;
            });

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#regresar").click(); e.stopPropagation(); });

            $("#regresar").click(function(){
                $("#toolsPantalla").html("");
                $("nav").menusinpres("quitaBreadcrumbs");
                comunica("' . $this->pantalla . '",{
                        metodo:"pintaPantalla",
                        regresa:"true"
                });
                desbloquearClasAdmin();
                return false;
            });

            $("#tpi_id").change(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaTablaIndicador",
                    tpi_id: $(this).val(),
                    nivel:' . $this->prm->nivel . ',
                    pro_id:' . (isset($this->prm->pro_id) ? $this->prm->pro_id : 0) . ',
                    com_id:' . (isset($this->prm->com_id) ? $this->prm->com_id : 0) . ',
                    act_id:' . (isset($this->prm->act_id) ? $this->prm->act_id : 0) . '
                    ' . (isset($this->prm->pdf_id) ? ', pdf_id:' . $this->prm->pdf_id . ',' : "") . '
                    ' . (isset($this->prm->pdi_id) ? 'pdi_id:' . $this->prm->pdi_id . '' : "") . '
                });
            });

            $("#comboprg").change(function(){
                if($("#comboprg").val() >= 10 && $("#comboprg").val()<=89 ){
                    $("nav").menusinpres("quitaBreadcrumbs");
                }
            });

            $("#btnEnvValInd").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaciones",
                    estadoM: 1,
                    tipoI: "indicador"
                });
            });

            $("#btnRecibir").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaciones",
                    estadoM: 3,
                    tipoI: "indicador"
                });
            });

            $("#btnValidarInd").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"validaciones",
                    estadoM: 2,
                    tipoI: "indicador"
                });
            });

            $("#btnRechazar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"rechazar",
                    tipo:"I"
                });
            });
        ');
    }
    
    private function muestraVerificacion()
    {
        $html = '';
        $nomenclatura = '';
        switch ($this->prm->nivel) {
        case 1: case 2:
            # code...
            $nomenclatura = 'mvpm_';
            $this->setSql('
                SELECT pmi_id 
                FROM vin_ProgramaMirIndicador 
                WHERE vpm_id = ? AND nim_id = ? AND ind_id = ?;
            ');
            $this->conectar();
            $this->consultar([$this->vpm_id,$this->prm->nivel, $this->prm->ind_id]);
            $datos = $this->extraer();
            $this->desconectar();

            $this->prm->pmi_id = $datos['pmi_id'];
            $this->conectar();
                $this->setSql('
                    SELECT mvpm_id,mvpm_nombre,gpo_id,ur_id,
                    if((gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id=' . $_SESSION[_S]['ur_id'] . ') AND nim_id>1 ,1,0) editar
                    FROM cat_MedioVerificacionProgramaMir
                    INNER JOIN vin_ProgramasMir USING(vpm_id)
                    WHERE prgs_id =? AND nim_id =?
                    AND vpm_id = ? AND pmi_id = ?
                    AND mvpm_trimestreCierre=?
                    AND mvpm_origen=?;
                ');
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    $this->prm->nivel,
                    $this->vpm_id,
                    $this->prm->pmi_id,
                    $this->trimestreCierre,
                    "P",
                ];
                $this->consultar($parametros);
                $this->desconectar();
            break;
        
        case 3:
            # code...
            $nomenclatura = 'mvcm_';
            $this->setSql('
                SELECT epfi_id 
                FROM enc_ProductoFinalIndicador 
                WHERE pdf_id = ? AND ind_id = ?;
            ');
            $this->conectar();
            $this->consultar([$this->prm->pdf_id, $this->prm->ind_id]);
            $datos = $this->extraer();
            $this->desconectar();

            $this->prm->epfi_id = $datos['epfi_id'];
            $this->conectar();
            $this->setSql('
                SELECT mvcm_id,mvcm_nombre, gpo_id,ur_id,
                    if((gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id=' . $_SESSION[_S]['ur_id'] . '), 1, 0) editar
                FROM cat_MedioVerificacionComponente
                INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
                INNER JOIN cat_Subprogramas USING(prgs_id, sprs_id)
                WHERE prgs_id =:prg_id
                AND pdf_id =:pdf_id AND epfi_id = :epfi_id 
                AND mvcm_trimestreCierre= :trimestreCierre
                ORDER BY gpo_id, ur_id;
            ');
            $parametros = [
                ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                ':pdf_id' => $this->prm->pdf_id,
                ':epfi_id' => $this->prm->epfi_id,
                ':trimestreCierre' => $this->trimestreCierre,
            ];
            $this->consultar($parametros);
            $this->desconectar();
            break;
        case 4:
            $nomenclatura = 'mvac_';
            $this->setSql('
                SELECT pii_id 
                FROM vin_ProductoIntermedioIndicador 
                WHERE pdf_id = ? AND pdi_id = ? AND ind_id = ?;
            ');
            $this->conectar();
            $this->consultar([$this->prm->pdf_id, $this->prm->pdi_id, $this->prm->ind_id]);
            $datos = $this->extraer();
            $this->desconectar();

            $this->prm->pii_id = $datos['pii_id'];
            $this->conectar();
            $this->setSql('
                SELECT mvac_id,mvac_nombre, gpo_id,ur_id,
                if((gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id=' . $_SESSION[_S]['ur_id'] . '), 1, 0) editar
                FROM cat_MedioVerificacionActividad
                INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
                INNER JOIN vin_ProductoFinalProductoIntermedio USING(pdf_id, pdi_id)
                WHERE prgs_id = :prg_id
                AND pdf_id = :pdf_id
                AND pdi_id = :pdi_id
                AND pii_id = :pii_id
                AND mvac_trimestreCierre= :trimestreCierre
                ORDER BY gpo_id, ur_id;
            ');

            $parametros = [
                ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                ':pdf_id' => $this->prm->pdf_id,
                ':pdi_id' => $this->prm->pdi_id,
                ':pii_id' => $this->prm->pii_id,
                ':trimestreCierre' => $this->trimestreCierre,
            ];

            $this->consultar($parametros);
            $this->desconectar();
            break;
        }
        $html .= '
            <div class="panel panel-default">
                <a class="btn btn-block success" role="button" data-bs-toggle="collapse" data-parent="#accordion" href="#collapseOnem" aria-expanded="true" aria-controls="collapseOne">
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            MEDIOS DE VERIFICACIÓN
                        </h4>
                    </div>
                </a>
                <div id="collapseOnem" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>MEDIO DE VERIFICACIÓN</th>
                                    <th style="width:80px; text-align:center;">Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
        ';

        while ($medios = $this->extraer()) {

            $html .= '
                <tr>
                    <td>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>' . $medios[$nomenclatura . 'nombre'] . '</span>
                            <span class="badge bg-info">UR: ' . $medios['gpo_id'] . $medios['ur_id'] . '</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        ' . ((($this->prm->nivel == 2 && $this->urResponsable) || ($this->prm->nivel != 2 && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $this->activo && ($this->estadoMir == 1 || $this->estadoMir == 3 || $this->estadoMir == 5))) && $medios['editar'] == 1 ? '
                            <button class="btn btn-outline-danger btn-sm btnEliminar" 
                                medio="' . $medios[$nomenclatura.'id'] . '" texto="' . $medios[$nomenclatura.'nombre'] . '">
                                <span class="fa fa-solid fa-trash-can"></span>
                            </button>
                        ' : '') . '
                    </td>
                </tr>
            ';
        }

        $html .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ';

        $this->mixScript('
        $(".btnEliminar").click(function(){
                var id = $(this).attr("medio");
                var nombre = $(this).attr("texto");
                var nivelM = '. $this->prm->nivel .';
                if(nivelM == 1 || nivelM == 2) {
                    comunica("'. $this->pantalla .'", {
                        metodo:"abcMedioVerificacion",
                        abc:"b",
                        ' . $nomenclatura . 'id: id,
                        pmi_id: '. (isset($this->prm->pmi_id) ? $this->prm->pmi_id : 0) .',
                        vpm_id: '. (isset($this->vpm_id) ? $this->vpm_id : 0) .',
                        nivel: ' . $this->prm->nivel . ',
                        nombre:nombre,
                        tabla:"MedioVerificacion"
                    });
                } else if(nivelM == 3) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcMedioVerificacion",
                        abc:"b",
                        ' . $nomenclatura . 'id: id,
                        pdf_id:"' . (isset($this->prm->pdf_id) ? $this->prm->pdf_id : 0) . '",
                        pdf_id:"' . (isset($this->prm->pdf_id) ? $this->prm->pdf_id : 0) . '",
                        epfi_id:"' . (isset($this->prm->epfi_id) ? $this->prm->epfi_id : 0) . '",
                        nivel:' . $this->prm->nivel . ',
                        nombre:nombre,
                        tabla:"MedioVerificacion"
                    });
                } else if(nivelM == 4) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcMedioVerificacion",
                        abc:"b",
                        ' . $nomenclatura . 'id: id,
                        pdf_id:"' . (isset($this->prm->pdf_id) ? $this->prm->pdf_id : 0) . '",
                        pdi_id:"' . (isset($this->prm->pdi_id) ? $this->prm->pdi_id : 0) . '",
                        pii_id:"' . (isset($this->prm->pii_id) ? $this->prm->pii_id : 0) . '",
                        ' .(isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                        nivel:' . $this->prm->nivel . ',
                        nombre:nombre,
                        tabla:"MedioVerificacion"
                    });
                }
            });
        ');

        return $html;
    }

    private function muestraAlineacion()
    {
        $rn = $nivel = '';
        switch ($this->prm->nivel) {
            case 1:
                $nivel = 'FIN';
                $rn = 'mir_resumenNarrativo';
            case 2:
                $nivel = (empty($nivel)) ? 'PROPOSITO' : $nivel;
                $rn = (empty($rn)) ? 'vpm_resumenNarrativo' : $rn;
                break;
            case 3:
                $nivel = 'COMPONENTE';
                $rn = '(SELECT pdf_mir FROM cat_ProductoFinal WHERE pdf_id = ' . $this->prm->pdf_id . ')';
                break;
            case 4:
                $nivel = 'ACTIVIDAD';
                $rn = '(SELECT pdi_mir FROM cat_ProductoIntermedio WHERE pdi_id = ' . $this->prm->pdi_id . ')';
                break;
        }
        $this->setSql('
            SELECT eje_nombre, tem_nombre,obj_nombre,prgs_nombre, spe_nombre, obp_nombre,' . $rn . ' rn,
            ple_nombre, ope_nombre
            FROM vin_Programa vp
            INNER JOIN vin_ProgramasMir pm USING (prgs_id)
            INNER JOIN cat_Mir m ON m.mir_id=pm.mir_id
            INNER JOIN cat_Programas p USING(prgs_id)
            INNER JOIN enc_PedObjetivo e USING(epo_id)
            INNER JOIN cat_Eje USING(eje_id)
            INNER JOIN cat_Tema t USING(eje_id, tem_id)
            INNER JOIN cat_Objetivo o ON o.eje_id=e.eje_id AND o.tem_id=t.tem_id AND o.obj_id=e.obj_id
            INNER JOIN enc_Pes ps ON ps.obp_id=vp.obp_id AND ps.spe_id=vp.spe_id
            INNER JOIN cat_ObjetivoPes op ON op.obp_id=vp.obp_id AND vp.spe_id=op.spe_id
            INNER JOIN cat_SectorPes s ON s.spe_id=vp.spe_id
            INNER JOIN enc_PedODS USING (epods_id)
            INNER JOIN cat_ODS  USING (ods_id)
            INNER JOIN cat_PlanEspecial USING(ple_id)
            INNER JOIN cat_ObjetivoPe pl ON vp.ple_id = pl.ple_id AND vp.ope_id = pl.ope_id
            WHERE prgs_id = ?
            GROUP BY prgs_id;
        ');
        $this->conectar();
        $this->consultar([$_SESSION[_S]['clasAdm-prg']]);
        $this->desconectar();
        $datos = $this->extraer();
        
        $pesPe='';
        if ($datos['obp_nombre'] != 'N/A') {
                $pesPe ='
                <tr>
                    <th class="warning border-warning" rowspan="2" style="vertical-align: middle; background: rgb(255, 234, 168)">PES</th>
                    <th class="warning border-warning" style="text-align:right;background: rgb(255, 234, 168)">Sector:</th>
                    <td class="table-light">' . $datos['spe_nombre'] . '</td>
                </tr>
                <tr>
                    <th class="warning border-warning" style="text-align:right;background: rgb(255, 234, 168)">Objetivo:</th>
                    <td class="table-light">' . $datos['obp_nombre'] . '</td>
                </tr>
                ';
        }
        else {
            $pesPe ='
                <tr>
                    <th class="warning border-warning" rowspan="2" style="vertical-align: middle; background: rgb(255, 234, 168)">PE</th>
                    <th class="warning border-warning" style="text-align:right;background: rgb(255, 234, 168)">Plan:</th>
                    <td class="table-light">' . $datos['ple_nombre'] . '</td>
                </tr>
                <tr>
                    <th class="warning border-warning" style="text-align:right;background: rgb(255, 234, 168)">Objetivo:</th>
                    <td class="table-light">' . $datos['ope_nombre'] . '</td>
                </tr>
                ';
        }

        $datoComponente ='';

        if ($this->prm->nivel == 4){
            $this->setSql('
            SELECT pdf_mir FROM cat_ProductoFinal WHERE pdf_id = ?
        ');
            $this->conectar();
            $this->consultar([$this->prm->pdf_id]);
            $this->desconectar();
            $valorComponente = $this->extraer();
            
            $datoComponente ='
                    <tr>
                        <th class="warning border-warning" colspan="2" style="text-align:right;background: rgb(255, 234, 168)">Componente:</th>
                        <td class="table-light">' .$valorComponente['pdf_mir'] . '</td>
                    </tr>
                ';
        }
        return '
            <div id="accordion">
                <div class="panel panel-default">
                    <a class="btn btn-block success" role="button" data-bs-toggle="collapse" data-parent="#accordion" href="#collapseOnes" aria-expanded="true" aria-controls="collapseOne">
                        <div class="panel-heading" role="tab" id="headingOne">
                            <h4 class="panel-title">
                                ALINEACIÓN
                            </h4>
                        </div>
                    </a>
                    <div id="collapseOnes" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                        <div class="panel-body">
                            <table class="table table-hover"  style="border-collapse: unset; border-spacing: 8px; ">
                            <tbody>
                                <tr>
                                    <th rowspan="3" style="vertical-align: middle;width: 5px; background: rgb(255, 234, 168)" class="warning border-warning">PED</th>
                                    <th class="warning border-warning" style="width: 175px; text-align:right; background: rgb(255, 234, 168)">Eje:</th>
                                    <td class="table-light">' . $datos['eje_nombre'] . '</td>
                                </tr>
                                <tr>
                                    <th class="warning border-warning" style="text-align:right;background: rgb(255, 234, 168)">Tema:</th>
                                    <td class="table-light">' . $datos['tem_nombre'] . '</td>
                                </tr>
                                <tr >
                                    <th class="warning border-warning" style="text-align:right;background: rgb(255, 234, 168)">Objetivo:</th>
                                    <td class="table-light" style="border: 2px solid dark; ">' . $datos['obj_nombre'] . '</td>
                                </tr>

                                ' .$pesPe. '

                                <tr>
                                    <th class="warning border-warning" colspan="2" style="text-align:right;background: rgb(255, 234, 168)">Programa:</th>
                                    <td class="table-light">' . $datos['prgs_nombre'] . '</td>
                                </tr>
                                ' .$datoComponente. '
                                <tr>
                                    <th class="warning border-warning" colspan="2" style="text-align:right;background: rgb(255, 234, 168)">Resumen Narrativo Nivel ' . $nivel . ':</th>
                                    <td class="table-light">' . $datos['rn'] . '</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    private function llenaTablaIndicador()
    {
        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];
        $tpi = (isset($this->prm->tpi_id) && $this->prm->tpi_id != '') ? ' AND tpi_id=' . $this->prm->tpi_id : '';

        $this->conectar();

        switch ($this->prm->nivel) {
            case 1:
                $this->setSql('
                    SELECT ind_id, ind_nombre, ind_definicion, ind_objetivo, ind_formulaCalculo,
                        tpi_id, tpi_nombre, umi_id, umi_nombre,dim_id, frm_id, ses_id, tiv_id, nim_id,
                        pmi_id AS eri_id, ind_nombreMIR, vpm_id
                    FROM  vin_ProgramaMirIndicador v
                    INNER JOIN cat_Indicador USING(nim_id,ind_id)
                    INNER JOIN cat_TipoIndicador USING(tpi_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    INNER JOIN (
                        SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id
                        FROM vin_ProgramasMir
                        UNION
                        SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id
                        FROM vin_UrProgramaMir ur
                        INNER JOIN vin_ProgramasMir USING(vpm_id)
                    )ur USING(vpm_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND prgs_id=' . $prg_id . ' AND v.trim_0=1 AND
                        nim_id=' . $this->prm->nivel . $tpi . '
                    ;
                ');
                break;
            case 2:
                $this->setSql('
                    SELECT  ind_id, ind_nombre, ind_definicion, ind_objetivo, ind_formulaCalculo,
                        tpi_id, tpi_nombre, umi_id, umi_nombre,dim_id, frm_id, ses_id, tiv_id, nim_id,
                        pmi_id AS eri_id, ind_nombreMIR
                    FROM vin_ProgramaMirIndicador v
                    INNER JOIN cat_Indicador USING(ind_id,nim_id)
                    INNER JOIN cat_TipoIndicador USING(tpi_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    INNER JOIN (
                        SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id
                        FROM vin_ProgramasMir
                        UNION
                        SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id
                        FROM vin_UrProgramaMir ur
                        INNER JOIN vin_ProgramasMir USING(vpm_id)
                        )ur USING(vpm_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND prgs_id=' . $prg_id . ' AND
                        nim_id=' . $this->prm->nivel . $tpi . ' AND v.trim_0=1;
                ');
                break;
            case 3:
                $this->setSql('
                    SELECT DISTINCT epfi_id AS eri_id, spr_id, pdf_id,pdf_nombre, ind_id, ind_nombre, ind_definicion, ind_objetivo, ind_formulaCalculo,
                    tpi_id, tpi_nombre, umi_id, umi_nombre,dim_id, frm_id, ses_id, tiv_id, nim_id, ind_nombreMIR
                    FROM cat_Subprograma
                    INNER JOIN cat_ProductoFinal USING(pdf_id)
                    INNER JOIN enc_ProductoFinalIndicador e USING (pdf_id)
                    JOIN cat_Indicador USING(nim_id,ind_id)
                    INNER JOIN cat_TipoIndicador USING(tpi_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    WHERE e.trim_0=1 AND nim_id=3
                    AND pdf_id = ' . $this->prm->pdf_id . '
                    AND prg_id = ' . $_SESSION[_S]['clasAdm-prg'] .
                    ((!$this->urResponsable && !$this->validador) ? '
                    AND gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] : '') . $tpi . '
                    GROUP BY eri_id,spr_id;
                ');
                break;
            case 4:
                $this->setSql('
                    SELECT DISTINCT pii_id AS eri_id,
                    pdf_id,pdi_id,pdi_nombre,
                    ind_id, ind_nombre, ind_definicion, ind_objetivo, ind_formulaCalculo,
                    tpi_id, tpi_nombre, umi_id, umi_nombre,dim_id, frm_id, ses_id, tiv_id, nim_id, ind_nombreMIR
                    FROM vin_ProductoIntermedioIndicador v
                    INNER JOIN cat_ProductoIntermedio USING(pdi_id)
                    INNER JOIN cat_Indicador USING(nim_id,ind_id)
                    INNER JOIN cat_TipoIndicador USING(tpi_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    WHERE nim_id = 4 AND v.trim_0=1
                    AND pdf_id = ' . $this->prm->pdf_id . '
                    AND pdi_id = ' . $this->prm->pdi_id . $tpi . '
                ');
        }
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Indicador';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        } else {
            $this->mixScript('
                $(".verIndicador").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"addmodIndicador",
                        ban:true,
                        nivel:' . $this->prm->nivel . ',
                        ind_id:$(this).attr("ind_id"),
                        eri_id:$(this).attr("eri_id"),
                        umi_id:$(this).attr("umi_id"),
                        regresaInd:1
                        ' . (isset($this->prm->com_id) ? ', com_id:' . $this->prm->com_id : "") . '
                        ' . (isset($this->prm->pdf_id) ? ', pdf_id:' . $this->prm->pdf_id . ',' : "") . '
                        ' . (isset($this->prm->pdi_id) ? 'pdi_id:' . $this->prm->pdi_id . '' : "") . '
                    });
                });
            ');
        }
    }

    private function listaMetas($ind_id = 0)
    {
        $parametros = array();
        $editar = '';
        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];
        if ($this->prm->nivel > 2) {
            $editar = new AccesoDatos();
            $editar->conectar();
            $editar->setSql('
                SELECT prg_id,pdf_id,gpo_id, ur_id
                FROM cat_Subprograma
                WHERE gpo_id !=:grupo AND prg_id=:prg_id AND
                gpo_id=:gpo_id and ur_id=:ur_id and pdf_id=:pdf_id;
            ');
            $parametros = [
                ':grupo' => 0,
                ':prg_id' => $prg_id,
                ':gpo_id' => $_SESSION[_S]['gpo_id'],
                ':ur_id' => $_SESSION[_S]['ur_id'],
                ':pdf_id' => $this->prm->pdf_id,
            ];
            $editar->consultar($parametros);
            $editar->desconectar();
        }
        //nivel de USUARIO
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
                switch ($this->prm->nivel) {
                    case 1: // porque a nivel UR  no deben capturar a este este NIVEL
                        $this->lectura = true;
                        break;
                    case 2:
                        if (!($this->estadoMir == 2 || $this->estadoMir == 4 || $this->estadoMir == 6 || $this->estadoMir == 7 || $this->estadoMir == 8 || $this->estadoMir == 9)) {
                            $this->lectura = ($this->urResponsable ? false : true);
                        }
                        break;
                    case 3:
                        if ($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) {
                            $this->lectura = ($editar->getNumeroRegistros() > 0 ? false : true);
                        }
                        break;
                    case 4: // Se agrega para nueva funcionalidad de registro de metas nivel Actividad
                        if ($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) {
                            $this->lectura = ($editar->getNumeroRegistros() > 0 ? false : true);
                        }
                        break;
                }

                break;
            default:

                if (($this->estadoMir == 1 || $this->estadoMir == 5 || $this->estadoMir == 6)) {
                    if ($this->prm->nivel == 1 && $_SESSION[_S]['ssc_id'] == 2) {
                        $this->lectura = false;
                    }
                } else {
                    if ($this->prm->nivel != 1) {
                        $this->lectura = true;
                    } else {
                        if ($this->estadoMir == 5) {
                            $this->lectura = false;
                        }
                    }
                }
                break;
        }

        $metodoNivel = $texto= '';
        switch ($this->prm->nivel) {
            case 1:
                $metodoNivel = 'addModMetaFin';
                $texto = 'nivel Fin';
                break;
            case 2:
                $metodoNivel = 'addModMetaProposito';
                $texto = 'nivel Propósito';
                break;
            case 3:
                $metodoNivel = 'addModMetaComponente';
                $texto = 'nivel Componente';
                break;
            case 4:
                $metodoNivel = 'addModMetaActividad';
                $texto = 'nivel Actividad';
                break;
        }

        $sqlestado = '
            SELECT
            MAX( IF(tri_id=1 AND nim_id=1, etr_id, NULL)) AS est_nim1,
            MAX( IF(tri_id=1 AND nim_id=2, etr_id, NULL)) AS est_nim12,
            MAX( IF(tri_id=1 AND nim_id=3, etr_id, NULL)) AS est_nim13,
            MAX( IF(tri_id=2 AND nim_id=1, etr_id, NULL)) AS est_nim21,
            MAX( IF(tri_id=2 AND nim_id=2, etr_id, NULL)) AS est_nim22,
            MAX( IF(tri_id=2 AND nim_id=3, etr_id, NULL)) AS est_nim23,
            MAX( IF(tri_id=3 AND nim_id=1, etr_id, NULL)) AS est_nim31,
            MAX( IF(tri_id=3 AND nim_id=2, etr_id, NULL)) AS est_nim32,
            MAX( IF(tri_id=3 AND nim_id=3, etr_id, NULL)) AS est_nim33,
            MAX( IF(tri_id=4 AND nim_id=1, etr_id, NULL)) AS est_nim41,
            MAX( IF(tri_id=4 AND nim_id=2, etr_id, NULL)) AS est_nim42,
            MAX( IF(tri_id=4 AND nim_id=3, etr_id, NULL)) AS est_nim43
            FROM mir_TrimestreUr
            WHERE gpo_id=? AND ur_id=?
            GROUP BY gpo_id, ur_id;';

        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
        ];
        $this->conectar();
        $this->setSql($sqlestado);
        $this->consultar($parametros);
        $estadoTrim = $this->extraer();

        $hoy = date("Y-m-d");

        if (
            !$this->lectura && $estadoTrim['est_nim1'] == "C" && $estadoTrim['est_nim12'] == "C" && $estadoTrim['est_nim13'] == "C" &&
            $estadoTrim['est_nim21'] == "C" && $estadoTrim['est_nim22'] == "C" && $estadoTrim['est_nim23'] == "C" &&
            $estadoTrim['est_nim31'] == "C" && $estadoTrim['est_nim32'] == "C" && $estadoTrim['est_nim33'] == "C" || 
            ($hoy <= APP_EJERCICIO_ANTERIOR.'-12-31' && $this->estadoMir == 10 && 
            ($this->usu == 'LGARCIAC' || $this->usu == 'MDIONICIOM' || $this->usu == 'ALCRUZV' || $this->usu == 'ADIAZL' 
            ||  $this->usu == 'JHERNANDEZR' || $this->usu == 'NMARTINEZC') && $this->prm->nivel == 4)
        ) {

            $addMeta = new stdClass();
            $addMeta->id = 'NuevaMeta';
            $addMeta->icon = 'plus me-2';
            $addMeta->class = 'me-1';
            $addMeta->color = 'azul';
            $addMeta->value = ' Nueva Meta';

            $addMeta->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"' . $metodoNivel . '",
                    nim_id:' . $this->prm->nivel . ',
                    ind_id:' . $this->prm->ind_id . ',
                    eri_id:' . $this->prm->eri_id . ',
                    pmi_id:' . $this->prm->eri_id . ',
                    tri_id: 0,' .
                    (isset($this->prm->pdf_id) ? 'pdf_id:' . $this->prm->pdf_id . ', ' : '') . '
                    ' . (isset($this->prm->pdi_id) ? 'pdi_id:' . $this->prm->pdi_id . ', ' : '') . '
                    ' .(isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                    abc:"a",
                    lectura:false
                });
            ';

            isset($_SESSION[_S]['prm']['A']['12']['2']['2']) ? $this->agregaTools($this->ponBoton($addMeta)) : '';
        }

        $btnNuevoMedioV = '';
        $mayusculas = '$("#nuevoNombre").attr("style","text-transform:none");';

        if (!$this->lectura && $this->estadoMir == 1 || $this->estadoMir == 3 || $this->estadoMir == 5 || $this->estadoMir == 10 || $this->estadoMir == 11) {
            switch ($this->prm->nivel) {
                case 1: case 2:
                    # code...
                    $btnNuevoMedioV = new stdClass();
                    $btnNuevoMedioV->id = 'addMedioFP';
                    $btnNuevoMedioV->icon = 'plus me-2';
                    $btnNuevoMedioV->color = 'azul';
                    $btnNuevoMedioV->value = ' Nuevo Medio';
                    $btnNuevoMedioV->title = 'Agregar Medio Verificación';
                    $btnNuevoMedioV->click = '
                        comunica("' . $this->pantalla . '",{
                                metodo: "modalMedioVer",
                                pmi_id:"' . $this->prm->eri_id . '",
                                vpm_id:' . $this->vpm_id . ',
                                nivel:' . $this->prm->nivel . ',
                                ind_id: ' . $this->prm->ind_id . '
                        });
                    ';
                    break;
                case 3:
                    # code...
                    $btnNuevoMedioV = new stdClass();
                    $btnNuevoMedioV->id = 'addMedioC';
                    $btnNuevoMedioV->icon = 'plus me-2';
                    $btnNuevoMedioV->color = 'azul';
                    $btnNuevoMedioV->value = ' Nuevo Medio';
                    $btnNuevoMedioV->title = 'Agregar Medio Verificación';
                    $btnNuevoMedioV->click = '
                        comunica("' . $this->pantalla . '",{
                                metodo: "modalMedioVer",
                                pdf_id:"' . $this->prm->pdf_id . '",
                                nivel:' . $this->prm->nivel . ',
                                ind_id: ' . $this->prm->ind_id . '
                        });
                    ';
                    break;
                case 4:
                    $btnNuevoMedioV = new stdClass();
                    $btnNuevoMedioV->id = 'addMedioA';
                    $btnNuevoMedioV->icon = 'plus me-2';
                    $btnNuevoMedioV->color = 'azul';
                    $btnNuevoMedioV->value = ' Nuevo Medio';
                    $btnNuevoMedioV->title = 'Agregar Medio Verificación';
                    $btnNuevoMedioV->click = '
                        comunica("' . $this->pantalla . '",{
                            metodo: "modalMedioVer",
                            pdf_id:"' . $this->prm->pdf_id . '",
                            pdi_id:"' . $this->prm->pdi_id . '",
                            nivel:' . $this->prm->nivel . ',
                            ' .(isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                            ind_id: ' . $this->prm->ind_id . '
                        });
                    ';
                    break;
            }

            isset($_SESSION[_S]['prm']['A']['12']['2']['2']) ? $this->agregaTools($this->ponBoton($btnNuevoMedioV)) : '';
        }

        $tabla = new stdClass();
        $tabla->titulo = 'Metas';
        $tabla->id = 'tbl-metasIndicador';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMetas';
        $tabla->comunica->eri_id = $this->prm->eri_id;
        $tabla->comunica->ind_id = $this->prm->ind_id;
        $tabla->comunica->nim_id = $this->prm->nim_id;
        $tabla->comunica->tri_id = 0;
        $tabla->desplegable = new stdClass();
        $tabla->desplegable->metodo = $metodoNivel;
        $tabla->desplegable->titulo = 'Meta';
        $tabla->desplegable->encabezado = 'Meta del indicador ' . $this->prm->ind_id . ' ' . $texto;
        $tabla->desplegable->enFila = false;
        $tabla->desplegable->prm = array('mem_id', 'nim_id', 'ind_id', 'eri_id', 'lectura', 'ur', 'pmi_id', 'pdf_id', 'epg_id', 'macm_id', 'tri_id');

        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '5px';
        $th->rowspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Descripcion';
        $th->rowspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Linea Base';
        $th->width = '30px';
        $th->colspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Valor Deseado';
        $th->width = '30px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Eliminar';
        $th->width = '5px';
        $th->rowspan = 2;
        array_push($tr, $th);

        $trbody = array();
        $tabla->encabezado = array($tr);

        $tr2 = array();
        $th = new stdClass();
        $th->texto = 'año';
        $th->width = '5px';
        array_push($tr2, $th);
        $th = new stdClass();
        $th->texto = 'valor';
        $th->width = '5px';
        array_push($tr2, $th);
        $th = new stdClass();
        $th->texto = APP_EJERCICIO;
        $th->width = '5px';
        array_push($tr2, $th);

        array_push($tabla->encabezado, $tr2);

        $td = new stdClass();
        $td->campoTxt = "ur";
        array_push($trbody, $td);
        $td = new stdClass();
        ($this->prm->nivel == 3) ? $td->campoTxt = 'ind_nombreMIR' : $td->campoTxt = "descripcion";
        array_push($trbody, $td);

        switch ($this->prm->nim_id) {
            case 2:
                switch ($this->prm->umi_id) {
                    case 2:
                    case 3:
                    case 4: //PORCENTAJE Y RAZÓN
                        $td = new stdClass();
                        $td->campoTxt = "mem_anio";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mlb_valorIndicador";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mvp_valorIndicador";
                        $td->decimales = '4';
                        array_push($trbody, $td);
                        break;
                    case 5: // TASA
                        $td = new stdClass();
                        $td->campoTxt = "mlb_anioT0";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mlb_valorIndicador";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mvp_valorIndicador";
                        $td->decimales = '4';
                        array_push($trbody, $td);
                        break;
                    default:
                        $td = new stdClass();
                        $td->campoTxt = "mem_anio";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mem_valor";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mem_original";
                        $td->decimales = '4';
                        array_push($trbody, $td);
                        break;
                }
                break;
            case 3:
                switch ($this->prm->umi_id) {
                    case 5: //TASA
                        $td = new stdClass();
                        $td->campoTxt = "mco_anterior";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mco_lbIndicador";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mco_original";
                        array_push($trbody, $td);
                        break;
                    default:
                        $td = new stdClass();
                        $td->campoTxt = "mco_lb";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mco_lbIndicador";
                        array_push($trbody, $td);
                        $td = new stdClass();
                        $td->campoTxt = "mco_original";

                        array_push($trbody, $td);
                        break;
                }
                break;
            case 4:
                $td = new stdClass();
                $td->campoTxt = "mac_anio";
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = "mac_valor";
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = "mac_original";
                array_push($trbody, $td);
                break;
            default:
                $td = new stdClass();
                $td->campoTxt = "mtf_anio";
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = "mem_valor";
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = "mem_original";
                $td->decimales = '4';
                array_push($trbody, $td);
                break;
        }

        if ($this->prm->nivel != 4 && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $this->activo) {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = "lectura";
            $td->casos->valor = "0";
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = "boton";
            $td->casos->verdadero->color = "rojo";
            $td->casos->verdadero->icono = "trash-can";
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->ind_id = "ind_id";
            $td->casos->verdadero->attr->mem_id = "mem_id";
            $td->casos->verdadero->attr->pmi_id = "pmi_id";
            $td->casos->verdadero->attr->nim_id = "nim_id";
            $td->casos->verdadero->class = "deleteMeta";
            array_push($trbody, $td);
        } else if ($this->prm->nivel == 4 && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $this->activo) {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = "lectura";
            $td->casos->valor = "0";
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = "boton";
            $td->casos->verdadero->color = "rojo";
            $td->casos->verdadero->icono = "trash-can";
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->gpo_id = $_SESSION[_S]['gpo_id'];
            $td->casos->verdadero->attr->ur_id = $_SESSION[_S]['ur_id'];
            $td->casos->verdadero->attr->pii_id = "pii_id";
            $td->casos->verdadero->attr->macm_id = "macm_id";
            $td->casos->verdadero->attr->nim_id = "nim_id";
            $td->casos->verdadero->attr->pdi_id = "pdi_id";
            $td->casos->verdadero->attr->com_id = "com_id";
            $td->casos->verdadero->class = "deleteMeta";
            array_push($trbody, $td);
        }
        $tabla->cuerpo = $trbody;
        $metas = $this->wrPonTabla($tabla);

        return $metas;
    }

    private function llenaTablaMetas()
    {

        $medios = $this->existenMedios();
        $metodo = '';
        (isset($this->prm->unidadMedida) ? $this->prm->umi_id = $this->prm->unidadMedida : '');
        switch ($this->prm->nim_id) {
            case 1:
                $metodo = 'abcMetaPed';
                $this->setSql('
                    SELECT  CONCAT(gpo_id,ur_id) ur, umv_id, prgs_id, pmi_id AS eri_id, nim_id, ind_id, mtf_id AS mem_id, vpm_id,pmi_id,
                    ind_nombreMIR AS descripcion, mtf_anio, round(mtf_original,4) AS mem_original, mtf_modificado, round(mtf_valor,4) AS mem_valor,
                    true lectura
                    FROM cat_MetaFin mf
                    INNER JOIN vin_ProgramaMirIndicador USING (pmi_id)
                    INNER JOIN vin_ProgramasMir USING(vpm_id)
                    INNER JOIN cat_Indicador USING(nim_id, ind_id)
                    where prgs_id=' . $_SESSION[_S]['clasAdm-prg'] . ' AND pmi_id=' . $this->prm->eri_id . ' 
                    AND mf.usr_fecha < "' . APP_EJERCICIO .'-01-01" AND nim_id =' . $this->prm->nim_id . ';
                ');
                break;
            case 2:
                $metodo = 'abcMetaPes';
                $this->setSql('
                    SELECT CONCAT(gpo_id,ur_id) ur,  prgs_id, pmi_id AS eri_id, nim_id, ind_id, mpr_id AS mem_id, vpm_id,
                    ind_nombreMIR AS descripcion, mpr_lb AS mem_anio,mpr_original AS mem_original,mpr_modificado AS mem_modificado, mpr_lbIndicador as mlb_valorIndicador,
                    umi_id,mpr_anteriorValor as mlb_valorT0,mpr_anterior AS mlb_anioT0,mpr_anteriorIndicador AS mvp_valorIndicador, mpr_lbValor AS mem_valor,
                    ' . (($this->lectura) ? 'true' : 'false') . ($this->validador ? '' : '
                    OR concat(gpo_id,ur_id)!="' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '"') . ' lectura
                    FROM cat_MetaProposito mp
                    INNER JOIN vin_ProgramaMirIndicador USING (pmi_id)
                    INNER JOIN vin_ProgramasMir USING(vpm_id)
                    INNER JOIN cat_Indicador USING(nim_id, ind_id)
                    INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                    where prgs_id=' . $_SESSION[_S]['clasAdm-prg'] . ' AND pmi_id=' . $this->prm->eri_id . ' 
                    AND mp.usr_fecha < "' . APP_EJERCICIO .'-01-01" AND nim_id =' . $this->prm->nim_id . ';
                ');
                break;
            case 3:
                $metodo = 'abcMetaComponente';
                $this->setSql('
                    SELECT CONCAT(gpo_id, ur_id) ur, umi_id, prgs_id, epfi_id AS eri_id, nim_id, ind_id, pdf_id,
                    ind_nombreMIR AS descripcion, mco_lb, mco_lbIndicador, mco_lbValor, mco_original, mco_anteriorIndicador,
                    ' . ($this->lectura ? 'true' : 'false') . ($this->validador ? '' : '
                    OR CONCAT(gpo_id, ur_id) != "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '"') . ' lectura,
                    ind_nombreMIR,mco_anterior
                    FROM cat_MetaComponente M
                    INNER JOIN enc_ProductoFinalIndicador USING (epfi_id)
                    INNER JOIN cat_Indicador USING(nim_id, ind_id)
                    INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
                    WHERE prgs_id = ' . $_SESSION[_S]['clasAdm-prg'] . ' AND epfi_id=' . $this->prm->eri_id .
                    ($this->urResponsable || $this->validador ? '' : '
                    AND gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND mco_trimestreCierre=' . $this->trimestreCierre . '
                    AND ur_id = ' . $_SESSION[_S]['clasAdm-ur']) . ' AND M.usr_fecha < "' . APP_EJERCICIO .'-01-01";
                ');
                break;
            case 4:
                $metodo = 'abcMetaActividadMir';
                $this->setSql('
                    SELECT CONCAT(gpo_id, ur_id) ur, umi_id, pii_id AS eri_id, nim_id, ind_id, pdf_id, pdi_id,MAX(macm_id) macm_id,
                    MAX(ind_nombreMIR) AS descripcion,  MAX(macm_lb) mac_anio , MAX(macm_lbIndicador) AS mac_valor, IF(umi_id =5 ,
                    SUM(macm_anteriorIndicador),SUM(macm_original)) mac_original,
                    ' . ($this->lectura ? 'true' : 'false') . ($this->validador ? '' : '
                    OR CONCAT(gpo_id, ur_id) != "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '"') . ' lectura
                    FROM cat_MetaActividadMir ma
                    INNER JOIN vin_ProductoIntermedioIndicador USING (pii_id)
                    INNER JOIN cat_Indicador USING(nim_id, ind_id)
                    WHERE pdf_id = ' . $this->prm->pdf_id . '
                    AND pii_id = ' . $this->prm->eri_id .
                    ($this->urResponsable || $this->validador ? '' : '
                    AND gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id = ' . $_SESSION[_S]['clasAdm-ur']) . ' AND ma.usr_fecha < "' . APP_EJERCICIO .'-01-01"
                    GROUP BY gpo_id, ur_id;');
        }

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-metasIndicador';
        $nav->nfDefault = 1000;
        $tieneMetas = $this->wrLlenaTabla($nav);
        if ($tieneMetas == 0 && $this->activo) {

            $this->mixScript('
                $("#NuevaMeta").show();
                notify("No hay registros","error");
            ');
        } else {
            $this->mixScript('
                $("#NuevaMeta").hide();
            ');
        }

        if ($this->lectura) {
            $this->mixScript('
                $(".deleteMeta").remove();
            ');
        }
        if ($tieneMetas > 0 && $medios == 0) {
            $this->mixScript('
                notify("No hay registros","error");
            ');
        }
        $this->mixScript('
            $(".deleteMeta").click(function(){
                var button = $(this);
                alertify.confirm("ELIMINAR META","¿ Confirma que desea eliminar la meta ?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "' . $metodo . '",
                        ' . (isset($this->prm->eri_id) ? 'eri_id: ' . $this->prm->eri_id . ', ' : '') . '
                        mem_id: button.attr("mem_id"),
                        nim_id: ' . $this->prm->nim_id . ',
                        macm_id: button.attr("macm_id"),
                        ' . (isset($this->prm->gpo_id) ? 'gpo_id: ' . $this->prm->gpo_id . ', ' : '') . '
                        ' . (isset($this->prm->ur_id) ? 'ur_id: ' . $this->prm->ur_id . ', ' : '') . '
                        ' . (isset($this->prm->umi_id) ? 'umi_id: ' . $this->prm->umi_id . ', ' : '') . '
                        tri_id: 0,
                        ' . (isset($this->prm->pdf_id) ? 'pdf_id: ' . $this->prm->pdf_id . ', ' : '') . '
                        ' . (isset($this->prm->pdi_id) ? 'pdi_id:' . $this->prm->pdi_id . ', ' : '') . '
                        ' . (isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                        abc:"b"
                    });
                },
                function(){}).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
        if ($this->prm->nim_id == 1 || $this->prm->nim_id == 2) {
            if ($this->prm->umi_id == 2 || $this->prm->umi_id == 5) {

                $this->mixScript('
                    $(".mem_valor label").each(function(){
                        $(this).html($(this).html() + "%");
                    });
                    $(".mem_original label").each(function(){
                        $(this).html($(this).html() + "%");
                    });

                    $(".mvp_valorIndicador label").each(function(){
                        $(this).html($(this).html() + "%");
                    });
                    $(".mlb_valorIndicador label").each(function(){
                        $(this).html($(this).html() + "%");
                    });
                ');
            }
        }

        if ($this->prm->nim_id == 3) {
            if ($this->existeMetaUr($this->prm->pdf_id, $this->prm->eri_id)) {
                $this->mixScript('
                    $("#NuevaMeta").hide();
                ');
            } else {
                $this->mixScript('
                    $("#NuevaMeta").show();
                ');
            }

            if ($this->prm->umi_id == 2 || $this->prm->umi_id == 5) {

                $this->mixScript('
                    $(".mco_lbIndicador label").each(function(){
                        $(this).html($(this).html() + "%");
                    });
                    $(".mco_original label").each(function(){
                        $(this).html($(this).html() + "%");
                    });
                    $(".mco_anteriorIndicador label").each(function(){
                        $(this).html($(this).html() + "%");
                    });
                ');
            }
        }
        if ($this->prm->nim_id == 4) {
            if ($this->existeMetaMir($this->prm->eri_id)) {
                $this->mixScript('
                    $("#NuevaMeta").hide();
                ');
            } else {
                $this->mixScript('
                    $("#NuevaMeta").show();
                ');
            }
        }
    }

    private function addModMetaFin()
    {
        $parametros = array();
        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];
        if (!isset($this->prm->modalDeTabla)) {
            $this->setContenedor('divContenedorGeneralModal');
        }

        $this->conectar();
        $this->setSql('
            SELECT IF(tiv_id=1,"S","C") tiv_id, ind_nombre, ind_definicion, ind_nombreMIR,
            IF(nim_id=3,2,frm_id) AS frm_id, umi_id, nim_id, ind_definicionMIR
            FROM cat_Indicador WHERE nim_id = ?
            AND ind_id =?;'
        );
        $parametros = [
            $this->prm->nim_id,
            $this->prm->ind_id,
        ];
        $this->consultar($parametros);
        $tiv_id = $this->extraer();
        $tiv = $tiv_id['tiv_id'];

        $nom_indMIR = $tiv_id['ind_nombreMIR'];
        $frm = $tiv_id['frm_id'];
        $umi = $tiv_id['umi_id'];
        $this->desconectar();
        $this->prm->vpm_id = $this->vpm_id;
        $this->prm->tri_id = $this->trimestreCierre; // se agrega para saber que trimestre debe consultar en el historico
        $calendario = $this->calendarioMeta();

        $meta = new MetaIndicadorPed($this->prm);
        $historicoMetaFin = new HistoricoModificacionMetaFin($this->prm);

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 500;
        $descripcion->width = '300px';
        $descripcion->height = '100px';
        $descripcion->disabled = true;
        $descripcion->texto = $nom_indMIR;

        $anio = new stdClass();
        $anio->required = true;
        $anio->id = 'anio';
        $anio->enDiv = true;
        $anio->value = (isset($this->prm->mem_id)) ? $meta->getAnio() : APP_EJERCICIO_ANTERIOR;
        $anio->maxlength = 4;
        $anio->formato = 'entero';

        $valor = new stdClass();
        $valor->id = 'valor';
        $valor->enDiv = true;
        $valor->value = ($meta->getValor() != 0 ? $meta->getValor() : 0);
        $valor->decimales = '4';
        $valor->formato = 'numero';

        $trimestre1 = new stdClass();
        $trimestre1->id = 'ori_trimestre1';
        $trimestre1->enDiv = true;
        $trimestre1->label = '1° Trimestre';
        $trimestre1->value = (isset($calendario['mtft_original1']) ? $calendario['mtft_original1'] : '');
        $trimestre1->formato = 'numero';
        $trimestre1->disabled = true;

        $trimestre2 = new stdClass();
        $trimestre2->required = true;
        $trimestre2->id = 'ori_trimestre2';
        $trimestre2->enDiv = true;
        $trimestre2->label = '2° Trimestre';
        $trimestre2->value = (isset($calendario['mtft_original2']) ? $calendario['mtft_original2'] : '');
        $trimestre2->formato = 'numero';
        $trimestre2->disabled = true;

        $trimestre3 = new stdClass();
        $trimestre3->required = true;
        $trimestre3->id = 'ori_trimestre3';
        $trimestre3->enDiv = true;
        $trimestre3->label = '3° Trimestre';
        $trimestre3->value = (isset($calendario['mtft_original3']) ? $calendario['mtft_original3'] : '');
        $trimestre3->formato = 'numero';
        $trimestre3->disabled = true;

        $trimestre4 = new stdClass();
        $trimestre4->required = true;
        $trimestre4->id = 'ori_trimestre4';
        $trimestre4->enDiv = true;
        $trimestre4->label = '4° Trimestre';
        $trimestre4->value = (isset($calendario['mtft_original4']) ? $calendario['mtft_original4'] : '');
        $trimestre4->decimales = '4';
        $trimestre4->formato = 'numero';

        $v2020 = new stdClass();
        $v2020->id = 'original';
        $v2020->enDiv = true;
        $v2020->maxlength = 18;
        $v2020->disabled = true;
        $v2020->value = $historicoMetaFin->getModificado();
        $v2020->formato = 'numero';

        if ($calendario != NULL) {
            switch ($frm) {
                case 1: //trimestral
                    $trimestre1->value = $calendario['mtft_original1'];
                    $trimestre2->value = $calendario['mtft_original2'];
                    $trimestre3->value = $calendario['mtft_original3'];
                    $trimestre4->value = $calendario['mtft_original4'];
                    break;
                case 2: //semestral
                    $trimestre1->disabled = true;
                    $trimestre2->value = $calendario['mtft_original2'];
                    $trimestre3->disabled = true;
                    $trimestre4->value = $calendario['mtft_original4'];
                    break;
                default: //anual, bianual...
                    $trimestre1->disabled = true;
                    $trimestre2->disabled = true;
                    $trimestre3->disabled = true;
                    $trimestre4->value = (isset($calendario['mtft_original4']) ? $calendario['mtft_original4'] : '');
                    break;
            }
        }

        $encValor2 = '<th>' . APP_EJERCICIO . '</th>';
        $defnomInd = 'NOMBRE DEL INDICADOR';
        $legendp = 'Línea Base';
        $legend = 'Valor Programado Anual';

        $vpm = new AccesoDatos();
        $vpm->conectar();
        $vpm->setSql('
            SELECT vpm_id
            FROM(
                SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id
                FROM vin_ProgramasMir
                UNION
                SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id
                FROM vin_UrProgramaMir ur
                INNER JOIN vin_ProgramasMir USING(vpm_id)
            ) a
            WHERE gpo_id=?
            AND ur_id=? and prgs_id=?;');

        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $prg_id,
        ];
        $vpm->consultar($parametros);
        $vpm->desconectar();

        $form = new stdClass();
        $form->id = 'metaInd';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMetaPed';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = (isset($this->prm->mem_id)) ? 'c' : 'a';
        $form->datosExtra->nim_id = (string) $this->prm->nim_id;
        $form->datosExtra->ind_id = (string) $this->prm->ind_id;
        $form->datosExtra->eri_id = (string) $this->prm->eri_id;
        $form->datosExtra->pmi_id = (string) $this->prm->pmi_id;
        $form->datosExtra->mtf_id = (string) "1";
        $form->datosExtra->lectura = (string) $this->lectura;
        $form->datosExtra->tipoCalendario = $tiv;
        $form->datosExtra->unidadMedida = (string) $umi;
        $form->datosExtra->descripcionM = $nom_indMIR;

        $form->datosExtra->valor = '"{$("#valor").val()}"';
        $form->datosExtra->original = '"{$("#original").val()}"';
        $form->datosExtra->ori_trimestre1 = '"{$("#ori_trimestre1").val()}"';
        $form->datosExtra->ori_trimestre2 = '"{$("#ori_trimestre2").val()}"';
        $form->datosExtra->ori_trimestre3 = '"{$("#ori_trimestre3").val()}"';
        $form->datosExtra->ori_trimestre4 = '"{$("#ori_trimestre4").val()}"';

        $abc = 'a';

        if (isset($this->prm->mem_id)) {
            $form->datosExtra->mem_id = (string) $this->prm->mem_id;
            $abc = 'c';
        }

        $form = new form($form);
        $form->mixHtml('
            <fieldset class="col-md-12 col-xs-12">
                <legend>' . $defnomInd . '</legend>
                ' . $this->ponTextArea($descripcion) . '
            </fieldset>
            <fieldset class="col-md-8 col-xs-8">
                <legend><small>' . $legendp . '</small></legend>
                <table class="table table-bordered table-striped">
                    <tbody> <tr><td>' . $this->ponInput($anio) . '</td><td>' . $this->ponInput($valor) . '</td>' . '</tr></tbody>
                </table>
                <legend><small>' . $legend . '</small></legend>
                <table class="table table-bordered table-striped ">
                    <thead> <tr>' . $encValor2 . '</tr> </thead>
                    <tbody> <tr><td width="25%">' . $this->ponInput($v2020) . '</td></tr>  </tbody>
                </table>

            </fieldset>
            <table class="table table-bordered table-striped ">
                <tbody>
                    <tr>
                        <td>' . $this->ponInput($trimestre1) . '</td>
                        <td>' . $this->ponInput($trimestre2) . '</td>
                        <td>' . $this->ponInput($trimestre3) . '</td>
                        <td>' . $this->ponInput($trimestre4) . '</td>
                    </tr>
                </tbody>
            </table>
        ');
        if (isset($this->prm->modalDeTabla)) {
            $this->mixHtml(
                $form->ponForm() .
                ($_SESSION[_S]['nvj_id'] >= 3 && isset($_SESSION[_S]['prm']['A']['12']['2']['62']) && $_SESSION[_S]['prm']['A']['12']['2']['62'] && $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == $this->prm->ur && ($this->estadoMir < 6 || $this->estadoMir == 11) && $this->activo ?
                    '<div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                    </div>' : '')
            );
            $this->mixScript('
                $("#anio,#mlb_numerador,#mlb_Denominador").blur(function(){
                    if($("#anio").val() < ' . APP_EJERCICIO_ANTERIOR . ') {
                        $("#valor").attr("disabled",false);
                    } else if($("#anio").val() == ' . APP_EJERCICIO_ANTERIOR . ') {
                        $("#valor").attr("disabled","disabled");
                        $("#valor").val(0);
                    } else {
                        $("#anio").val('.APP_EJERCICIO_ANTERIOR.');
                        $("#valor").attr("disabled",true);
                        $("#valor").val(0);
                        alertify.alert("IMPORTANTE", "El año no puede ser mayor al actual", 
                            function(){}
                        ).set("labels", {ok:"Aceptar"});
                        
                    }
                });

                $("#valor").blur(function(){
                    if($("#anio").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#valor").val() == 0) {
                        alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                            function(){}
                        ).set("labels", {ok:"Aceptar"});
                    } else if($("#anio").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#valor").val() != 0) {
                        $("#saveMeta").attr("disabled",false);
                    }
                });

                $("#saveMeta").click(function(){
                    if($("#anio").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#anio").val() <= ' . APP_EJERCICIO_ANTERIOR . ' && $("#valor").val() == 0) {
                        $("#saveMeta").attr("disabled",true);
                        alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                            function(){}
                        ).set("labels", {ok:"Aceptar"});
                    } else {
                        $("#saveMeta").attr("disabled",false);
                    }
                });
            ');
        } else {
            $this->mixHtml('
                <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" style="width: 80% !IMPORTANT;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Meta de Indicador ' . $this->prm->ind_id . ' nivel FIN</h4>
                                <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $form->ponForm() . '</div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                                    <button id="cerrar" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });
                $("#close").click(function(){ $("#myModal").hide(); });
                $("#cerrar").click(function(){ $("#myModal").remove(); });
                $("#myModal").modal().show();
            ');
        }
        if ($this->lectura) {
        }
        $this->mixScript('

            $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur(function(){
                if( "' . $tiv . '" == "S" )
                    $("#original").val( formatNumber( unformatNumber( $("#ori_trimestre1").val() ) + unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre3").val() ) + unformatNumber( $("#ori_trimestre4").val() ) ) );
                else{
                    if( unformatNumber( $("#ori_trimestre1").val() ) > 0 ){
                        $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre1").val() ) ) );
                        if( $(this).val() != 0 )
                        $(this).val( formatNumber(  unformatNumber( $("#ori_trimestre1").val() ) ) );
                    }else if( unformatNumber( $("#ori_trimestre2").val() ) > 0 ){
                        $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre2").val() ) ) );
                        if( $(this).val() != 0 )
                        $(this).val( formatNumber(  unformatNumber( $("#ori_trimestre2").val() ) ) );
                    }else if( unformatNumber( $("#ori_trimestre3").val() ) > 0 ){
                        $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre3").val() ) ) );
                        if( $(this).val() != 0 )
                        $(this).val( formatNumber(  unformatNumber( $("#ori_trimestre3").val() ) ) );
                    }else if( unformatNumber( $("#ori_trimestre4").val() ) > 0 ){

                        $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre4").val() ) ) );
                        if( $(this).val() != 0 )
                        $(this).val( formatNumber( $("#ori_trimestre4").val(),"","4"  ) );
                    }else $("#original").val( formatNumber( 0 ) );
                }
            });

            $("#anio,#mlb_numerador,#mlb_Denominador").blur(function(){
                if($("#anio").val() < ' . APP_EJERCICIO_ANTERIOR . ') {
                    $("#valor").attr("disabled",false);
                    $("#valor").attr("disabled",false);
                } else if($("#anio").val() == ' . APP_EJERCICIO_ANTERIOR . ') {
                    $("#valor").attr("disabled",true);
                    $("#valor").val(0);
                } else {
                    $("#anio").val('.APP_EJERCICIO_ANTERIOR.');
                    $("#valor").attr("disabled",true);
                    $("#valor").val(0);
                    alertify.alert("IMPORTANTE", "El año no puede ser mayor al actual", 
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                }
            });

            $("#saveMeta").click(function(){
                if($("#anio").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#valor").val() == 0) {
                    $("#saveMeta").attr("disabled",true);
                    alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                        function(){}
                    ).set("labels", {ok:"Aceptar"});
                } else {
                    $("#saveMeta").attr("disabled",false);
                }
            });

            if("' . $abc . '" == "c"){
                $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur();
            }
        ');
    }

    private function calendarioMeta()
    {
        $parametros = array();
        $calendario = new AccesoDatos();
        $calendario->conectar();
        if (isset($this->prm->pmi_id)) {
            $calendario->setSql('
                SELECT 0.0 mtft_original1, 0.0 mtft_modificado1, 0.0 mtft_alcanzado1,0.0 mtft_original2, 0.0 mtft_modificado2, 0.0 mtft_alcanzado2,
                    0.0 mtft_original3, 0.0 mtft_modificado3, 0.0 mtft_alcanzado3,
                    mtf_original AS mtft_original4, mtft_modificado AS mtft_modificado4, mtft_alcanzado AS mtft_alcanzado4
                FROM cat_MetaFinTrimestre m
                INNER JOIN aux_HistoricoModificacionMetaFin aux USING(pmi_id,mtf_id)
                WHERE pmi_id= ' .$this->prm->pmi_id . ' AND m.tri_id=4
                AND aux.tri_id=0
                ;'
            );
        } else {
            $calendario->setSql('
                SELECT 0.0000 mtft_original1, 0.0000 mtft_modificado1, 0.0000 mtft_alcanzado1,0.0000 mtft_original2, 0.0000 mtft_modificado2, 0.0000 mtft_alcanzado2,
                    0.0000 mtft_original3, 0.0000 mtft_modificado3, 0.0000 mtft_alcanzado3, 0.0000 mtft_original4, 0.0000 mtft_modificado4, 0.0000 mtft_alcanzado4;
            ');
        }
        $calendario->consultar();
        $metas = $calendario->extraer();
        return $metas;
    }

    private function modalListadoMedioSupuestoComponente($tabla = 'Supuesto')
    {
        $parametros = array();
        $mayusculas = $nomenclatura = '';

        $this->setContenedor('divContenedorGeneralModal');
        if (!isset($this->prm->regresa)) {
            $this->titulo("Supuestos");
        }
        switch ($tabla) {
            case 'MedioVerificacion':
                $nomenclatura = 'mvcm_';
                $mayusculas = '$("#nuevoNombre").attr("style","text-transform:none");';
                break;
            case 'Supuesto':
                $nomenclatura = 'spc_';
                $mayusculas = "";
                break;
        }

        $consulta2 = new AccesoDatos();
        $consulta2->conectar();
        $consulta2->setSql('
            SELECT pdf_id, prg_id
            FROM cat_Subprograma WHERE gpo_id=? and ur_id=?
            AND prg_id=? and pdf_id=?;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $_SESSION[_S]['clasAdm-prg'],
            $this->prm->pdf_id,
        ];
        $consulta2->consultar($parametros);

        $prmComunica = ',pdf_id:' . $this->prm->pdf_id;

        $puedeEditar = !$this->validador && $consulta2->getNumeroRegistros() > 0;

        $botonNuevo = '';

        if ($puedeEditar && ($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && $tabla == 'Supuesto') {

            $btnNuevoSupuesto = new stdClass();
            $btnNuevoSupuesto->id = 'addSupuesto';
            $btnNuevoSupuesto->icon = 'plus me-2';
            $btnNuevoSupuesto->color = 'azul';
            $btnNuevoSupuesto->value = ' Nuevo';
            $btnNuevoSupuesto->title = 'Agregar ' . $tabla;
            $btnNuevoSupuesto->click = '
                if($("#nuevoSupuesto").length == 0){
                    $("#listaSupuestos").append(
                        $("<li/>").addClass("list-group-item active d-flex justify-content-between").attr("id","nuevoSupuesto").html(
                            $("<div />").addClass("col-md-11").html(
                                $("<input/>").addClass("form-control").attr("id","nuevoNombre").attr("type","text")
                            )
                        ).append(
                        $("<button/>").addClass("btn btn-test").html($("<span/>").addClass("fa-solid fa-floppy-disk fa-2x")).click(function(){
                            if($("#nuevoNombre").val()!=""){
                                comunica("' . $this->pantalla . '",{
                                        metodo: "abc' . $tabla . '",
                                        abc:"a",
                                        nombre:$("#nuevoNombre").val(),
                                        epo_id:"' . $this->prm->epe_id . '",
                                        eps_id:"' . $this->prm->eps_id . '",
                                        pdf_id:"' . $this->prm->pdf_id . '",
                                        pdi_id:"' . $this->prm->pdi_id . '",
                                        nivel:' . $this->prm->nivel . '
                                });
                            }
                        })
                        )
                    );
                    ' . $mayusculas . '
                }else{
                    $("#nuevoNombre").focus();
                }
            ';

            $botonNuevo = $this->ponBoton($btnNuevoSupuesto);
        }
        $this->conectar();
        $this->setSql('
            SELECT ' . $nomenclatura . 'id,' . $nomenclatura . 'nombre, gpo_id,ur_id,
                if((gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id=' . $_SESSION[_S]['ur_id'] . '), 1, 0) editar
            FROM cat_' . $tabla . 'Componente
            INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
            INNER JOIN cat_Subprogramas USING(prgs_id, sprs_id)
            WHERE prgs_id =:prg_id
            AND pdf_id =:pdf_id AND ' . $nomenclatura . 'trimestreCierre=:trimestreCierre AND ' . $nomenclatura . 'origen=:valor;
        ');
        $parametros = [
            ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
            ':pdf_id' => $this->prm->pdf_id,
            ':trimestreCierre' => $this->trimestreCierre,
            ':valor' => "P",
        ];
        $this->consultar($parametros);
        $this->desconectar();
        $this->mixHtml('
            <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"">
                <div class="modal-dialog modal-lg" style="width: 80% !IMPORTANT;">
                    <br /><br /><br /><br />
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">' . $tabla . ' nivel <b>COMPONENTE</b></h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-12 ">
                                    <ul class="list-group" id="listaSupuestos" >
        ');
        while ($supuestos = $this->extraer()) {
            $this->mixHtml('
                <li class="list-group-item d-flex flex-column" style="word-wrap: break-word; white-space: normal;" id="' . $supuestos[$nomenclatura . 'id'] . '">
                    <div>' . $supuestos[$nomenclatura . 'nombre'] . '</div>
                    <div class="d-flex justify-content-end gap-2 mt-2">
            ');
            #si el estado de la MIR del programa es 2 0 3 y puede editar en el nivel y ademas es un registro realizado por la UR se le permite eliminar
            if (($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && $supuestos['editar'] == 1 && $_SESSION[_S]['nvj_id'] <= 2 && $this->activo && $tabla == 'Supuesto') {
                $this->mixHtml('<button type="button" class="btn btn-outline-danger btn-sm deleteSup me-2" title="Eliminar" style="align-self: end;"><span class="fa fa-solid fa-trash-can"></span></button>');
            } else {
                if (isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && ($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && $supuestos['editar'] == 1 && $this->activo && $tabla == 'Supuesto') {
                    $this->mixHtml('<button type="button" class="btn btn-outline-danger btn-sm deleteSup me-2" title="Eliminar" style="align-self: end;"><span class="fa fa-solid fa-trash-can"></span></button>');
                }
            }
            $this->mixHtml('<span class="badge bg-info" style="align-self: end;">UR: ' . $supuestos['gpo_id'] . $supuestos['ur_id'] . '</span>');
            $this->mixHtml('</div></li>');
        }
        $this->mixHtml('
                                    </ul>
                                </fieldset>
                            </div>
                        </div>
                        <div class="modal-footer">
                            ' . (isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $this->activo ? $botonNuevo : '') . '
                            <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
                $("nav").menusinpres("quitaBreadcrumbs");
            });
            $(".deleteSup").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"abc' . $tabla . '",
                    abc:"b",
                    ' . $nomenclatura . 'id: $(this).parent().parent().attr("id"),
                    nivel:' . $this->prm->nivel . $prmComunica . '
                })
            });
            $("#close").click(function(){ $("#myModal").hide(); });
            $("#cerrar").click(function(){ $("#myModal").remove(); });
            $("#myModal").modal().show();
        ');
    }

    private function abcMedioSupuestoComponente($tabla = 'Supuesto')
    {
        $parametros = array();
        $idNum = $insert = $nombre = $nomenclatura = $mensaje = '';
        switch ($tabla) {
            case 'MedioVerificacion':
                $insert = 'INSERT INTO cat_MedioVerificacionComponente (gpo_id, ur_id, prgs_id, sprs_id, epfi_id, mvcm_origen, mvcm_nombre, mvcm_trimestreCierre, usr_id)';
                $nomenclatura = 'mvcm_';
                $id = (isset($this->prm->mvcm_id)) ? $this->prm->mvcm_id : '';
                $idNum = 'medioVeri';
                $nombre = trim((isset($this->prm->nombre)) ? strtoupper($this->prm->nombre) : '');
                $mensaje = 'Medio de verificación componente registrado.';
                break;
            case 'Supuesto':
                $nomenclatura = 'spc_';
                $insert = 'INSERT INTO cat_SupuestoComponente (gpo_id, ur_id, prgs_id, sprs_id, spc_origen, spc_nombre, spc_trimestreCierre, usr_id)';
                $id = (isset($this->prm->spc_id)) ? $this->prm->spc_id : '';
                $idNum = 'supuestos';
                $nombre = trim(strtoupper((isset($this->prm->nombre)) ? strtoupper($this->prm->nombre) : ''));
                break;
        }

        $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->prm->prg_id = $_SESSION[_S]['clasAdm-prg'];
        $idNum = $idNum . 'Com' . $this->prm->pdf_id;
        $prmComunica = ', pdf_id:' . $this->prm->pdf_id;

        $spr_id = $this->obtenerSubprograma($_SESSION[_S]['clasAdm-prg'], $this->prm->pdf_id);

        $this->conectar();

        switch ($this->prm->abc) {
            case 'a': //agregar supuesto o medio de verificación
                if ($tabla == 'MedioVerificacion') {
                    # code...
                    $this->setSql($insert . ' VALUES (:gpo_id, :ur_id, :prg_id, :spr_id, :epfi_id, :valor, :nombre, :trimestreCierre, :usr_id);');
                    $parametros = [
                        ':gpo_id' => $_SESSION[_S]['gpo_id'],
                        ':ur_id' => $_SESSION[_S]['ur_id'],
                        ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                        ':spr_id' => $spr_id,
                        ':epfi_id' => $this->prm->epfi_id,
                        ':valor' => "P",
                        ':nombre' => $nombre,
                        ':trimestreCierre' => $this->trimestreCierre,
                        ':usr_id' => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($parametros);
                    $id = $this->getUltimoId();
                } else {
                    $this->setSql($insert . ' VALUES (:gpo_id, :ur_id, :prg_id, :spr_id, :valor, :nombre, :trimestreCierre, :usr_id);');
                    $parametros = [
                        ':gpo_id' => $_SESSION[_S]['gpo_id'],
                        ':ur_id' => $_SESSION[_S]['ur_id'],
                        ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                        ':spr_id' => $spr_id,
                        ':valor' => "P",
                        ':nombre' => $nombre,
                        ':trimestreCierre' => $this->trimestreCierre,
                        ':usr_id' => $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($parametros);
                    $id = $this->getUltimoId();
                }
                $this->mixScript('
                    $("#nuevoSupuesto").html("' . $nombre . '")
                        .append(
                            $("<button/>").attr("type","button").addClass("btn btn-outline-danger btn-sm me-2").attr("title","Eliminar").html($("<span/>").addClass("fa fa-solid fa-trash-can"))
                            .click(function(){comunica("' . $this->pantalla . '",{metodo:"abc' . $tabla . '",abc:"b",' . $nomenclatura . 'id:' . $id . ',nivel:' . $this->prm->nivel . $prmComunica . '})})
                        ).removeClass("active").attr("id",' . $id . ');
                ');
                break;
            case 'b': //eliminar
                $this->setSql('DELETE FROM cat_' . $tabla . 'Componente WHERE ' . $nomenclatura . 'id = ?');
                $this->consultar([$id]);
                $this->mixScript(' $("#' . $id . '").remove();');
                $mensaje = 'Medio de Verificación componente eliminado';
                break;
        }
        $this->setSql('
            SELECT COUNT(' . $nomenclatura . 'id) cuantos
            FROM cat_' . $tabla . 'Componente
            INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
            INNER JOIN cat_Subprogramas USING(prgs_id, sprs_id)
            WHERE prgs_id =:prg_id AND pdf_id =:pdf_id
            AND ' . $nomenclatura . 'trimestreCierre=:trimestreCierre AND ' . $nomenclatura . 'origen=:valor;
        ');
        $parametros = [
            ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
            ':pdf_id' => $this->prm->pdf_id,
            ':trimestreCierre' => $this->trimestreCierre,
            ':valor' => "P",
        ];
        $this->consultar($parametros);
        $cuantos = $this->extraer();
        $cuantos = $cuantos['cuantos'];

        if ($tabla == 'MedioVerificacion') {
            # code...
            $this->setSql('
                INSERT INTO ' . BD_BITACORA . '.bit_MedioVerificacionComponente (bit_observacion, gpo_id, ur_id, prgs_id, sprs_id, epfi_id, mvcm_nombre, mvcm_trimestreCierre, usr_id)
                VALUES (:observacion,:gpo_id,:ur_id,:prg_id, :spr_id, :epfi_id, :nombre, :trimestreCierre,:usr_id)
            ');
            $parametros = [
                ':observacion' => $mensaje,
                ':gpo_id' => $_SESSION[_S]['gpo_id'],
                ':ur_id' => $_SESSION[_S]['ur_id'],
                ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                ':spr_id' => $spr_id,
                ':epfi_id' => $this->prm->epfi_id,
                ':nombre' => $this->prm->nombre,
                ':trimestreCierre' => $this->trimestreCierre,
                ':usr_id' => $_SESSION[_S]['usr_id']
            ];
            $this->consultar($parametros);
        }
        $this->desconectar();
        $this->mixScript('
            $("#myModal").modal("hide");
            $("#' . $idNum . '").html("' . $cuantos . '");
            if("'.$tabla.'" == "MedioVerificacion") {
                notify("'.$mensaje.'");
                comunica("' . $this->pantalla . '",{
                    metodo:"listaIndicador",
                    pdf_id: ' . $this->prm->pdf_id . ',
                    ' .(isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                    nivel: ' . $this->prm->nivel . '
                });
            }
        ');
        $this->Bitacora($sql = '');
    }

    private function obtenerSubprograma($prg_id, $pdf_id)
    {
        $parametros = array();
        $this->setSql('
            SELECT spr_id FROM cat_Subprograma WHERE prg_id = ?
            AND pdf_id = ?;
        ');
        $parametros = [
            $prg_id,
            $pdf_id,
        ];
        $this->conectar();
        $this->consultar($parametros);
        $datos = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            return $datos['spr_id'];
        } else {
            return '00';
        }
    }

    private function abcMetaPed()
    {
        $mensaje = '';
        if ($this->prm->abc != 'b') {
            $this->prm->vum_id = (isset($this->prm->vum_id) ? $this->prm->vum_id : '');
            $this->prm->pmi_id = $this->prm->pmi_id;
            $this->prm->descripcion = $this->prm->descripcionM;
            $this->prm->ori_trimestre1 = $this->prm->ori_trimestre1;
            $this->prm->ori_trimestre2 = $this->prm->ori_trimestre2;
            $this->prm->ori_trimestre3 = $this->prm->ori_trimestre3;
            $this->prm->ori_trimestre4 = $this->prm->ori_trimestre4;
            $this->prm->trimestreCierre = (string) $this->trimestreCierre;

            if ($this->prm->tipoCalendario == 'S') {
                $this->prm->original = $this->prm->ori_trimestre1 + $this->prm->ori_trimestre2 + $this->prm->ori_trimestre3 + $this->prm->ori_trimestre4;
            } else {
                if ($this->prm->ori_trimestre1 > 0) {
                    $this->prm->original = $this->prm->ori_trimestre1;
                }

                if ($this->prm->ori_trimestre2 > 0) {
                    $this->prm->original = $this->prm->ori_trimestre2;
                }

                if ($this->prm->ori_trimestre3 > 0) {
                    $this->prm->original = $this->prm->ori_trimestre3;
                }

                if ($this->prm->ori_trimestre4 > 0) {
                    $this->prm->original = $this->prm->ori_trimestre4;
                }
            }
        }

        switch ($this->prm->abc) {
            case 'a': //agregar
                $this->prm->mem_id = '0';
                $mensaje = 'Meta Generada exitosamente';
                $this->prm->observacion = 'Meta Fin registrada desde mir inicial';
                break;
            case 'c': //actualizar
                $mensaje = 'Meta Modificada exitosamente';
                $this->prm->observacion = 'Meta Fin modificada desde mir inicial';
                break;
            case 'b': //eliminar
                $this->prm->ipe_id = $this->prm->eri_id;
                $mensaje = 'Meta Eliminada exitosamente';
                $this->prm->observacion = 'Meta Fin eliminada desde mir inicial';
                break;
        }
        $this->prm->origen = "Controlador MIR Inicial";
        MetaIndicadorPed::abc($this->prm);

        $this->mixScript('
            notify("' . $mensaje . '");
            $("#myModal,#modalDeTablatbl-metasIndicador").modal().remove();
        ');

        $this->llenaTablaMetas();
    }

    private function addModMetaComponente()
    {
        $parametros = array();

        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];

        $abc = 'c';

        if (!isset($this->prm->modalDeTabla)) {
            $this->setContenedor('divContenedorGeneralModal');
            $abc = 'a';
        }

        $this->conectar();
        //La FRECUENCIA DE Medición A NIVEL COMPONENTE SIEMPRE SERÁ SEMESTRAL AUNQUE EN EL CATALOGO TENGA OTRA INFORMACIÓN.
        // Y el tipo de valor siempre será sumable.
        $this->setSql('
            SELECT  "S" AS tiv, tiv_id, ind_nombre, ind_definicion, ind_nombreMIR,
                2 AS frm_id, umi_id, nim_id, ind_definicionMIR,
                dim_id,dim_nombre, frm_id,frm_nombre, tpi_id,tpi_nombre,
                ses_id,ses_nombre,umv_id,umv_nombre, tiv_id AS tiv,tiv_nombre,
                ind_formulaCalculo
            FROM cat_Indicador
            INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
            INNER JOIN cat_UnidadMedidaVariable USING(umv_id)
            INNER JOIN cat_Dimension USING (dim_id)
            INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            INNER JOIN cat_TipoIndicador USING(tpi_id)
            INNER JOIN cat_SentidoEsperado USING(ses_id)
            INNER JOIN cat_TipoValor USING(tiv_id)
            WHERE nim_id = 3
            AND ind_id = ?;'
        );

        $this->consultar([$this->prm->ind_id]);
        $datos = $this->extraer();
        $tiv = $datos['tiv'];

        $nom_ind = $datos['ind_nombre'];
        $nom_MIR = $datos['ind_nombreMIR'];
        $umi = $datos['umi_id'];
        $umv = $datos['umv_id'];
        $unom = $datos['umv_nombre'];
        $calculo = $datos['ind_formulaCalculo'];
        $this->desconectar();

        if ($abc == 'a') {
            $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $this->prm->gpo_id = substr($this->prm->ur, 0, 1);
            $this->prm->ur_id = substr($this->prm->ur, 1, 2);
        }

        $this->prm->prgs_id = $prg_id;
        $this->prm->sprs_id = $this->obtenerSubprograma($prg_id, $this->prm->pdf_id);
        $this->prm->epfi_id = $this->prm->eri_id;
        $this->prm->tri_id = 0;

        $meta = new MetaComponente($this->prm);
        $historicoMeta = new HistoricoModificacionMetaComponente($this->prm);

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion_mod';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 500;
        $descripcion->width = '300px';
        $descripcion->height = '100px';
        $descripcion->disabled = true;
        $descripcion->texto = $nom_MIR;

        $variable = new stdClass();
        $variable->id = 'variable_mod';
        $variable->required = true;
        $variable->enDiv = true;
        $variable->maxlength = 100;
        $variable->width = '300px';
        $variable->height = '100px';
        $variable->disabled = true;
        $variable->texto = $nom_ind;

        $metodocal = new stdClass();
        $metodocal->id = 'calculo_mod';
        $metodocal->required = true;
        $metodocal->enDiv = true;
        $metodocal->maxlength = 100;
        $metodocal->width = '300px';
        $metodocal->height = '100px';
        $metodocal->disabled = true;
        $metodocal->texto = $calculo;

        $medida = new stdClass();
        $medida->id = 'umi_mod';
        $medida->required = true;
        $medida->enDiv = true;
        $medida->maxlength = 100;
        $medida->width = '300px';
        $medida->height = '100px';
        $medida->disabled = true;
        $medida->texto = $umv . "-" . $unom;

        //***********************LINEA BASE***************************************//
        $mcoLb = new stdClass();
        $mcoLb->required = true;
        $mcoLb->id = 'lb';
        $mcoLb->enDiv = true;
        $mcoLb->value = !empty($meta->getLb()) ? $meta->getLb() : APP_EJERCICIO_ANTERIOR;
        $mcoLb->maxlength = 4;
        $mcoLb->formato = 'entero';

        $mcoLbValor = new stdClass();
        $mcoLbValor->required = true;
        $mcoLbValor->id = 'lbValor';
        $mcoLbValor->enDiv = true;
        $mcoLbValor->value = $meta->getLbValor();
        $mcoLbValor->formato = 'numero';

        $mcoLbIndicador = new stdClass();
        $mcoLbIndicador->required = true;
        $mcoLbIndicador->id = 'lbIndicador';
        $mcoLbIndicador->enDiv = true;
        $mcoLbIndicador->value = $meta->getLbIndicador();
        $mcoLbIndicador->formato = 'numero';

        //***********************AÑO ANTERIOR***************************************//
        $mcoAnterior = new stdClass();
        $mcoAnterior->required = true;
        $mcoAnterior->id = 'anterior';
        $mcoAnterior->enDiv = true;
        $mcoAnterior->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO_ANTERIOR;
        $mcoAnterior->maxlength = 4;
        $mcoAnterior->formato = 'entero';

        $mcoAnteriorValor = new stdClass();
        $mcoAnteriorValor->required = true;
        $mcoAnteriorValor->id = 'anteriorValor';
        $mcoAnteriorValor->enDiv = true;
        $mcoAnteriorValor->value = $meta->getAnteriorValor();
        $mcoAnteriorValor->formato = 'numero';

        $mcoOriginal = new stdClass();
        $mcoOriginal->required = true;
        $mcoOriginal->id = 'original';
        $mcoOriginal->enDiv = true;
        $mcoOriginal->value = $historicoMeta->getOriginal();
        $mcoOriginal->formato = 'numero';
        $mcoOriginal->decimales = 4;

        $mcoAnteriorIndicador = new stdClass();
        $mcoAnteriorIndicador->required = true;
        $mcoAnteriorIndicador->id = 'anteriorIndicador';
        $mcoAnteriorIndicador->enDiv = true;
        $mcoAnteriorIndicador->formato = 'numero';

        //****************************CALENDARIO 1 GENERAL*************************************//

        $trimestre1 = new stdClass();
        $trimestre1->id = 'ori_trimestre1';
        $trimestre1->enDiv = true;
        $trimestre1->label = '1° Trimestre';
        $trimestre1->formato = 'numero';
        $trimestre1->positivo = true;

        $trimestre2 = new stdClass();
        $trimestre2->required = true;
        $trimestre2->id = 'ori_trimestre2';
        $trimestre2->enDiv = true;
        $trimestre2->label = '2° Trimestre';
        $trimestre2->formato = 'numero';
        $trimestre2->positivo = true;

        $trimestre3 = new stdClass();
        $trimestre3->required = true;
        $trimestre3->id = 'ori_trimestre3';
        $trimestre3->enDiv = true;
        $trimestre3->label = '3° Trimestre';
        $trimestre3->formato = 'numero';
        $trimestre3->positivo = true;

        $trimestre4 = new stdClass();
        $trimestre4->required = true;
        $trimestre4->id = 'ori_trimestre4';
        $trimestre4->enDiv = true;
        $trimestre4->label = '4° Trimestre';
        $trimestre4->formato = 'numero';
        $trimestre4->positivo = true;

        $total = new stdClass();
        $total->disabled = true;
        $total->id = 'ori_total';
        $total->enDiv = true;
        $total->label = 'Total';

        //****************************CALENDARIO 1*************************************//

        $trimestre1Cal1 = new stdClass();
        $trimestre1Cal1->id = 'num_trimestre1';
        $trimestre1Cal1->enDiv = true;
        $trimestre1Cal1->label = '1° Trimestre';
        $trimestre1Cal1->formato = 'numero';
        $trimestre1Cal1->positivo = true;

        $trimestre2Cal1 = new stdClass();
        $trimestre2Cal1->required = true;
        $trimestre2Cal1->id = 'num_trimestre2';
        $trimestre2Cal1->enDiv = true;
        $trimestre2Cal1->label = '2° Trimestre';
        $trimestre2Cal1->formato = 'numero';
        $trimestre2Cal1->positivo = true;

        $trimestre3Cal1 = new stdClass();
        $trimestre3Cal1->required = true;
        $trimestre3Cal1->id = 'num_trimestre3';
        $trimestre3Cal1->enDiv = true;
        $trimestre3Cal1->label = '3° Trimestre';
        $trimestre3Cal1->formato = 'numero';
        $trimestre3Cal1->positivo = true;

        $trimestre4Cal1 = new stdClass();
        $trimestre4Cal1->required = true;
        $trimestre4Cal1->id = 'num_trimestre4';
        $trimestre4Cal1->enDiv = true;
        $trimestre4Cal1->label = '4° Trimestre';
        $trimestre4Cal1->formato = 'numero';
        $trimestre4Cal1->positivo = true;

        $totalCal1 = new stdClass();
        $totalCal1->disabled = true;
        $totalCal1->id = 'num_total';
        $totalCal1->enDiv = true;
        $totalCal1->label = 'Total';

        //****************************CALENDARIO 2*************************************//
        $trimestre1Cal2 = new stdClass();
        $trimestre1Cal2->id = 'den_trimestre1';
        $trimestre1Cal2->enDiv = true;
        $trimestre1Cal2->label = '1° Trimestre';
        $trimestre1Cal2->formato = 'numero';
        $trimestre1Cal2->positivo = true;

        $trimestre2Cal2 = new stdClass();
        $trimestre2Cal2->required = true;
        $trimestre2Cal2->id = 'den_trimestre2';
        $trimestre2Cal2->enDiv = true;
        $trimestre2Cal2->label = '2° Trimestre';
        $trimestre2Cal2->formato = 'numero';
        $trimestre2Cal2->positivo = true;

        $trimestre3Cal2 = new stdClass();
        $trimestre3Cal2->required = true;
        $trimestre3Cal2->id = 'den_trimestre3';
        $trimestre3Cal2->enDiv = true;
        $trimestre3Cal2->label = '3° Trimestre';
        $trimestre3Cal2->formato = 'numero';
        $trimestre3Cal2->positivo = true;

        $trimestre4Cal2 = new stdClass();
        $trimestre4Cal2->required = true;
        $trimestre4Cal2->id = 'den_trimestre4';
        $trimestre4Cal2->enDiv = true;
        $trimestre4Cal2->label = '4° Trimestre';
        $trimestre4Cal2->formato = 'numero';
        $trimestre4Cal2->positivo = true;

        $totalCal2 = new stdClass();
        $totalCal2->disabled = true;
        $totalCal2->id = 'den_total';
        $totalCal2->enDiv = true;
        $totalCal2->label = 'Total';
        $totalCal2->formato = 'numero';

        //********************************LINEA BASE Numerador Denominador****************************************************//

        $mcoNumerador = new stdClass();
        $mcoNumerador->required = true;
        $mcoNumerador->id = 'numerador';
        $mcoNumerador->enDiv = true;
        $mcoNumerador->label = 'Numerador';
        $mcoNumerador->formato = 'decimal';
        $mcoNumerador->positivo = true;

        $mcoDenominador = new stdClass();
        $mcoDenominador->required = true;
        $mcoDenominador->id = 'denominador';
        $mcoDenominador->enDiv = true;
        $mcoDenominador->label = 'Denominador';
        $mcoDenominador->formato = 'numero';
        $mcoDenominador->positivo = true;

        //********************************VALOR DESEADO Y PROGRAMADO (RAZON Y TASA) *********************//

        $mcoDeseadoNumerador = new stdClass();
        $mcoDeseadoNumerador->required = true;
        $mcoDeseadoNumerador->id = 'deseadoNumerador';
        $mcoDeseadoNumerador->enDiv = true;
        $mcoDeseadoNumerador->label = 'Numerador';
        $mcoDeseadoNumerador->formato = 'numero';
        $mcoDeseadoNumerador->disabled = true;

        $mcoDeseadoDenominador = new stdClass();
        $mcoDeseadoDenominador->required = true;
        $mcoDeseadoDenominador->id = 'deseadoDenominador';
        $mcoDeseadoDenominador->enDiv = true;
        $mcoDeseadoDenominador->label = 'Denominador';
        $mcoDeseadoDenominador->formato = 'numero';
        $mcoDeseadoDenominador->disabled = true;

        //********************************RESULTADO PARCIAL *********************//
        $resParcialT1 = new stdClass();
        $resParcialT1->id = 'rpar_trimestre1';
        $resParcialT1->enDiv = true;
        $resParcialT1->label = '1° Trimestre';
        $resParcialT1->disabled = true;

        $resParcialT2 = new stdClass();
        $resParcialT2->id = 'rpar_trimestre2';
        $resParcialT2->enDiv = true;
        $resParcialT2->label = '2° Trimestre';
        $resParcialT2->disabled = true;

        $resParcialT3 = new stdClass();
        $resParcialT3->id = 'rpar_trimestre3';
        $resParcialT3->enDiv = true;
        $resParcialT3->label = '3° Trimestre';
        $resParcialT3->disabled = true;

        $resParcialT4 = new stdClass();
        $resParcialT4->id = 'rpar_trimestre4';
        $resParcialT4->enDiv = true;
        $resParcialT4->label = '4° Trimestre';
        $resParcialT4->disabled = true;

        $resParcialTo = new stdClass();
        $resParcialTo->id = 'totalParcial';
        $resParcialTo->enDiv = true;
        $resParcialTo->label = 'Total';
        $resParcialTo->disabled = true;
        //********************************FIN RESULTADO PARCIAL *********************//

        $mcoT0 = new stdClass();
        $mcoT0->required = true;
        $mcoT0->id = 't0';
        $mcoT0->enDiv = true;
        $mcoT0->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO_ANTERIOR;
        $mcoT0->maxlength = 4;
        $mcoT0->formato = 'entero';
        $mcoT0->label = 'AÑO T0';
        $mcoT0->disabled = true;

        $mcoT0Valor = new stdClass();
        $mcoT0Valor->required = true;
        $mcoT0Valor->id = 't0Valor';
        $mcoT0Valor->enDiv = true;
        $mcoT0Valor->formato = 'numero';
        $mcoT0Valor->label = 'VALOR AÑO T0';
        $mcoT0Valor->disabled = true;

        $mcoT1 = new stdClass();
        $mcoT1->required = true;
        $mcoT1->id = 't1';
        $mcoT1->enDiv = true;
        $mcoT1->value = !empty($meta->getAnio()) ? $meta->getAnio() : APP_EJERCICIO_ANTERIOR;
        $mcoT1->maxlength = 4;
        $mcoT1->formato = 'entero';
        $mcoT1->label = 'AÑO T1';
        $mcoT1->disabled = true;

        $mcoT1Valor = new stdClass();
        $mcoT1Valor->required = true;
        $mcoT1Valor->id = 't1Valor';
        $mcoT1Valor->enDiv = true;
        $mcoT1Valor->formato = 'numero';
        $mcoT1Valor->label = 'VALOR AÑO T1';
        $mcoT1Valor->disabled = true;

        //*********************************************************************************************//
        //semestral
        $trimestre1->disabled = true;
        $trimestre2->value = $historicoMeta->getModificadoT2();
        $trimestre3->disabled = true;
        $trimestre4->value = $historicoMeta->getModificadoT4();

        $trimestre1Cal1->disabled = true;
        $trimestre2Cal1->value = $historicoMeta->getNumeradorT2();
        $trimestre3Cal1->disabled = true;
        $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

        $trimestre1Cal2->disabled = true;
        $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();

        $trimestre3Cal2->disabled = true;
        $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();

        if ($datos["tiv_id"] == 1) {
            $mcoDeseadoNumerador->value = $trimestre2Cal1->value + $trimestre4Cal1->value;
            $mcoDeseadoDenominador->value = $trimestre2Cal2->value + $trimestre4Cal2->value;
            $mcoT1Valor->value = $trimestre2Cal1->value + $trimestre4Cal1->value;
        } else {
            $mcoDeseadoNumerador->value = $trimestre4Cal1->value;
            $mcoDeseadoDenominador->value = $trimestre4Cal2->value;
            $mcoT1Valor->value = $trimestre4Cal1->value;
        }

        $lb = '';
        $lbValor = '';
        $lbIndicador = '';

        $anterior = '';
        $anteriorValor = '';
        $anteriorIndicador = '';

        $original = '';

        $numerador = '';
        $denominador = '';
        $deseadoNumerador = '';
        $deseadoDenominador = '';
        $t0 = '';
        $t0Valor = '';
        $t1 = '';

        $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
        $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';
        $tCal2 = $resParcial1 = $resParcial2 = $resParcial3 = $resParcial4 = $totalParcial = '';
        $legendCal1 = $legendCal2 = $legendCalT = '';

        switch ($umi) {
            case 2:
            case 3:
            case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                if ($trimestre2Cal1->value != NULL && $trimestre4Cal1->value != NULL &&
                    $trimestre2Cal2->value != NULL && $trimestre4Cal2->value != NULL) {
                    if ($umi == 2) {
                        $resParcialT1->value = "";
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format((($trimestre2Cal1->value / $trimestre2Cal2->value) * 100), 4) .'%' : '0%');
                        $resParcialT3->value = "";
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format((($trimestre4Cal1->value / $trimestre4Cal2->value) * 100), 4) .'%' : '0%');
                    } else {
                        $resParcialT1->value = "";
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format(($trimestre2Cal1->value / $trimestre2Cal2->value), 4) : '0');
                        $resParcialT3->value = "";
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format(($trimestre4Cal1->value / $trimestre4Cal2->value), 4) : '0');
                    }
                    if ($datos["tiv_id"] == 1) {
                        if ($umi == 2) {
                            $totalCal1->value = $trimestre2Cal1->value + $trimestre4Cal1->value;
                            $totalCal2->value = $trimestre2Cal2->value + $trimestre4Cal2->value;
                            $resParcialTo->value = ($mcoOriginal->value) . '%';
                        } else {
                            $resParcialTo->value = ($mcoOriginal->value);
                            $totalCal1->value = $trimestre2Cal1->value + $trimestre4Cal1->value;
                            $totalCal2->value = $trimestre2Cal2->value + $trimestre4Cal2->value;
                        }
                    } else {
                        if ($umi == 2) {
                            $resParcialTo->value = $resParcialT4->value . '%';
                            $totalCal1->value = $trimestre4Cal1->value;
                            $totalCal2->value = $trimestre4Cal2->value;
                        } else {
                            $resParcialTo->value = $resParcialT4->value;
                            $totalCal1->value = $trimestre4Cal1->value;
                            $totalCal2->value = $trimestre4Cal2->value;
                        }
                    }
                    
                } else {
                    if ($umi == 2) {
                        $resParcialT1->value = "0%";
                        $resParcialT2->value = "0%";
                        $resParcialT3->value = "0%";
                        $resParcialT4->value = "0%";
                        $resParcialTo->value = "0%";
                        $totalCal1->value = "0";
                        $totalCal2->value = "0";
                    } else {
                        $resParcialT1->value = "0";
                        $resParcialT2->value = "0";
                        $resParcialT3->value = "0";
                        $resParcialT4->value = "0";
                        $resParcialTo->value = "0";
                        $totalCal1->value = "0";
                        $totalCal2->value = "0";
                    }
                }
                $legendp = 'LÍNEA BASE';
                $legend = 'VALOR DESEADO';
                $legendCal1 = '
                    <tr>
                        <td colspan=5><legend><small>Numerador</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=5><legend><small>Denominador</small></legend></td>
                    </tr>
                ';
                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';
                $mcoLb->value = !empty($meta->getLb()) ? $meta->getLb() : APP_EJERCICIO_ANTERIOR;

                $mcoLb->label = 'Año';
                $mcoLbIndicador->label = 'Valor LB';
                $mcoLbIndicador->disabled = true;
                $mcoOriginal->label = APP_EJERCICIO;
                $mcoOriginal->disabled = true;

                $mcoNumerador->value = $historicoMeta->getNumerador();
                $mcoDenominador->value = $historicoMeta->getDenominador();
                $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();

                $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                $numerador = '<td>' . $this->ponInput($mcoNumerador) . '</td>';
                $denominador = '<td>' . $this->ponInput($mcoDenominador) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';

                $deseadoNumerador = '<td>' . $this->ponInput($mcoDeseadoNumerador) . '</td>';
                $deseadoDenominador = '<td>' . $this->ponInput($mcoDeseadoDenominador) . '</td>';
                $original = '<td>' . $this->ponInput($mcoOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';

                break;
            case 5: // TASA
                if ($trimestre2Cal1->value != NULL && $trimestre4Cal1->value != NULL &&
                    $trimestre2Cal2->value != NULL && $trimestre4Cal2->value != NULL) {

                    $resParcialT1->value = "";
                    $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format(((($trimestre2Cal1->value / $trimestre2Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT3->value = "";
                    $resParcialT4->value = (($trimestre4Cal2->value != 0) ?number_format(((($trimestre4Cal1->value / $trimestre4Cal2->value) - 1) * 100), 4) : '0');

                    if ($datos["tiv_id"] == 1) {

                        $totalCal1->value = number_format(($trimestre2Cal1->value + $trimestre4Cal1->value), 4);
                        $totalCal2->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                        $resParcialTo->value = (($totalCal2->value != 0) ?number_format(((($totalCal1->value / $totalCal2->value) - 1) * 100), 4) . '%' : '0%');
                        $mcoT0Valor->value = number_format(($trimestre2Cal2->value + $trimestre4Cal2->value), 4);
                        $mcoAnteriorIndicador->value = $resParcialTo->value;
                    } else {
                        $resParcialTo->value = $resParcialT4->value . '%';
                        $totalCal1->value = $trimestre4Cal1->value;
                        $totalCal2->value = $trimestre4Cal2->value;
                        $mcoT0Valor->value = $trimestre4Cal2->value;
                        $mcoAnteriorIndicador->value = $resParcialTo->value;
                    }
                    $resParcialT1->value .= "%";
                    $resParcialT2->value .= "%";
                    $resParcialT3->value .= "%";
                    $resParcialT4->value .= "%";
                } else {
                    $resParcialT1->value = "0%";
                    $resParcialT2->value = "0%";
                    $resParcialT3->value = "0%";
                    $resParcialT4->value = "0%";
                    $resParcialTo->value = "0%";
                    $totalCal1->value = "0";
                    $totalCal2->value = "0";
                }
                $legendp = 'LÍNEA BASE';
                $legend = 'VALORES PROGRAMADOS';
                $legendCal1 = '
                    <tr>
                        <td colspan=5><legend><small>T1</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=5><legend><small>T0</small></legend></td>
                    </tr>
                ';

                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';

                $mcoLb->value = !empty($meta->getLb()) ? $meta->getLb() : APP_EJERCICIO_ANTERIOR;
                $mcoAnterior->value = !empty($meta->getAnterior()) ? $meta->getAnterior() : APP_EJERCICIO_ANTERIOR;

                $mcoLb->label = 'AÑO T-1';
                $mcoLbValor->label = 'VALOR AÑO T-1';
                $mcoAnterior->label = 'AÑO T0';
                $mcoAnteriorValor->label = 'VALOR AÑO T0';
                $mcoLbIndicador->label = 'VALOR INDICADOR';
                $mcoAnteriorIndicador->label = 'VALOR INDICADOR';
                $mcoOriginal->label = 'VALOR AÑO T1';

                $mcoLbIndicador->disabled = true;
                $mcoOriginal->disabled = true;
                $mcoAnteriorIndicador->disabled = true;

                $anterior = '<td>' . $this->ponInput($mcoAnterior) . '</td>';
                $anteriorValor = '<td>' . $this->ponInput($mcoAnteriorValor) . '</td>';
                $anteriorIndicador = '<td>' . $this->ponInput($mcoAnteriorIndicador) . '</td>';

                $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                $lbValor = '<td>' . $this->ponInput($mcoLbValor) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';

                $t0 = '<td>' . $this->ponInput($mcoT0) . '</td>';
                $t0Valor = '<td>' . $this->ponInput($mcoT0Valor) . '</td>';
                $t1 = '<td>' . $this->ponInput($mcoT1) . '</td>';
                $original = '<td>' . $this->ponInput($mcoT1Valor) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';

                break;
            default:
                if ($trimestre2->value != NULL && $trimestre4->value != NULL) {
                    $resParcialT1->value = "";
                    $resParcialT2->value = number_format(($trimestre2->value), 4);
                    $resParcialT3->value = "";
                    $resParcialT4->value = number_format(($trimestre4->value), 4);

                    if ($datos["tiv_id"] == 1) {
                        $totalCal1->value = number_format(($resParcialT2->value + $resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT2->value + $resParcialT4->value), 4);
                    } else {
                        $totalCal1->value = number_format(($resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT4->value), 4);
                    }
                } else {
                    $resParcialT1->value = "0%";
                    $resParcialT2->value = "0%";
                    $resParcialT3->value = "0%";
                    $resParcialT4->value = "0%";
                    $resParcialTo->value = "0%";
                    $total->value = "0%";
                }
                $legendp = 'Línea Base';
                $legend = 'Valor Programado Anual';

                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';

                $mcoLb->label = 'Año';
                $mcoLbIndicador->label = 'Valor';

                $mcoOriginal->disabled = true;

                $lb = '<td>' . $this->ponInput($mcoLb) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($mcoLbIndicador) . '</td>';
                $original = '<td>' . $this->ponInput($mcoOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($total) . '</td>';

                break;
        }

        $form = new stdClass();
        $form->id = 'metaInd';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMetaComponente';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->nim_id = $this->prm->nim_id;
        $form->datosExtra->ind_id = $this->prm->ind_id;
        $form->datosExtra->eri_id = $this->prm->eri_id;
        $form->datosExtra->epfi_id = $this->prm->eri_id;
        $form->datosExtra->gpo_id = $this->prm->gpo_id;
        $form->datosExtra->ur_id = $this->prm->ur_id;
        $form->datosExtra->prgs_id = $this->prm->prgs_id;
        $form->datosExtra->sprs_id = $this->prm->sprs_id;
        $form->datosExtra->pdf_id = $this->prm->pdf_id;
        $form->datosExtra->lectura = (string) $this->lectura;
        $form->datosExtra->tipoCalendario = $tiv;
        $form->datosExtra->unidadMedida = $umi;
        $form->datosExtra->descripcion = $nom_MIR;
        $form->datosExtra->anio = (string) APP_EJERCICIO;
        $form->datosExtra->umi_id = $umi;

        switch ($umi) {
            case 2:
            case 3:
            case 4: // RAZÓN
                $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                $form->datosExtra->original = '"{$("#original").val()}"';
                break;
            case 5: // TASA
                $form->datosExtra->lbValor = '"{$("#lbValor").val()}"';
                $form->datosExtra->original = '"{$("#anteriorIndicador").val()}"';
                $form->datosExtra->anteriorValor = '"{$("#anteriorValor").val()}"';
                $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                break;
            default:
                $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                $form->datosExtra->original = '"{$("#original").val()}"';
                break;
        }

        $form = new form($form);

        $form->mixHtml('
            <fieldset class="col-md-12 col-xs-12">
                <legend>NOMBRE DEL INDICADOR</legend>
                ' . $this->ponTextArea($descripcion) . '
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <label for="ind_nombre" class="col-sm-3 control-label">NOMBRE DE LA VARIABLE</label>
                ' . $this->ponTextArea($variable) . '
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <label for="ind_nombre" class="col-sm-3 control-label">MÉTODO DE CÁLCULO</label>
                ' . $this->ponTextArea($metodocal) . '
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <label for="ind_nombre" class="col-sm-3 control-label">UNIDAD DE MEDIDA</label>
                ' . $this->ponTextArea($medida) . '
            </fieldset>
            <fieldset class="col-md-8 col-xs-8">
                <legend><small>' . $legendp . '</small></legend>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            ' . $lb . $lbValor . $anterior . $anteriorValor . $numerador . $denominador . $lbIndicador . '
                        </tr>
                    </tbody>
                </table>
                <legend><small>' . $legend . '</small></legend>
                <table class="table table-bordered table-striped ">
                    <tbody>
                        <tr>
                            ' . $deseadoNumerador . $deseadoDenominador . $t0 . $t0Valor . $t1 . $original . $anteriorIndicador . '
                        </tr>
                    </tbody>
                </table>

            </fieldset>

            <table class="table table-bordered table-striped ">
                <tbody>
                    ' . $legendCal1 . '
                    <tr>
                        ' . $trim1Cal1 . $trim2Cal1 . $trim3Cal1 . $trim4Cal1 . $tCal1 . '
                    </tr>
                    ' . $legendCal2 . '
                    <tr>
                        ' . $trim1Cal2 . $trim2Cal2 . $trim3Cal2 . $trim4Cal2 . $tCal2 . '
                    </tr>
                    ' . $legendCalT . '
                    <tr>
                        ' . $resParcial1 . $resParcial2 . $resParcial3 . $resParcial4 . $totalParcial . '
                    </tr>
                </tbody>
            </table>
        ');
        if (isset($this->prm->modalDeTabla)) {
            $this->mixHtml($form->ponForm());
            if (($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == $this->prm->ur && $this->activo) {
                $this->mixHtml('
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                    <button id="close" type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            ');
            }

            $this->mixScript('
            $("#close").click(function(){ $("#modalDeTablatbl-metasIndicador").remove(); });
            ');
        } else {
            $this->mixHtml('
                <div id="myModal" class="modal ">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Meta de Indicador ' . $this->prm->ind_id . ' de nivel Componente</h4>
                                <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $form->ponForm() . '</div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                                    <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").modal();
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });
                $("#cerrar").click(function(){ $("#myModal").remove(); });
                $("#close").click(function(){ $("#myModal").hide(); });
                $("#myModal").modal().show();
            ');
        }
        $this->mixScript('
            var calculoLBIndicador=0;
            var calculoVPIndicador=0;

            switch(' . $umi . '){
                case 2:case 3:case 4:    // RAZÓN
                    $("#numerador,#denominador").blur(function() {
                        if($("#denominador").val() != 0){
                            if(' . $umi . ' == 2){ //porcentaje
                               calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) ) * 100;
                            }else{ // razón
                                calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) );
                            }
                            $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                        }else{
                            $("#lbIndicador").val(0);
                        }
                        if($("#lb").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#numerador").val() == 0 && $("#denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else if($("#lb").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#numerador").val() != 0 && $("#denominador").val() != 0) {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });

                    //calendario 1
                    $("#num_trimestre2,#num_trimestre4").blur(function() {

                        if(' . $umi . ' == 2){
                            if($("#den_trimestre2").val() != 0) {
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())*100);
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#den_trimestre4").val() != 0) { 
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())*100);
                            } else {
                                porc_trimestre4 = 0;
                            }
                        } else {
                            if($("#den_trimestre2").val() != 0) {
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val()));
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#den_trimestre4").val() != 0) { 
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()));
                            } else {
                                porc_trimestre4 = 0;
                            }
                        }

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                            $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                            $("#deseadoNumerador").val(formatNumber($("#num_total").val()));
                            if(' . $umi . ' == 2){
                                if($("#num_total").val() != 0 && $("#den_total").val() != 0) {
                                    $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                    $("#totalParcial").val($("#original").val() + "%");
                                } else {
                                    $("#original").val(0);
                                    $("#totalParcial").val($("#original").val() + "%");
                                }
                            } else {
                                $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                $("#totalParcial").val($("#original").val() + "%");
                            }
                        } else {
                            if(' . $umi . ' == 2){ //porcentaje
                                $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                            } else {
                                $("#totalParcial").val(formatNumber(porc_trimestre4));
                            }
                            $("#num_total").val(unformatNumber($("#num_trimestre4").val()));
                            $("#deseadoNumerador").val(formatNumber($("#num_total").val()));
                            $("#original").val(formatNumber(porc_trimestre4));
                        }

                        if(' . $umi . ' == 2){ //porcentaje
                            $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                            $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                        } else {
                            $("#rpar_trimestre2").val(formatNumber(porc_trimestre2));
                            $("#rpar_trimestre4").val(formatNumber(porc_trimestre4));
                        }

                    });

                    //calendario 2
                    $("#den_trimestre2,#den_trimestre4").blur(function() {

                        if(' . $umi . ' == 2){
                            if($("#den_trimestre2").val() != 0) {
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())*100);
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#den_trimestre4").val() != 0) { 
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())*100);
                            } else {
                                porc_trimestre4 = 0;
                            }
                        } else {
                            if($("#den_trimestre2").val() != 0) {
                                porc_trimestre2 = formatNumber(unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val()));
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#den_trimestre4").val() != 0) { 
                                porc_trimestre4 = formatNumber(unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()));
                            } else {
                                porc_trimestre4 = 0;
                            }
                        }

                        if(' . $datos["tiv_id"] . ' == 1){

                            $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                            $("#deseadoDenominador").val(formatNumber($("#den_total").val()));
                            if(' . $umi . ' == 2){
                                if($("#num_total").val() != 0 && $("#den_total").val() != 0) {
                                    $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                    $("#totalParcial").val($("#original").val() + "%");
                                } else {
                                    $("#original").val(0);
                                    $("#totalParcial").val($("#original").val() + "%");
                                }
                            } else {
                                $("#original").val(((($("#num_total").val() / $("#den_total").val()) * 100)).toFixed(4));
                                $("#totalParcial").val($("#original").val() + "%");
                            }
                        } else {
                            if(' . $umi . ' == 2){ //porcentaje
                                $("#totalParcial").val(formatNumber(porc_trimestre4) +"%");
                            } else {
                                $("#totalParcial").val(formatNumber(porc_trimestre4));
                            }
                            $("#den_total").val(unformatNumber($("#den_trimestre4").val()));
                            $("#deseadoDenominador").val(formatNumber($("#den_total").val()));
                            $("#original").val(formatNumber(porc_trimestre4));
                        }

                        if(' . $umi . ' == 2){ //porcentaje
                            $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                            $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                        } else {
                            $("#rpar_trimestre2").val(formatNumber(porc_trimestre2));
                            $("#rpar_trimestre4").val(formatNumber(porc_trimestre4));
                        }
                    });
                    break;
                case 5: // TASA
                    $("#anteriorValor, #lbValor").blur(function(){
                        calculoLBIndicador=((unformatNumber($("#anteriorValor").val()) / unformatNumber($("#lbValor").val()))-1) * 100;
                        $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                    });

                    //calendario 1
                    $("#num_trimestre2,#num_trimestre4").blur(function() {

                        porc_trimestre2 = formatNumber((unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())-1)*100);
                        porc_trimestre4 = formatNumber((unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())-1)*100);

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                            $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                            $("#totalParcial").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100) + "%");
                            $("#original").val(formatNumber($("#num_total").val()));
                            $("#anteriorIndicador").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100));
                            $("#t0Valor").val(formatNumber($("#den_total").val()));
                        } else {
                            $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                            $("#num_total").val(unformatNumber($("#num_trimestre4").val()));
                            $("#den_total").val(unformatNumber($("#den_trimestre4").val()));
                            $("#original").val(formatNumber($("#num_trimestre4").val()));
                            $("#anteriorIndicador").val(porc_trimestre4);
                            $("#t0Valor").val(formatNumber($("#den_total").val()));
                        }

                        $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                        $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                    });


                    //calendario 2
                    $("#den_trimestre2,#den_trimestre4,#lbValor").blur(function(){

                        porc_trimestre2 = formatNumber((unformatNumber($("#num_trimestre2").val()) / unformatNumber($("#den_trimestre2").val())-1)*100);
                        porc_trimestre4 = formatNumber((unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val())-1)*100);

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#num_total").val(unformatNumber($("#num_trimestre2").val()) + unformatNumber($("#num_trimestre4").val()));
                            $("#den_total").val(unformatNumber($("#den_trimestre2").val()) + unformatNumber($("#den_trimestre4").val()));
                            $("#totalParcial").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100) + "%");
                            $("#macm_original").val(formatNumber($("#num_total").val()));
                            $("#anteriorIndicador").val(formatNumber((($("#num_total").val() / $("#den_total").val())-1)*100));
                            $("#t0Valor").val(formatNumber($("#den_total").val()));
                        } else {
                            $("#totalParcial").val(formatNumber(porc_trimestre4) + "%");
                            $("#num_total").val(formatNumber($("#num_trimestre4").val()));
                            $("#den_total").val(formatNumber($("#den_trimestre4").val()));
                            $("#macm_original").val(formatNumber($("#num_total").val()));
                            $("#anteriorIndicador").val(porc_trimestre4);
                            $("#t0Valor").val(formatNumber($("#den_total").val()));
                        }

                        $("#rpar_trimestre2").val(formatNumber(porc_trimestre2) + "%");
                        $("#rpar_trimestre4").val(formatNumber(porc_trimestre4) + "%");
                    });

                break;
                default: // todas las demás unidades de medida del indicador

                    $("#ori_trimestre2,#ori_trimestre4").blur(function() {

                        $("#rpar_trimestre2").val($("#ori_trimestre2").val());
                        $("#rpar_trimestre4").val($("#ori_trimestre4").val());

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#num_total").val(formatNumber(unformatNumber($("#ori_trimestre2").val())+ unformatNumber($("#ori_trimestre4").val())));
                            $("#ori_total").val(formatNumber(unformatNumber($("#ori_trimestre2").val()) + unformatNumber($("#ori_trimestre4").val())));
                            $("#original").val(formatNumber(unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre4").val())));
                        } else {
                            $("#ori_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#num_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#original").val(unformatNumber($("#ori_trimestre4").val()));
                        }
                    });

                    if("' . $abc . '" == "c"){
                        $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur();
                    }
                    break;

            }
                    $("#lb").blur(function(){
                        if($("#lb").val() < ' . APP_EJERCICIO_ANTERIOR . ') {
                            $("#numerador").attr("disabled",false);
                            $("#denominador").attr("disabled",false);
                            if(' . $umi . ' == 2){ //porcentaje
                               calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) ) * 100;
                            }else{ // razón
                                calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) );
                            }
                            $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                        } else if($("#lb").val() == ' . APP_EJERCICIO_ANTERIOR . ') {
                            $("#lb").val('.APP_EJERCICIO_ANTERIOR.');
                            $("#numerador").attr("disabled",true);
                            $("#denominador").attr("disabled",true);
                            $("#numerador").val(0);
                            $("#denominador").val(0);
                            $("#lbIndicador").val(0);
                        } else {
                            $("#lb").val('.APP_EJERCICIO_ANTERIOR.');
                            $("#numerador").attr("disabled",true);
                            $("#denominador").attr("disabled",true);
                            $("#numerador").val(0);
                            $("#denominador").val(0);
                            $("#lbIndicador").val(0);
                            alertify.alert("IMPORTANTE", "El año no puede ser mayor al actual", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        }
                    });

                    $("#saveMeta").click(function(){
                        if($("#lb").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#numerador").val() == 0 && $("#denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });
        ');
    }

    //INSERCIÓN, ACTUALIZACIÓN Y ELIMINACIÓN DE LA META A NIVEL COMPONENTE
    private function abcMetaComponente()
    {
        $mensaje = '';
        if ($this->prm->abc != 'b') {
            switch ($this->prm->unidadMedida) {
                case 2:
                case 3:
                case 4: //RAZÓN
                    $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;

                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;

                    $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    break;
                case 5: //TASA
                    $this->prm->num_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->den_trimestre2 = $this->prm->den_trimestre2;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    $t1 = $this->prm->num_trimestre2 + $this->prm->num_trimestre4;
                    $t0 = $this->prm->den_trimestre2 + $this->prm->den_trimestre4;

                    $this->prm->lbValor = $this->prm->lbValor;
                    $this->prm->lbIndicador = $this->prm->lbIndicador;

                    break;
                case 3: // PORCENTAJE
                    $this->prm->original = $this->prm->original;
                    break;
                default:
                    $this->prm->original = $this->prm->original;
                    break;
            }
        }

        switch ($this->prm->abc) {
            case 'a': //agregar
                $mensaje = 'Meta Generada exitosamente';
                $this->prm->observacion = 'Meta Componente registrada desde mir inicial';
                break;
            case 'c': //actualizar
                $mensaje = 'Meta Modificada exitosamente';
                $this->prm->observacion = 'Meta Componente modificada desde mir inicial';
                break;
            case 'b': //eliminar
                $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
                $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
                $this->prm->prgs_id = $_SESSION[_S]['clasAdm-prg'];
                $this->prm->sprs_id = $this->obtenerSubprograma($this->prm->prgs_id, $this->prm->pdf_id);
                $this->prm->epfi_id = $this->prm->eri_id;
                $mensaje = 'Meta Eliminada exitosamente';
                $this->prm->observacion = 'Meta Componente eliminada desde mir inicial';
                break;
        }
        $this->prm->origen = "Controlador MIR Inicial";
        $this->prm->trimestreCierre = (string) $this->trimestreCierre;
        $this->prm->tri_id = 0;
        MetaComponente::abc($this->prm);

        $this->mixScript('
            notify("' . $mensaje . '");
            $("#myModal,#modalDeTablatbl-metasIndicador").modal().remove();
        ');

        $this->llenaTablaMetas();
    }

    private function existeMetaUr($pdf_id, $epfi_id)
    {
        $parametros = array();
        $gpo = $_SESSION[_S]['clasAdm-gpo'];
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $prg = $_SESSION[_S]['clasAdm-prg'];
        $spr = $this->obtenerSubprograma($prg, $pdf_id);
        $this->conectar();
        $this->setSql('
            SELECT COUNT(*) AS existe FROM cat_MetaComponente
            WHERE gpo_id = ? AND ur_id = ?
            AND prgs_id = ? AND sprs_id = ?
            AND epfi_id = ?;
        ');
        $parametros = [
            $gpo,
            $ur,
            $prg,
            $spr,
            $epfi_id,
        ];
        $this->consultar($parametros);
        $datos = $this->extraer();
        $this->desconectar();
        return ($datos['existe'] > 0);
    }

    private function modalListadoMedioSupuestoActividad($tabla = 'Supuesto')
    {
        $parametros = array();
        $mayusculas = $nomenclatura = '';
        $this->setContenedor('divContenedorGeneralModal');
        if (!isset($this->prm->regresa)) {
            $this->titulo("Supuestos");
        }
        switch ($tabla) {
            case 'MedioVerificacion':
                $nomenclatura = 'mvac_';
                $mayusculas = '$("#nuevoNombre").attr("style","text-transform:none");';
                break;
            case 'Supuesto':
                $nomenclatura = 'spa_';
                $mayusculas = '';
                break;
        }

        $prmComunica = ', pdf_id:' . $this->prm->pdf_id . ', pdi_id:' . $this->prm->pdi_id;
        $consulta2 = new AccesoDatos();
        $consulta2->conectar();
        $consulta2->setSql('
            SELECT pdf_id, prg_id
            FROM cat_Subprograma WHERE gpo_id=? and ur_id=?
            AND prg_id=? and pdf_id=?;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $_SESSION[_S]['clasAdm-prg'],
            $this->prm->pdf_id,
        ];
        $consulta2->consultar($parametros);

        $puedeEditar = !$this->validador && $consulta2->getNumeroRegistros() > 0;

        $botonNuevo = '';
        if ($puedeEditar && ($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && $this->activo && $tabla == 'Supuesto' || 
        ($this->activo && $this->estadoMir == 10 && $tabla == 'Supuesto' && 
            ($this->usu == 'LGARCIAC' || $this->usu == 'MDIONICIOM' || $this->usu == 'ALCRUZV' || $this->usu == 'ADIAZL' 
            ||  $this->usu == 'JHERNANDEZR' || $this->usu == 'NMARTINEZC') && $this->prm->nivel == 4 && $tabla == 'MedioVerificacion')) {
            $btnNuevoSupuesto = new stdClass();
            $btnNuevoSupuesto->id = 'addSupuesto';
            $btnNuevoSupuesto->icon = 'plus me-2';
            $btnNuevoSupuesto->color = 'azul';
            $btnNuevoSupuesto->value = ' Nuevo';
            $btnNuevoSupuesto->title = 'Agregar ' . $tabla;
            $btnNuevoSupuesto->click = '
                if($("#nuevoSupuesto").length == 0){
                    $("#listaSupuestos").append(
                        $("<li/>").addClass("list-group-item active d-flex justify-content-between ").attr("id","nuevoSupuesto").html(
                            $("<div />").addClass("col-md-11").html(
                                $("<input/>").addClass("form-control").attr("id","nuevoNombre").attr("type","text")
                            )
                        ).append(
                        $("<button/>").addClass("btn ").html($("<span/>").addClass("fa-solid fa-floppy-disk fa-2x")).click(function(){
                            if($("#nuevoNombre").val()!=""){
                                comunica("' . $this->pantalla . '",{
                                    metodo: "abc' . $tabla . '",
                                    abc:"a",
                                    nombre:$("#nuevoNombre").val(),
                                    epo_id:"' . $this->prm->epe_id . '",
                                    eps_id:"' . $this->prm->eps_id . '",
                                    pdf_id:"' . $this->prm->pdf_id . '",
                                    pdi_id:"' . $this->prm->pdi_id . '",
                                    nivel:' . $this->prm->nivel . '
                                });
                            }
                        })
                        )
                    );
                    ' . $mayusculas . '
                }else{
                    $("#nuevoNombre").focus();
                }
            ';

            $botonNuevo = $this->ponBoton($btnNuevoSupuesto);
        }
        $this->conectar();
        $this->setSql('
            SELECT ' . $nomenclatura . 'id,' . $nomenclatura . 'nombre, gpo_id,ur_id,
            if((gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id=' . $_SESSION[_S]['ur_id'] . '), 1, 0) editar
            FROM cat_' . $tabla . 'Actividad
            INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
            INNER JOIN vin_ProductoFinalProductoIntermedio USING(pdf_id, pdi_id)
            WHERE prgs_id = :prg_id
            AND pdf_id = :pdf_id
            AND pdi_id = :pdi_id
            AND ' . $nomenclatura . 'trimestreCierre= :trimestreCierre
            AND ' . $nomenclatura . 'origen=:valor
        ');

        $parametros = [
            ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
            ':pdf_id' => $this->prm->pdf_id,
            ':pdi_id' => $this->prm->pdi_id,
            ':trimestreCierre' => $this->trimestreCierre,
            ':valor' => "P",
        ];

        $this->consultar($parametros);
        $this->desconectar();
        $this->mixHtml('
            <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog modal-lg" style="width: 80% !IMPORTANT;">
                    <br /><br /><br /><br />
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">' . $tabla . ' nivel <b>ACTIVIDAD</b></h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    <ul class="list-group" id="listaSupuestos" >
        ');
        while ($supuestos = $this->extraer()) {
            $this->mixHtml('
                <li class="list-group-item d-flex flex-column" style="word-wrap: break-word; white-space: normal;" id="' . $supuestos[$nomenclatura . 'id'] . '">
                    <div>' . $supuestos[$nomenclatura . 'nombre'] . '</div>
                    <div class="d-flex justify-content-end gap-2 mt-2">
            ');
            #si el estado de la MIR del programa es 2 0 3 y puede editar en el nivel y ademas es un registro realizado por la UR se le permite eliminar
            if (($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && $supuestos['editar'] == 1 && $_SESSION[_S]['nvj_id'] <= 2 && $this->activo) {
                $this->mixHtml('<button type="button" class="btn btn-outline-danger btn-sm deleteSup me-2" title="Eliminar" style="align-self: end;"><span class="fa fa-solid fa-trash-can"></span></button>');
            } else {
                if (isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && ($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && $supuestos['editar'] == 1 && $this->activo) {
                    $this->mixHtml('<button type="button" class="btn btn-outline-danger btn-sm deleteSup me-2" title="Eliminar" style="align-self: end;"><span class="fa fa-solid fa-trash-can"></span></button>');
                }
            }
            $this->mixHtml('<span class="badge bg-info" style="align-self: end;">UR: ' . $supuestos['gpo_id'] . $supuestos['ur_id'] . '</span>');
            $this->mixHtml('</div></li>');
        }
        $this->mixHtml('
                                    </ul>
                                </fieldset>
                            </div>
                        </div>
                        <div class="modal-footer">
                            ' . (isset($_SESSION[_S]['prm']['A']['12']['2']['2']) ? $botonNuevo : '') . '
                            <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
                $("nav").menusinpres("quitaBreadcrumbs");
            });
            $(".deleteSup").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"abc' . $tabla . '",
                    abc:"b",
                    ' . $nomenclatura . 'id: $(this).parent().parent().attr("id"),
                    nivel:' . $this->prm->nivel . $prmComunica . '
                })
            });
            $("#close").click(function(){ $("#myModal").hide(); });
            $("#cerrar").click(function(){ $("#myModal").remove(); });
            $("#myModal").modal().show();
        ');
    }

    private function abcMedioSupuestoActividad($tabla = 'Supuesto')
    {
        $idNum = $insert = $nombre = $nomenclatura = $mensaje = '';
        $parametros = [];
        switch ($tabla) {
            case 'MedioVerificacion':
                $insert = 'INSERT INTO cat_MedioVerificacionActividad (gpo_id, ur_id, prgs_id, sprs_id, pdf_id, pdi_id, pii_id, mvac_origen, mvac_nombre, mvac_trimestreCierre, usr_id)';
                $nomenclatura = 'mvac_';
                $id = (isset($this->prm->mvac_id)) ? $this->prm->mvac_id : '';
                $idNum = 'medioVeri';
                $nombre = trim(strtoupper(isset($this->prm->nombre)) ? strtoupper($this->prm->nombre) : '');
                $mensaje = 'Medio de verificación actividad registrado';
                break;
            case 'Supuesto':
                $nomenclatura = 'spa_';
                $insert = 'INSERT INTO cat_SupuestoActividad (gpo_id, ur_id, prgs_id, sprs_id, pdf_id, pdi_id, spa_origen, spa_nombre, spa_trimestreCierre, usr_id)';
                $id = (isset($this->prm->spa_id)) ? $this->prm->spa_id : '';
                $idNum = 'supuestos';
                $nombre = trim(strtoupper((isset($this->prm->nombre)) ? strtoupper($this->prm->nombre) : ''));
                break;
        }

        $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->prm->prg_id = $_SESSION[_S]['clasAdm-prg'];
        $idNum = $idNum . 'Act' . $this->prm->pdi_id;
        $prmComunica = ', pdf_id:' . $this->prm->pdf_id . ', pdi_id:' . $this->prm->pdi_id;

        $spr_id = $this->obtenerSubprograma($_SESSION[_S]['clasAdm-prg'], $this->prm->pdf_id);

        $this->conectar();

        switch ($this->prm->abc) {
            case 'a': //agregar supuesto o medio de verificación
                if ($tabla == 'MedioVerificacion') {
                    $this->setSql($insert . ' VALUES (:gpo_id, :ur_id, :prg_id, :spr_id, :pdf_id, :pdi_id, :pii_id, :valor, :nombre, :trimestreCierre, :usr_id);');

                    if ($_SESSION[_S]['ur_id'] == 15) {
                        # code...
                        $parametros = [
                            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
                            ':ur_id' => $_SESSION[_S]['clasAdm-ur'],
                        ];
                    } else if ($_SESSION[_S]['ur_id'] != 15) {
                    $parametros = [
                        ':gpo_id' => $_SESSION[_S]['gpo_id'],
                        ':ur_id' => $_SESSION[_S]['ur_id'],
                        ];
                    }

                    $parametros = array_merge($parametros,[
                        ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                        ':spr_id' => $spr_id,
                        ':pdf_id' => $this->prm->pdf_id,
                        ':pdi_id' => $this->prm->pdi_id,
                        ':pii_id' => $this->prm->pii_id,
                        ':valor' => "P",
                        ':nombre' => $nombre,
                        ':trimestreCierre' => $this->trimestreCierre,
                        ':usr_id' => $_SESSION[_S]['usr_id']
                    ]);
                    $this->consultar($parametros);
                    $id = $this->getUltimoId();
                } else {
                    $this->setSql($insert . ' VALUES (:gpo_id, :ur_id, :prg_id, :spr_id, :pdf_id, :pdi_id, :valor, :nombre, :trimestreCierre, :usr_id);');

                    if ($_SESSION[_S]['ur_id'] == 15) {
                        # code...
                        $parametros = [
                            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
                            ':ur_id' => $_SESSION[_S]['clasAdm-ur'],
                        ];
                    } else if ($_SESSION[_S]['ur_id'] != 15) {
                    $parametros = [
                        ':gpo_id' => $_SESSION[_S]['gpo_id'],
                        ':ur_id' => $_SESSION[_S]['ur_id'],
                        ];
                    }

                    $parametros = array_merge($parametros,[
                        ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                        ':spr_id' => $spr_id,
                        ':pdf_id' => $this->prm->pdf_id,
                        ':pdi_id' => $this->prm->pdi_id,
                        ':valor' => "P",
                        ':nombre' => $nombre,
                        ':trimestreCierre' => $this->trimestreCierre,
                        ':usr_id' => $_SESSION[_S]['usr_id']
                    ]);
                    $this->consultar($parametros);
                    $id = $this->getUltimoId();
                }
                $this->mixScript('
                    $("#nuevoSupuesto").html("' . $nombre . '")
                    .append(
                        $("<button/>").attr("type","button").addClass("btn btn-outline-danger btn-sm me-2").attr("title","Eliminar").html($("<span/>").addClass("fa fa-solid fa-trash-can"))
                        .click(function(){comunica("' . $this->pantalla . '",{metodo:"abc' . $tabla . '",abc:"b",' . $nomenclatura . 'id:' . $id . ',nivel:' . $this->prm->nivel . $prmComunica . '})})
                    ).removeClass("active").attr("id",' . $id . ');
                ');
                break;
            case 'b': //eliminar
                $this->setSql('DELETE FROM cat_' . $tabla . 'Actividad WHERE ' . $nomenclatura . 'id = ?');
                $this->consultar([$id]);
                $mensaje = 'Medio de Verificación actividad eliminado';
                break;
        }
        $this->setSql('
            SELECT COUNT(' . $nomenclatura . 'id) cuantos
            FROM cat_' . $tabla . 'Actividad
            INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
            INNER JOIN vin_ProductoFinalProductoIntermedio USING(pdf_id, pdi_id)
            WHERE prgs_id = :prg_id
            AND pdf_id = :pdf_id
            AND pdi_id = :pdi_id
            AND ' . $nomenclatura . 'trimestreCierre= :trimestreCierre AND ' . $nomenclatura . 'origen= :valor;
        ');

        $parametros = [
            ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
            ':pdf_id' => $this->prm->pdf_id,
            ':pdi_id' => $this->prm->pdi_id,
            ':trimestreCierre' => $this->trimestreCierre,
            ':valor' => "P",
        ];

        $this->consultar($parametros);
        $cuantos = $this->extraer();
        $cuantos = $cuantos['cuantos'];

        if ($tabla == 'MedioVerificacion') {
            $this->setSql('
                INSERT INTO ' . BD_BITACORA . '.bit_MedioVerificacionActividad (bit_observacion, gpo_id, ur_id, prgs_id, sprs_id, pdf_id, pdi_id, pii_id, mvac_nombre, mvac_trimestreCierre, usr_id)
                VALUES (:observacion,:gpo_id,:ur_id,:prg_id, :spr_id, :pdf_id, :pdi_id, :pii_id, :nombre, :trimestreCierre,:usr_id)
            ');
            $parametros = [
                ':observacion' => $mensaje,
                ':gpo_id' => $_SESSION[_S]['gpo_id'],
                ':ur_id' => $_SESSION[_S]['ur_id'],
                ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
                ':spr_id' => $spr_id,
                ':pdf_id' => $this->prm->pdf_id,
                ':pdi_id' => $this->prm->pdf_id,
                ':pii_id' => $this->prm->pii_id,
                ':nombre' => $this->prm->nombre,
                ':trimestreCierre' => $this->trimestreCierre,
                ':usr_id' => $_SESSION[_S]['usr_id']
            ];
            $this->consultar($parametros);
        }
        $this->desconectar();
        $this->mixScript('
            $("#myModal").modal("hide");
            $("#' . $idNum . '").html("' . $cuantos . '");
            if("'. $tabla .'" == "MedioVerificacion") {
                notify("'.$mensaje.'");
                comunica("' . $this->pantalla . '",{
                    metodo:"listaIndicador",
                    pdf_id: ' . $this->prm->pdf_id . ',
                    pdi_id: ' . $this->prm->pdi_id . ',
                    ' .(isset($this->prm->com_id) ? 'com_id:' . $this->prm->com_id . ', ' : '') . '
                    nivel: ' . $this->prm->nivel . '
                });
            }
        ');
        $this->Bitacora($sql = '');
    }

    private function addModMetaActividad()
    {
        $abc = 'c';
        $this->prm->pii_id = $this->prm->eri_id;
        $this->prm->macm_id = (isset($this->prm->macm_id) ? $this->prm->macm_id : '');

        if (!isset($this->prm->modalDeTabla)) {
            $this->setContenedor('divContenedorGeneralModal');
            $abc = 'a';
        }

        $this->conectar();
        $this->setSql(
            'SELECT "S" AS tiv, tiv_id, ind_nombre, ind_definicion,
            1 AS frm_id, umi_id, nim_id, ind_definicionMIR, ind_nombreMIR,
            umv_id,umv_nombre, ind_formulaCalculo
            FROM cat_Indicador
            INNER JOIN cat_UnidadMedidaVariable USING(umv_id)
            WHERE nim_id = ?
            AND ind_id = ?;'
        );
        $parametros = [
            4,
            $this->prm->ind_id,
        ];
        $this->consultar($parametros);
        $datos = $this->extraer();

        $tiv = $datos['tiv'];

        $nom_ind = $datos['ind_nombre'];
        $nom_MIR = $datos['ind_nombreMIR'];
        $umi = $datos['umi_id'];
        $umv = $datos['umv_id'];
        $unom = $datos['umv_nombre'];
        $calculo = $datos['ind_formulaCalculo'];

        if ($abc == 'a') {
            $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $this->prm->gpo_id = substr($this->prm->ur, 0, 1);
            $this->prm->ur_id = substr($this->prm->ur, 1, 2);
        }

        $this->prm->tri_id = (string) 0;

        $metaActividad = new MetaActividadMir($this->prm);
        $historicoMeta = new HistoricoMetaActividadMir($this->prm);

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 500;
        $descripcion->width = '300px';
        $descripcion->height = '100px';
        $descripcion->disabled = true;
        $descripcion->texto = $nom_MIR;

        $variable = new stdClass();
        $variable->id = 'umvariable';
        $variable->required = true;
        $variable->enDiv = true;
        $variable->maxlength = 500;
        $variable->width = '300px';
        $variable->height = '100px';
        $variable->disabled = true;
        $variable->texto = $nom_ind;

        $metodocal = new stdClass();
        $metodocal->id = 'calculo_mod';
        $metodocal->required = true;
        $metodocal->enDiv = true;
        $metodocal->maxlength = 100;
        $metodocal->width = '300px';
        $metodocal->height = '100px';
        $metodocal->disabled = true;
        $metodocal->texto = $calculo;

        $medida = new stdClass();
        $medida->id = 'umi_mod';
        $medida->required = true;
        $medida->enDiv = true;
        $medida->maxlength = 100;
        $medida->width = '300px';
        $medida->height = '100px';
        $medida->disabled = true;
        $medida->texto = $umv . "-" . $unom;

        //***********************LINEA BASE***************************************//
        $macLb = new stdClass();
        $macLb->required = true;
        $macLb->id = 'lb';
        $macLb->enDiv = true;
        $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO_ANTERIOR;
        $macLb->maxlength = 4;
        $macLb->formato = 'entero';

        $macLbValor = new stdClass();
        $macLbValor->required = true;
        $macLbValor->id = 'lbValor';
        $macLbValor->enDiv = true;
        $macLbValor->value = $metaActividad->getlbValor();
        $macLbValor->formato = 'numero';

        $macLbIndicador = new stdClass();
        $macLbIndicador->required = true;
        $macLbIndicador->id = 'lbIndicador';
        $macLbIndicador->enDiv = true;
        $macLbIndicador->value = $metaActividad->getLbIndicador();
        $macLbIndicador->formato = 'numero';

        //***********************AÑO ANTERIOR***************************************//
        $macAnterior = new stdClass();
        $macAnterior->required = true;
        $macAnterior->id = 'anterior';
        $macAnterior->enDiv = true;
        $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO_ANTERIOR;
        $macAnterior->maxlength = 4;
        $macAnterior->formato = 'entero';

        $macAnteriorValor = new stdClass();
        $macAnteriorValor->required = true;
        $macAnteriorValor->id = 'anteriorValor';
        $macAnteriorValor->enDiv = true;
        $macAnteriorValor->value = !empty($metaActividad->getAnteriorvalor()) ? $metaActividad->getAnteriorvalor() : 0;
        $macAnteriorValor->formato = 'numero';

        $macOriginal = new stdClass();
        $macOriginal->required = true;
        $macOriginal->id = 'macm_original';
        $macOriginal->enDiv = true;
        $macOriginal->decimales = 4;
        $macOriginal->value = $historicoMeta->getOriginal();
        $macOriginal->formato = 'numero';

        $macAnteriorIndicador = new stdClass();
        $macAnteriorIndicador->required = true;
        $macAnteriorIndicador->id = 'anteriorIndicador';
        $macAnteriorIndicador->enDiv = true;
        $macAnteriorIndicador->value = $historicoMeta->getAnteriorindicador();
        $macAnteriorIndicador->formato = 'numero';
        $macAnteriorIndicador->decimales = 4;

        //****************************CALENDARIO 1 GENERAL*************************************//

        $trimestre1 = new stdClass();
        $trimestre1->id = 'ori_trimestre1';
        $trimestre1->enDiv = true;
        $trimestre1->label = '1° Trimestre';
        $trimestre1->value = $metaActividad->getOriTrimestre(1);
        $trimestre1->formato = 'numero';
        $trimestre1->positivo = true;

        $trimestre2 = new stdClass();
        $trimestre2->required = true;
        $trimestre2->id = 'ori_trimestre2';
        $trimestre2->enDiv = true;
        $trimestre2->label = '2° Trimestre';
        $trimestre2->value = $metaActividad->getOriTrimestre(2);
        $trimestre2->formato = 'numero';
        $trimestre2->positivo = true;

        $trimestre3 = new stdClass();
        $trimestre3->required = true;
        $trimestre3->id = 'ori_trimestre3';
        $trimestre3->enDiv = true;
        $trimestre3->label = '3° Trimestre';
        $trimestre3->value = $metaActividad->getOriTrimestre(3);
        $trimestre3->formato = 'numero';
        $trimestre3->positivo = true;

        $trimestre4 = new stdClass();
        $trimestre4->required = true;
        $trimestre4->id = 'ori_trimestre4';
        $trimestre4->enDiv = true;
        $trimestre4->label = '4° Trimestre';
        $trimestre4->value = $metaActividad->getOriTrimestre(4);
        $trimestre4->formato = 'numero';
        $trimestre4->positivo = true;

        $total = new stdClass();
        $total->disabled = true;
        $total->id = 'ori_total';
        $total->enDiv = true;
        $total->label = 'Total';
        $total->decimales = 4;

        //****************************CALENDARIO 1*************************************//

        $trimestre1Cal1 = new stdClass();
        $trimestre1Cal1->id = 'numA_trimestre1';
        $trimestre1Cal1->enDiv = true;
        $trimestre1Cal1->label = '1° Trimestre';
        $trimestre1Cal1->formato = 'numero';
        $trimestre1Cal1->positivo = true;

        $trimestre2Cal1 = new stdClass();
        $trimestre2Cal1->required = true;
        $trimestre2Cal1->id = 'numA_trimestre2';
        $trimestre2Cal1->enDiv = true;
        $trimestre2Cal1->label = '2° Trimestre';
        $trimestre2Cal1->formato = 'numero';
        $trimestre2Cal1->positivo = true;

        $trimestre3Cal1 = new stdClass();
        $trimestre3Cal1->required = true;
        $trimestre3Cal1->id = 'numA_trimestre3';
        $trimestre3Cal1->enDiv = true;
        $trimestre3Cal1->label = '3° Trimestre';
        $trimestre3Cal1->formato = 'numero';
        $trimestre3Cal1->positivo = true;

        $trimestre4Cal1 = new stdClass();
        $trimestre4Cal1->required = true;
        $trimestre4Cal1->id = 'numA_trimestre4';
        $trimestre4Cal1->enDiv = true;
        $trimestre4Cal1->label = '4° Trimestre';
        $trimestre4Cal1->formato = 'numero';
        $trimestre4Cal1->positivo = true;

        $totalCal1 = new stdClass();
        $totalCal1->disabled = true;
        $totalCal1->id = 'numA_total';
        $totalCal1->enDiv = true;
        $totalCal1->label = 'Total';
        $totalCal1->decimales = 4;

        //****************************CALENDARIO 2*************************************//
        $trimestre1Cal2 = new stdClass();
        $trimestre1Cal2->id = 'denA_trimestre1';
        $trimestre1Cal2->enDiv = true;
        $trimestre1Cal2->label = '1° Trimestre';
        $trimestre1Cal2->formato = 'numero';
        $trimestre1Cal2->positivo = true;

        $trimestre2Cal2 = new stdClass();
        $trimestre2Cal2->required = true;
        $trimestre2Cal2->id = 'denA_trimestre2';
        $trimestre2Cal2->enDiv = true;
        $trimestre2Cal2->label = '2° Trimestre';
        $trimestre2Cal2->formato = 'numero';
        $trimestre2Cal2->positivo = true;

        $trimestre3Cal2 = new stdClass();
        $trimestre3Cal2->required = true;
        $trimestre3Cal2->id = 'denA_trimestre3';
        $trimestre3Cal2->enDiv = true;
        $trimestre3Cal2->label = '3° Trimestre';
        $trimestre3Cal2->formato = 'numero';
        $trimestre3Cal2->positivo = true;

        $trimestre4Cal2 = new stdClass();
        $trimestre4Cal2->required = true;
        $trimestre4Cal2->id = 'denA_trimestre4';
        $trimestre4Cal2->enDiv = true;
        $trimestre4Cal2->label = '4° Trimestre';
        $trimestre4Cal2->formato = 'numero';
        $trimestre4Cal2->positivo = true;

        $totalCal2 = new stdClass();
        $totalCal2->disabled = true;
        $totalCal2->id = 'denA_total';
        $totalCal2->enDiv = true;
        $totalCal2->label = 'Total';
        $totalCal2->formato = 'numero';
        $totalCal2->decimales = 4;

        //********************************LINEA BASE Numerador Denominador****************************************************//

        $macNumerador = new stdClass();
        $macNumerador->required = true;
        $macNumerador->id = 'numerador';
        $macNumerador->enDiv = true;
        $macNumerador->label = 'Numerador';
        $macNumerador->formato = 'numero';

        $macDenominador = new stdClass();
        $macDenominador->required = true;
        $macDenominador->id = 'denominador';
        $macDenominador->enDiv = true;
        $macDenominador->label = 'Denominador';
        $macDenominador->formato = 'numero';

        //********************************VALOR DESEADO Y PROGRAMADO (RAZON Y TASA) *********************//

        $macDeseadoNumerador = new stdClass();
        $macDeseadoNumerador->required = true;
        $macDeseadoNumerador->id = 'deseadoNumerador';
        $macDeseadoNumerador->enDiv = true;
        $macDeseadoNumerador->label = 'Numerador';
        $macDeseadoNumerador->formato = 'numero';
        $macDeseadoNumerador->disabled = true;

        $macDeseadoDenominador = new stdClass();
        $macDeseadoDenominador->required = true;
        $macDeseadoDenominador->id = 'deseadoDenominador';
        $macDeseadoDenominador->enDiv = true;
        $macDeseadoDenominador->label = 'Denominador';
        $macDeseadoDenominador->formato = 'numero';
        $macDeseadoDenominador->disabled = true;

        //********************************RESULTADO PARCIAL *********************//
        $resParcialT1 = new stdClass();
        $resParcialT1->id = 'rpar1_trimestre1';
        $resParcialT1->enDiv = true;
        $resParcialT1->label = '1° Trimestre';
        $resParcialT1->disabled = true;
        $resParcialT1->decimales = 4;

        $resParcialT2 = new stdClass();
        $resParcialT2->id = 'rpar2_trimestre2';
        $resParcialT2->enDiv = true;
        $resParcialT2->label = '2° Trimestre';
        $resParcialT2->disabled = true;
        $resParcialT2->decimales = 4;

        $resParcialT3 = new stdClass();
        $resParcialT3->id = 'rpar3_trimestre3';
        $resParcialT3->enDiv = true;
        $resParcialT3->label = '3° Trimestre';
        $resParcialT3->disabled = true;
        $resParcialT3->decimales = 4;

        $resParcialT4 = new stdClass();
        $resParcialT4->id = 'rpar4_trimestre4';
        $resParcialT4->enDiv = true;
        $resParcialT4->label = '4° Trimestre';
        $resParcialT4->disabled = true;
        $resParcialT4->decimales = 4;

        $resParcialTo = new stdClass();
        $resParcialTo->id = 'totalParcialA';
        $resParcialTo->enDiv = true;
        $resParcialTo->label = 'Total';
        $resParcialTo->disabled = true;
        $resParcialTo->decimales = 4;
        //********************************FIN RESULTADO PARCIAL *********************//

        $macT0 = new stdClass();
        $macT0->required = true;
        $macT0->id = 't0';
        $macT0->enDiv = true;
        $macT0->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO_ANTERIOR;
        $macT0->maxlength = 4;
        $macT0->formato = 'entero';
        $macT0->label = 'AÑO T0';
        $macT0->disabled = true;

        $macT0Valor = new stdClass();
        $macT0Valor->required = true;
        $macT0Valor->id = 't0Valor';
        $macT0Valor->enDiv = true;
        $macT0Valor->formato = 'numero';
        $macT0Valor->label = 'VALOR AÑO T0';
        $macT0Valor->disabled = true;

        $macT1 = new stdClass();
        $macT1->required = true;
        $macT1->id = 't1';
        $macT1->enDiv = true;
        $macT1->value = !empty($metaActividad->getAnio()) ? $metaActividad->getAnio() : APP_EJERCICIO_ANTERIOR;
        $macT1->maxlength = 4;
        $macT1->formato = 'entero';
        $macT1->label = 'AÑO T1';
        $macT1->disabled = true;

        $macT1Valor = new stdClass();
        $macT1Valor->required = true;
        $macT1Valor->id = 't1Valor';
        $macT1Valor->enDiv = true;
        $macT1Valor->formato = 'numero';
        $macT1Valor->label = 'VALOR AÑO T1';
        $macT1Valor->disabled = true;

        //*********************************************************************************************//
        //semestral
        if ($historicoMeta->getOriginal() == 0) {
            $trimestre1->value = $historicoMeta->getModificadoT1();
            $trimestre2->value = $historicoMeta->getModificadoT2();
            $trimestre3->value = $historicoMeta->getModificadoT3();
            $trimestre4->value = $historicoMeta->getModificadoT4();
        }

        $trimestre1Cal1->value = $historicoMeta->getNumeradorT1();
        $trimestre2Cal1->value = $historicoMeta->getNumeradorT2();
        $trimestre3Cal1->value = $historicoMeta->getNumeradorT3();
        $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

        $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
        $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
        $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
        $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();

        if ($datos["tiv_id"] == 1) {
            $macDeseadoNumerador->value = number_format($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value, 4);
            $macDeseadoDenominador->value = number_format($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value, 4);
        } else if ($datos["tiv_id"] == 2) {
            $macDeseadoNumerador->value = $trimestre4Cal1->value;
            $macDeseadoDenominador->value = $trimestre4Cal2->value;
        }

        $macT1Valor->value = number_format($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value, 4);
        $lb = '';
        $lbValor = '';
        $lbIndicador = '';

        $anterior = '';
        $anteriorValor = '';
        $anteriorIndicador = '';

        $original = '';

        $numerador = '';
        $denominador = '';
        $deseadoNumerador = '';
        $deseadoDenominador = '';
        $t0 = '';
        $t0Valor = '';
        $t1 = '';
        $t1Valor = '';

        $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
        $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';
        $tCal2 = $resParcial1 = $resParcial2 = $resParcial3 = $resParcial4 = $totalParcial = '';
        $trim1 = $trim2 = $trim3 = $trim4 = '';
        $legendCal1 = $legendCal2 = $legendCalT = '';

        switch ($umi) {
            case 2:
            case 3:
            case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                if ($trimestre1Cal1->value != NULL && $trimestre2Cal1->value != NULL && $trimestre3Cal1->value != NULL && $trimestre4Cal1->value != NULL && $trimestre1Cal2->value != NULL &&
                    $trimestre2Cal2->value != NULL && $trimestre3Cal2->value != NULL && $trimestre4Cal2->value != NULL) {

                    if ($this->estadoMir == 10) {
                        # code...
                        $macLb->disabled = true;
                        $macNumerador->disabled = true;
                        $macDenominador->disabled = true;
                    }

                    if ($umi == 2) {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format((($trimestre1Cal1->value / $trimestre1Cal2->value) * 100), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format((($trimestre2Cal1->value / $trimestre2Cal2->value) * 100), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format((($trimestre3Cal1->value / $trimestre3Cal2->value) * 100), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format((($trimestre4Cal1->value / $trimestre4Cal2->value) * 100), 4) : '0');
                    } else {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format(($trimestre1Cal1->value / $trimestre1Cal2->value), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format(($trimestre2Cal1->value / $trimestre2Cal2->value), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format(($trimestre3Cal1->value / $trimestre3Cal2->value), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format(($trimestre4Cal1->value / $trimestre4Cal2->value), 4) : '0');

                    }

                    if ($datos["tiv_id"] == 1) {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($macOriginal->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($macOriginal->value), 4);
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        }
                    } else {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4);
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        }
                        
                    }
                } else {
                    if ($umi == 2) {
                        $resParcialT1->value = "0%";
                        $resParcialT2->value = "0%";
                        $resParcialT3->value = "0%";
                        $resParcialT4->value = "0%";
                        $resParcialTo->value = "0%";
                        $totalCal1->value = "0";
                        $totalCal2->value = "0";
                    } else {
                        $resParcialT1->value = "0";
                        $resParcialT2->value = "0";
                        $resParcialT3->value = "0";
                        $resParcialT4->value = "0";
                        $resParcialTo->value = "0";
                        $totalCal1->value = "0";
                        $totalCal2->value = "0";
                    }
                }
                $legendp = 'LÍNEA BASE';
                $legend = 'VALOR DESEADO';
                $legendCal1 = '
                    <tr>
                        <td colspan=5><legend><small>Numerador</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=5><legend><small>Denominador</small></legend></td>
                    </tr>
                ';
                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';
                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO_ANTERIOR;

                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor LB';
                $macLbIndicador->disabled = true;
                $macOriginal->label = APP_EJERCICIO;
                $macOriginal->disabled = true;

                $macNumerador->value = $historicoMeta->getNumerador();
                $macDenominador->value = $historicoMeta->getDenominador();
                $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();

                $lb = '<td>' . $this->ponInput($macLb) . '</td>';
                $numerador = '<td>' . $this->ponInput($macNumerador) . '</td>';
                $denominador = '<td>' . $this->ponInput($macDenominador) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($macLbIndicador) . '</td>';

                $deseadoNumerador = '<td>' . $this->ponInput($macDeseadoNumerador) . '</td>';
                $deseadoDenominador = '<td>' . $this->ponInput($macDeseadoDenominador) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';

                break;
            case 5: // TASA

                if ($trimestre1Cal1->value != NULL && $trimestre2Cal1->value != NULL && $trimestre3Cal1->value != NULL && $trimestre4Cal1->value != NULL && $trimestre1Cal2->value != NULL &&
                    $trimestre2Cal2->value != NULL && $trimestre3Cal2->value != NULL && $trimestre4Cal2->value != NULL) {

                    $resParcialT1->value = ($trimestre1Cal2->value != 0 ? number_format(((($trimestre1Cal1->value / $trimestre1Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT2->value = ($trimestre2Cal2->value != 0 ? number_format(((($trimestre2Cal1->value / $trimestre2Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT3->value = ($trimestre3Cal2->value != 0 ? number_format(((($trimestre3Cal1->value / $trimestre3Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT4->value = ($trimestre4Cal2->value != 0 ? number_format(((($trimestre4Cal1->value / $trimestre4Cal2->value) - 1) * 100), 4) : '0');

                    if ($datos["tiv_id"] == 1) {
                        $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                        $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        $resParcialTo->value = (($totalCal2->value != 0) ? number_format(((($totalCal1->value / $totalCal2->value) - 1) * 100), 4) . '%' : '0%');
                        $macT0Valor->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                    } else {
                        $resParcialTo->value = $resParcialT4->value . '%';
                        $totalCal1->value = (number_format(($trimestre4Cal1->value), 4));
                        $totalCal2->value = (number_format(($trimestre4Cal2->value), 4));
                        $macT0Valor->value = (number_format(($trimestre4Cal2->value), 4));
                    }
                    $resParcialT1->value .= "%";
                    $resParcialT2->value .= "%";
                    $resParcialT3->value .= "%";
                    $resParcialT4->value .= "%";
                } else {
                    $resParcialT1->value = "0%";
                    $resParcialT2->value = "0%";
                    $resParcialT3->value = "0%";
                    $resParcialT4->value = "0%";
                    $resParcialTo->value = "0%";
                    $totalCal1->value = "0";
                    $totalCal2->value = "0";
                }
                $legendp = 'LÍNEA BASE';
                $legend = 'VALORES PROGRAMADOS';
                $legendCal1 = '
                    <tr>
                        <td colspan=5><legend><small>T1</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=5><legend><small>T0</small></legend></td>
                    </tr>
                ';
                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';

                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO_ANTERIOR;
                $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO_ANTERIOR;

                $macLb->label = 'AÑO T-1';
                $macLbValor->label = 'VALOR AÑO T-1';
                $macAnterior->label = 'AÑO T0';
                $macAnteriorValor->label = 'VALOR AÑO T0';
                $macLbIndicador->label = 'VALOR INDICADOR';
                $macAnteriorIndicador->label = 'VALOR INDICADOR';
                $macOriginal->label = 'VALOR AÑO T1';

                $macLbIndicador->disabled = true;
                $macOriginal->disabled = true;
                $macAnteriorIndicador->disabled = true;

                $anterior = '<td>' . $this->ponInput($macAnterior) . '</td>';
                $anteriorValor = '<td>' . $this->ponInput($macAnteriorValor) . '</td>';
                $anteriorIndicador = '<td>' . $this->ponInput($macAnteriorIndicador) . '</td>';

                $lb = '<td>' . $this->ponInput($macLb) . '</td>';
                $lbValor = '<td>' . $this->ponInput($macLbValor) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($macLbIndicador) . '</td>';

                $t0 = '<td>' . $this->ponInput($macT0) . '</td>';
                $t0Valor = '<td>' . $this->ponInput($macT0Valor) . '</td>';
                $t1 = '<td>' . $this->ponInput($macT1) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';
                break;
            default:
                if ($trimestre1->value != NULL && $trimestre2->value != NULL && $trimestre3->value != NULL && $trimestre4->value != NULL) {
                    $resParcialT1->value = number_format(($trimestre1->value), 4);
                    $resParcialT2->value = number_format(($trimestre2->value), 4);
                    $resParcialT3->value = number_format(($trimestre3->value), 4);
                    $resParcialT4->value = number_format(($trimestre4->value), 4);

                    if ($datos["tiv_id"] == 1) {
                        $totalCal1->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                    } else {
                        $totalCal1->value = number_format(($resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT4->value), 4);
                    }
                    $resParcialT1->value .= "%";
                    $resParcialT2->value .= "%";
                    $resParcialT3->value .= "%";
                    $resParcialT4->value .= "%";
                } else {
                    $resParcialT1->value = "0%";
                    $resParcialT2->value = "0%";
                    $resParcialT3->value = "0%";
                    $resParcialT4->value = "0%";
                    $resParcialTo->value = "0%";
                    $total->value = "0%";
                }
                $eanio = 'Año';
                $legendp = 'Línea Base';
                $legend = 'Valor Programado Anual';
                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';
                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor';

                $macOriginal->disabled = true;

                $lb = '<td>' . $this->ponInput($macLb) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($macLbIndicador) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($total) . '</td>';

                break;
        }

        $form = new stdClass();
        $form->id = 'metaInd';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMetaActividadMir';
        $form->datosExtra = new stdClass();
        $form->datosExtra->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $form->datosExtra->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $form->datosExtra->eri_id = $this->prm->eri_id;
        $form->datosExtra->epg_id = (isset($this->prm->epg_id) ? $this->prm->epg_id : '');
        $form->datosExtra->macm_id = (isset($this->prm->macm_id) ? $this->prm->macm_id : '');
        $form->datosExtra->abc = $abc;
        $form->datosExtra->nim_id = $this->prm->nim_id;
        $form->datosExtra->ind_id = $this->prm->ind_id;
        $form->datosExtra->pii_id = $this->prm->pii_id;
        $form->datosExtra->epfi_id = $this->prm->eri_id;
        $form->datosExtra->gpo_id = $this->prm->gpo_id;
        $form->datosExtra->ur_id = $this->prm->ur_id;
        $form->datosExtra->prgs_id = $_SESSION[_S]['clasAdm-prg'];
        $form->datosExtra->pdf_id = $this->prm->pdf_id;
        $form->datosExtra->lectura = (string) $this->lectura;
        $form->datosExtra->tipoCalendario = $tiv;
        $form->datosExtra->unidadMedida = $umi;
        $form->datosExtra->descripcion = $nom_MIR;
        $form->datosExtra->anio = (string) APP_EJERCICIO;
        $form->datosExtra->umi_id = $umi;
        if (isset($this->prm->pdi_id) && isset($this->prm->com_id)) {
            # code...
            $form->datosExtra->pdi_id = $this->prm->pdi_id;
            $form->datosExtra->com_id = $this->prm->com_id;
        }

        switch ($umi) {
            case 2:
            case 3:
            case 4: // RAZÓN
                $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                $form->datosExtra->original = '"{$("#macm_original").val()}"';
                break;
            case 5: // TASA
                $form->datosExtra->lbValor = '"{$("#lbValor").val()}"';
                $form->datosExtra->original = '"{$("#macm_original").val()}"';
                $form->datosExtra->anteriorValor = '"{$("#anteriorValor").val()}"';
                $form->datosExtra->anteriorIndicador = '"{$("#anteriorIndicador").val()}"';
                $form->datosExtra->lbIndicador = '"{$("#lbIndicador").val()}"';
                break;
            default:
                $form->datosExtra->lbValor = '"{$("#lbIndicador").val()}"';
                $form->datosExtra->original = '"{$("#macm_original").val()}"';
                break;
        }

        $form = new form($form);

        $wr = new WebResponse();

        $form->mixHtml('
            <fieldset class="col-md-12 col-xs-12">
                <legend>NOMBRE DEL INDICADOR</legend>
                ' . $this->ponTextArea($descripcion) . '
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <label for="ind_nombre" class="col-sm-3 control-label">NOMBRE DE LA VARIABLE</label>
                ' . $this->ponTextArea($variable) . '
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <label for="ind_nombre" class="col-sm-3 control-label">MÉTODO DE CÁLCULO</label>
                ' . $this->ponTextArea($metodocal) . '
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <label for="ind_nombre" class="col-sm-3 control-label">UNIDAD DE MEDIDA</label>
                ' . $this->ponTextArea($medida) . '
            </fieldset>
            <fieldset class="col-md-8 col-xs-8">
                <legend><small>' . $legendp . '</small></legend>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            ' . $lb . $lbValor . $anterior . $anteriorValor . $numerador . $denominador . $lbIndicador . '
                        </tr>
                    </tbody>
                </table>
                <legend><small>' . $legend . '</small></legend>
                <table class="table table-bordered table-striped ">
                    <tbody>
                        <tr>
                            ' . $deseadoNumerador . $deseadoDenominador . $t0 . $t0Valor . $t1 . $original . $anteriorIndicador . '
                        </tr>
                    </tbody>
                </table>
            </fieldset>

            <table class="table table-bordered table-striped ">
                <tbody>
                    ' . $legendCal1 . '
                    <tr>
                        ' . $trim1Cal1 . $trim2Cal1 . $trim3Cal1 . $trim4Cal1 . $tCal1 . '
                    </tr>
                    ' . $legendCal2 . '
                    <tr>
                        ' . $trim1Cal2 . $trim2Cal2 . $trim3Cal2 . $trim4Cal2 . $tCal2 . '
                    </tr>
                    ' . $legendCalT . '
                    <tr>
                        ' . $resParcial1 . $resParcial2 . $resParcial3 . $resParcial4 . $totalParcial . '
                    </tr>
                </tbody>
            </table>
        ');
        if (isset($this->prm->modalDeTabla)) {
            $this->mixHtml($form->ponForm());
            if (($this->estadoMir == 1 || $this->estadoMir == 3 || ($this->urResponsable && $this->estadoMir == 5)) && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == $this->prm->ur && $this->activo || 
            ($this->activo && $this->estadoMir == 10 && 
            ($this->usu == 'LGARCIAC' || $this->usu == 'MDIONICIOM' || $this->usu == 'ALCRUZV' || $this->usu == 'ADIAZL' 
            ||  $this->usu == 'JHERNANDEZR' || $this->usu == 'NMARTINEZC'))) {
                $this->mixHtml('
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                    <button id="close" type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            ');
            }

            $this->mixScript('
                $("#close").click(function(){ $("#modalDeTablatbl-metasIndicador").remove(); });
            ');
        } else {
            $this->mixHtml('
                <div id="myModal" class="modal ">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Meta de Indicador ' . $this->prm->ind_id . ' de nivel Actividad</h4>
                                <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $form->ponForm() . '</div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                                    <button id="cerrar" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });
                $("#cerrar").click(function(){ $("#myModal").remove(); });
                $("#close").click(function(){ $("#myModal").hide(); });
                $("#myModal").modal().show();
            ');
        }
        $this->mixScript('
            var calculoLBIndicador=0;
            var calculoVPIndicador=0;

            switch(' . $umi . '){
                case 2:case 3:case 4:    // RAZÓN
                    $("#numerador,#denominador").blur(function() {
                        if($("#denominador").val() != 0){
                            if(' . $umi . ' == 2){ //porcentaje
                               calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) ) * 100;
                            }else{ // razón
                                calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) );
                            }
                            $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                        }else{
                            $("#lbIndicador").val(0);
                        }
                        if($("#lb").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#numerador").val() == 0 && $("#denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else if($("#lb").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#numerador").val() != 0 && $("#denominador").val() != 0) {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });

                    //calendario 1
                    $("#numA_trimestre1,#numA_trimestre2,#numA_trimestre3,#numA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $umi . ' == 2){
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = (numATrimestre1 / denATrimestre1)*100;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = (numATrimestre2 / denATrimestre2)*100;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = (numATrimestre3 / denATrimestre3)*100;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = (numATrimestre4 / denATrimestre4)*100;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        } else {
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = numATrimestre1 / denATrimestre1;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = numATrimestre2 / denATrimestre2;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = numATrimestre3 / denATrimestre3;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = numATrimestre4 / denATrimestre4;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        }

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#numA_total").val(numATotal.toFixed(4));
                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#deseadoNumerador").val(numATotal.toFixed(4));
                            if(' . $umi . ' == 2){
                                $("#macm_original").val(((numATotal / denATotal)*100).toFixed(4));
                                $("#totalParcialA").val(((numATotal / denATotal)*100).toFixed(4) + "%");
                            } else {
                                $("#macm_original").val((numATotal / denATotal).toFixed(4));
                                $("#totalParcialA").val((numATotal / denATotal).toFixed(4));
                            }
                        } else {
                            if(' . $umi . ' == 2){
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            } else {
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4));
                            }
                            $("#numA_total").val(numATrimestre4.toFixed(4));
                            $("#deseadoNumerador").val(numATotal.toFixed(4));
                            $("#macm_original").val(porc_trimestre4.toFixed(4));
                        }

                        if(' . $umi . ' == 2){
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                        } else {
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4));
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4));
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4));
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4));
                        }

                    });

                    //calendario 2
                    $("#denA_trimestre1,#denA_trimestre2,#denA_trimestre3,#denA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $umi . ' == 2){
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = (numATrimestre1 / denATrimestre1)*100;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = (numATrimestre2 / denATrimestre2)*100;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = (numATrimestre3 / denATrimestre3)*100;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = (numATrimestre4 / denATrimestre4)*100;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        } else {
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = numATrimestre1 / denATrimestre1;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = numATrimestre2 / denATrimestre2;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = numATrimestre3 / denATrimestre3;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = numATrimestre4 / denATrimestre4;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        }

                        if(' . $datos["tiv_id"] . ' == 1){

                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#deseadoDenominador").val(denATotal.toFixed(4));
                            if(' . $umi . ' == 2){
                                $("#macm_original").val(((numATotal / denATotal)*100).toFixed(4));
                                $("#totalParcialA").val(((numATotal / denATotal)*100).toFixed(4) + "%");
                            } else {
                                $("#macm_original").val((numATotal / denATotal).toFixed(4));
                                $("#totalParcialA").val((numATotal / denATotal).toFixed(4));
                            }
                        } else {
                            if(' . $umi . ' == 2){
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            } else {
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4));
                            }
                            $("#denA_total").val(denATrimestre4.toFixed(4));
                            $("#deseadoDenominador").val(denATotal.toFixed(4));
                            $("#macm_original").val(porc_trimestre4.toFixed(4));
                        }

                        if(' . $umi . ' == 2){
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                        } else {
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4));
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4));
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4));
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4));
                        }
                    });

                break;
                case 5: // TASA
                    $("#anteriorValor,#lbValor").blur(function(){
                        calculoLBIndicador=((unformatNumber($("#anteriorValor").val()) / unformatNumber($("#lbValor").val()))-1) * 100;
                        $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                    });

                    //calendario 1
                    $("#numA_trimestre1,#numA_trimestre2,#numA_trimestre3,#numA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        if($("#denA_trimestre1").val() != 0 ) {
                            porc_trimestre1 = (((numATrimestre1 / denATrimestre1)-1)*100);
                        } else {
                            porc_trimestre1 = 0;
                        }
                        if($("#denA_trimestre2").val() != 0 ) {
                            porc_trimestre2 = (((numATrimestre2 / denATrimestre2)-1)*100);
                        } else {
                            porc_trimestre2 = 0;
                        }
                        if($("#denA_trimestre3").val() != 0 ) {
                            porc_trimestre3 = (((numATrimestre3 / denATrimestre3)-1)*100);
                        } else {
                            porc_trimestre3 = 0;
                        }
                        if($("#denA_trimestre4").val() != 0 ) {
                            porc_trimestre4 = (((numATrimestre4 / denATrimestre4)-1)*100);
                        } else {
                            porc_trimestre4 = 0;
                        }

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#numA_total").val(numATotal.toFixed(4));
                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#totalParcialA").val((((numATotal / denATotal)-1)*100).toFixed(4) + "%");
                            $("#macm_original").val(numATotal.toFixed(4));
                            $("#anteriorIndicador").val((((numATotal / denATotal)-1)*100).toFixed(4));
                            $("#t0Valor").val(denATotal.toFixed(4));
                        } else {
                            $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            $("#numA_total").val(numATrimestre4.toFixed(4));
                            $("#denA_total").val(denATrimestre4.toFixed(4));
                            $("#macm_original").val(numATrimestre4.toFixed(4));
                            $("#anteriorIndicador").val(porc_trimestre4.toFixed(4));
                            $("#t0Valor").val(denATrimestre4.toFixed(4));
                        }

                        $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                        $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                        $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                        $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                    });


                    //calendario 2
                    $("#denA_trimestre1,#denA_trimestre2,#denA_trimestre3,#denA_trimestre4").blur(function(){

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        if($("#denA_trimestre1").val() != 0 ) {
                            porc_trimestre1 = (((numATrimestre1 / denATrimestre1)-1)*100);
                        } else {
                            porc_trimestre1 = 0;
                        }
                        if($("#denA_trimestre2").val() != 0 ) {
                            porc_trimestre2 = (((numATrimestre2 / denATrimestre2)-1)*100);
                        } else {
                            porc_trimestre2 = 0;
                        }
                        if($("#denA_trimestre3").val() != 0 ) {
                            porc_trimestre3 = (((numATrimestre3 / denATrimestre3)-1)*100);
                        } else {
                            porc_trimestre3 = 0;
                        }
                        if($("#denA_trimestre4").val() != 0 ) {
                            porc_trimestre4 = (((numATrimestre4 / denATrimestre4)-1)*100);
                        } else {
                            porc_trimestre4 = 0;
                        }

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#numA_total").val(numATotal.toFixed(4));
                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#totalParcialA").val((((numATotal / denATotal)-1)*100).toFixed(4) + "%");
                            $("#macm_original").val(numATotal.toFixed(4));
                            $("#anteriorIndicador").val((((numATotal / denATotal)-1)*100).toFixed(4));
                            $("#t0Valor").val(denATotal.toFixed(4));
                        } else {
                            $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            $("#numA_total").val(numATrimestre4.toFixed(4));
                            $("#denA_total").val(denATrimestre4.toFixed(4));
                            $("#macm_original").val(numATrimestre4.toFixed(4));
                            $("#anteriorIndicador").val(porc_trimestre4.toFixed(4));
                            $("#t0Valor").val(denATrimestre4.toFixed(4));
                        }
                        $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                        $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                        $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                        $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                    });

                break;
                default: // todas las demás unidades de medida del indicador

                    $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur(function() {

                        $("#rpar1_trimestre1").val($("#ori_trimestre1").val());
                        $("#rpar2_trimestre2").val($("#ori_trimestre2").val());
                        $("#rpar3_trimestre3").val($("#ori_trimestre3").val());
                        $("#rpar4_trimestre4").val($("#ori_trimestre4").val());

                        if(' . $datos["tiv_id"] . ' == 1){
                            $("#numA_total").val(formatNumber(unformatNumber($("#ori_trimestre1").val()) + unformatNumber($("#ori_trimestre2").val())+ unformatNumber($("#ori_trimestre3").val()) + unformatNumber($("#ori_trimestre4").val())));
                            $("#ori_total").val(formatNumber(unformatNumber($("#ori_trimestre1").val()) + unformatNumber($("#ori_trimestre2").val()) + unformatNumber($("#ori_trimestre3").val()) + unformatNumber($("#ori_trimestre4").val())));
                            $("#macm_original").val( formatNumber( unformatNumber( $("#ori_trimestre1").val() ) + unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre3").val() ) + unformatNumber( $("#ori_trimestre4").val() ) ) );
                        } else {
                            $("#ori_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#numA_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#macm_original").val(unformatNumber($("#ori_trimestre4").val()));
                        }
                    });

                    if("' . $abc . '" == "c"){
                        $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur();
                    }
                    break;
            }
                    $("#lb,#numerador,#denominador").blur(function(){
                        if($("#lb").val() < ' . APP_EJERCICIO_ANTERIOR . ') {
                            $("#numerador").attr("disabled",false);
                            $("#denominador").attr("disabled",false);
                            if(' . $umi . ' == 2){ //porcentaje
                               calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) ) * 100;
                            }else{ // razón
                                calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) );
                            }
                            $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                        } else if($("#lb").val() == ' . APP_EJERCICIO_ANTERIOR . ') {
                            $("#lb").val('.APP_EJERCICIO_ANTERIOR.');
                            $("#numerador").attr("disabled",true);
                            $("#denominador").attr("disabled",true);
                            $("#numerador").val(0);
                            $("#denominador").val(0);
                            $("#lbIndicador").val(0);
                        } else {
                            $("#lb").val('.APP_EJERCICIO_ANTERIOR.');
                            $("#numerador").attr("disabled",true);
                            $("#denominador").attr("disabled",true);
                            $("#numerador").val(0);
                            $("#denominador").val(0);
                            $("#lbIndicador").val(0);
                            alertify.alert("IMPORTANTE", "El año no puede ser mayor al actual", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        }
                    });

                    $("#saveMeta").click(function(){
                        if($("#lb").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#numerador").val() == 0 && $("#denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });
        ');
    }

    private function editaMetaActividad()
    {
        $parametros = array();
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        //Edita metas

        $editaMetaAnual = new sp_RegistroMetaActividad();
        $editaMetaAnual->setGpo_id($this->prm->gpo_id);
        $editaMetaAnual->setUr_id($this->prm->ur_id);
        $editaMetaAnual->setPii_id($this->prm->eri_id);
        $editaMetaAnual->setEpg_id($this->prm->epg_id);
        $editaMetaAnual->setAbc('U');
        $editaMetaAnual->setSis_id('POA');
        $editaMetaAnual->setBit_observacion('META EDITADA DESDE PANTALLA MIR INICIAL');
        $editaMetaAnual->setEjercicio(APP_EJERCICIO);
        $editaMetaAnual->conectar();
        $editaMetaAnual->setSql('
            SET
            @usr_ip = INET_ATON(?),
            @usr_id = ?,
            @bit_observacion = "META EDITADA DESDE PANTALLA MIR INICIAL",
            @mac_anio = ?,
            @mac_valor = ?,
            @desde_mir = ?;');

        $parametros = [
            $ip,
            $_SESSION[_S]['usr_id'],
            $this->prm->mac_anio,
            $this->prm->mac_valor,
            1,
        ];

        $editaMetaAnual->consultar($parametros);
        $editaMetaAnual->execute(false, false);
        $editaMetaAnual->desconectar();

        if ($editaMetaAnual->getBandera()) {
            $this->mixScript('
                notify("Meta Modificada exitosamente");
                $("#myModal,#modalDeTablatbl-metasIndicador").modal("hide");
            ');

            $this->llenaTablaMetas();
        } else {
            $this->mixScript('
                notify("' . $editaMetaAnual->getMensaje() . '", "error");
            ');
        }
    }

    public function abcMetaActividadMir()
    {
        $mensaje = '';
        if ($this->prm->abc != 'b') {
            switch ($this->prm->unidadMedida) {
                case 2:
                case 3:
                case 4: //RAZÓN
                    $this->prm->num_trimestre1 = $this->prm->numA_trimestre1;
                    $this->prm->num_trimestre2 = $this->prm->numA_trimestre2;
                    $this->prm->num_trimestre3 = $this->prm->numA_trimestre3;
                    $this->prm->num_trimestre4 = $this->prm->numA_trimestre4;

                    $this->prm->ori_trimestre1 = $this->prm->num_trimestre1;
                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre3 = $this->prm->num_trimestre3;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;

                    $this->prm->den_trimestre1 = $this->prm->denA_trimestre1;
                    $this->prm->den_trimestre2 = $this->prm->denA_trimestre2;
                    $this->prm->den_trimestre3 = $this->prm->denA_trimestre3;
                    $this->prm->den_trimestre4 = $this->prm->denA_trimestre4;
                    $this->prm->original = $this->prm->original;
                    break;
                case 5: //TASA
                    $this->prm->num_trimestre1 = $this->prm->numA_trimestre1;
                    $this->prm->num_trimestre2 = $this->prm->numA_trimestre2;
                    $this->prm->num_trimestre3 = $this->prm->numA_trimestre3;
                    $this->prm->num_trimestre4 = $this->prm->numA_trimestre4;
                    $this->prm->den_trimestre1 = $this->prm->denA_trimestre1;
                    $this->prm->den_trimestre2 = $this->prm->denA_trimestre2;
                    $this->prm->den_trimestre3 = $this->prm->denA_trimestre3;
                    $this->prm->den_trimestre4 = $this->prm->denA_trimestre4;
                    $this->prm->ori_trimestre1 = $this->prm->num_trimestre1;
                    $this->prm->ori_trimestre2 = $this->prm->num_trimestre2;
                    $this->prm->ori_trimestre3 = $this->prm->num_trimestre3;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;

                    $t1 = $this->prm->num_trimestre1 + $this->prm->num_trimestre2 + $this->prm->num_trimestre3 + $this->prm->num_trimestre4;
                    $t0 = $this->prm->den_trimestre1 + $this->prm->den_trimestre2 + $this->prm->den_trimestre3 + $this->prm->den_trimestre4;

                    $this->prm->lbIndicador = $this->prm->lbIndicador;

                    break;
                default:
                    $this->prm->original = $this->prm->original;
                    break;
            }
        }

        switch ($this->prm->abc) {
            case 'a': //agregar
                $mensaje = 'Meta Generada exitosamente';
                $this->prm->observacion = 'Meta Actividad registrada desde mir inicial';
                break;
            case 'c': //actualizar
                $mensaje = 'Meta Modificada exitosamente';
                $this->prm->observacion = 'Meta Actividad modificada desde mir inicial';
                break;
            case 'b': //eliminar
                $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
                $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
                $this->prm->pii_id = $this->prm->eri_id;
                $mensaje = 'Meta Eliminada exitosamente';
                $this->prm->observacion = 'Meta Actividad eliminada desde mir inicial';
                break;
        }

        $this->prm->origen = "Controlador MIR Inicial";
        $this->prm->trimestreCierre = (string) $this->trimestreCierre;
        $this->prm->tri_id = (string) $this->trimestreCierre;
        $this->prm->pdi_id = $this->prm->pdi_id;
        $this->prm->com_id = $this->prm->com_id;

        MetaActividadMir::abc($this->prm);

        $this->mixScript('
            notify("' . $mensaje . '");
            $("#myModal,#modalDeTablatbl-metasIndicador").modal().remove();
        ');

        $this->llenaTablaMetas();
    }

    public function obtenerTrimestre($trimestre, $epg_id, $pii_id, $campo = 'mact_original')
    {
        $parametros = array();
        $this->conectar();
        $this->setSql('
            SELECT SUM(' . $campo . ') AS ' . $campo . ' FROM cat_MetaActividadTrimestre
            INNER JOIN enc_Programatico USING(epg_id)
            WHERE pii_id = ?
            AND tri_id = ?
            AND gpo_id= ? AND ur_id= ?;
        ');
        $parametros = [
            $pii_id,
            $trimestre,
            substr($this->prm->ur, 0, 1),
            substr($this->prm->ur, 1, 2),
        ];
        $this->consultar($parametros);
        $datos = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            return $datos[$campo];
        } else {
            return null;
        }
    }

    private function guardarOrden()
    {
        $parametros = array();
        if (isset($this->prm->pdi_id)) {
            $this->setSql('
                UPDATE vin_ProductoIntermedioIndicador SET pii_orden = ' . $this->prm->orden . '
                WHERE pdf_id = ? and pdi_id= ?;
            ');
            $parametros = [
                $this->prm->pdf_id,
                $this->prm->pdi_id,
            ];
            $this->conectar();
            $this->consultar($parametros);
            $this->desconectar();
        } else {
            $this->setSql('
                UPDATE enc_ProductoFinalIndicador SET epfi_orden = ' . $this->prm->orden . '
                WHERE pdf_id = ?;
            ');
            $parametros = [
                $this->prm->pdf_id
            ];
            $this->conectar();
            $this->consultar($parametros);
            $this->desconectar();
        }
    }

    private function addModMetaProposito()
    {
        $parametros = array();

        if (!isset($this->prm->modalDeTabla)) {
            $this->setContenedor('divContenedorGeneralModal');
        }

        $this->conectar();
        $this->setSql(
            'SELECT IF(tiv_id=1,"S","C") tiv_id, ind_nombre, ind_definicion, ind_nombreMIR,
            IF(nim_id=3,2,frm_id) AS frm_id, umi_id, nim_id, ind_definicionMIR
            FROM cat_Indicador WHERE nim_id = ?
            AND ind_id = ?;'
        );
        $parametros = [
            2,
            $this->prm->ind_id,
        ];
        $this->consultar($parametros);
        $tiv_id = $this->extraer();
        $tiv = $tiv_id['tiv_id'];

        $nom_indMIR = $tiv_id['ind_nombreMIR'];
        $umi = $tiv_id['umi_id'];
        $this->desconectar();
        $this->prm->pmi_id = $this->prm->eri_id;
        $this->prm->mpr_id = (isset($this->prm->mem_id) ? $this->prm->mem_id : '');
        $this->prm->tri_id = $this->trimestreCierre;

        $meta = new MetaProposito($this->prm);

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 500;
        $descripcion->width = '300px';
        $descripcion->height = '100px';
        $descripcion->disabled = true;
        $descripcion->texto = $nom_indMIR;

        //***********************LINEA BASE***************************************//
        $mlbAnio = new stdClass();
        $mlbAnio->required = true;
        $mlbAnio->id = 'mlb_anioT1';
        $mlbAnio->enDiv = true;
        $mlbAnio->maxlength = 4;
        $mlbAnio->formato = 'entero';

        $mlbValor = new stdClass();
        $mlbValor->required = true;
        $mlbValor->id = 'mlb_valorT1';
        $mlbValor->enDiv = true;
        $mlbValor->value = $meta->getLbValor();
        $mlbValor->decimales = '4';
        $mlbValor->formato = 'numero';

        $mlbAnioT0 = new stdClass();
        $mlbAnioT0->required = true;
        $mlbAnioT0->id = 'mlb_anioT0';
        $mlbAnioT0->enDiv = true;
        $mlbAnioT0->value = (isset($this->prm->mem_id)) ? $meta->getAnterior() : APP_EJERCICIO_ANTERIOR;
        $mlbAnioT0->maxlength = 4;
        $mlbAnioT0->formato = 'entero';

        $mlbValorT0 = new stdClass();
        $mlbValorT0->required = true;
        $mlbValorT0->id = 'mlb_valorT0';
        $mlbValorT0->enDiv = true;
        $mlbValorT0->value = $meta->getAnteriorValor();
        $mlbValorT0->formato = 'numero';

        $mlbNumeradorP = new stdClass();
        $mlbNumeradorP->id = 'numeradorp';
        $mlbNumeradorP->label = 'Numerador';
        $mlbNumeradorP->enDiv = true;
        $mlbNumeradorP->decimales = '4';
        $mlbNumeradorP->formato = 'numero';
        
        $mlbDenominadorP = new stdClass();
        $mlbDenominadorP->id = 'denominadorp';
        $mlbDenominadorP->label = 'Denominador';
        $mlbDenominadorP->enDiv = true;
        $mlbDenominadorP->decimales = '4';
        $mlbDenominadorP->formato = 'numero';

        $mlbValorInd = new stdClass();
        $mlbValorInd->required = true;
        $mlbValorInd->id = 'mlb_valorIndicador';
        $mlbValorInd->enDiv = true;
        $mlbValorInd->value = ($meta->getLbIndicador() != "") ? $meta->getLbIndicador() : 0;
        $mlbValorInd->formato = 'numero';

        //***********************VALORES PROGRAMADOS***************************************//
        $mvpAnioT0 = new stdClass();
        $mvpAnioT0->required = true;
        $mvpAnioT0->id = 'mvp_anioT0';
        $mvpAnioT0->enDiv = true;
        $mvpAnioT0->value = $meta->getAnterior();
        $mvpAnioT0->maxlength = 4;
        $mvpAnioT0->formato = 'entero';

        $mvpValorT0 = new stdClass();
        $mvpValorT0->required = true;
        $mvpValorT0->id = 'mvp_valorT0';
        $mvpValorT0->enDiv = true;
        $mvpValorT0->value = $meta->getDenTrimestre4();
        $mvpValorT0->formato = 'numero';

        $mvpAnioT1 = new stdClass();
        $mvpAnioT1->required = true;
        $mvpAnioT1->id = 'mvp_anioT1';
        $mvpAnioT1->enDiv = true;
        $mvpAnioT1->value = (isset($this->prm->mem_id)) ? $meta->getAnio() : APP_EJERCICIO_ANTERIOR;
        $mvpAnioT1->maxlength = 4;
        $mvpAnioT1->formato = 'entero';

        $mvpValorT1 = new stdClass();
        $mvpValorT1->required = true;
        $mvpValorT1->id = 'mvp_valorT1';
        $mvpValorT1->enDiv = true;
        $mvpValorT1->value = $meta->getNumTrimestre4();
        $mvpValorT1->formato = 'numero';

        $mvpValorInd = new stdClass();
        $mvpValorInd->required = true;
        $mvpValorInd->id = 'mvp_valorIndicador';
        $mvpValorInd->enDiv = true;
        $mvpValorInd->value = $meta->getAnteriorIndicador();
        $mvpValorInd->formato = 'numero';

        //****************************CALENDARIO 1 GENERAL*************************************//

        $trimestre1 = new stdClass();
        $trimestre1->id = 'ori_trimestre1';
        $trimestre1->enDiv = true;
        $trimestre1->label = '1° Trimestre';
        $trimestre1->formato = 'numero';
        $trimestre1->positivo = true;

        $trimestre2 = new stdClass();
        $trimestre2->required = true;
        $trimestre2->id = 'ori_trimestre2';
        $trimestre2->enDiv = true;
        $trimestre2->label = '2° Trimestre';
        $trimestre2->formato = 'numero';
        $trimestre2->positivo = true;

        $trimestre3 = new stdClass();
        $trimestre3->required = true;
        $trimestre3->id = 'ori_trimestre3';
        $trimestre3->enDiv = true;
        $trimestre3->label = '3° Trimestre';
        $trimestre3->formato = 'numero';
        $trimestre3->positivo = true;

        $trimestre4 = new stdClass();
        $trimestre4->required = true;
        $trimestre4->id = 'ori_trimestre4';
        $trimestre4->enDiv = true;
        $trimestre4->label = '4° Trimestre';
        $trimestre4->decimales = '4';
        $trimestre4->formato = 'numero';

        //****************************CALENDARIO 1*************************************//

        $trimestre1Cal1 = new stdClass();
        $trimestre1Cal1->id = 'num_trimestre1';
        $trimestre1Cal1->enDiv = true;
        $trimestre1Cal1->label = '1° Trimestre';
        $trimestre1Cal1->formato = 'numero';

        $trimestre2Cal1 = new stdClass();
        $trimestre2Cal1->required = true;
        $trimestre2Cal1->id = 'num_trimestre2';
        $trimestre2Cal1->enDiv = true;
        $trimestre2Cal1->label = '2° Trimestre';
        $trimestre2Cal1->formato = 'numero';

        $trimestre3Cal1 = new stdClass();
        $trimestre3Cal1->required = true;
        $trimestre3Cal1->id = 'num_trimestre3';
        $trimestre3Cal1->enDiv = true;
        $trimestre3Cal1->label = '3° Trimestre';
        $trimestre3Cal1->formato = 'numero';

        $trimestre4Cal1 = new stdClass();
        $trimestre4Cal1->required = true;
        $trimestre4Cal1->id = 'num_trimestre4';
        $trimestre4Cal1->enDiv = true;
        $trimestre4Cal1->label = '4° Trimestre';
        $trimestre4Cal1->decimales = '4';
        $trimestre4Cal1->formato = 'numero';

        //****************************CALENDARIO 2*************************************//
        $trimestre1Cal2 = new stdClass();
        $trimestre1Cal2->id = 'den_trimestre1';
        $trimestre1Cal2->enDiv = true;
        $trimestre1Cal2->label = '1° Trimestre';
        $trimestre1Cal2->formato = 'numero';

        $trimestre2Cal2 = new stdClass();
        $trimestre2Cal2->required = true;
        $trimestre2Cal2->id = 'den_trimestre2';
        $trimestre2Cal2->enDiv = true;
        $trimestre2Cal2->label = '2° Trimestre';
        $trimestre2Cal2->formato = 'numero';

        $trimestre3Cal2 = new stdClass();
        $trimestre3Cal2->required = true;
        $trimestre3Cal2->id = 'den_trimestre3';
        $trimestre3Cal2->enDiv = true;
        $trimestre3Cal2->label = '3° Trimestre';
        $trimestre3Cal2->formato = 'numero';

        $trimestre4Cal2 = new stdClass();
        $trimestre4Cal2->required = true;
        $trimestre4Cal2->id = 'den_trimestre4';
        $trimestre4Cal2->enDiv = true;
        $trimestre4Cal2->label = '4° Trimestre';
        $trimestre4Cal2->decimales = '4';
        $trimestre4Cal2->formato = 'numero';

        //********************************LINEA BASE Numerador Denominador****************************************************//

        $mlbNumerador = new stdClass();
        $mlbNumerador->required = true;
        $mlbNumerador->id = 'mlb_numerador';
        $mlbNumerador->enDiv = true;
        $mlbNumerador->label = 'Numerador';
        $mlbNumerador->decimales = '4';
        $mlbNumerador->formato = 'numero';

        $mlbDenominador = new stdClass();
        $mlbDenominador->required = true;
        $mlbDenominador->id = 'mlb_denominador';
        $mlbDenominador->enDiv = true;
        $mlbDenominador->label = 'Denominador';
        $mlbDenominador->decimales = '4';
        $mlbDenominador->formato = 'numero';

        //*********************************************************************************************//

        $v2020 = new stdClass();
        $v2020->required = true;
        $v2020->id = 'original';
        $v2020->enDiv = true;
        $v2020->maxlength = 18;
        $v2020->readonly = true;
        $v2020->value = $meta->getOriginal();
        $v2020->formato = 'numero';

        $trimestre1->disabled = true;
        $trimestre2->disabled = true;
        $trimestre3->disabled = true;
        $trimestre4->value = $meta->getOriTrimestre4();

        $trimestre1Cal1->disabled = true;
        $trimestre2Cal1->disabled = true;
        $trimestre3Cal1->disabled = true;
        $trimestre4Cal1->value = $meta->getNumTrimestre4();

        $trimestre1Cal2->disabled = true;
        $trimestre2Cal2->disabled = true;
        $trimestre3Cal2->disabled = true;
        $trimestre4Cal2->value = $meta->getDenTrimestre4();

        $lbAnioT1 = '';
        $lbValorT1 = '';
        $lbAnioT0 = '';
        $lbValorT0 = '';
        $lbValorIndicador = '';

        $vpAnioT1 = '';
        $vpValorT1 = '';
        $vpAnioT0 = '';
        $vpValorT0 = '';
        $vpValorIndicador = '';

        $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
        $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';
        $legendCal1 = $legendCal2 = '';
        switch ($umi) {
            case 2:
            case 3:
            case 4: //PORCENTAJE Y RAZÓN
                $defnomInd = 'NOMBRE DEL INDICADOR';
                $legendp = 'LÍNEA BASE';
                $legend = 'VALOR DESEADO';
                $legendCal1 = '
                    <tr>
                        <td colspan=4><legend><small>Numerador</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=4><legend><small>Denominador</small></legend></td>
                    </tr>
                ';
                $mlbAnio->value = (isset($this->prm->mem_id)) ? $meta->getLb() : APP_EJERCICIO_ANTERIOR;

                $mlbAnio->label = 'Año';
                $mlbValorInd->label = 'Valor LB';

                $mlbValorT0->disabled = true;
                $mlbValorInd->disabled = true;

                $mlbNumerador->value = $meta->getNumerador();
                $mlbDenominador->value = $meta->getDenominador();
                $mlbNumeradorP->value = $meta->getNumTrimestre4();
                $mlbDenominadorP->value = $meta->getDenTrimestre4();

                $lbAnioT1 = '<td>' . $this->ponInput($mlbAnio) . '</td>';
                $lbAnioT0 = '<td>' . $this->ponInput($mlbNumerador) . '</td>';
                $lbValorT0 = '<td>' . $this->ponInput($mlbDenominador) . '</td>';
                $lbValorIndicador = '<td>' . $this->ponInput($mlbValorInd) . '</td>';

                $mvpValorT0->label = 'Numerador';
                $mvpValorT1->label = 'Denominador';
                $mvpValorInd->label = APP_EJERCICIO;

                $mvpValorT0->disabled = true;
                $mvpValorT1->disabled = true;
                $mvpValorInd->disabled = true;
                $mlbDenominadorP->disabled = true;
                $mlbNumeradorP->disabled = true;

                $vpValorT0 = '<td>' . $this->ponInput($mlbNumeradorP) . '</td>';
                $vpValorT1 = '<td>' . $this->ponInput($mlbDenominadorP) . '</td>';
                $vpValorIndicador = '<td>' . $this->ponInput($mvpValorInd) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                break;
            case 5: //  TASA

                $defnomInd = 'NOMBRE DEL INDICADOR MIR';
                $legendp = 'LÍNEA BASE';
                $legend = 'VALORES PROGRAMADOS';
                $legendCal1 = '
                    <tr>
                        <td colspan=4><legend><small>T1</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=4><legend><small>T0</small></legend></td>
                    </tr>
                ';

                $mlbAnio->value = (isset($this->prm->mem_id)) ? $meta->getLb() : APP_EJERCICIO_ANTERIOR;

                $mlbAnio->label = 'AÑO T-1';
                $mlbValor->label = 'VALOR AÑO T-1';
                $mlbAnioT0->label = 'AÑO T0';
                $mlbValorT0->label = 'VALOR AÑO T0';
                $mlbValorInd->label = 'VALOR INDICADOR';

                $mlbValorInd->disabled = true;
                $mlbValorT0->disabled = true;

                $lbAnioT1 = '<td>' . $this->ponInput($mlbAnio) . '</td>';
                $lbValorT1 = '<td>' . $this->ponInput($mlbValor) . '</td>';
                $lbAnioT0 = '<td>' . $this->ponInput($mlbAnioT0) . '</td>';
                $lbValorT0 = '<td>' . $this->ponInput($mlbValorT0) . '</td>';
                $lbValorIndicador = '<td>' . $this->ponInput($mlbValorInd) . '</td>';

                $mvpAnioT0->label = 'AÑO T0';
                $mvpValorT0->label = 'VALOR AÑO T0';
                $mvpAnioT1->label = 'AÑO T1';
                $mvpValorT1->label = 'VALOR AÑO T1';
                $mvpValorInd->label = 'VALOR INDICADOR';

                $mvpAnioT0->disabled = true;
                $mvpValorT0->disabled = true;
                $mvpAnioT1->disabled = true;
                $mvpValorT1->disabled = true;
                $mvpValorInd->disabled = true;

                $vpAnioT0 = '<td>' . $this->ponInput($mvpAnioT0) . '</td>';
                $vpValorT0 = '<td>' . $this->ponInput($mvpValorT0) . '</td>';
                $vpAnioT1 = '<td>' . $this->ponInput($mvpAnioT1) . '</td>';
                $vpValorT1 = '<td>' . $this->ponInput($mvpValorT1) . '</td>';
                $vpValorIndicador = '<td>' . $this->ponInput($mvpValorInd) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                break;
            default:
                $defnomInd = 'NOMBRE DEL INDICADOR';
                $legendp = 'Línea Base';
                $legend = 'Valor Programado Anual';

                $mlbAnio->label = 'Año';
                $mlbValor->label = 'Valor';
                $mlbAnio->value = (isset($this->prm->mem_id)) ? $meta->getLb() : APP_EJERCICIO_ANTERIOR;

                $lbAnioT1 = '<td>' . $this->ponInput($mlbAnio) . '</td>';
                $lbValorT1 = '<td>' . $this->ponInput($mlbValor) . '</td>';
                $vpValorIndicador = '<td>' . $this->ponInput($v2020) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4) . '</td>';

                break;
        }

        $form = new stdClass();
        $form->id = 'metaInd';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMetaPes';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = (isset($this->prm->mem_id)) ? 'c' : 'a';
        $form->datosExtra->nim_id = $this->prm->nim_id;
        $form->datosExtra->ind_id = $this->prm->ind_id;
        $form->datosExtra->pmi_id = $this->prm->pmi_id;
        $form->datosExtra->eri_id = $this->prm->eri_id;
        $form->datosExtra->lectura = (string) $this->lectura; //revisar
        $form->datosExtra->tipoCalendario = $tiv;
        $form->datosExtra->umi_id = $umi;
        $form->datosExtra->descripcionM = $nom_indMIR;
        $form->datosExtra->tri_id = (string) $this->trimestreCierre;

        switch ($umi) {
            case 2:
            case 3:
            case 4: // PORCENTAJE Y RAZÓN

                $form->datosExtra->mlb_valorIndicador = '"{$("#mlb_valorIndicador").val()}"';
                $form->datosExtra->mvp_valorT0 = '"{$("#numeradorp").val()}"';
                $form->datosExtra->mvp_valorT1 = '"{$("#denominadorp").val()}"';
                $form->datosExtra->mvp_valorIndicador = '"{$("#mvp_valorIndicador").val()}"';

                break;
            case 5: //  TASA

                $form->datosExtra->mlb_valorT0 = '"{$("#mlb_valorT0").val()}"';
                $form->datosExtra->mlb_valorIndicador = '"{$("#mlb_valorIndicador").val()}"';
                $form->datosExtra->mvp_anioT0 = '"{$("#mvp_anioT0").val()}"';
                $form->datosExtra->mvp_valorT0 = '"{$("#mvp_valorT0").val()}"';
                $form->datosExtra->mvp_anioT1 = '"{$("#mvp_anioT1").val()}"';
                $form->datosExtra->mvp_valorT1 = '"{$("#mvp_valorT1").val()}"';
                $form->datosExtra->mvp_valorIndicador = '"{$("#mvp_valorIndicador").val()}"';

                break;
            default:
                $form->datosExtra->mvp_valorT1 = '"{$("#original").val()}"';
                break;
        }

        $abc = 'a';

        if (isset($this->prm->mem_id)) {
            $form->datosExtra->mem_id = $this->prm->mem_id;
            $abc = 'c';
        }

        $form = new form($form);
        $form->mixHtml('
            <fieldset class="col-md-12 col-xs-12">
                <legend>' . $defnomInd . '</legend>
                ' . $this->ponTextArea($descripcion) . '
            </fieldset>
            <fieldset class="col-md-8 col-xs-8">
                <legend><small>' . $legendp . '</small></legend>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            ' . $lbAnioT1 . $lbValorT1 . $lbAnioT0 . $lbValorT0 . $lbValorIndicador . '
                        </tr>
                    </tbody>
                </table>
                <legend><small>' . $legend . '</small></legend>
                <table class="table table-bordered table-striped ">
                    <tbody>
                        <tr>
                            ' . $vpAnioT0 . $vpValorT0 . $vpAnioT1 . $vpValorT1 . $vpValorIndicador . '
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <table class="table table-bordered table-striped ">
                <tbody>
                    ' . $legendCal1 . '
                    <tr>
                        ' . $trim1Cal1 . $trim2Cal1 . $trim3Cal1 . $trim4Cal1 . '
                    </tr>
                    ' . $legendCal2 . '
                    <tr>
                        ' . $trim1Cal2 . $trim2Cal2 . $trim3Cal2 . $trim4Cal2 . '
                    </tr>
                </tbody>
            </table>
        ');

        if (isset($this->prm->modalDeTabla)) {
            $this->mixHtml(
                $form->ponForm() .
                ($this->prm->lectura == 0 && $this->urResponsable && isset($_SESSION[_S]['prm']['A']['12']['2']['2']) && $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == $this->prm->ur && $this->activo ?
                    '<div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                    </div>' : '')
            );
            $this->mixScript('
                $("#close").click(function(){ $("#myModal").hide(); });
                $("#cerrar").click(function(){ $("#myModal").remove(); });
            ');
        } else {
            $this->mixHtml('
                <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Meta de Indicador ' . $this->prm->ind_id . ' de nivel Propósito</h4>
                                <button id="close" type="button" class="btn-close data-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $form->ponForm() . '</div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                                    <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });
                $("#close").click(function(){ $("#myModal").hide(); });
                $("#cerrar").click(function(){ $("#myModal").remove(); });
                $("#myModal").modal().show();
            ');
        }
        if ($this->lectura) {
        }
        $this->mixScript('

            var calculoLBIndicador=0;
            var calculoVPIndicador=0;

            switch(' . $umi . '){
                case 2: case 3: case 4: //PORCENTAJE Y RAZÓN

                    $("#mlb_numerador,#mlb_denominador").blur(function(){
                        if($("#mlb_denominador").val() != 0){
                            if(' . $umi . ' == 2){ //porcentaje
                                calculoLBIndicador=( unformatNumber($("#mlb_numerador").val()) / unformatNumber($("#mlb_denominador").val()) ) * 100;
                            }else{ // razón
                                calculoLBIndicador=( unformatNumber($("#mlb_numerador").val()) / unformatNumber($("#mlb_denominador").val()) );
                            }
                            $("#mlb_valorIndicador").val(calculoLBIndicador.toFixed(4));
                        }else{
                            $("#mlb_valorIndicador").val(0);
                        }
                        if($("#mlb_anioT1").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#mlb_numerador").val() == 0 && $("#mlb_denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });

                    //calendario 1
                    $("#num_trimestre1,#num_trimestre2,#num_trimestre3,#num_trimestre4").blur(function(){
                        if( "' . $tiv . '" == "S" )
                            $("#numeradorp").val( formatNumber( unformatNumber( $("#num_trimestre1").val() ) + unformatNumber( $("#num_trimestre2").val() ) + unformatNumber( $("#num_trimestre3").val() ) + unformatNumber( $("#num_trimestre4").val() ) ) );
                        else{
                            if( unformatNumber( $("#num_trimestre1").val() ) > 0 ){
                                $("#mvp_valorT0").val( formatNumber(  unformatNumber( $("#num_trimestre1").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre1").val() ) ) );
                            }else if( unformatNumber( $("#num_trimestre2").val() ) > 0 ){
                                $("#mvp_valorT0").val( formatNumber(  unformatNumber( $("#num_trimestre2").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre2").val() ) ) );
                            }else if( unformatNumber( $("#num_trimestre3").val() ) > 0 ){
                                $("#mvp_valorT0").val( formatNumber(  unformatNumber( $("#num_trimestre3").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre3").val() ) ) );
                            }else if( unformatNumber( $("#num_trimestre4").val() ) > 0 ){
                                $("#numeradorp").val( formatNumber(  unformatNumber( $("#num_trimestre4").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre4").val() ) ) );
                            }else $("#mvp_valorT0").val( formatNumber( 0 ) );
                        }

                        if($("#num_trimestre4").val() != 0 ){
                            if(' . $umi . ' == 2){ //porcentaje
                                calculoVPIndicador=( unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()) ) * 100;
                            }else{
                                calculoVPIndicador=( unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()) );
                            }
                            $("#mvp_valorIndicador").val(calculoVPIndicador.toFixed(4));
                        }else{
                            $("#mvp_valorIndicador").val(0);
                        }
                    });

                    //calendario 2
                    $("#den_trimestre1,#den_trimestre2,#den_trimestre3,#den_trimestre4").blur(function(){
                        if( "' . $tiv . '" == "S" ){
                            $("#denominadorp").val( formatNumber( unformatNumber( $("#den_trimestre1").val() ) + unformatNumber( $("#den_trimestre2").val() ) + unformatNumber( $("#den_trimestre3").val() ) + unformatNumber( $("#den_trimestre4").val() ) ) );
                        }else{
                            if( unformatNumber( $("#den_trimestre1").val() ) > 0 ){
                                $("#mvp_valorT1").val( formatNumber(  unformatNumber( $("#den_trimestre1").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre1").val() ) ) );
                            }else if( unformatNumber( $("#den_trimestre2").val() ) > 0 ){
                                $("#mvp_valorT1").val( formatNumber(  unformatNumber( $("#den_trimestre2").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre2").val() ) ) );
                            }else if( unformatNumber( $("#den_trimestre3").val() ) > 0 ){
                                $("#mvp_valorT1").val( formatNumber(  unformatNumber( $("#den_trimestre3").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre3").val() ) ) );
                            }else if( unformatNumber( $("#den_trimestre4").val() ) > 0 ){
                                $("#denominadorp").val( formatNumber(  unformatNumber( $("#den_trimestre4").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre4").val() ) ) );
                            }else $("#mvp_valorT1").val( formatNumber( 0 ) );
                        }

                        if($("#den_trimestre4").val() != 0 ){
                            if(' . $umi . ' == 2){ //porcentaje
                                calculoVPIndicador=( unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()) ) * 100;
                            }else{
                                calculoVPIndicador=( unformatNumber($("#num_trimestre4").val()) / unformatNumber($("#den_trimestre4").val()) );
                            }
                            $("#mvp_valorIndicador").val(calculoVPIndicador.toFixed(4));
                        }else{
                            $("#mvp_valorIndicador").val(0);
                        }
                    });
                    break;
                case 5: //  TASA

                    //calendario 1
                    $("#num_trimestre1,#num_trimestre2,#num_trimestre3,#num_trimestre4").blur(function(){
                        if( "' . $tiv . '" == "S" ) {
                            $("#mvp_valorT1").val( formatNumber( unformatNumber( $("#num_trimestre1").val() ) + unformatNumber( $("#num_trimestre2").val() ) + unformatNumber( $("#num_trimestre3").val() ) + unformatNumber( $("#num_trimestre4").val() ) ) );
                            calculoVPIndicador=( ( unformatNumber($("#mvp_valorT1").val()) / unformatNumber($("#mvp_valorT0").val()) )-1 ) * 100;
                            $("#mvp_valorIndicador").val(calculoVPIndicador.toFixed(4));
                        }else{
                            if( unformatNumber( $("#num_trimestre1").val() ) > 0 ){
                                $("#mvp_valorT1").val( formatNumber(  unformatNumber( $("#num_trimestre1").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre1").val() ) ) );
                            }else if( unformatNumber( $("#num_trimestre2").val() ) > 0 ){
                                $("#mvp_valorT1").val( formatNumber(  unformatNumber( $("#num_trimestre2").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre2").val() ) ) );
                            }else if( unformatNumber( $("#num_trimestre3").val() ) > 0 ){
                                $("#mvp_valorT1").val( formatNumber(  unformatNumber( $("#num_trimestre3").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre3").val() ) ) );
                            }else if( unformatNumber( $("#num_trimestre4").val() ) > 0 ){
                                $("#mvp_valorT1").val( formatNumber(  unformatNumber( $("#num_trimestre4").val() ) ) );

                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#num_trimestre4").val() ) ) );
                            }else $("#mvp_valorT1").val( formatNumber( 0 ) );
                        }
                    });

                    //Para llenar los valores programados de años t0 y valor t0
                    $("#mlb_anioT0,#mvp_valorT0").blur(function(){
                        $("#mvp_anioT0").val(  $("#mlb_anioT0").val()  );
                        $("#mvp_valorT0").val( formatNumber(  unformatNumber( $("#mlb_valorT0").val() ) ) );
                    });

                    $("#mlb_valorT1").blur(function(){
                        calculoLBIndicador=( ( unformatNumber($("#mlb_valorT0").val()) / unformatNumber($("#mlb_valorT1").val()) )-1 ) * 100;
                        $("#mlb_valorIndicador").val(calculoLBIndicador.toFixed(4));
                    });

                    //calendario 2
                    $("#den_trimestre1,#den_trimestre2,#den_trimestre3,#den_trimestre4").blur(function(){
                        if( "' . $tiv . '" == "S" ){
                            $("#mlb_valorT0").val( formatNumber( unformatNumber( $("#den_trimestre1").val() ) + unformatNumber( $("#den_trimestre2").val() ) + unformatNumber( $("#den_trimestre3").val() ) + unformatNumber( $("#den_trimestre4").val() ) ) );
                        }else{
                            if( unformatNumber( $("#den_trimestre1").val() ) > 0 ){
                                $("#mlb_valorT0").val( formatNumber(  unformatNumber( $("#den_trimestre1").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre1").val() ) ) );
                            }else if( unformatNumber( $("#den_trimestre2").val() ) > 0 ){
                                $("#mlb_valorT0").val( formatNumber(  unformatNumber( $("#den_trimestre2").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre2").val() ) ) );
                            }else if( unformatNumber( $("#den_trimestre3").val() ) > 0 ){
                                $("#mlb_valorT0").val( formatNumber(  unformatNumber( $("#den_trimestre3").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre3").val() ) ) );
                            }else if( unformatNumber( $("#den_trimestre4").val() ) > 0 ){
                                $("#mlb_valorT0").val( formatNumber(  unformatNumber( $("#den_trimestre4").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#den_trimestre4").val() ) ) );
                            }else $("#mlb_valorT0").val( formatNumber( 0 ) );
                        }

                        if($("#mlb_valorT0").val() != 0){
                            $("#mvp_valorT0").blur();
                            calculoLBIndicador=( ( unformatNumber($("#mlb_valorT0").val()) / unformatNumber($("#mlb_valorT1").val()) )-1 ) * 100;
                            $("#mlb_valorIndicador").val(calculoLBIndicador.toFixed(4));
                            calculoVPIndicador=( ( unformatNumber($("#mvp_valorT1").val()) / unformatNumber($("#mvp_valorT0").val()) )-1 ) * 100;
                            $("#mvp_valorIndicador").val(calculoVPIndicador.toFixed(4));
                        }
                    });
                    break;
                default: // todas las demás unidades de medida del indicador

                    $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur(function(){
                        if( "' . $tiv . '" == "S" )
                            $("#original").val( formatNumber( unformatNumber( $("#ori_trimestre1").val() ) + unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre3").val() ) + unformatNumber( $("#ori_trimestre4").val() ) ) );
                        else{
                            if( unformatNumber( $("#ori_trimestre1").val() ) > 0 ){
                                $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre1").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#ori_trimestre1").val() ) ) );
                            }else if( unformatNumber( $("#ori_trimestre2").val() ) > 0 ){
                                $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre2").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#ori_trimestre2").val() ) ) );
                            }else if( unformatNumber( $("#ori_trimestre3").val() ) > 0 ){
                                $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre3").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#ori_trimestre3").val() ) ) );
                            }else if( unformatNumber( $("#ori_trimestre4").val() ) > 0 ){
                                $("#original").val( formatNumber(  unformatNumber( $("#ori_trimestre4").val() ) ) );
                                if( $(this).val() != 0 )
                                $(this).val( formatNumber(  unformatNumber( $("#ori_trimestre4").val() ) ) );
                            }else $("#original").val( formatNumber( 0 ) );
                        }
                    });

                    if("' . $abc . '" == "c"){
                        $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur();
                    }
                    break;
            }
                    $("#mlb_anioT1,#mlb_numerador,#mlb_denominador").blur(function(){
                        if($("#mlb_anioT1").val() < ' . APP_EJERCICIO_ANTERIOR . ') {
                            $("#mlb_numerador").attr("disabled",false);
                            $("#mlb_denominador").attr("disabled",false);
                            if(' . $umi . ' == 2){ //porcentaje
                               calculoLBIndicador=( unformatNumber($("#mlb_numerador").val()) / unformatNumber($("#mlb_denominador").val()) ) * 100;
                            }else{ // razón
                                calculoLBIndicador=( unformatNumber($("#mlb_numerador").val()) / unformatNumber($("#mlb_denominador").val()) );
                            }
                            $("#mlb_valorIndicador").val(calculoLBIndicador.toFixed(4));
                        } else if($("#mlb_anioT1").val() == ' . APP_EJERCICIO_ANTERIOR . ') {
                            $("#mlb_anioT1").val('.APP_EJERCICIO_ANTERIOR.');
                            $("#mlb_numerador").attr("disabled",true);
                            $("#mlb_denominador").attr("disabled",true);
                            $("#mlb_numerador").val(0);
                            $("#mlb_denominador").val(0);
                            $("#mlb_valorIndicador").val(0);
                        } else {
                            $("#mlb_anioT1").val('.APP_EJERCICIO_ANTERIOR.');
                            $("#mlb_numerador").attr("disabled",true);
                            $("#mlb_denominador").attr("disabled",true);
                            $("#mlb_numerador").val(0);
                            $("#mlb_denominador").val(0);
                            $("#mlb_valorIndicador").val(0);
                            alertify.alert("IMPORTANTE", "El año no puede ser mayor al actual", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        }
                    });

                    $("#saveMeta").click(function(){
                        if($("#mlb_anioT1").val() != ' . APP_EJERCICIO_ANTERIOR . ' && $("#mlb_anioT1").val() <= ' . APP_EJERCICIO_ANTERIOR . ' && $("#mlb_numerador").val() == 0 && $("#mlb_denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });
        ');
    }

    //INSERCIÓN, ACTUALIZACIÓN Y ELIMINACIÓN DE LA META A NIVEL PROPÓSITO
    private function abcMetaPes()
    {
        $mensaje = '';
        $this->prm->trimestreCierre = (string) $this->trimestreCierre;
        $this->prm->umi_id = $this->prm->umi_id;
        if ($this->prm->abc != 'b') {

            switch ($this->prm->umi_id) {
                case 2:
                case 3:
                case 4: //PORCENTAJE Y RAZÓN
                    $this->prm->vum_id = (isset($this->prm->vum_id) ? $this->prm->vum_id : ''); //revisar si se ocupa
                    $this->prm->ips_id = $this->prm->eri_id;
                    $this->prm->descripcion = $this->prm->descripcionM;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    $this->prm->original = $this->prm->mvp_valorIndicador;

                    break;
                case 5: //TASA
                    $this->prm->vum_id = (isset($this->prm->vum_id) ? $this->prm->vum_id : '');
                    $this->prm->ips_id = $this->prm->eri_id;
                    $this->prm->descripcion = $this->prm->descripcionM;
                    $this->prm->num_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->ori_trimestre4 = $this->prm->num_trimestre4;
                    $this->prm->den_trimestre4 = $this->prm->den_trimestre4;

                    $this->prm->original = $this->prm->mvp_valorIndicador;

                    break;
                default:
                    if ($this->prm->abc != 'b') {
                        $this->prm->vum_id = (isset($this->prm->vum_id) ? $this->prm->vum_id : '');
                        $this->prm->ips_id = $this->prm->eri_id;
                        $this->prm->descripcion = $this->prm->descripcionM;
                        $this->prm->ori_trimestre4 = $this->prm->ori_trimestre4;

                        $this->prm->original = $this->prm->mvp_valorIndicador;
                    }
                    break;
            }
        }

        switch ($this->prm->abc) {

            case 'a': //agregar
                $this->prm->mem_id = 1;
                $mensaje = 'Meta Generada exitosamente';
                $this->prm->observacion = 'Meta Propósito registrada desde mir inicial';
                break;
            case 'c': //actualizar
                $mensaje = 'Meta Modificada exitosamente';
                $this->prm->observacion = 'Meta Propósito modificada desde mir inicial';
                break;
            case 'b': //eliminar
                $this->prm->pmi_id = $this->prm->eri_id;
                $this->prm->mpr_id = $this->prm->mem_id;
                $mensaje = 'Meta Eliminada exitosamente';
                $this->prm->observacion = 'Meta Propósito eliminada desde mir inicial';
                break;
        }

        $this->prm->origen = "Controlador MIR Inicial";
        MetaProposito::abc($this->prm);

        $this->mixScript('
            notify("' . $mensaje . '");
            $("#myModal,#modalDeTablatbl-metasIndicador").modal().remove();
        ');

        $this->llenaTablaMetas();
    }

    private function enviarURParticipante()
    {
        $parametros = array();
        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];
        #Para ENVIAR a Validación debe de capturar las metas de los Componentes junto con sus Medio(s) de Verificación y Supuesto(s)
        #y al menos una Actividad de estos Componentes junto con sus Medio(s) de Verificación y Supuesto(s)
        #PENDIENTE LO DE LA VALIDACIÓN CON PRESUPUESTO, PERO SI SE DEBE VALIDAR
        /*
        Zuli.
        Según Fernando mediante ticket #821328:
        Solo los que hayan registrado actividades (y validados por la dirección de presupuesto)
        en el subprograma 00 tendrán que registrar información del componente 00, las demás URS no.

        Por lo cual se agregó en la consulta lo siguiente:
        OR (oa_id = 0 AND spr_id = 0 ) en la variable de validacion para que los que cumplan esta condición
         */
        $this->setSql('
            SELECT sb.gpo_id,sb.ur_id,prg_id,spr_id,sb.pdf_id,pdf_plural,MAX(mvcm_id) mvcm_id,max(spc_id) spc_id,
                if((MAX(mvcm_id) is not null and max(spc_id) is not null  and SUM(mco_original) IS NOT NULL)
                OR (SUM(oa_id) = 0 AND SUM(spr_id) = 0 )
                OR (MAX(mvcm_id) is not null and MAX(spc_id) is not null and SUM(mco_original) IS NOT NULL AND SUM(oa_id) > 0 AND SUM(spr_id) = 0 AND max(eoa_id) = 7 )
                ,"valido","no valido") as validacion#,
            FROM cat_Subprograma sb
            INNER JOIN(
                SELECT gpo_id, ur_id, prgs_id AS prg_id, vpm_id, s.sprs_id AS spr_id, esr_id
                FROM (
                    SELECT vpm_id, prgs_id, gpo_id, ur_id
                    FROM vin_ProgramasMir
                    UNION
                    SELECT  vpm_id, prgs_id, gpo_id, ur_id
                    FROM vin_UrProgramaMir
                )v
                INNER JOIN cat_Subprogramas s USING(prgs_id)
                WHERE gpo_id =:gpo_id AND ur_id=:ur_id AND prgs_id=:prg_id
            )v USING(gpo_id, ur_id,prg_id,spr_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN enc_ProductoFinalIndicador USING(pdf_id)
            INNER JOIN cat_ProductoFinal USING(pdf_id)
            LEFT JOIN cat_MetaComponente USING(gpo_id,ur_id,epfi_id)
            LEFT JOIN cat_Indicador USING(nim_id,ind_id)
            LEFT JOIN cat_SupuestoComponente sup ON  sb.gpo_id=sup.gpo_id AND sb.ur_id=sup.ur_id AND sb.prg_id=sup.prgs_id  AND sb.spr_id=sup.sprs_id
            LEFT JOIN cat_MedioVerificacionComponente mv ON  mv.gpo_id=sup.gpo_id AND mv.ur_id=sup.ur_id AND mv.prgs_id=sup.prgs_id  AND mv.sprs_id=sup.sprs_id
            WHERE sb.gpo_id =:gpo_id AND sb.ur_id=:ur_id AND sb.prg_id=:prg_id
            AND (esr_id=:esr_id OR spr_id=:valor) AND sb.pdf_id!=:valor
            GROUP BY spr_id,pdf_id;
        ');
        $parametros = [
            ':gpo_id' => $_SESSION[_S]['gpo_id'],
            ':ur_id' => $_SESSION[_S]['ur_id'],
            ':prg_id' => $prg_id,
            ':esr_id' => 4,
            ':valor' => 0,
        ];
        $this->conectar();
        $this->consultar($parametros);
        $this->desconectar();
        $completoComp = 1;
        $datosComponente = '';
        $datosActividad = '';

        $completoAct = 1;
        $actividadesv = false;
        $metasact = false;

        while ($CuantosComponentes = $this->extraer()) {

            if ($CuantosComponentes['validacion'] === "valido") {

                // $completoComp=1;
                // Hasta nuevo aviso para vigente > 0
                //solo se valida que a nivel actividad se tenga medios de verificación y supuestos
                $act = new AccesoDatos();
                $act->conectar();
                $act->setSql('
                    #1: Validado, 2: No validado
                    SELECT  IF(MAX(mvac_id) IS NOT NULL AND MAX(spa_id) IS NOT NULL AND MAX(macm_id) IS NOT NULL,1,0) AS validacion, gpo_id,ur_id,prg_id
                        epg_id, pdf_id, pdi_id, MAX(macm_id) metas
                    FROM cat_Subprograma
                    INNER JOIN(
                        SELECT gpo_id, ur_id, prgs_id AS prg_id,vpm_id AS vum_id, s.sprs_id AS spr_id, esr_id, pdf_id
                        FROM ( SELECT vpm_id, prgs_id, gpo_id, ur_id
                            FROM vin_ProgramasMir
                            UNION
                            SELECT  vpm_id, prgs_id, gpo_id, ur_id
                            FROM vin_UrProgramaMir)v
                        INNER JOIN cat_Subprogramas s USING(prgs_id)
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                        AND prgs_id=:prg_id AND (esr_id=:esr OR s.sprs_id=:sprs)
                    )v USING(gpo_id, ur_id,prg_id,spr_id,pdf_id)
                    INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING(pdf_id)
                    INNER JOIN vin_ProductoIntermedioIndicador vin USING(pdf_id,pdi_id)
                    LEFT JOIN cat_MetaActividadMir USING(pii_id,gpo_id,ur_id)
                    LEFT JOIN cat_SupuestoActividad USING( pdf_id,pdi_id,gpo_id,ur_id)
                    LEFT JOIN cat_MedioVerificacionActividad USING( pdf_id, pdi_id,gpo_id,ur_id)
                    WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                        AND nim_id=:nim AND pdf_id=:pdf_id
                        AND vin.trim_0=:sts_id
                        AND vpfi.sts_id=:sts_id
                    GROUP BY pdf_id,pdi_id;
                ');
                $parametros = [
                    ':gpo_id' => $_SESSION[_S]['gpo_id'],
                    ':ur_id' => $_SESSION[_S]['ur_id'],
                    ':prg_id' => $prg_id,
                    'esr' => 4,
                    'sprs' => 0,
                    'nim' => 4,
                    ':pdf_id' => $CuantosComponentes['pdf_id'],
                    ':sts_id' => 1,

                ];
                $act->consultar($parametros);
                $act->desconectar();
                if ($act->getNumeroRegistros() > 0) {
                    while ($actividades = $act->extraer()) {
                        if ($actividades['validacion'] == 1) {
                            $actividadesv = true;
                        }
                        if ($actividades['metas'] != '') {
                            $metasact = true;
                        }
                    }
                    if ($actividadesv && $metasact) {
                        $completoAct = 1;
                    } else {
                        $completoAct = 0;
                        $datosActividad = ' Producto Final ' . $CuantosComponentes['pdf_id'];
                    }
                }
            } else {
                $completoComp = 0;
                $datosComponente = ' Producto Final ' . $CuantosComponentes['pdf_id'] . ' - ' . $CuantosComponentes['pdf_plural'];
            }
        }

        /* si es la UR Responsable la que va a enviar a PLANEACIÓN, para enviar debe tener
        $completoComp - valida que tenga meta, medio de verificación y supuesto a nivel componente
        $completoAct - valida que tenga medio de verificación y supuesto a nivel actividad
        $this->prm->metaProp - $this->prm->mediosVerPro - $this->prm->supuestoPro - valida que tenga meta,
        medio de verificación y supuesto a nivel proposito */
        if ($this->prm->urRespon == 1) {

            // ANTES DE ENVIAR A VALIR LA MIR DEL PROGRAMA DEBEN ESTAR VALIDADA LA UR PARTICIPANTE
            $validacion = new AccesoDatos();
            $validacion->setSql('
                SELECT emi_id,ur_nombre,gpo_id,ur_id
                FROM(   SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id, emi_id
                    FROM cat_Mir
                    INNER JOIN vin_ProgramasMir USING (mir_id)
                    UNION
                    SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id, ur.emi_id
                    FROM  cat_Mir
                    INNER JOIN vin_ProgramasMir USING (mir_id)
                    INNER JOIN vin_UrProgramaMir ur USING(vpm_id))v
                JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                WHERE prgs_id = ? AND emi_id IN(?,?,?,?,?,?) AND NOT (gpo_id =? AND ur_id =? );');
            $parametros = [
                $_SESSION[_S]['clasAdm-prg'],
                1, 2, 3, 6, 8, 10,
                $_SESSION[_S]['gpo_id'],
                $_SESSION[_S]['ur_id'],
            ];
            $validacion->conectar();
            $validacion->consultar($parametros);

            if ($validacion->getNumeroRegistros() > 0) {

                $urs = '';
                while ($r = $validacion->extraer()) {

                    $urs .= $r['gpo_id'] . str_pad($r['ur_id'], 2, "0", STR_PAD_LEFT) . ' - ' . addslashes($r['ur_nombre']);
                    $urs .= ($r['emi_id'] == 1) ? ' : No ha enviado sus datos a revisión ' : ' : Sus datos han sido rechazados ';
                    $urs .= ($r['emi_id'] == 2) ? ' : No ha validado su datos ' : '';
                }
                $this->mixScript('notify("ES NECESARIO QUE ESTEN VALIDADOS LOS DATOS DE LAS UR´s PARTICIPANTES ' . $urs . ' ","error",false);');
            } else {
                //validamos que la UR Responsable cargue los 3 archivos obligatorios Árbol de problema, Árbol de objetivos y Selección de alternativas
                $archivos = new AccesoDatos();
                $archivos->conectar();
                $archivos->setSql('
                    #archivos 1: valido 0: no valido
                    SELECT if(mir_archivoAlternativas !="" AND mir_archivoArbol !="" AND mir_archivoObjetivo !="" AND mir_archivoDiagnostico!="", 1, 0) AS archivos
                    FROM cat_Mir where mir_id=?;
                ');
                $archivos->consultar([$this->mir_id]);
                $completoArchivos = $archivos->extraer();

                if ($completoArchivos['archivos'] == 0 || $completoComp == 0 || $completoAct == 0 || $this->prm->metaProp == 0 || $this->prm->mediosVerPro == 0 || $this->prm->supuestoPro == 0) {

                    if ($this->prm->metaProp == 0 || $this->prm->mediosVerPro == 0 || $this->prm->supuestoPro == 0) {
                        $this->mixScript('notify("Falta complementar Meta(s), Medio(s) de Verificación, Supuesto(s) y Resumen Narrativo a nivel Propósito","error", false);');
                    } else if ($completoComp == 0) {
                        $this->mixScript('notify("Falta complementar en el ' . $datosComponente . ' por lo menos una Meta, Medio(s) de Verificación y Supuesto(s)","error", false);');
                    } else if ($completoAct == 0) {
                        $this->mixScript('notify("Falta complementar a nivel Actividad en el ' . $datosActividad . ' Meta(s), Medio(s) de Verificación y Supuesto(s)","error", false);');
                    } else if ($completoArchivos['archivos'] == 0) {
                        $this->mixScript('notify("Debe subir al sistema los archivos: Árbol de problema, Árbol de objetivos, Selección de alternativas y Diagnóstico","error", false);');
                    }
                } else {
                    $this->mixScript('comunica("' . $this->pantalla . '",{metodo:"enviar"});');
                }
            }
        } else {
            if ($completoComp == 0) {
                $this->mixScript('notify("Falta complementar en el ' . $datosComponente . ' por lo menos una Meta, Medio(s) de Verificación y Supuesto(s)","error", false);');
            } else if ($completoAct == 0) {
                $this->mixScript('notify("Falta complementar a nivel Actividad en el ' . $datosActividad . ' Metas, Medio(s) de Verificación y Supuesto(s)", "error", false);');
            } else {
                $this->mixScript('comunica("' . $this->pantalla . '",{metodo:"enviar"});');
            }
        }
    }

    private function validaUrParticipante()
    {

        $parametros = array();
        $this->conectar();
        $this->setSql('
            SELECT emi_id,ur_nombre,gpo_id, v.ur AS ur_id
            FROM( SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id, emi_id, concat(ur_id) as ur
                FROM cat_Mir
                INNER JOIN vin_ProgramasMir USING (mir_id)
                UNION
                SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id, ur.emi_id,  concat(ur.ur_id) as ur
                FROM  cat_Mir
                INNER JOIN vin_ProgramasMir USING (mir_id)
                INNER JOIN vin_UrProgramaMir ur USING(vpm_id)
            )v
            JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            WHERE prgs_id = ? AND NOT (gpo_id = ? AND ur_id = ? );
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-prg'],
            $_SESSION[_S]['gpo_id'],
            $_SESSION[_S]['ur_id'],
        ];
        $this->consultar($parametros);
        $this->desconectar();
        $this->setContenedor('divContenedorGeneralModal');

        $this->mixHtml('
            <div id="myModal" class="modal " id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <br /><br /><br /><br />
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"><b>LISTA DE UR\'S PARTICIPANTES</b></h4>
                            <button id="close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    <ul class="list-group" id="listaSupuestos" >
        ');
        while ($r = $this->extraer()) {
            $this->mixHtml('<li class="list-group-item d-flex justify-content-between">' . $r['gpo_id'] . $r['ur_id'] . '-' . $r['ur_nombre'] . '<div>');
            #si el estado de la MIR del programa es 1 0 3 y puede validar
            if ($_SESSION[_S]['nvj_id'] <= 2) {

                switch ($r['emi_id']) {
                    //Enviado a validacion de UR responsable
                    // Rechazado por Planeacion
                    case 2:
                    case 5:
                        $this->mixHtml('
                            <button type="button" id="ok' . $r['gpo_id'] . $r['ur_id'] . '" gpo_id ="' . $r['gpo_id'] . '" ur_id ="' . $r['ur_id'] . '" class="btn btn-outline-success btn-sm ok me-1" title="Validar datos"><span class="fa fa-solid fa-circle-check"></span></button>
                            <button type="button" id="delete' . $r['gpo_id'] . $r['ur_id'] . '" gpo_id ="' . $r['gpo_id'] . '" ur_id ="' . $r['ur_id'] . '" class="btn btn-outline-danger btn-sm delete" title="Rechazar datos"><span class="fa fa-solid fa-trash-can"></span></button>
                        ');
                        break;
                    // Rechazado por Ur responsable
                    case 3:
                        $this->mixHtml('<span class="btn btn-outline-danger btn-sm disabled" title="Rechazado"><span class="fa fa-solid fa-xmark"></span></span>');
                        break;
                    //Validado por Ur responsable
                    case 4:
                        $this->mixHtml('
                            <span class="btn btn-outline-success btn-sm disabled me-1" title="Validado"><span class="fa fa-solid fa-circle-check"></span></span>
                            <button type="button" id="delete' . $r['gpo_id'] . $r['ur_id'] . '" gpo_id ="' . $r['gpo_id'] . '" ur_id ="' . $r['ur_id'] . '" class="btn btn-outline-danger btn-sm delete" title="Rechazar datos"><span class="fa fa-solid fa-trash-can"></span></button>
                        ');
                        break;
                }
            }
            $this->mixHtml('</div></li>');
        }
        $this->mixHtml('            </ul>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
                <div id="modalMensaje"></div>
            ');

        $this->mixScript('
            // bloquearClasAdmin();
            $("#myModal").on("hidden.bs.modal", function (e) { $("#myModal").remove(); });
            $(".ok").click(function(){
                ur = $(this).attr("ur_id");
                gpo = $(this).attr("gpo_id");
                alertify.confirm("VALIDAR DATOS", "¿ Confirma que desea validar los datos de la UR "+ gpo + ur +" <h5 style=\"display:inline\"></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '", {
                            metodo: "validarMir",
                            nivel:"UR",
                            ur_id:ur,
                            gpo_id:gpo,
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel").set("labels", {ok:"VALIDAR", cancel: "Cancelar"});
            });
            $(".delete").click(function(){
                ur = $(this).attr("ur_id");
                gpo = $(this).attr("gpo_id");
                alertify.confirm("RECHAZAR DATOS", "¿ Confirma que desea rechazar los datos de la UR "+ gpo + ur +" <h5 style=\"display:inline\"></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '", {
                            metodo: "addMensajeRechazo",
                            nivel:"UR",
                            ur_id:ur,
                            gpo_id:gpo
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel").set("labels", {ok:"RECHAZAR", cancel: "Cancelar"});
            });

            $("#close").click(function(){ $("#myModal").hide(); });
            $("#cerrar").click(function(){ $("#myModal").remove(); });
            $("#myModal").modal().show();
        ');
    }

    private function enviar()
    {
        $estado = '';
        $this->usrIp = ($this->getIp() != "::1") ? $this->getIp() : '127.0.0.1';
        $parametros = array();
        $parametrosbit = array();
        $variablesBit = '
            SET @bit_observacion="Enviar a revisión desde MIR Inicial",
            @usr_id=?,
            @usr_ip=INET_ATON(?)';
        $parametrosbit = [
            $_SESSION[_S]['usr_id'],
            $this->usrIp,
        ];
        if (!$this->validador) {

            if ($this->urResponsable) {
                $resUR = new AccesoDatos();
                $resUR->conectar();
                $resUR->setSql($variablesBit);
                $resUR->consultar($parametrosbit);
                $resUR->setSql(' UPDATE vin_UrProgramaMir SET emi_id = ? WHERE prgs_id = ? AND sprs_id= ? ;');
                $parametros = [
                    6,
                    $_SESSION[_S]['clasAdm-prg'],
                    0,
                ];
                $resUR->consultar($parametros);
                $resUR->desconectar();

                $resMir = new AccesoDatos();
                $resMir->conectar();
                $resMir->setSql(' UPDATE cat_Mir SET emi_id = 6 WHERE mir_id = ?');
                $resMir->consultar([$this->mir_id]);
                $resMir->desconectar();
                $this->mixScript('notify("Datos enviados a revisión de Planeación");');
                $this->mixScript('$("#btnEnviarR").remove();');
            } else {
                $enviado = new AccesoDatos();
                $enviado->conectar();
                $enviado->setSql($variablesBit);
                $enviado->consultar($parametrosbit);
                $enviado->setSql(' UPDATE vin_UrProgramaMir SET emi_id = 2 WHERE gpo_id = ? AND ur_id = ? AND prgs_id = ? AND sprs_id= ? AND emi_id IN(?,?,?) ');
                $parametros = [
                    $_SESSION[_S]['gpo_id'],
                    $_SESSION[_S]['ur_id'],
                    $_SESSION[_S]['clasAdm-prg'],
                    0,
                    1, 3, 6,
                ];
                $enviado->consultar($parametros);
                $enviado->desconectar();
                $this->mixScript('notify("Datos enviados a revisión de la UR responsable del programa");');
                $this->mixScript('$("#btnEnviarR").remove();');
            }
        } elseif ($this->validador) {
            $this->conectar();

            switch ($this->estadoMir) {
                case 6: #validación planeación
                case 11:
                    if (isset($_SESSION[_S]['prm']['A']['12']['2']['62'])) {

                        $validarFin = new AccesoDatos();
                        $validarFin->conectar();
                        $validarFin->setSql('
                            SELECT  IF(COUNT(mvpm_id) IS NOT NULL AND COUNT(spm_id) IS NOT NULL AND SUM(mtf_id) IS NOT NULL,1,0) AS validacion
                            FROM vin_ProgramasMir
                            LEFT JOIN (
                            SELECT vpm_id, nim_id, mtf_id
                            FROM vin_ProgramaMirIndicador
                            INNER JOIN cat_MetaFin USING(pmi_id)
                            ) M USING (vpm_id)
                            LEFT JOIN cat_SupuestoProgramaMir USING(vpm_id,nim_id)
                            LEFT JOIN cat_MedioVerificacionProgramaMir USING(vpm_id,nim_id)
                            WHERE prgs_id=?
                            AND nim_id=?;
                        ');
                        $parametros = [
                            $_SESSION[_S]['clasAdm-prg'],
                            1,
                        ];
                        $validarFin->consultar($parametros);
                        $completoFin = $validarFin->extraer();

                        if ($completoFin['validacion'] == 1) {
                            $estado = 7;
                            $this->mixScript('notify("Datos enviados a revisión de la Instancia Técnica de Evaluación");');
                            $this->mixScript('$("#btnValidarMir").remove(); $("#btnRechazarMir").remove();');
                        } else {
                            $estado = 6;
                            $this->mixScript('notify("Para enviar a validar debe capturar Meta(s), Medio(s) de verificación y Supuesto(s) a nivel FIN","error", false);');
                        }
                    }
                    break;
                case 7: #validación para gobernatura
                    if (isset($_SESSION[_S]['prm']['A']['12']['2']['60'])) {
                        $estado = 9;
                        $this->mixScript('notify("MIR VALIDADA");');
                        $this->mixScript('$("#btnValidarMir").remove(); $("#btnRechazarMir").remove();');
                    }
                    break;
            }
            $this->setSql($variablesBit);
            $this->consultar($parametrosbit);
            $this->setSql(' UPDATE vin_UrProgramaMir SET emi_id = ' . $estado . ' WHERE prgs_id = ? AND sprs_id= ?;');
            $parametros = [
                $_SESSION[_S]['clasAdm-prg'],
                0,
            ];
            $this->consultar($parametros);
            $this->setSql(' UPDATE cat_Mir SET emi_id = ' . $estado . ' WHERE mir_id = ?');
            $this->consultar([$this->mir_id]);
            $this->desconectar();
        }
        $this->pintaPantalla();
    }

    private function addMensajeRechazo()
    {
        if ($this->prm->nivel == 'UR') {
            $this->setContenedor('modalMensaje');
        } else {
            $this->setContenedor('divContenedorGeneralModal');
        }

        $mensajeRechazo = new stdClass();
        $mensajeRechazo->id = 'mensajeRechazo';
        $mensajeRechazo->required = true;
        $mensajeRechazo->enDiv = true;
        $mensajeRechazo->maxlength = 1000;
        $mensajeRechazo->width = '300px';
        $mensajeRechazo->height = '100px';
        $this->mixHtml('
            <div id="myModalMensaje" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Motivo de rechazo</h4>
                            <button id="close2" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <form id="Mensaje">
                            ' . $this->ponTextArea($mensajeRechazo) . '
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-outline-danger" id="btnGuardarMensaje" form="">Aceptar</button>
                            <button id="cerrar2" type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixScript('

            $("#myModalMensaje").on("hidden.bs.modal", function (e) { $("#myModalMensaje").remove(); });
            $("#btnGuardarMensaje").click(function(){
                valido = true;
                $("#Mensaje").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                    if(!this.checkValidity()){
                        $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  padding-top: 100px; margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
                    }
                });
                $( ".formError" ).show("pulsate");
                setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
                setTimeout(function() { $( ".formError" ).remove(); }, 4500 );

                if(valido){
                    comunica("' . $this->pantalla . '",{
                        metodo: "rechazarMir",
                        nivel:"' . $this->prm->nivel . '",
                        ' . (($this->prm->nivel == 'UR') ? '
                            ur_id:' . $this->prm->ur_id . ',
                            gpo_id:' . $this->prm->gpo_id . ',
                        ' : '') . '
                        observacion:$("#mensajeRechazo").val()
                    });
                    $("#myModalMensaje").modal("hide");
                }
            });
            $("#close2").click(function(){ $("#myModalMensaje").remove(); });
            $("#cerrar2").click(function(){ $("#myModalMensaje").hide(); });
            $("#btnGuardarMensaje").click(function(){ $("#myModalMensaje").hide(); });
            $("#myModalMensaje").modal().show();
        ');
    }

    private function rechazarMir()
    {
        $parametros = array();
        $estadoUr = $estado = '';

        switch ($this->prm->nivel) {
            case 'UR':
                $this->conectar();
                $this->setSql(' UPDATE vin_UrProgramaMir SET emi_id = 3 WHERE gpo_id = ? AND ur_id = ? AND prgs_id = ? AND sprs_id= ? AND emi_id IN(?,?,?,?,?) ');
                $parametros = [
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                    $_SESSION[_S]['clasAdm-prg'],
                    0,
                    2, 4, 6, 7, 8,
                ];
                $this->consultar($parametros);
                $this->desconectar();
                $this->mixScript('
                    notify("Datos de la UR ' . $this->prm->gpo_id . str_pad($this->prm->ur_id, 2, "0", STR_PAD_LEFT) . ' rechazados","error");
                    $("#ok' . $this->prm->gpo_id . str_pad($this->prm->ur_id, 2, "0", STR_PAD_LEFT) . '").remove();
                    $("#delete' . $this->prm->gpo_id . str_pad($this->prm->ur_id, 2, "0", STR_PAD_LEFT) . '").remove();
                ');
                $this->setSql('
                    SELECT gpo_id,ur_id,ur_nombre,emi_id
                    FROM vin_UrProgramaMir
                    JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    WHERE prgs_id = ? AND emi_id IN(?,?,?) AND NOT (gpo_id = ? AND ur_id = ? );');
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    1, 2, 3,
                    $_SESSION[_S]['gpo_id'],
                    $_SESSION[_S]['ur_id'],
                ];
                $this->conectar();
                $this->consultar($parametros);
                if ($this->getNumeroRegistros() > 0) {
                    $urs = '';
                    while ($r = $this->extraer()) {
                        $urs .= $r['gpo_id'] . $r['ur_id'] . ' - ' . $r['ur_nombre'];
                        $urs .= ($r['emi_id'] == 1) ? ' : No ha enviado sus datos a revisión' : ' : Sus datos han sido rechazados';
                        $urs .= ($r['emi_id'] == 2) ? ' : No ha validado su datos ' : '';
                    }
                }

                $this->desconectar();
                break;
            case 'VALIDADOR':
                $this->conectar();
                switch ($this->estadoMir) {
                    case 6:
                    case 7:
                    case 11:
                        if (isset($_SESSION[_S]['prm']['A']['12']['2']['60'])) {
                            $estado = 11;
                            $estadoUr = 11;
                        } else {
                        $estado = 5;
                        $estadoUr = 4;
                        }
                        $this->mixScript('notify("Datos rechazados");');
                        break;
                }
                $this->setSql(' UPDATE vin_UrProgramaMir SET emi_id = ' . $estadoUr . ' WHERE prgs_id = ? AND sprs_id=?;');
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    0,
                ];
                $this->consultar($parametros);
                $this->setSql(' UPDATE cat_Mir SET emi_id = ' . $estado . ' WHERE mir_id = ?');
                $this->consultar([$this->mir_id]);
                $this->desconectar();
                $this->pintaPantalla();
                $this->mixScript('
                    $("#btnEnviarR").remove();
                    $("#btnValidarMir").remove();
                    $("#btnRechazarMir").remove();
                ');
                break;
        }
        $mensaje = new stdClass();
        $mensaje->gpo_id = $_SESSION[_S]['gpo_id'];
        $mensaje->ur_id = $_SESSION[_S]['ur_id'];
        $mensaje->remitente = $_SESSION[_S]['usr_id'];
        $mensaje->mensaje = $this->prm->observacion;
        $mensaje->asunto = 'Motivo de rechazo';
        //ALFONSO 2020-11-08: Se aprovecho el error generado por el modo estricto para cambiar
        //la forma en que se mostraban los mensajes del historial.
        //Si el que rechazo es el validador el mensaje lo deben ver todas las UR Participantes
        if ($this->prm->nivel == 'VALIDADOR') {
            $mensaje->destinatario = 1;
        }
        //En caso contrario es la UR Responsable la que rechaza, el mensaje solo lo debe ver la UR Participante que se rechazo
        else {
            $mensaje->destinatario = $this->prm->gpo_id . str_pad($this->prm->ur_id, 2, "0", STR_PAD_LEFT);
        }
        $this->conectar();
        $this->setSql('
            SELECT mjm_id FROM cat_MensajeMir
            INNER JOIN cat_Mir USING(mir_id)
            WHERE mir_id = ? AND NOT ISNULL(mjm_id) AND mjm_id != ?;');
        $parametros = [
            $this->mir_id,
            0,
        ];
        $this->consultar($parametros);
        if ($this->getNumeroRegistros() == 1) {
            $m = $this->extraer();
            $mensaje->respuesta = $m['mjm_id'];
        }
        $mensaje->fechaEnvio = date('Y/m/d H:i:s');
        $mensaje->emj_id = 1;
        $mensaje->abc = "a";
        $mensaje->mir_id = $this->mir_id;
        $mje_id = MensajeMir::abc($mensaje);
        $this->desconectar();
    }

    private function actualizaMir() {
        $completoAct = 0;
        switch ($this->estadoMir) {
            case 9:
                $this->conectar();
                $estado = 10;
                $this->mixScript('notify("Datos rechazados");');
                $this->setSql(' UPDATE vin_UrProgramaMir SET emi_id = ' . $estado . ' WHERE prgs_id = ? AND sprs_id=?;');
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    0,
                ];
                $this->consultar($parametros);
                $this->setSql(' UPDATE cat_Mir SET emi_id = ' . $estado . ' WHERE mir_id = ?');
                $this->consultar([$this->mir_id]);
                $this->desconectar();
                $this->pintaPantalla();
                break;
            case 10;
                $act = new AccesoDatos();
                $act->conectar();
                $act->setSql('
                    #1: Validado, 2: No validado
                    SELECT  IF(MAX(mvac_id) IS NOT NULL AND MAX(spa_id) IS NOT NULL AND MAX(macm_id) IS NOT NULL,1,0) AS validacion, gpo_id,ur_id,prg_id
                        epg_id, pdf_id, pdi_id, MAX(macm_id) metas
                    FROM cat_Subprograma
                    INNER JOIN(
                        SELECT gpo_id, ur_id, prgs_id AS prg_id,vpm_id AS vum_id, s.sprs_id AS spr_id, esr_id, pdf_id
                        FROM ( SELECT vpm_id, prgs_id, gpo_id, ur_id
                            FROM vin_ProgramasMir
                            UNION
                            SELECT  vpm_id, prgs_id, gpo_id, ur_id
                            FROM vin_UrProgramaMir)v
                        INNER JOIN cat_Subprogramas s USING(prgs_id)
                        WHERE prgs_id=:prg_id AND (esr_id=:esr OR s.sprs_id=:sprs)
                    )v USING(gpo_id, ur_id,prg_id,spr_id,pdf_id)
                    INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING(pdf_id)
                    INNER JOIN vin_ProductoIntermedioIndicador vin USING(pdf_id,pdi_id)
                    LEFT JOIN cat_MetaActividadMir USING(pii_id,gpo_id,ur_id)
                    LEFT JOIN cat_SupuestoActividad USING( pdf_id,pdi_id,gpo_id,ur_id)
                    LEFT JOIN cat_MedioVerificacionActividad USING( pdf_id, pdi_id,gpo_id,ur_id)
                    WHERE nim_id=:nim
                        AND vin.sts_id=:sts_id
                        AND vpfi.sts_id=:sts_id
                    GROUP BY pdf_id,pdi_id, ind_id;
                ');
                $parametros = [
                    ':prg_id' => $this->prm->prg_id,
                    'esr' => 4,
                    'sprs' => 0,
                    'nim' => 4,
                    ':sts_id' => 1,

                ];
                $act->consultar($parametros);
                $act->desconectar();
                if ($act->getNumeroRegistros() > 0) {
                    $actividadesv = true;  // asumimos todo bien
                    $metasact = false;

                    while ($actividades = $act->extraer()) {
                        if ($actividades['validacion'] == 0) {
                            // si existe al menos un 0, invalida todo
                            $actividadesv = false;
                            $actividadFaltante = $actividades['pdf_id'];
                        }
                        if ($actividades['metas'] != '') {
                            $metasact = true;
                        }
                    }
                
                    if ($actividadesv && $metasact) {
                        $completoAct = 1;
                    } else {
                        $completoAct = null; // o 0 según lo que quieras devolver
                    }
                }

                $this->conectar();
                if ($completoAct == 1) {
                    $estado = 9;
                    $this->mixScript('notify("MIR Actualizada");');
                    $this->setSql(' UPDATE vin_UrProgramaMir SET emi_id = ' . $estado . ' WHERE prgs_id = ? AND sprs_id=?;');
                    $parametros = [
                        $_SESSION[_S]['clasAdm-prg'],
                        0,
                    ];
                    $this->consultar($parametros);
                    $this->setSql(' UPDATE cat_Mir SET emi_id = ' . $estado . ' WHERE mir_id = ?');
                    $this->consultar([$this->mir_id]);
                    $this->desconectar();
                    
                } else {
                    $this->mixScript('notify("Falta complementar a nivel Actividad en el Producto final ' . $actividadFaltante . ' Meta(s), Medio(s) de Verificación y Supuesto(s)","error", false);');
                }
                $this->pintaPantalla();
                break;
        }
    }

    private function validarMir()
    {
        switch ($this->prm->nivel) {
            case 'UR':
                $this->conectar();
                $this->setSql(' UPDATE vin_UrProgramaMir SET emi_id = 4 WHERE gpo_id = ? AND ur_id = ? AND prgs_id = ? AND sprs_id= ? AND emi_id IN(?,?) ');
                $parametros = [
                    $this->prm->gpo_id,
                    $this->prm->ur_id,
                    $_SESSION[_S]['clasAdm-prg'],
                    0,
                    2, 5,
                ];
                $this->consultar($parametros);
                $this->desconectar();
                $this->mixScript('
                    notify("Datos de la UR ' . $this->prm->gpo_id . str_pad($this->prm->ur_id, 2, "0", STR_PAD_LEFT) . ' validados");
                    $("#ok' . $this->prm->gpo_id . str_pad($this->prm->ur_id, 2, "0", STR_PAD_LEFT) . ',#delete' . $this->prm->gpo_id . str_pad($this->prm->ur_id, 2, "0", STR_PAD_LEFT) . '").remove();
                ');
                $this->setSql('
                    SELECT gpo_id,ur_id,ur_nombre,emi_id
                    FROM vin_UrProgramaMir
                    JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    WHERE prgs_id = ? AND emi_id IN(?,?,?) AND NOT (gpo_id = ? AND ur_id = ? );');
                $this->conectar();
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    1, 2, 3,
                    $_SESSION[_S]['gpo_id'],
                    $_SESSION[_S]['ur_id'],
                ];
                $this->consultar($parametros);
                if ($this->getNumeroRegistros() > 0) {
                    $urs = '';
                    while ($r = $this->extraer()) {
                        $urs .= $r['gpo_id'] . $r['ur_id'] . ' - ' . $r['ur_nombre'];
                        $urs .= ($r['emi_id'] == 1) ? ': No ha enviado sus datos a revisión' : ' Sus datos han sido rechazados';
                        $urs .= ($r['emi_id'] == 2) ? ' : No ha validado su datos ' : '';
                    }
                }
                $this->desconectar();
                break;
        }
    }

    private function uploadArchivo($archivo)
    {
        $parametros = array();

        $borrado = $nomFol = '';
        if ($this->urResponsable === true || $this->cargaPlaneacion === true) {
            $gpo = $_SESSION[_S]['gpo_id'];
            $ur = $_SESSION[_S]['ur_id'];
            $ur_clave = $gpo . $ur;
            $prg_clave = str_pad($_SESSION[_S]['clasAdm-prg'], 3, "0", STR_PAD_LEFT);
            $name = $archivo . "File";
            $respuesta = new stdClass();

            if ($this->cargaPlaneacion && !$this->cargaUR) {
                //Obtenemos la UR responsable del programa ya que Planeación está realizando
                //La actualización del archivo
                $this->conectar();
                $this->setSql('
                    SELECT gpo_id, ur_id
                    FROM vin_ProgramasMir
                    WHERE prgs_id = ' . $_SESSION[_S]['clasAdm-prg'] . ';
                ');
                
                $this->consultar();
                $this->desconectar();
                $registros = $this->extraer();
                $gpo = $registros['gpo_id'];
                $ur = $registros['ur_id'];
                $ur_clave = $gpo . $ur;
            }

            if (isset($_FILES[$name]) && $_FILES[$name]['error'] == '0') {
                switch ($archivo) {
                    case 'Objetivo':
                        $nomFol = "/arb_objetivos";
                        break;
                    case 'Alternativas':
                        $nomFol = "/sel_alternativas";
                        break;
                    case 'Arbol':
                        $nomFol = "/arb_problemas";
                        break;
                    case 'Diagnostico':
                        $nomFol = "/diagnostico";
                        break;
                }
                $up = new FicheroSefip();
                $up->setTipos(array("pdf"));
                $up->setFichero($_FILES[$name]);
                $up->setModulo("poa");
                $up->setfile($prg_clave . "_arbol" . $archivo . ".pdf");
                $up->setUrFichero($ur_clave);
                $up->setAnioFichero(APP_EJERCICIO);
                $up->setRuta("PLANEACION/" . $prg_clave . $nomFol);

                $nomDir = $up->getRutaMod();

                if ($nomDir === false && $up->getError() === true) {
                    $this->setStatus("error");
                    $respuesta->mensaje = $up->getMsg();
                } else {
                    $nomFile = $ur_clave . '_' . $archivo;
                    $nomFileDb = $_FILES[$name]['name'];
                    $countArchivos = 0;

                    foreach (glob($nomDir . $nomFile . '*.pdf') as $nombre_fichero) {
                        $countArchivos++;
                    }

                    if ($up->upload()) {
                        $this->conectar();
                        #la actualizacion de archivos unicamente es permitida para la UR responsable del programa
                        $this->setSql('
                            UPDATE cat_Mir
                            JOIN vin_ProgramasMir USING(mir_id)
                            JOIN enc_UrSubprogramas USING(gpo_id,ur_id,prgs_id)
                            SET mir_archivo' . $archivo . ' = "' . $nomFileDb . '"
                            WHERE prgs_id = ? AND gpo_id = ? AND ur_id = ?
                        ');
                        $parametros = [
                            $_SESSION[_S]['clasAdm-prg'],
                            $gpo,
                            $ur,
                        ];
                        $this->consultar($parametros);
                        $this->desconectar();

                        $this->setStatus("success");

                        $respuesta->mensaje = $borrado . " Archivo cargado con éxito";
                        $respuesta->nombre = $nomFileDb;
                    } else {
                        $this->setStatus("error");
                        $respuesta->mensaje = $up->getMensaje();
                    }
                }
            } else {
                $this->setStatus("error");
                $respuesta->mensaje = "Ocurrio un error al cargar el archivo";
            }
            $this->setValidacion($respuesta);
        } else {
            $this->mixScript('notify("Unicamente la UR responsable tiene permitido actualizar estos archivos","error");');
        }
    }

    private function abcResumenNarrativo()
    {
        $parametros = array();
        $parametros1 = array();
        $sql = '';
        switch ($this->prm->nivel) {
            case 1:
                $sql = "UPDATE cat_Mir SET mir_resumenNarrativo='" . mb_strtoupper($this->prm->resumen, 'utf-8') . "' WHERE mir_id=?;";
                $parametros1 = [
                    $this->mir_id,
                ];
                break;
            case 2:
                $sql = "UPDATE vin_ProgramasMir SET vpm_resumenNarrativo='" . mb_strtoupper($this->prm->resumen, 'utf-8') . "' WHERE vpm_id=?;";
                $parametros1 = [
                    $this->vpm_id,
                ];
                break;
        }

        $ip = ($this->getIp() != "::1") ? $this->getIp() : '127.0.0.1';

        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql(
            'SET @bit_observacion="Resumen Narrativo registrado desde MIR Inicial",
            @usr_id=?,
            @usr_ip=INET_ATON(?);'
        );
        $parametros = [
            $_SESSION[_S]['usr_id'],
            $ip,
        ];
        $c->consultar($parametros);
        $c->setSql($sql);
        $c->consultar($parametros1);

        $this->mixScript('
            notify("Resumen Narrativo guardado exitosamente");
            comunica("' . $this->pantalla . '",{metodo:"pintaPantalla"});
        ');
    }

    private function verHistorial()
    {
        $parametros = array();
        $this->setContenedor('divContenedorGeneralModal');
        $this->conectar();
        $this->setSql('
            SELECT m.*, u.usr_usuario
            FROM cat_MensajeMir m
            INNER JOIN cat_Usuario u USING(usr_id)
            WHERE mir_id = :mir_id' .
            ($this->validador || $this->urResponsable ? '' :
                ' AND (mjm_destinatario = :mjm_dest OR mjm_destinatario = :mjm_destinatario)') . '
            ORDER BY mjm_id DESC;
        ');
        $parametros = [
            ':mir_id' => $this->mir_id,
        ];

        if (!$this->validador && !$this->urResponsable) {
            $parametros[':mjm_dest'] = $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'];
            $parametros[':mjm_destinatario'] = 1;
        }
        $this->consultar($parametros);
        $this->desconectar();
        $remitente = '';
        $mensajes = '';
        $tipo = '';

        while ($m = $this->extraer()) {
            $remitente = ($remitente == '') ? $m['mjm_remitente'] : $remitente;
            if ($remitente != $m['mjm_remitente']) {
                $tipo = ($tipo == '') ? 'reply' : '';
            }
            $mensajes .= '
                <div class="msg ' . $tipo . '">
                    <img src="img/user-2.png">
                    <div class="mensajes">
                        <h3><small>' . $m['mjm_fechaEnvio'] . '</small><label>' . $m['usr_usuario'] . ' </label><span> ' . $m['mjm_asunto'] . '</span></h3>
                        <p>' . $m['mjm_mensaje'] . '</p>
                    </div>
                </div>
            ';
            $remitente = $m['mjm_remitente'];
        }
        $this->mixHtml('
            <div id="myModalHistorial" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: block;">
                <div class="modal-dialog modal-md" style="width:50%;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Historial</h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $mensajes . '</div>
                        </div>
                        <div class="modal-footer">
                            <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixStyle('
            .msg {
                position: relative;
                margin-bottom: 20px;
            }
            .msg>img {
                left: 26px;
                right: auto;
            }
            .msg.reply>img {
                right: 26px;
                left: auto;
            }
            .msg .mensajes {
                color: #707070;
                background: #fff;
                position: relative;
                padding: 15px;
                margin-left: 80px;
                border: 1px solid #ccc;
                border-radius: 3px;
                box-shadow: 0 2px 3px rgba(0,0,0,0.23);
            }
            .reply .mensajes {
                margin-left: 0;
                margin-right: 80px;
            }
            .msg .mensajes:after {
                display: block;
                position: absolute;
                height: 17px;
                width: 11px;
                left: -11px;
                top: 6px;
                content: "";
                background: url("img/content-arrow.png");
            }
            .reply .mensajes:after {
                left: auto;
                right: -11px;
                background: url("img/content-arrow-right.png");
            }

            .msg .mensajes h3, .msg .mensajes h3 label {
                font-size: 12px;
                color: #2e2e2e;
            }
            .msg .mensajes h3 {
                padding: 5px 0;
                padding-top: 0;
                border-bottom: 1px solid #d6d6d6;
            }
            .msg .mensajes h3 span{
                background: none !important;
                height:auto !important;
                float: none !important;
                width: auto !important;
            }
            .msg .mensajes h3 span, .msg .mensajes h3 small {
                font-size: 11px;
                font-weight: normal;
                color: #7a7a7a;
            }
            .msg .mensajes h3 small {
                float: right;
            }
            .msg .mensajes p {
                margin: 15px 0 0 0;
            }
            .msg>img {
                left: 26px;
                right: auto;
            }
            .msg>img {
                display: block;
                position: absolute;
                padding: 5px;
                right: 0;
                border: 1px solid #bdbdbd;
                border-radius: 3px;
            }
        ');
        $this->mixScript('

            $("#myModalHistorial").on("hidden.bs.modal", function (e) { $("#myModalHistorial").remove();});
            $("#close").click(function(){ $("#myModalHistorial").hide(); });
            $("#cerrar").click(function(){ $("#myModalHistorial").remove(); });
            $("#myModalHistorial").modal().show();
        ');
    }

    private function verHistorialCambios()
    {
        $tabla = new stdClass();
        $tabla->id = 'tblHistorialCambios';
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaHistorialCambios';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Fecha y hora';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Usuario';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Detalle del cambio';
        $th->width = '100px';
        array_push($tr, $th);

        $trbody = [];
        //contenido clave
        $td = new stdClass();
        $td->campoTxt = 'usr_fecha';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'bit_observacion';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->setContenedor('divContenedorGeneralModal');

        $this->mixHtml('
            <div id="myModalHistorialCambios" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header"">
                            <h4 class="modal-title" id="myModalLabel">Historial de cambios</h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $this->wrPonTabla($tabla) . '</div>
                        </div>
                        <div class="modal-footer">
                            <button id="cerrar" type="button" class="btn btn-outline-secondary" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript(
            '
            $("#myModalHistorialCambios").on("hidden.bs.modal", function (e) { $("#myModalHistorialCambios").remove();});
            $("#close").click(function(){ $("#myModalHistorialCambios").hide(); });
            $("#cerrar").click(function(){ $("#myModalHistorialCambios").remove(); });
            $("#myModalHistorialCambios").modal().show();
            '
        );

        $this->llenaHistorialCambios();
    }

    private function llenaHistorialCambios()
    {
        $condicion1 = '(bmc.gpo_id = :gpo_id AND bmc.ur_id = :ur_id)';
        $condicion2 = '(vpm.gpo_id = :gpo_id AND vpm.ur_id = :ur_id)';
        $condicion3 = '(bmva.gpo_id= :gpo_id AND bmva.ur_id = :ur_id)';
        $condicion4 = '(bmvc.gpo_id= :gpo_id AND bmvc.ur_id = :ur_id)';
        $condicion = '(bpm.gpo_id = :gpo_id AND bpm.ur_id = :ur_id)';
        $parametros = [':gpo_id'=> $_SESSION[_S]['clasAdm-gpo'], ':ur_id' => $_SESSION[_S]['clasAdm-ur'], ':prg_id' => $_SESSION[_S]['clasAdm-prg']];
        if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 115) {
            $condicion1 .= ' OR (bmc.gpo_id = :gpo AND bmc.ur_id = :ur)';
            $condicion2 .= ' OR (vpm.gpo_id = :gpo AND vpm.ur_id = :ur)';
            $condicion3 .= ' OR (bmva.gpo_id = :gpo AND bmva.ur_id = :ur)';
            $condicion4 .= ' OR (bmvc.gpo_id = :gpo AND bmvc.ur_id = :ur)';
            $condicion .= ' OR (bpm.gpo_id = :gpo AND bpm.ur_id = :ur)';

            $parametros [':ur'] = $_SESSION[_S]['ur_id'];
            $parametros [':gpo'] = $_SESSION[_S]['gpo_id'];
        }

        $sql = "SELECT DATE_FORMAT(bmam.usr_fecha,'%d/%m/%Y %H:%i:%s') AS usr_fecha, bmam.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion
            FROM " . BD_BITACORA . '.bit_MetaActividadMir bmam
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u USING (usr_id)
            WHERE bmam.gpo_id = :gpo_id AND bmam.ur_id = :ur_id
            UNION ALL
            SELECT DATE_FORMAT(bmc.usr_fecha,"%d/%m/%Y %H:%i:%s") AS usr_fecha, bmc.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion
            FROM ' . BD_BITACORA . '.bit_MetaComponente bmc
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u USING (usr_id)
            WHERE bmc.prgs_id = :prg_id
            AND (
                ' . $condicion1 . '
            )
            UNION ALL
            SELECT DATE_FORMAT(bmf.usr_fecha,"%d/%m/%Y %H:%i:%s") AS usr_fecha, bmf.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion
            FROM ' . BD_CTRL . '.cat_Mir
            INNER JOIN ' . BD_CTRL . '.vin_ProgramasMir vpm USING(mir_id)
            LEFT JOIN(
            SELECT * FROM ' . BD_CTRL . '.vin_ProgramaMirIndicador) s USING(vpm_id)
            INNER JOIN ' . BD_BITACORA . '.bit_MetaFin bmf USING(pmi_id)
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u ON u.usr_id = bmf.usr_id
            WHERE prgs_id = :prg_id
            AND (
                ' . $condicion2 . '
            )
            UNION ALL
            SELECT DATE_FORMAT(bmp.usr_fecha,"%d/%m/%Y %H:%i:%s") AS usr_fecha, bmp.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion
            FROM ' . BD_CTRL . '.cat_Mir
            INNER JOIN ' . BD_CTRL . '.vin_ProgramasMir vpm USING(mir_id)
            LEFT JOIN(
            SELECT * FROM ' . BD_CTRL . '.vin_ProgramaMirIndicador) s USING(vpm_id)
            INNER JOIN ' . BD_BITACORA . '.bit_MetaProposito bmp USING(pmi_id)
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u ON u.usr_id = bmp.usr_id
            WHERE prgs_id = :prg_id
            AND (
                ' . $condicion2 . '
            )
            UNION ALL
            SELECT DATE_FORMAT(bmva.usr_fecha,"%d/%m/%Y %H:%i:%s") AS usr_fecha, bmva.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion
            FROM ' . BD_BITACORA . '.bit_MedioVerificacionActividad bmva
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u USING (usr_id)
            WHERE bmva.prgs_id = :prg_id
            AND (
                ' . $condicion3 . '
            )
            UNION ALL
            SELECT DATE_FORMAT(bmvc.usr_fecha,"%d/%m/%Y %H:%i:%s") AS usr_fecha, bmvc.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion
            FROM ' . BD_BITACORA . '.bit_MedioVerificacionComponente bmvc
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u USING (usr_id)
            WHERE bmvc.prgs_id = :prg_id
            AND (
                ' . $condicion4 . '
            )
            UNION ALL
            SELECT DATE_FORMAT(bmvpm.usr_fecha,"%d/%m/%Y %H:%i:%s") AS usr_fecha, bmvpm.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion 
            FROM 
            ' . BD_CTRL . '.cat_Mir
            INNER JOIN ' . BD_CTRL . '.vin_ProgramasMir vpm USING(mir_id)
            LEFT JOIN(
            SELECT * FROM ' . BD_CTRL . '.vin_ProgramaMirIndicador) s USING(vpm_id)
            INNER JOIN ' . BD_BITACORA . '.bit_MedioVerificacionProgramaMir bmvpm USING(pmi_id)
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u ON u.usr_id = bmvpm.usr_id
            WHERE prgs_id = :prg_id 
            AND (
                ' . $condicion2 . '
            )
            UNION ALL
            SELECT DATE_FORMAT(bpm.usr_fecha,"%d/%m/%Y %H:%i:%s") AS usr_fecha, bpm.usr_fecha as fecha_orden, u.usr_usuario, bit_observacion 
            FROM ' . BD_BITACORA . '.bit_ProgramasMir bpm
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u USING (usr_id)
            WHERE bpm.prgs_id = :prg_id 
            AND (
                ' . $condicion . '
            )
            ORDER BY fecha_orden DESC
            ;';
        $this->setParams($parametros);

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblHistorialCambios';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
    }

    //Función para visualizar PDF
    private function download()
    {
        $responsable = '';
        $this->conectar();
        $this->setSql('
            SELECT gpo_id,ur_id
            FROM vin_ProgramasMir
            WHERE prgs_id=?
            ;
        ');
        $this->consultar([$_SESSION[_S]['clasAdm-prg']]);
        $responsable = $this->extraer();
        $this->desconectar();

        $fichero = '';
        $base = '';
        if (isset($this->prm->lnk)) {
            $tmp = explode("/", $this->prm->lnk);
            $base = $tmp[0] . "/" . $tmp[1] . "/" . $tmp[2];
            $fichero = explode("?", $tmp[3])[0];
        }

        $r = new RutaArchivoSefip();
        $r->setBase("POA");
        $r->setPath($base);
        $r->setAnioFichero(APP_EJERCICIO);
        $r->setUrFichero($responsable['gpo_id'] . $responsable['ur_id']);
        $ruta = $r->getRutaMod();

        $ruta .= $fichero;

        $dest = __DIR__ . "/../../tmps/" . $fichero;
        copy($ruta, $dest);
        $this->mixScript(' $("#det_fsvis1").attr("src","lib/file_download.php?r=' . $fichero . '");');
    }

    //FUNCIÓN PARA VALIDAR SI LA UR YA REGISTRÓ UNA META ACTIVIDAD
    private function existeMetaMir($pii_id)
    {
        $parametros = array();
        $gpo = $_SESSION[_S]['clasAdm-gpo'];
        $ur = $_SESSION[_S]['clasAdm-ur'];

        $this->conectar();
        $this->setSql('
            SELECT COUNT(macm_id) metas
            FROM cat_MetaActividadMir
            WHERE pii_id=?
            AND gpo_id=?
            AND ur_id=?;
        ');
        $parametros = [
            $pii_id,
            $gpo,
            $ur,
        ];
        $this->consultar($parametros);
        $datos = $this->extraer();
        $this->desconectar();
        return ($datos['metas'] > 0);
    }

    public function existenMedios() {
        $parametros = [];
        $this->conectar();
        switch ($this->prm->nim_id) {
            case 1:
                $this->setSql('
                    SELECT COUNT(mvpm_id) medios
                    FROM cat_MetaFin mf
                    INNER JOIN vin_ProgramaMirIndicador USING (pmi_id)
                    INNER JOIN vin_ProgramasMir USING(vpm_id)
                    INNER JOIN cat_MedioVerificacionProgramaMir USING (vpm_id,pmi_id,nim_id)
                    WHERE prgs_id= ? AND pmi_id= ?
                    AND nim_id = ?
                    AND mvpm_trimestreCierre = 0;
                ');
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    $this->prm->eri_id,
                    $this->prm->nim_id
                ];
                break;
            case 2:
                $this->setSql('
                    SELECT COUNT(mvpm_id) medios
                    FROM cat_MetaProposito mp
                    INNER JOIN vin_ProgramaMirIndicador USING (pmi_id)
                    INNER JOIN vin_ProgramasMir USING(vpm_id)
                    INNER JOIN cat_MedioVerificacionProgramaMir USING (vpm_id,pmi_id,nim_id)
                    WHERE prgs_id= ? AND pmi_id= ?
                    AND nim_id = ?
                    AND mvpm_trimestreCierre = 0;
                ');
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    $this->prm->eri_id,
                    $this->prm->nim_id
                ];
                break;
            case 3:
                $this->setSql('
                    SELECT COUNT(mvcm_id) medios
                    FROM cat_MetaComponente M
                    INNER JOIN enc_ProductoFinalIndicador USING (epfi_id)
                    INNER JOIN enc_UrSubprogramas USING (gpo_id, ur_id, prgs_id, sprs_id)
                    INNER JOIN cat_MedioVerificacionComponente USING (gpo_id,ur_id,prgs_id, sprs_id, epfi_id)
                    WHERE prgs_id = ? AND epfi_id= ?
                    AND gpo_id = ?
                    AND ur_id = ?
                    AND mco_trimestreCierre = 0;
                ');
                $parametros = [
                    $_SESSION[_S]['clasAdm-prg'],
                    $this->prm->eri_id,
                    $_SESSION[_S]['clasAdm-gpo'],
                    $_SESSION[_S]['clasAdm-ur']
                ];
                break;
            case 4:
                $this->setSql('
                    SELECT COUNT(mvac_id) medios
                    FROM cat_MedioVerificacionActividad
                    WHERE gpo_id = ? AND ur_id = ? 
                    AND prgs_id = ? AND sprs_id = ? AND pdf_id = ? AND pdi_id = ?
                    AND pii_id = ? AND mvac_trimestreCierre = 0
                    GROUP BY gpo_id, ur_id;');
                $parametros = [
                    $_SESSION[_S]['clasAdm-gpo'],
                    $_SESSION[_S]['clasAdm-ur'],
                    $_SESSION[_S]['clasAdm-prg'],
                    $this->prm->com_id,
                    $this->prm->pdf_id,
                    $this->prm->pdi_id,
                    $this->prm->eri_id,
                ];
                break;
        }

        $this->consultar($parametros);
        $this->desconectar();
        $mediosVerificacion = $this->extraer();
        return (int) ($mediosVerificacion['medios'] ?? 0);

    }
}

if (isset($_SESSION[_S]['prm']['A']['12']['2'])) {
    new ControladorMir();
} else {
    WebResponse::sinPermisos();
}
