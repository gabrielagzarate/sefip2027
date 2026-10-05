<?php

session_start();
date_default_timezone_set('America/Mexico_City');

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

class ControladorMovimientoContable extends WebResponse
{
    public function __construct()
    {
        //Constructor
        parent::__construct();
        /*
         *
         *
        //Se indica la pantalla de manera explicita, el controlador Web Response, no la genera correctamente
         */
        $this->pantalla = 'scp/Consultas/ControladorMovimientoContable.php';

        switch ($this->metodo) {
            case 'main':
                $this->main();
                break;
            case 'cargaCuentas':
                $this->cargaCuentas();
                break;
            case 'TablaDetalleCuentas':
                $this->TablaDetalleCuentas();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $this->agregaTools(' ');
        $this->titulo(' ');
        $tabla = new stdClass();
        $tabla->titulo = 'Consulta Movimientos Cuenta Contable';
        $tabla->id = 'tablaCuentas';
        $tabla->tools = '
            <div class="input-group col-md-3 mb-4" style="width: 25%">
                <input maxlength ="' . (($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) ? '25' : '22') . ' type="text" class="form-control" placeholder="Cuenta Contable" id="filtroCuenta" name="control" list="listaAutocomplete-cuentacontapre">
                <datalist id="listaAutocomplete-filtroCuenta"></datalist>
                <span class="input-group-btn">
                    <button class="btn btn-secondary" type="button" id="filtrar"><span class="fa fa-search"></span></button>
                </span>
            </div>
        ';
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Ver';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Pagado';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Retenido';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Importe Neto';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'cargaCuentas';
        $tabla->comunica->filtroCuenta = '{$("#filtroCuenta").val()}';
        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->attr = new stdClass();
        $td->attr->ger_id = 'ger_id';
        $td->attr->cgp_id = 'cgp_id';
        $td->attr->rub_id = 'rub_id';
        $td->attr->cgcu_id = 'cgcu_id';
        $td->attr->cgsc_id = 'cgsc_id';
        $td->attr->cta_gpo_id = 'cta_gpo_id';
        $td->attr->cta_ur_id = 'cta_ur_id';
        $td->attr->cta_ue_id = 'cta_ue_id';
        $td->attr->cgc_id = 'cgc_id';
        $td->attr->cta_id = 'cta_id';
        $td->icono = 'edit';
        $td->class = 'detalleCuentas';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'cta_cuenta';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cta_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'percepciones';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'retenciones';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($tabla->cuerpo, $td);
        $tabla->numFilas = 20;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript('
            $("#filtrar").click(function(){  ' . $this->wrLlamaLlenaTabla('tablaCuentas') . '});
            $("#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
            $("#filtroCuenta").keyup(function(){
                if ($(this).val().length == 0) {
                    $("#listaAutocomplete-filtroCuenta").empty();
                }
                if ($(this).val().length >= 9 && $("#listaAutocomplete-filtroCuenta").children().length == 0) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"cargaCuentas",
                        filtroCuenta:$(this).val(),
                    });
                }
            });

            $("#filtroCuenta").keypress(function(e){
                if(e.which>47 && e.which <58 ){
                    switch ($(this).val().length){
                        case 5:
                            $("#filtroCuenta").val($("#filtroCuenta").val()+".' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '.");
                            return false;
                        break;
                        case 9:case 13:case 17:
                            $("#filtroCuenta").val($("#filtroCuenta").val()+".");
                        break;
                    }
                }else     if (e.which!=0 && e.which!=8){
                        return false;
                }
            });
        ');
        $this->cargaCuentas();
    }

    private function cargaCuentas()
    {
        $cuentas = (isset($this->prm->filtroCuenta)) ? explode('.', $this->prm->filtroCuenta) : [];
        $filtro = '';
        $params = ['gpo' => $_SESSION[_S]['clasAdm-gpo'], 'ur' => $_SESSION[_S]['clasAdm-ur']];

        switch (count($cuentas)) {
            case 6:
                $w = $cuentas[5];
                // no break
            case 5:
                $w = (empty($w)) ? $cuentas[4] : $cuentas[4] . $cuentas[5];
                $filtro = 'cta_id like "' . $w . '%"';
                // no break
            case 4:
                if (strlen($cuentas[3]) == 3) {
                    $w = 'cgc_id = :cgc';
                    $params['cgc'] = $cuentas[3];
                    $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                }
                // no break
            case 3:
                if (strlen($cuentas[2]) == 3) {
                    $w = 'cta_ue_id = :cta_ue';
                    $params['cta_ue'] = $cuentas[2];
                    $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                }
                // no break
            case 2:
                switch (strlen($cuentas[1])) {
                    case 3:
                        $w = 'cta_ur_id = :cta_ur';
                        $params['cta_ur'] = $_SESSION[_S]['clasAdm-ur'];
                        $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                        // no break
                    case 2:
                    case 1:
                        $w = 'cta_gpo_id = :cta_gpo';
                        $params['cta_gpo'] = $_SESSION[_S]['clasAdm-gpo'];
                        $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                }
                // no break
            case 1:
                switch (strlen($cuentas[0])) {
                    case 5:
                        $w = 'cgsc_id = :cgsc';
                        $params['cgsc'] = substr($cuentas[0], 4, 1);
                        $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                        // no break
                    case 4:
                        $w = 'cgcu_id = :cgcu';
                        $params['cgcu'] = substr($cuentas[0], 3, 1);
                        $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                        // no break
                    case 3:
                        $w = 'rub_id = :rub';
                        $params['rub'] = substr($cuentas[0], 2, 1);
                        $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                        // no break
                    case 2:
                        $w = 'cgp_id = :cgp';
                        $params['cgp'] = substr($cuentas[0], 1, 1);
                        $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                        // no break
                    case 1:
                        $w = 'ger_id = :ger';
                        $params['ger'] = substr($cuentas[0], 0, 1);
                        $filtro = ($filtro == '') ? $w : $w . ' AND ' . $filtro;
                }
        }
        $filtro = (empty($filtro)) ? '' : ' AND (' . $filtro . ')';
        $sql = '
            SELECT
            cta_gpo_id,cta_ur_id,cta_cuenta, cta_nombre,ncc_referencia,
            sum(ncc_percepcion) as percepciones,
            sum(ncc_retencion) as retenciones,
            sum(ncc_percepcion)-sum(ncc_retencion)as total,
            ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id
            FROM scp_Contable
            INNER JOIN cg_PlanCuentas USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
            WHERE gpo_id = :gpo AND ur_id = :ur
            ' . $filtro . '
            GROUP BY ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,ncc_referencia
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaCuentas';
        $nav->nfDefault = 20;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".detalleCuentas").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"TablaDetalleCuentas",
                    ger_id:$(this).attr("ger_id"),
                    cgp_id:$(this).attr("cgp_id"),
                    rub_id:$(this).attr("rub_id"),
                    cgcu_id:$(this).attr("cgcu_id"),
                    cgsc_id:$(this).attr("cgsc_id"),
                    cta_gpo_id:$(this).attr("cta_gpo_id"),
                    cta_ur_id:$(this).attr("cta_ur_id"),
                    cta_ue_id:$(this).attr("cta_ue_id"),
                    cgc_id:$(this).attr("cgc_id"),
                    cta_id:$(this).attr("cta_id")
                });
            });
        ');
    }

    private function TablaDetalleCuentas()
    {

        $boton = new stdClass();
        $boton->value = ' Regresar';
        $boton->icon = 'solid fa-circle-left me-2';
        $boton->class = 'btn-outline-secondary';
        $boton->id = 'regresarPrin';
        $boton->click = 'comunica("' . $this->pantalla . '");';

        $this->agregaTools($this->ponBoton($boton));
        $tabla = new stdClass();
        $tabla->titulo = 'DETALLE DEL NUMERO DE CUENTA: ' . $this->prm->ger_id . $this->prm->cgp_id . $this->prm->rub_id . $this->prm->cgcu_id . $this->prm->cgsc_id . '.' . $this->prm->cta_gpo_id . $this->prm->cta_ur_id . '.' . $this->prm->cta_ue_id . '.' . $this->prm->cgc_id . '.' . $this->prm->cta_id;
        $tabla->id = 'detalle';
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Clc';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Afe.';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. Control';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. Cereco';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'concepto';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'referencia';
        $th->width = '30px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Percepcion';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Retenciones';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Neto';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Tipo';
        $th->width = '30px';
        array_push($tr, $th);
        $tabla->encabezado = [$tr];

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetalle';
        $tabla->comunica->otb_id = '.$this->prm->otb_id.';
        $tabla->numFilas = 400;
        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ncc_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ncc_concepto';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ncc_referencia';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'percepciones';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'retenciones';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ncc_tipoMovimiento';
        array_push($tabla->cuerpo, $td);

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaDetalle();
    }

    private function llenaTablaDetalle()
    {
        $ger = $this->prm->ger_id;
        $cgp = $this->prm->cgp_id;
        $rub = $this->prm->rub_id;
        $cgcu = $this->prm->cgcu_id;
        $cgsc = $this->prm->cgsc_id;
        $gpo = $this->prm->cta_gpo_id;
        $ur = $this->prm->cta_ur_id;
        $ue = $this->prm->cta_ue_id;
        $cgc = $this->prm->cgc_id;
        $cta = $this->prm->cta_id;
        $params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$ger,$cgp,$rub,$cgcu,$cgsc,$gpo,$ur,$ue,$cgc,$cta];
        $sql = '
            SELECT ncc_id,afe_id,ncc_concepto,ncc_referencia,
                sum(ncc_percepcion) as percepciones,
                sum(ncc_retencion) as retenciones,
                sum(ncc_percepcion)-sum(ncc_retencion)as total,
                ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,clc_id,
                clc_cereCo,
                ncc_tipoMovimiento
            FROM scp_Contable a
            JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
            JOIN scp_Clc USING(gpo_id,ur_id,clc_id)
            LEFT JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
            WHERE  gpo_id = ?
            AND ur_id = ?
            AND ger_id = ? 
            AND cgp_id = ? 
            AND rub_id = ?
            AND cgcu_id = ? 
            AND cgsc_id = ? 
            AND cta_gpo_id = ? 
            AND cta_ur_id = ? 
            AND cta_ue_id = ?
            AND cgc_id = ? 
            AND cta_id = ?            
            GROUP by ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,gpo_id,ur_id,cta_ue_id,cgc_id,cta_id,ncc_id,afe_id,ncc_referencia,ncc_concepto,clc_id;
        ';
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'detalle';
        $nav->nf = 400;
        $this->wrLlenaTabla($nav);
    }
}
if (isset($_SESSION[_S]['prm']['S']['5']['15']['1'])) {
    new ControladorMovimientoContable();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
