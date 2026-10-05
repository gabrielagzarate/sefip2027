<?php
function consultaReporteManual($lot_id="", $fpa_id="", $msc_id="", $lot_ejercicio=""){

	$sql= "
	SELECT 
	    LO.fpa_id,
	    LO.msc_id,
	    LO.lot_id,
	    LO.lot_ejercicio,
	    CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
	    LO.lom_importe,
	    LO.cbs_id,
	    LO.cbb_id,
	    LO.lom_concepto,
	    LO.lom_beneficiario
    from tesoreria.lot_PropuestaManual LO
    WHERE
        LO.lot_id = ?
        AND LO.fpa_id= ?
        AND LO.msc_id= ?
        AND LO.lot_ejercicio= ? ;";

    $conexion= new AccesoDatos();
	$conexion->conectar();
	$conexion->setSql($sql);
	$conexion->consultar(array($lot_id,$fpa_id,$msc_id,$lot_ejercicio));
	$conexion->desconectar();

	$result = array();

	while ( $row = $conexion->extraer() ) {
		$dato = array(
			'lote_id' 		=> $row['lote_id'],
			'lom_beneficiario' => $row['lom_beneficiario'],
			'lom_concepto' 	=> $row['lom_concepto'],
			'cbs_id' 		=> $row['cbs_id'],
			'cbb_id' 		=> $row['cbb_id'],
			'lom_importe'	=> $row['lom_importe']
		);

		array_push( $result, $dato );
	}
	return $result;

}