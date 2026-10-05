<?php
function consultaDetalleMunicipiosDocumentos($lot_ejercicio = '', $fpa_id = '', $msc_id = '', $lot_id = '') {
	$contador = 1;

	if (!empty($lot_ejercicio)) {
		$ejercicios = [
			0 => 2026,
			1 => 2025,
			2 => 2024,
			3 => 2023,
			4 => 2022,
			5 => 2021,
			6 => 2020,
			7 => 2019,
			8 => 2018,
			9 => 2017,
		];
		$clave = (int) $lot_ejercicio;
		if (isset($ejercicios[$clave])) {
			$lot_ejercicio = $ejercicios[$clave];
		}
	}

	$sql = '
		SELECT
			CASE WHEN
				l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
			THEN
				"FEDERAL"
			ELSE
				"ESTATAL"
			END AS modo, 
			tlo.tlo_nombre AS tipo,
			CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
				LPAD(l.msc_id, 2, "0"),
				LPAD(l.lot_id, 4, "0"),
				SUBSTR(l.lot_ejercicio, 3)
			) AS lote,
			l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id,
			l.lot_fechaGeneracion, l.lot_fechaPago, lca.clc_generacionCereCo, lca.clc_cereCo,
			concat(lc.gpo_id, lc.ur_id) AS ur, lc.clc_id, ecf_id,
			m.cbb_id AS ctaAbono, l.tlo_id, sf.cbs_id AS ctaCargo,
			((d.bdo_percepcionPresupuestal + d.bdo_percepcionContable ) - d.bdo_retencionContable ) AS importe,
			SUBSTR(lot_ejercicio, 3) AS anio, doc.bdo_id AS orden, CURDATE() AS fecha, d.bdo_referencia AS concepto,
			pr.mun_nombre AS benef, l.elo_id, doc.ue AS ue, cb.tipoF,
			"" AS lote_egreso,
			cb.nombre_cta, ecf_clave, cb.banco, e.ecl_nombre AS estado
		FROM ' . BD_TESORERIA . '.lot_Lote AS l
		INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
		INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
		INNER JOIN ' . BD_CTRL . '.scp_Clc AS c ON (lc.gpo_id = c.gpo_id AND lc.ur_id = c.ur_id AND lc.clc_id = c.clc_id)
		INNER JOIN ' . BD_CTRL . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
		INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar AS lca ON (lca.gpo_id = c.gpo_id AND lca.ur_id = c.ur_id AND lca.clc_id = c.clc_id)
		INNER JOIN (
			SELECT gpo_id, ur_id, clc_id, bdo_id, pr.mun_id AS ue, rfc_id
			FROM ' . BD_CTRL . '.scp_Presupuestal AS p
			JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor AS pr USING (rfc_id)
			WHERE p.gpo_id = 9
			AND p.ur_id = 01
			AND p.clc_id > 0
			UNION
			SELECT gpo_id, ur_id, clc_id, bdo_id, pr.mun_id AS ue, rfc_id
			FROM ' . BD_CTRL . '.scp_Contable AS c
			JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor AS pr USING (rfc_id)
			WHERE c.gpo_id = 9
			AND c.ur_id = 01
			AND c.clc_id > 0
		) AS doc ON (doc.gpo_id = lc.gpo_id AND doc.ur_id = lc.ur_id AND doc.clc_id = lc.clc_id)
		INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento USING (ecf_id)
		INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS sf USING (ecf_id)
		INNER JOIN (
			SELECT IF ( tfn_id > 4 && tfn_id < 9, 2, tfn_id ) AS tipoF, cbs_id,
			cbs_nombre AS nombre_cta, iba_abreviatura AS banco
			FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs
			INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
			WHERE tfn_id != 1
		) AS cb USING (cbs_id)
		INNER JOIN ' . BD_CTRL . '.cat_BancoDocumentos AS d ON (doc.gpo_id = d.gpo_id AND doc.ur_id = d.ur_id AND doc.bdo_id = d.bdo_id)
		INNER JOIN ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS m ON (m.mun_id = doc.ue AND m.tfn_id = cb.tipoF)
		INNER JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor AS pr ON (pr.rfc_id = doc.rfc_id)
		WHERE l.lot_ejr = :ejer and l.fpa_id = :fpa
		AND l.msc_id = :msc
		AND l.lot_ejercicio = :lotEjer
		AND l.lot_id = :lotId
		ORDER BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id
	';

	$params = [
        'ejer' => APP_EJERCICIO,
        'fpa' => $fpa_id,
        'msc' => $msc_id,
        'lotEjer' => $lot_ejercicio,
        'lotId' => $lot_id
    ];

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar($params);
    $con->desconectar();

    $result = array();

    while ($fila = $con->extraer()) {
    	$dato = array(
    		'contador' => $contador++,
    		'modo' => $fila['modo'],
    		'tipo' => $fila['tipo'],
    		'lot_ejercicio'=> $fila['lot_ejercicio'],
    		'lot_fechaGeneracion' => $fila['lot_fechaGeneracion'],
    		'lot_fechaPago' => $fila['lot_fechaPago'],
    		'clc_generacionCereCo' => $fila['clc_generacionCereCo'],
    		'lote_egreso' => $fila['lote_egreso'],
    		'ur' => $fila['ur'],
    		'clc_id' => $fila['clc_id'],
    		'clc_cereCo' => $fila['clc_cereCo'],
    		'benef' => $fila['benef'],
    		'concepto' => $fila['concepto'],
    		'importe' => number_format($fila['importe'], 2, '.', ','),
    		'banco' => $fila['banco'],
    		'ctaCargo' => $fila['ctaCargo'],
    		'nombre_cta' => $fila['nombre_cta'],
    		'ctaAbono' => $fila['ctaAbono'],
    		'ecf_clave' => $fila['ecf_clave'],
    		'lote' => $fila['lote'],
    		'estado' => $fila['estado']
    	);
	    array_push( $result, $dato );
    }
    return $result;
}
