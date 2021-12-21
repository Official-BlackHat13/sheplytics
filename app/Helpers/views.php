<?php

/**
 * Convert a number into a readable one.
 *
 * @param   int   $number  The number to be transformed
 * @return  string
 */
function shortenNumber($number)
{
    $suffix = ["", "K", "M", "B"];
    $precision = 1;
    for($i = 0; $i < count($suffix); $i++) {
        $divide = $number / pow(1000, $i);
        if($divide < 1000){
            return round($divide, $precision).$suffix[$i];
        }
    }

    return $number;
}

/**
 * Calculate the growth between two values.
 *
 * @param $current
 * @param $previous
 * @return array|int
 */
function calcGrowth($current, $previous)
{
    if ($previous == 0 || $previous == null || $current == 0) {
        return 0;
    }

    return $result = (($current - $previous) / $previous * 100);
}