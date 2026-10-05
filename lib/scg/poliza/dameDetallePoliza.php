<?php
// Si no llegaron aquí por AJAX ...
if( !( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ) ) {
    echo "Poor Cyrus ...";
    return;
}

require_once dirname(__FILE__)."/../../constantes.php"; 
require_once dirname(__FILE__).'/../../AccesoDatos.php';


if (!isset($_SESSION)) { session_start(); }

class dameDetallePoliza extends AccesoDatos{

    public $prm;
    
    public function __construct(){
        parent::__construct();
        
        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if( !empty($_POST) )$this->prm = json_decode( json_encode($_POST) );
        else $this->prm = new stdClass();
        
        $p  = ( ( isset( $this->prm->p  ) ) ? $this->prm->p  : "" );
        $r  = ( ( isset( $this->prm->r  ) ) ? $this->prm->r  : "" );
        $n  = ( ( isset( $this->prm->n  ) ) ? $this->prm->n  :  0 );
        $m  = ( ( isset( $this->prm->m  ) ) ? $this->prm->m  : 99 );
        $ue = ( ( isset( $this->prm->ue ) ) ? $this->prm->ue : 99 );
        $epl = ( ( isset( $this->prm->epl ) ) ? $this->prm->ue : "N" );

        $modoPoliza = ( ( isset( $this->prm->mp ) ) ? $this->prm->mp : "" );
        
        // Para mostrar la póliza, podemos pedir que también se incluyan las cuentas de orden
        $cuentasDeOrden = ( ( isset( $this->prm->o ) ) ? true : false );
        
        // Si no pasan el mes, es que están intentando recuperar una póliza temporal
        $prefijoTabla = "cg";
        $campoTmp = "pdx_id";
        if ( $m == 99 ) {
            $prefijoTabla = "ctrl2020Temporal.tem";
            $campoTmp = "tem_pdx";
        }
        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $this->conectar();
        // ERROR: CAMBIAR LEFT JOIN A INNER JOIN DEL cg_PlanCuentas
        
        // Si es para modificarla, NO incluimos las cuentas de orden
        if ( !$cuentasDeOrden) {

            // Marcos: 29/abr/2016
            // Recaudación en el 2016 lleva el sistema SIOX... y carga masivamente sus pólizas desde ahí
            // pero puede hacer capturas manuales en sinpres por solicitud de contabilidad (ajustes)
            // Entonces se les permitirá modificar las cuentas 8, ya que ellos también las capturan
            if ( $_SESSION[_S]['clasAdm-gpo'] == 1 && $_SESSION[_S]['clasAdm-ur'] == 16 && $ue == 2 ) {
                $sqlOrden = "";
            } else {
                $sqlOrden = "AND ger_id <> 8";
            }

            // Marcos: cambiamos la línea pdx_id por tem_pdx
            $this->setSql('SELECT
                CONCAT( ger_id, ".", cgp_id, ".", rub_id, ".", cgcu_id, ".", RIGHT(cgsc_id,1) ) AS mayor,
                CONCAT( ger_id, ".", cgp_id, ".", rub_id, ".", cgcu_id, ".",RIGHT(cgsc_id,1), ".", cgc_id, ".", cta_id ) AS cta,
                p.cta_nombre AS nom, pdx_debe AS d, pdx_haber AS h,
            CASE LENGTH( pdx_referencia ) WHEN 0 THEN "" ELSE CONCAT( tdf_id, "-", pdx_referencia ) END AS df,
            ' . $campoTmp . ' AS nL, cta_ue_id AS ue,p.cgc_id as g
                FROM ' . $prefijoTabla . '_PolizaDetalleAuxiliar INNER JOIN cg_PlanCuentas p
                    USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id )
                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' AND ue_id = ' . $ue . '
                AND pot_id = "' . $p . '" AND prf_id = "' . $r . '" ' .
                ( ( $m == 99 ) ? '' : ' AND msc_id = ' . $m ) . ' AND pma_id = ' . $n . '
                ' . $sqlOrden . ';');
        // Si es para consultarla, sí...
        } else {

            switch ($modoPoliza) {
                case '2':   // Registro
                    $strGrupo = ' GROUP BY cta_cuenta';
                    break;
                case '3':   // Mayor
                    $strGrupo = ' GROUP BY cgm_cuenta';
                    break;
                case '4':   // Sumarizado V
                    $strGrupo = ' GROUP BY cta_cuenta, tdf_id, pdx_referencia';
                    break;
                case '5':   // Sumarizado V + A ( Aquí no ponemos afectaciones, así que es lo mismo que la anterior )
                    $strGrupo = ' GROUP BY cta_cuenta, tdf_id, pdx_referencia';
                    break;
                default:
                    $strGrupo = "";   // Normal no lleva agrupamiento
                    break;
            }

            // Marcos: cambiamos la línea pdx_id por tem_pdx
            $this->setSql('SELECT
                    cgsc_cuenta AS mayor, ' .
                    ( ( $modoPoliza == "3" ) ? 'cgm_cuenta' : 'cta_cuenta' ) . ' AS cta, ' .
                    ( ( $modoPoliza == "3" ) ? 'cgm_nombre' : 'p.cta_nombre' ) . ' AS nom, ' .
                    ( ( $modoPoliza == "1" ) ? 'pdx_debe AS d,' : 'SUM( pdx_debe ) AS d,' ) .
                    ( ( $modoPoliza == "1" ) ? 'pdx_haber AS h,' : 'SUM( pdx_haber ) AS h,' ) .
                    ( ( $modoPoliza == "2" || $modoPoliza == "3" ) ? '""' :
                    'CASE LENGTH( pdx_referencia ) WHEN 0 THEN "" ELSE CONCAT( tdf_id, "-", pdx_referencia ) END' ) . ' AS df,
                    ' . $campoTmp . ' AS nL, d.nat_id AS nat,
                    "" AS modif, cta_ue_id AS ue,p.cgc_id as g
                FROM cg_PolizaDetalleAuxiliar d
                    LEFT JOIN cg_PlanCuentas p USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id )
                    INNER JOIN cat_CgSubcuenta sub USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' AND ue_id = ' . $ue . '
                AND pot_id = "' . $p . '" AND prf_id = "' . $r . '" AND msc_id = ' . $m . ' AND pma_id = ' . $n .
                $strGrupo . '
                ORDER BY d.nat_id, cta, pdx_id
                ;');
        }

        $this->consultar();
        $this->desconectar();
        
        if ( $this->getNumeroRegistros() == 0 ) {
            echo json_encode( array( array( 'cta'=>'', 'nom'=>'', 'd'=>'', 'h'=>'', 'df'=>'', 'nL'=>'', 'ue'=>'' )  ) );
        } else {
            $data = array();
            while ($row=$this->extraer()) {
                $data[] = $row;
            }
            echo json_encode( $data );
        }
    }
}
new dameDetallePoliza();