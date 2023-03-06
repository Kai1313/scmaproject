<?php

function normalizeNumber($number = 0)
{
    if (strpos($number, ',')) {
        $number = str_replace(',', '.', str_replace('.', '', $number));
    } else {
        $number = str_replace('.', '', $number);
    }

    return $number;
}

function handleNull($number)
{
    return $number ? $number : 0;
}
