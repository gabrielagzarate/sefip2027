<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function ordenarArray($array, $campo, $orden, $asociativo = false)
{
    if ($campo != '') {
        $campo = array_reverse($campo, true);
    }
    if ($campo != '') {
        foreach ($campo as $keyCampo => $valCampo) {
            $tem = array();
            switch ($valCampo) {
                case 'date':
                    switch ($orden) {
                        case 'ASC':
                            $keyActual = -1;
                            $valorActual = '2015-01-01';
                            $k = count($array);
                            for ($i = 0; $i < $k; $i++) {

                                $keyTemporal = -1;
                                $valorTemporal = '2022-12-31';

                                foreach ($array as $key => $val) {
                                    $valorActual = $val[$keyCampo];

                                    if ($valorActual < $valorTemporal) {
                                        $valorTemporal = $valorActual;
                                        $keyTemporal = $key;
                                    }
                                }

                                array_push($tem, $array[$keyTemporal]);

                                unset($array[$keyTemporal]);
                            }
                            break;
                        case 'DESC':
                            $keyActual = -1;
                            $valorActual = '2022-12-31';
                            $k = count($array);
                            for ($i = 0; $i < $k; $i++) {

                                $keyTemporal = -1;
                                $valorTemporal = '2015-01-01';

                                foreach ($array as $key => $val) {
                                    $valorActual = $val[$keyCampo];

                                    if ($valorActual > $valorTemporal) {
                                        $valorTemporal = $valorActual;
                                        $keyTemporal = $key;
                                    }
                                }

                                array_push($tem, $array[$keyTemporal]);

                                unset($array[$keyTemporal]);
                            }
                            break;
                    }
                    break;
                case 'int':
                    switch ($orden) {
                        case 'ASC':
                            $keyActual = -1;
                            $valorActual = '';
                            $k = count($array);
                            for ($i = 0; $i < $k; $i++) {

                                $keyTemporal = -1;
                                $valorTemporal = 9999999999;

                                foreach ($array as $key => $val) {
                                    $valorActual = $val[$keyCampo];

                                    if ($valorActual < $valorTemporal) {
                                        $valorTemporal = $valorActual;
                                        $keyTemporal = $key;
                                    }
                                }

                                array_push($tem, $array[$keyTemporal]);

                                unset($array[$keyTemporal]);
                            }
                            break;
                        case 'DESC':
                            $keyActual = -1;
                            $valorActual = 9999999999;
                            $k = count($array);
                            for ($i = 0; $i < $k; $i++) {

                                $keyTemporal = -1;
                                $valorTemporal = '';

                                foreach ($array as $key => $val) {
                                    $valorActual = $val[$keyCampo];

                                    if ($valorActual > $valorTemporal) {
                                        $valorTemporal = $valorActual;
                                        $keyTemporal = $key;
                                    }
                                }

                                array_push($tem, $array[$keyTemporal]);

                                unset($array[$keyTemporal]);
                            }
                            break;
                    }
                    break;
                case 'string':
                    switch ($orden) {
                        case 'ASC':
                            $keyActual = -1;
                            $valorActual = '';
                            $k = count($array);
                            for ($i = 0; $i < $k; $i++) {

                                $keyTemporal = -1;
                                $valorTemporal = 'ZZZZZZZZZZ';

                                foreach ($array as $key => $val) {
                                    $valorActual = $val[$keyCampo];

                                    if ($valorActual < $valorTemporal) {
                                        $valorTemporal = $valorActual;
                                        $keyTemporal = $key;
                                    }
                                }

                                array_push($tem, $array[$keyTemporal]);

                                unset($array[$keyTemporal]);
                            }
                            break;
                        case 'DESC':
                            $keyActual = -1;
                            $valorActual = 'ZZZZZZZZZZ';
                            $k = count($array);
                            for ($i = 0; $i < $k; $i++) {

                                $keyTemporal = -1;
                                $valorTemporal = '';

                                foreach ($array as $key => $val) {
                                    $valorActual = $val[$keyCampo];

                                    if ($valorActual > $valorTemporal) {
                                        $valorTemporal = $valorActual;
                                        $keyTemporal = $key;
                                    }
                                }

                                array_push($tem, $array[$keyTemporal]);

                                unset($array[$keyTemporal]);
                            }
                            break;
                    }
                    break;
            }

            $array = $tem;
        }
    }
    if ($asociativo) {

        foreach ($array as $key => $val) {

            $datos[] = $val;
        }
    }

    $array = $datos;
    return $array;
}
