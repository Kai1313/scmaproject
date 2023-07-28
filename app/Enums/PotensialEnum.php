<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class PotensialEnum extends Enum
{
    //Here define your constants
    const Potensial = 2;
    const Perkenalan = 1;
    const Penawaran = 3;

    public static $array = [
        2 => 'Potensial',
        1 => 'Perkenalan',
        3 => 'Penawaran/Order',
    ];

    static function getLabel($value): string
    {

        return self::$array[$value];
    }
}
