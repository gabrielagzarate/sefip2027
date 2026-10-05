<?php

function PartidasporClaseGastos($omo_id, $tfm_id, $mmd_id, $tmd_id)
{
    $clg_id = "";
    switch ($omo_id . $tmd_id . $mmd_id . $tfm_id) {

        //FOLIOS INTERNOS
        case 'AAIB': // TRASPASO INTRA TIPO FOL. GASTOS DE OP.
            $clg_id = " clg_id in(2,3,4,5,6,9,10,11) AND ";
            break;
        case 'ACIB': // RECALENDARIZACIÓN TIPO FOL. GASTOS OP.
            $clg_id = " clg_id in(2,3,4,5,6,7,9,10,11) AND ";
            break;
        case 'ACIC': // RECALENDARIZACIÓN TIPO FOL. INVER. PÚBLIC.
            $clg_id = " (clg_id in(8) AND idp_id != 'V') AND ";
            break;
        // Folios Externos se agrega asi por si requieren modificación de algun tipo de folio en especifico
        // SERVCIOS PERSONALES
        case 'AAEA':
        case 'ABEA':
        case 'ACEA':
        case 'ADEA':
        case 'AEEA':
        case 'BAEA':
        case 'BBEA':
        case 'BCEA':
        case 'BDEA':
        case 'BEEA':
        case 'CAEA':
        case 'CBEA':
        case 'CCEA':
        case 'CDEA':
        case 'CEEA':
        case 'EAEA':
        case 'EBEA':
        case 'ECEA':
        case 'EDEA':
        case 'EEEA':
            $clg_id = " clg_id = 1 AND ";
            break;
        // GASTOS DE OPERACIÓN
        case 'AAEB':
        case 'ABEB':
        case 'ACEB':
        case 'ADEB':
        case 'AEEB':
        case 'BAEB':
        case 'BBEB':
        case 'BCEB':
        case 'BDEB':
        case 'BEEB':
        case 'DDEB':
        case 'EAEB':
        case 'EBEB':
        case 'ECEB':
        case 'EDEB':
        case 'EEEB':
            $clg_id = " clg_id in(2,3,4,5,6,9,10,11) AND ";
            break;
        // INVERSION
        case 'AAEC':
        case 'ABEC':
        case 'ACEC':
        case 'ADEC':
        case 'AEEC':
        case 'BAEC':
        case 'BBEC':
        case 'BCEC':
        case 'BDEC':
        case 'BEEC':
        case 'EAEC':
        case 'EBEC':
        case 'ECEC':
        case 'EDEC':
        case 'EEEC':
        case 'FBEC':
            $clg_id = " (clg_id in(7,8)) AND ";
            break;
    }
    return $clg_id;
} // Fin function PartidasporClaseGastos

function AgrupacionSql($tmd_id, $tipoMovimiento)
{
    $using = "";
    switch ($tmd_id) {
        case 'B':
            if ($tipoMovimiento == 'A') {
                $using = "USING( gpo_id, ur_id,epg_id, pg_id, pe_id, ecf_id)";
            } else {
                $using = "USING( gpo_id, ur_id,epg_id, pg_id, pe_id, ecf_id,tcp_id)";
            }
            break;
        default:
            $using = "USING( gpo_id, ur_id,epg_id, pg_id, pe_id, ecf_id,tcp_id)";
            break;
    } // fin switch tipo de modificación
    return $using;
}

function RevisionPartidasporClaseGastos($folio, $datos)
{
    $error = 0;
    switch ($folio['omo_id'] . $folio['tmd_id'] . $folio['mmd_id'] . $folio['tfm_id']) {
        //FOLIOS INTERNOS
        case 'AAIB': // TRASPASO INTRA TIPO FOL. GASTOS DE OP.
            if ((!in_array($datos['clg_id'], (array(2, 3, 4, 5, 6, 9, 10, 11)))) && $datos['pe_id'] != 409) {
                $error = 31;
            }
            break;
        case 'ACIB': // RECALENDARIZACIÓN TIPO FOL. GASTOS OP.
            if (!in_array($datos['clg_id'], array(2, 3, 4, 5, 6, 7, 9, 10, 11))) {
                $error = 32;
            }
            break;
        case 'ACIC': // RECALENDARIZACIÓN TIPO FOL. INVER. PÚBLIC.
            if ((!in_array($datos['clg_id'], (array(8)))) && $datos['idp_id'] != 'V') {
                $error = 33;
            }
            break;
    }
    return $error;
}