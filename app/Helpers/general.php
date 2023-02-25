<?php

function normalizeNumber($number = 0)
{
    if (strpos($number, ',')) {
        $number = str_replace(',', '.', str_replace('.', '', $number));
    }

    return $number;
}
