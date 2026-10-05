<?php

class UrNombre
{
    public static function toLowerCase(string $value): string
    {
        return substr($value, 0, 1) . substr(mb_strtolower($value), 1, strlen($value));
    }

    public static function titleCase($string, $delimiters = [' ', '-', '.', "'", "O'", 'Mc'], $exceptions = ['y/o','y','a','o','no','de', 'da', 'dos', 'das', 'do','por','la','con','el','las','para','del','los','en','al', 'I', 'II', 'III', 'IV', 'V', 'VI'])
    {
        $string = mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
        foreach ($delimiters as $dlnr => $delimiter) {
            $words = explode($delimiter, $string);
            $newwords = [];
            foreach ($words as $wordnr => $word) {
                if (in_array(mb_strtoupper($word, 'UTF-8'), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtoupper($word, 'UTF-8');
                } elseif (in_array(mb_strtolower($word, 'UTF-8'), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtolower($word, 'UTF-8');
                } elseif (!in_array($word, $exceptions)) {
                    // convert to uppercase (non-utf8 only)
                    $word = ucfirst($word);
                }
                array_push($newwords, $word);
            }
            $string = join($delimiter, $newwords);
        }//foreach
        return $string;
    }

    public static function conceptosPlan($string, $delimiters = [' ', '-', '.', "'", "O'", 'Mc'], $exceptions = ['y/o','y','a','o','u','no','de', 'da', 'dos', 'das', 'do','por','la','con','el','las','para','del','los','en', 'al', 'I', 'II', 'III', 'IV', 'V', 'VI'])
    {
        $string = mb_convert_case($string, MB_CASE_TITLE, 'ISO-8859-1');
        foreach ($delimiters as $dlnr => $delimiter) {
            $words = explode($delimiter, $string);
            $newwords = [];
            foreach ($words as $wordnr => $word) {
                if (in_array(mb_strtoupper($word, 'ISO-8859-1'), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtoupper($word, 'ISO-8859-1');
                } elseif (in_array(mb_strtolower($word, 'ISO-8859-1'), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtolower($word, 'ISO-8859-1');
                } elseif (!in_array($word, $exceptions)) {
                    // convert to uppercase (non-utf8 only)
                    $word = ucfirst($word);
                }
                array_push($newwords, $word);
            }
            $string = join($delimiter, $newwords);
        }//foreach
        return $string;
    }
}
