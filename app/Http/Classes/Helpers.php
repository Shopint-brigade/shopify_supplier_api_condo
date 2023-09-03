<?php

namespace App\Http\Classes;

class Helpers {
    public static function getShopifyIntIDFromStr(string $string): int {
        $parts = explode('/', $string);
        return intval(end($parts)) ;
    }
}