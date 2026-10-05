<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RC4
 *
 * @author 
 * Created: 13-ago-2018
 * Comment: 
 * 
 * */
    function RC4($key, $data)
    {
        if(function_exists('openssl'))
        {
            $cypherMethod = 'AES-256-CBC';
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cypherMethod));
            $decryptedData = openssl_encrypt($data, $cypherMethod, $key, $options=0, $iv);
            return $decryptedData;
        }
        else
        {
            static $last_key, $last_state;

            if($key != $last_key)
            {
                $k = str_repeat($key, intval(256/strlen($key))+1);
                $state = range(0, 255);
                $j = 0;
                for ($i=0; $i<256; $i++){
                    $t = $state[$i];
                    $j = ($j + $t + ord($k[$i])) % 256;
                    $state[$i] = $state[$j];
                    $state[$j] = $t;
                }
                $last_key = $key;
                $last_state = $state;
            }
            else
                $state = $last_state;

            $len = strlen($data);
            $a = 0;
            $b = 0;
            $out = '';
            for ($i=0; $i<$len; $i++){
                $a = ($a+1) % 256;
                $t = $state[$a];
                $b = ($b+$t) % 256;
                $state[$a] = $state[$b];
                $state[$b] = $t;
                $k = $state[($state[$a]+$state[$b]) % 256];
                $out .= chr(ord($data[$i]) ^ $k);
            }
            return $out;
        }
    }


?>