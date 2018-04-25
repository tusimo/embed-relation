<?php

if (!function_exists('string_to_array')) {
    /**
     * covert a string to array
     * @param $string
     * @param string $option json|JSON as covert a json string to array and else as delimiter for a string to explode
     * @param null $callback covert array value ,use intval strval floatval boolval and custom callback
     * @return array|mixed
     */
    function string_to_array($string, $option = ',', $callback = null)
    {
        if (is_array($string)) {
            return $string;
        }
        $item = [];
        if ($string != '' && !is_null($string)) {
            if (strtoupper($option) == 'JSON') {
                $item = json_decode($string, true);
                if (!is_array($item)) {
                    $item = [];
                }
            } else {
                $item = explode($option, $string);
            }
        }
        if ($callback) {
            return array_map($callback, $item);
        }
        return $item;
    }
}