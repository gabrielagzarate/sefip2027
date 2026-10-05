<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__).'/../../../ent/scg/cuentaContable/PlanCuentas.php';
require_once dirname(__FILE__).'/../../../ent/scg/cuentaContable/CuentaCuarto.php';
require_once dirname(__FILE__).'/../../../ent/scg/cuentaContable/CgSubcuenta.php';
require_once dirname(__FILE__).'/../../../ent/scg/cuentaContable/CuentaQuinto.php';

    function obtenerTituloCuentaAuxiliar( $gpo_id, $ur_id, $ue_id, $cuenta, $nombreCta ){
		$tituloCuenta = "";
		$nombreCuenta = "";
		$params = [];
        $arrayCuenta = explode( '.', $cuenta );
		$cgm = $arrayCuenta[0].$arrayCuenta[1].$arrayCuenta[2].$arrayCuenta[3].$arrayCuenta[4];
		$ger = substr( $cgm, 0, 1 );
		$cgp = substr( $cgm, 1, 1 );
		$rub = substr( $cgm, 2, 1 );
		$cgcu = substr( $cgm, 3, 1 );
		$cgsc = substr( $cgm, 4);
		$cgc = '';
		$cgq = '';
		$cta = '';
		$sql = '';
		if( isset( $arrayCuenta[5] ) )
			$cgc = substr( $arrayCuenta[5], 0 );
		if( isset( $arrayCuenta[6] ) )
			$cgq = substr( $arrayCuenta[6], 0 );
		if( strlen( $cgq ) > 2 )
			$cta = $cgq;
		if( isset( $arrayCuenta[7] ) )
			$cta = substr( $arrayCuenta[7], 0 );

		// para que encuentre las cuentas de quinto nivel
		if( strlen($cgq.$cta) == 6  )
			$cta = $cgq.$cta;
		
		if( strlen( $cta ) >= 4 ){
			$sql= '
			select cta_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id, cgc_id, cta_id 
			from cg_PlanCuentas 
			where cta_gpo_id = :gpo AND cta_ur_id = :ur 
				'.(($ue_id != "" && $ue_id != "%")?' AND cta_ue_id = '.$ue_id:'').'
				AND ger_id = :ger AND cgp_id = :cgp AND rub_id = :rub AND cgcu_id = :cgcu AND cgsc_id = :cgsc 
				AND cgc_id = :cgc 
				AND cta_id = :cta ;
			';
			$params = [':gpo' => $gpo_id, ':ur' => $ur_id, ':ger' => $ger,':cgp' => $cgp,':rub' =>$rub, ':cgcu' => $cgcu, ':cgsc' => $cgsc, ':cgc'=>$cgc,':cta' =>$cta];
			 
			$consultarH = new AccesoDatos();
			$consultarH->conectar();
			$consultarH->setSql($sql);
			$consultarH->consultar($params);
			$consultarH->desconectar();
		    $nTotalRegistros = $consultarH->getNumeroRegistros();
                if ($nTotalRegistros != 0) {
			        $row= $consultarH->extraer();
			        $nombreCuenta = $row['cuenta'];
				}else{
					$nombreCuenta = $nombreCta;
				}		
		}elseif( strlen( $cgq ) == 2 ){

			$cuentaQuinto = new CuentaQuinto( );
			$cuentaQuinto->setGer_id($ger);
			$cuentaQuinto->setCgp_id($cgp);
			$cuentaQuinto->setRub_id($rub);
			$cuentaQuinto->setCgcu_id($cgcu);
			$cuentaQuinto->setCgsc_id($cgsc);
			$cuentaQuinto->setCgc_id($cgc);
			$cuentaQuinto->setCgq_id($cgq);
			$cuentaQuinto->select();
            $nombreCuenta = $cuentaQuinto->getNombre();
		}elseif( strlen( $cgc ) == 3 ){
		
			$cuentaCuarto = new CuentaCuarto( );
			$cuentaCuarto->setGer_id($ger);
			$cuentaCuarto->setCgp_id($cgp);
			$cuentaCuarto->setRub_id($rub);
			$cuentaCuarto->setCgcu_id($cgcu);
			$cuentaCuarto->setCgsc_id($cgsc);
			$cuentaCuarto->setCgc_id($cgc);
			$cuentaCuarto->select();
            $nombreCuenta = $cuentaCuarto->getNombre();
		}elseif( strlen( $cgm ) == 5 ){
			
			$cgSubcuenta = new CgSubcuenta( );
			$cgSubcuenta->setGer_id($ger);
			$cgSubcuenta->setCgp_id($cgp);
			$cgSubcuenta->setRub_id($rub);
			$cgSubcuenta->setCgcu_id($cgcu);
			$cgSubcuenta->setCgsc_id($cgsc);
			$nombreCuenta = $cgSubcuenta->getNombre();
		}
		
		if( isset( $cuenta ) && $cuenta!='')
			$tituloCuenta = $cuenta.'-'.$nombreCuenta;
        return $tituloCuenta;
    }
    
?>