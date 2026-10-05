<?php

/* --------------------------------------------------------------------------------------------------------
Objetivo: MATRIZ DE INDICADOR DE RESULTADOS, por nivel (fin, proposito, componente y actividad), agrega supuestos e indicadores
Elaboró : Anuar López Morgan, Zuli D. López Barbosa, Soledad López Velasco
-------------------------------------------------------------------------------------------------------- */

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/Indicador.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaProposito.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoModificacionMetaProposito.php';
require_once dirname(__FILE__) . '/../../ent/HistorialMensajes.php';
set_time_limit(0);

class ControladorMirAvanceProposito extends WebResponse
{
    const VALOR_DEFECTO = '0.0000';

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        #si tiene el permiso de lectura le permitimos pasar
        if (isset($_SESSION[_S]['prm']['A']['10']['4']['1']) || isset($_SESSION[_S]['prm']['A']['10']['4']['2'])) {

            switch ($this->metodo) {
                case 'pintaPantalla':
                    $this->pintaPantalla();
                    break;
                case 'verMetas':
                    $this->verMetas();
                    break;
                case 'listaIndicador':
                    $this->listaIndicador();
                    break;
                case 'addModMeta':
                    $this->addModMeta();
                    break;
                case 'abcMeta':
                    $this->abcMeta();
                    break;
                case 'main':
                    $this->main();
                    break;
                default:
                    $this->main();
                    break;
            }
            $this->returnJson();
        } else {
            $this->sinPermisos();
        }

    }
    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
    ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function main()
    {
        $this->mixStyle('
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .modal-header{padding:8px!important; background: #E5D299; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:black; opacity: 0.8;};
        ');

        $clasAdmin = new stdClass();

        $clasAdmin->comboUe = false;
        $clasAdmin->comboSpr = false;
        $clasAdmin->comboPy = false;
        $clasAdmin->comboOa = false;
        $clasAdmin->change = '
            if($("#comboprg").val()!= ""){
                if($("#comboprg").val() * 1 >= 101 && $("#comboprg").val() <= 900 ){
                    comunica("' . $this->pantalla . '", {
                        metodo:"pintaPantalla"
                    });
                }
                if( $("#comboprg").val() * 1 >= 0 && $("#comboprg").val()<= 100  ) {
                    comunica("' . $this->pantalla . '", {
                        metodo:"listaIndicador"
                    });
                }
                if($("#comboprg").val()* 1 >= 901 && $("#comboprg").val() <= 999 ){
                    comunica("' . $this->pantalla . '", {metodo:"listaIndicador"});
                }
                $("#clasAdminSeccionCombos").hide();
            }$("#contenedor").html("<p class=\'text-danger\'>Seleccionar un Programa</p>");
        ';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'btn btn-outline-secondary';
        $btnregresarindex->icon = 'fa-solid fa-circle-left me-1';
        $btnregresarindex->click = 'comunica("' . $this->pantalla . '",{ metodo:"main"});';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin) . '
            comunica("' . $this->pantalla . '", {
                metodo:"pintaPantalla"
            });
        ');
    }

    private function listaIndicador()
    {
        $btnregresar = new stdClass();
        $btnregresar->id = 'btnregresar';
        $btnregresar->name = 'btnregresar';
        $btnregresar->text = 'Regresar';
        $btnregresar->value = 'Regresar';
        $btnregresar->type = 'button';
        $btnregresar->class = 'btn btn-test ';
        $btnregresar->icon = 'glyphicon glyphicon-hand-left';
        $this->agregaTools($this->ponBoton($btnregresar));

        $this->mixHtml('
            <fieldset class="col-md-12 col-xs-12">
                <div class="alert alert-info alert-dismissable" align="center">
                    <strong>NO CUENTA CON NIGUN INDICADOR</strong>
                </div>
            </fieldset>
        ');

        $this->mixScript('
            $("#btnregresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaPantalla",
                    prg0:1
                });
            });
        ');
    }

    private function pintaPantalla()
    {
        
        if (empty($_SESSION[_S]['clasAdm-prg']) || (isset($this->prm->prg0) && $this->prm->prg0 == 1)) {
            $prg_id = '';
            $params = [
                ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
                ':ur'  => $_SESSION[_S]['clasAdm-ur'],
            ];
        } else {
            $prg_id = ' AND prgs_id = :prgs ';
            $params[':prgs'] = $_SESSION[_S]['clasAdm-prg'];
        }

        $this->conectar();
        $this->setSql('
            SELECT ind_id, ind_nombreMIR, frm_nombre,pmi_id AS eri_id,prgs_id,prgs_nombre
            FROM vin_ProgramasMir
            INNER JOIN vin_UrProgramaMir USING (vpm_id,gpo_id,ur_id,prgs_id)
            JOIN enc_UrSubprogramas E USING(gpo_id,ur_id,prgs_id)
            INNER JOIN cat_Programas p USING(prgs_id)
            INNER JOIN vin_ProgramaMirIndicador USING(vpm_id)
            INNER JOIN cat_MetaProposito USING(pmi_id)
            INNER JOIN cat_Indicador USING(nim_id,ind_id)
            INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            WHERE emi_id=9 AND gpo_id = :gpo AND ur_id = :ur
            ' . $prg_id . '
            AND pmi_id IS NOT NULL
            AND nim_id=2
            GROUP BY prgs_id,vpm_id, ind_id,nim_id
            ORDER BY prgs_id;
        ');

        $this->consultar($params);
        $this->desconectar();
        $htm = '<table  class="table table table-bordered table-hover">';

        $prg = '';
        $clase = 'class="warning"';

        $htm .= '
            <tr>
                <th class="warning" align="center">VER</th>
                <th class="warning" align="center">ID INDICADOR</th>
                <th class="warning" align="center">NOMBRE INDICADOR</th>
                <th class="warning" align="center">FRECUENCIA</th>
            </tr>
        ';
        while ($row = $this->extraer()) {

            if ($prg != $row['prgs_id']) {
                $htm .= '
                    <tr class="warning">
                        <td ' . $clase . ' align="left" colspan="4">PRG: ' . $row['prgs_id'] . ' - ' . $row['prgs_nombre'] . '</td>
                    </tr>';
            }

            $htm .= '
                <tr>
                    <td class="warning" align="center" colspan="1">
                        <button type="button" id="verMeta" icon="eye-open" title="Ver Meta del Indicador" class="verMeta btn btn-outline-secondary"  eri_id=' . $row['eri_id'] . '>
                            <i class="fa fa-eye"></i>
                        </button>
                    </td>
                    <td align="center" colspan="1">' . $row['ind_id'] . '</td>
                    <td align="left" colspan="1">' . $row['ind_nombreMIR'] . '</td>
                    <td align="center" colspan="1">' . $row['frm_nombre'] . '</td>
                </tr>
                <tr >
                    <th class="warning" ></th>
                    <th class ="divMetas" id="metas' . $row['eri_id'] . '" colspan = "4" ></th>
                </tr>
            ';

            $prg = $row['prgs_id'];
        }

        $htm .= '</table>';

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verMeta").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    eri_id:$(this).attr("eri_id")
                });
            });
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->eri_id . '');
        $params = [
            ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur'  => $_SESSION[_S]['clasAdm-ur'],
            ':pmi' => $this->prm->eri_id,
        ];
        $this->conectar();
        $this->setSql('
            SELECT mpr_descripcion, mpr_original, mpr_lb AS mem_anio,
            CASE WHEN umi_id IN (3,5,6) THEN mpr_lbIndicador ELSE mpr_original END AS mem_valor,
            mpr_original AS mem_valorDeseado1, mpr_lbIndicador,mpr_anteriorIndicador, mpr_modificado,mpr_alcanzado,mpr_id,vpm_id,umi_id,umi_nombre
            FROM cat_MetaProposito
            INNER JOIN vin_ProgramaMirIndicador V USING (pmi_id)
            INNER JOIN vin_UrProgramaMir USING (vpm_id)
            INNER JOIN cat_Indicador USING(nim_id, ind_id)
            INNER JOIN cat_UnidadMedidaIndicador using(umi_id)
            WHERE gpo_id = :gpo AND ur_id = :ur
            AND emi_id=9 AND pmi_id = :pmi;
        ');
        $this->consultar($params);

        while ($meta = $this->extraer()) {

            $this->mixHtml('
                <tr class="warning">
                    <th align="center">
                        <button type="button" id="verMetaInd" icon="eye-open" title="Ver Meta" class="verMetaInd btn btn-outline-secondary"  eri_id=' . $this->prm->eri_id . ' mem_id=' . $meta['mpr_id'] . ' vum_id=' . $meta['vpm_id'] . '>
                            <i class="fa fa-eye"></i>
                        </button>
                    </th>
                    <th colspan = 12>
                        <table class="table table-hover table-bordered">
                            <tr>
                                <th class="warning" colspan = 12>META</th>
                            </tr>
                            <tr>
                                <th class="warning" >Descripcion:</th><th colspan = 11>' . $meta['mpr_descripcion'] . '</th>
                            </tr>
                            <tr>
                                <th class="warning" >Unidad de Medida:</th><th colspan = 11>' . $meta['umi_id'] . ' - ' . $meta['umi_nombre'] . '</th>
                            </tr>
                            <tr>
                                <td class="warning" >Linea de base:</td><td colspan = 5>' . $meta['mem_anio'] . ' - ' . $meta['mpr_lbIndicador'] . '</td>
                                <td class="warning" >Valor deseado:</td><td colspan = 2>' . $meta['mpr_anteriorIndicador'] . ' </td>
                            </tr>
                        </table>
                    </th>
                </tr>
            ');
        }
        $this->desconectar();

        $this->mixScript('
            $(".verMetaInd").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addModMeta",
                    eri_id:$(this).attr("eri_id"),
                    mem_id:$(this).attr("mem_id"),
                    pmi_id:$(this).attr("eri_id"),
                    mpr_id:$(this).attr("mem_id"),
                    vum_id:$(this).attr("vum_id")
                });
            });
        ');
    }

    private function obtenerInd()
    {
        $this->conectar();
        $this->setSql('
            SELECT  ind_nombre, ind_definicion, frm_id, umi_id, nim_id, ind_definicionMIR,
            IF(tiv_id=2,"S","C") tiv_id,umi_id, umi_nombre,ind_formulaCalculo
            FROM cat_Indicador
            INNER JOIN vin_ProgramaMirIndicador USING (nim_id,ind_id)
            INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
            INNER JOIN cat_MetaProposito USING(pmi_id)
            WHERE   nim_id = 2 and pmi_id = :eri;');

        $this->consultar(['eri' => $this->prm->eri_id]);
        $this->desconectar();
        

        return $this->extraer();
    }

    private function obtenerHistorico()
    {
        $historicometa = new AccesoDatos();
        $historicometa->conectar();
        $historicometa->setSql('
            SELECT h.pmi_id, h.mpr_id, h.tri_id, h.mpr_numeradorT1, h.mpr_denominadorT1, m.mpr_descripcion, m.mpr_lb, m.mpr_lbValor, m.mpr_lbIndicador,
            m.mpr_anterior, m.mpr_anteriorValor, m.mpr_anteriorIndicador, m.mpr_anio, m.mpr_original, m.mpr_modificado, m.mpr_tipoCalendario, m.mpr_trimestreCierre
            FROM aux_HistoricoModificacionMetaProposito h
            JOIN cat_MetaProposito m ON h.pmi_id = m.pmi_id AND h.mpr_id = m.mpr_id
            WHERE h.pmi_id = :eri
            AND h.mpr_id = :mpr
            AND h.tri_id = 4;
        ');
        $params = [':eri' => $this->prm->eri_id, ':mpr' => $this->prm->mpr_id];
        $historicometa->consultar($params);
        $historicometa->desconectar();

        $datosHistorico = $historicometa->extraer();
        $numRegHistoricoMeta = $historicometa->getNumeroRegistros();

        if ($numRegHistoricoMeta == 0 || $datosHistorico === false) {
            $datosHistorico = [
                'mpr_numeradorT1' => 0,
                'mpr_denominadorT1' => 0,
            ];
        }

        return $datosHistorico;
    }

    private function obtenerCaptura()
    {
        $params = [
            ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur'  => $_SESSION[_S]['clasAdm-ur'],
        ];
        $captura = new AccesoDatos();
        $captura->conectar();
        $captura->setSql('
            SELECT gpo_id,ur_id,
            IF(tru_fechaApertura<=NOW(),IF(NOW()<=tru_fechaCierre,1,0),0) trimestre4,etr_id1 AS estadotrimestre
            FROM mir_TrimestreUr
            WHERE gpo_id = :gpo AND ur_id = :ur
            AND nim_id=2 AND  tri_id=4
            GROUP BY gpo_id,ur_id;
        ');
        $captura->consultar($params);
        $captura->desconectar();

        return $captura->extraer();
    }

    private function addModMeta()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $datosInd = $this->obtenerInd();
        $meta = new MetaProposito($this->prm);
        $datosCaptura = $this->obtenerCaptura();
        $datosHistorico = $this->obtenerHistorico();

        $datosTrimestre = $this->calculaTrimestre4($meta, $datosCaptura, $datosHistorico, $datosInd['umi_id'], $datosInd['frm_id']);

        $tablas = $this->construyeTablasIndicador($datosInd['umi_id'], $meta, $datosHistorico, $datosTrimestre);

        $form = $this->armaFormularioMeta($meta, $datosInd['umi_id'], $datosTrimestre['trimAbierto']);
        $form->mixHtml($this->construyeHtmlDefinicion($meta, $datosInd) . $this->construyeHtmlTablas($tablas));

        $this->mixHtml($this->construyeModal($form, $datosTrimestre['banGuardar']));
        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });

            var alcanzadoA=0;
            switch(' . $datosInd['umi_id'] . '){
                case 2: case 4:
                    $("#AlcTrimestre4,#AlcTrimestre44").blur(function(){
                        if($("#AlcTrimestre44").val()!=0){
                            if(' . $datosInd['umi_id'] . ' == 2){
                                alcanzadoA= ( unformatNumber($("#AlcTrimestre4").val()) / unformatNumber($("#AlcTrimestre44").val()) ) * 100;
                            }else{
                                alcanzadoA=( unformatNumber($("#AlcTrimestre4").val()) / unformatNumber($("#AlcTrimestre44").val()) );
                            }
                            $("#alcAnual").val(alcanzadoA.toFixed(4));
                        }else{
                            $("#alcAnual").val(0);
                        }
                    });
                    break;
            }
        ');
    }

    private function calculaTrimestre4($meta, $datosCaptura, $datosHistorico, $umi_id, $frm)
    {
        $alcTrimestre4 = $this->creaInputNumero('AlcTrimestre4', true);
        $alcTrimestre44 = $this->creaInputNumero('AlcTrimestre44', false, true);

        $banGuardar = false;
        $trimAbierto = '';
        $seAbreTrimestre = ($datosCaptura['trimestre4'] == 1 && $datosCaptura['estadotrimestre'] == 'A') && $frm >= 3;

        if (!$seAbreTrimestre) {
            $tri4 = ($umi_id == 2 || $umi_id == 4) ? $meta->getAlcNumerador() : $meta->getAlcanzado();
            $tri44 = $meta->getAlcDenominador();
        } else {
            $alcTrimestre4->value = ($umi_id == 2 || $umi_id == 4) ? $meta->getAlcNumerador() : $meta->getAlcanzado();
            $tri4 = $this->ponInput($alcTrimestre4);

            $alcTrimestre44->value = $datosHistorico['mpr_denominadorT1'] ?? $meta->getModificado();
            $tri44 = $this->ponInput($alcTrimestre44);

            $banGuardar = true;
            $trimAbierto = 4;
        }

        $alcAnual = new stdClass();
        $alcAnual->required = true;
        $alcAnual->id = 'alcAnual';
        $alcAnual->enDiv = true;
        $alcAnual->formato = 'numero';
        $alcAnual->disabled = true;
        $alcAnual->value = $meta->getAlcanzado();

        $alcanzado = $this->ponInput($alcAnual);

        return [
            'tri1' => '0.00',
            'tri2' => '0.00',
            'tri3' => '0.00',
            'tri4' => $tri4,
            'tri44' => $tri44,
            'alcanzado' => $alcanzado,
            'banGuardar' => $banGuardar,
            'trimAbierto' => $trimAbierto,
        ];
    }

    private function construyeTablasIndicador($umi_id, $meta, $datosHistorico, $datosTrimestre)
    {
        switch ($umi_id) {
            case 5:
                return $this->tablasTasa($meta, $datosHistorico, $datosTrimestre);
            case 2:
            case 4:
                return $this->tablasPorcentajeRazon($umi_id, $meta, $datosHistorico, $datosTrimestre);
            default:
                return $this->tablasDefault($meta, $datosHistorico, $datosTrimestre);
        }
    }

    private function calculaVariacion($actual, $base)
    {
        if ($base == 0) {
            return self::VALOR_DEFECTO;
        }
        return (($actual / $base) - 1) * 100;
    }

    private function tablasTasa($meta, $datosHistorico, $dt)
    {
        $resultado = $this->calculaVariacion($meta->getAnteriorValor(), $meta->getLbValor());
        $res = $this->calculaVariacion($meta->getOriTrimestre4(), $meta->getAnteriorValor());
        $result = $this->calculaVariacion($datosHistorico['mpr_numeradorT1'], $meta->getAnteriorValor());
        $resultado2 = $this->calculaVariacion($meta->getAlcanzado(), $meta->getAnteriorValor());

        $trLineaBase = '
            <table class="table table-bordered table-striped">
                <thead><tr><th>T1</th><th>Valor A. T-1</th><th>Año T0</th><th>Valor A. T0</th><th>Valor Indicador</th></tr></thead>
                <tbody><tr><td>2017</td><td>' . $meta->getLbValor() . '</td><td>2018</td><td>' . $meta->getAnteriorValor() . '</td><td>' . $resultado . '</td></tr></tbody>
            </table>
            <table class="table table-bordered table-striped">
                <thead><tr><th>AÑO T0</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor A. T0</th></tr></thead>
                <tbody><tr><td>2018</td><td>0.00 </td><td>0.00 </td><td>0.00 </td><td>' . $meta->getOriTrimestre4() . '</td><td>' . $meta->getAnteriorValor() . '</td></tr></tbody>
            </table>
        ';

        $trProgramado = '
            <tr><th>Año T1</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor A. I1</th><th>Valor Indicador</th></tr>
            <tr><td>2018</td><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $meta->getOriTrimestre4() . '</td><td>' . $meta->getOriTrimestre4() . '</td><td>' . $res . '</td></tr>
        ';

        $trActualizado = '
            <tr><th>Año T1</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor A. I1</th><th>Valor Indicador</th></tr>
            <tr>
                <td>2019</td><td>0.00</td><td>0.00</td><td>0.00</td>
                <td>' . ($datosHistorico['mpr_numeradorT1'] ?? $meta->getModificado()) . '</td>
                <td>' . ($datosHistorico['mpr_denominadorT1'] ?? $meta->getModificado()) . '</td>
                <td>' . $result . '</td>
            </tr>
        ';

        $trAlcanzado = '
            <tr><th>Año T1</th><th>1° Trimestre </th><th>2° Trimestre </th><th>3° Trimestre </th><th>4° Trimestre </th><th>Valor A. I1</th><th>Valor Indicador</th></tr>
            <tr>
                <td>2019</td><td>0.00</td><td>0.00</td><td>0.00</td>
                <td>' . $dt['tri4'] . '</td><td>' . $meta->getAlcanzado() . '</td><td>' . $resultado2 . '</td>
            </tr>
        ';

        return compact('trLineaBase', 'trProgramado', 'trActualizado', 'trAlcanzado');
    }

    private function tablasPorcentajeRazon($umi_id, $meta, $datosHistorico, $dt)
    {
        $operacion = $meta->getNumerador() / $meta->getDenominador();

        if ($meta->getDenominador() == 0) {
            # code...
            $divpr = self::VALOR_DEFECTO;
        } else {
            $divpr = ($umi_id == 2 ? ($operacion * 100) : $operacion);
        }

        $style = 'style="vertical-align:middle;"';

        $trLineaBase = '
            <table class="table table-bordered table-striped">
                <thead><tr><th>Año</th><th>Numerador</th><th>Denominador</th><th>Valor LB</th></tr></thead>
                <tbody><tr><td>' . $meta->getLb() . '</td><td>' . $meta->getNumerador() . '</td><td>' . $meta->getDenominador() . '</td><td>' . $divpr . '</td></tr></tbody>
            </table>
        ';

        $valorAnualNum = $meta->getNumTrimestre4();
        $valorAnualDen = $meta->getDenTrimestre4();
        $division = $valorAnualNum / $valorAnualDen;
        $condicion2 = $umi_id == 2 ? ($division * 100) : $division;
        $valorIndicadorProgramado = $valorAnualDen == 0 ? 0 : $condicion2;

        $trProgramado = '
            <tr><th rowspan="2" ' . $style . '>NUMERADOR</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor Anual</th><th rowspan="2" ' . $style . '>Valor Indicador</th></tr>
            <tr><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $meta->getOriTrimestre4() . '</td><td>' . $valorAnualNum . '</td></tr>
            <tr><th rowspan="2" ' . $style . '>DENOMINADOR</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor Anual</th><th rowspan="2" ' . $style . '>' . $valorIndicadorProgramado . '</th></tr>
            <tr><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $meta->getDenTrimestre4() . '</td><td>' . $valorAnualDen . '</td></tr>
        ';

        $numActualizado = $datosHistorico['mpr_numeradorT1'] ?? $meta->getNumTrimestre4();
        $denActualizado = $datosHistorico['mpr_denominadorT1'] ?? $meta->getNumTrimestre4();
        if ($umi_id == 2) {
            # code...
            $valorIndicadorActualizado = ($datosHistorico['mpr_denominadorT1'] != 0 ? (($datosHistorico['mpr_numeradorT1'] / $datosHistorico['mpr_denominadorT1']) * 100) : 0);
        } else {
            $valorIndicadorActualizado = ($meta->getDenTrimestre4() != 0 ? ($division) : '0');
        }

        $trActualizado = '
            <tr><th rowspan="2" ' . $style . '>NUMERADOR</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor Anual</th><th rowspan="2" ' . $style . '>Valor Indicador</th></tr>
            <tr><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $numActualizado . '</td><td>' . $numActualizado . '</td></tr>
            <tr><th rowspan="2" ' . $style . '>DENOMINADOR</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor Anual</th><th rowspan="2" ' . $style . '>' . $valorIndicadorActualizado . '</th>
            <tr><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $denActualizado . '</td><td>' . $denActualizado . '</td></tr>
        ';

        $trAlcanzado = '
            <tr><th rowspan="2" ' . $style . '>NUMERADOR</th><th>1° Trimestre </th><th>2° Trimestre </th><th>3° Trimestre </th><th>4° Trimestre </th><th>Valor Anual</td><th rowspan="2" ' . $style . '>Valor Indicador</td></tr>
            <tr><td>' . $dt['tri1'] . '</td><td>' . $dt['tri2'] . '</td><td>' . $dt['tri3'] . '</td><td>' . $dt['tri4'] . '</td><td>' . $meta->getAlcNumerador() . '</td></tr>
            <tr><th rowspan="2" ' . $style . '>DENOMINADOR</th><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre </th><th>Valor Anual</th><th rowspan="2" ' . $style . '>' . $dt['alcanzado'] . '</th></tr>
            <tr><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $dt['tri44'] . '</td><td>' . $dt['tri44'] . '</td></tr>
        ';

        return compact('trLineaBase', 'trProgramado', 'trActualizado', 'trAlcanzado');
    }

    private function tablasDefault($meta, $datosHistorico, $dt)
    {
        $trLineaBase = '
            <table class="table table-bordered table-striped">
                <thead><tr><th>Año</th><th>Valor Anual</th></tr></thead>
                <tbody><tr><td>' . $meta->getLb() . '</td><td>' . $meta->getLbValor() . '</td></tr></tbody>
            </table>
        ';

        $trProgramado = '
            <tr><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor Indicador</th></tr>
            <tr><td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $meta->getOriginal() . '</td></tr>
        ';

        $valorActualizado = $datosHistorico['mpr_numeradorT1'] ?? $meta->getModificado();
        $trActualizado = '
            <tr><th>1° Trimestre</th><th>2° Trimestre</th><th>3° Trimestre</th><th>4° Trimestre</th><th>Valor Indicador</th></tr>
            <tr><td>0.00</td><td>0.00</td><td>0.00</td><td>' . $valorActualizado . '</td><td>' . $valorActualizado . '</td></tr>
        ';

        $trAlcanzado = '
            <tr><th>1° Trimestre </th><th>2° Trimestre </th><th>3° Trimestre </th><th>4° Trimestre </th><th>Valor Indicador</th></tr>
            <tr><td>' . $dt['tri1'] . '</td><td>' . $dt['tri2'] . '</td><td>' . $dt['tri3'] . '</td><td>' . $dt['tri4'] . '</td><td>' . $meta->getAlcanzado() . '</td></tr>
        ';

        return compact('trLineaBase', 'trProgramado', 'trActualizado', 'trAlcanzado');
    }

    private function armaFormularioMeta($meta, $umi_id, $trimAbierto)
    {
        $form = new stdClass();
        $form->id = 'metaInd';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMeta';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'c';
        $form->datosExtra->nim_id = '3';
        $form->datosExtra->eri_id = $meta->getPmi_id();
        $form->datosExtra->vum_id = $meta->getPmi_id();
        $form->datosExtra->mem_id = $meta->getMpr_id();
        $form->datosExtra->trimestreAbierto = (string) $trimAbierto;
        $form->datosExtra->alcAnual = '"{$("#alcAnual").val()}"';
        $form->datosExtra->umi_id = $umi_id;
        $form->datosExtra->AlcTrimestre44 = '"{$("#AlcTrimestre44").val()}"';

        return new form($form);
    }

    private function construyeHtmlDefinicion($meta, $datosInd)
    {
        return '
            <fieldset class="col-md-12 col-xs-12">
                <table class="table table-bordered">
                    <thead>
                        <tr style="background-color: #eaeaea"><th>Definición de variable</th></tr>
                        <tr><td>' . $meta->getDescripcion() . '</td></tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #eaeaea"><th>Unidad de medida</th></tr>
                        <tr><td>' . $datosInd['umi_id'] . ' - ' . $datosInd['umi_nombre'] . '</td></tr>
                        <tr style="background-color: #eaeaea"><th>Método de cálculo</th></tr>
                        <tr><td>' . $datosInd['ind_formulaCalculo'] . '</td></tr>
                    </tbody>
                </table>
            </fieldset>
        ';
    }

    private function construyeHtmlTablas($tablas)
    {
        return '
            <fieldset class="col-md-12 col-xs-12">
                <legend><small>Línea Base</small></legend>
                ' . $tablas['trLineaBase'] . '
                <table class="table table-bordered">
                    <tbody>
                        <tr><td style="background-color: #eaeaea" colspan="7"><b>PROGRAMADO</b></td></tr>
                        ' . $tablas['trProgramado'] . '
                        <tr><td style="background-color: #eaeaea" colspan="7"><b>ACTUALIZADO</b></td></tr>
                        ' . $tablas['trActualizado'] . '
                        <tr><td style="background-color: #eaeaea" colspan="7"><b>ALCANZADO</b></td></tr>
                        ' . $tablas['trAlcanzado'] . '
                    </tbody>
                </table>
            </fieldset>
        ';
    }

    private function construyeModal($form, $banGuardar)
    {
        $botonGuardar = $banGuardar
            ? '<button type="submit" class="btn btn-outline-success" id="saveMeta" form="metaInd">Guardar</button>'
            : '';

        return '
            <div id="myModal" class="modal fade modal-al80porciento">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">NIVEL PROPÓSITO</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $form->ponForm() . '</div>
                            <div class="modal-footer">
                                ' . $botonGuardar . '
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    private function abcMeta()
    {
        $datosMeta = new MetaProposito();
        $datosMeta->setPmi_id($this->prm->eri_id);
        $datosMeta->setMpr_id($this->prm->mem_id);
        $datosMeta->select();

        $consulta = '';
        $consulta2 = '';
        $params = [];

        if ($this->prm->umi_id == 2 || $this->prm->umi_id == 4) {
            $this->prm->Alcanzado = $this->prm->alcAnual;
        } else {
            $this->prm->Alcanzado = $this->prm->AlcTrimestre4;
        }

        if ($this->prm->umi_id == 2 || $this->prm->umi_id == 4) {
            $consulta = ' mprt_alcanzado = :mprt_alc ';
            $params[':mprt_alc'] = $this->prm->alcAnual;
        } else {
            $consulta = ' mprt_alcanzado = :mprt_alc ';
            $params[':mprt_alc'] = $this->prm->AlcTrimestre4;
        }

        $this->prm->AlcTrimestre44 = ($this->prm->AlcTrimestre44 === 'undefined' || $this->prm->AlcTrimestre44 === '')
            ? self::VALOR_DEFECTO
            : $this->prm->AlcTrimestre44;

        if (isset($this->prm->AlcTrimestre44)) {
            $this->prm->Alcanzado2 = $this->prm->AlcTrimestre44;
            $consulta2 = ',mprt_alcanzadodenominador = :alcDen, mprt_alcanzadoNumerador = :alcNum ';
            $params[':alcDen'] = $this->prm->AlcTrimestre44;
            $params[':alcNum'] = $this->prm->AlcTrimestre4;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $this->conectar();
        $this->setSql('SET @mip =INET_ATON(:ip);');
        $this->consultar([':ip' => $ip]);

        $this->setSql('
            UPDATE cat_MetaProposito
            INNER JOIN cat_MetaPropositoTrimestre USING (pmi_id)
            SET mpr_alcanzado = :alcanzado,
            mpr_capturaAvance = 1,
            ' . $consulta .
            $consulta2 . '
            WHERE pmi_id = :pmi
        ');
        $params[':alcanzado'] = $this->prm->Alcanzado;
        $params[':pmi'] = $this->prm->eri_id;
        $this->consultar($params);
        $this->desconectar();

        $this->mixScript('
            notify("Guardado exitosamente");
            $("#myModal,#modalDeTablatbl-metasIndicador").modal("hide");
        ');
    }
}

if (isset($_SESSION[_S]['prm']['A']['10']['4'])) {
    $controlador  = new ControladorMirAvanceProposito();
} else {
    WebResponse::sinPermisos();
}
