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

function checkAccessMenu($menu_name = '')
{
    $datas = session()->get('menu_access') ? session()->get('menu_access') : [];
    foreach ($datas as $data) {
        if ($data->alias_menu == $menu_name) {
            return '1';
        }
    }

    return '0';
}
