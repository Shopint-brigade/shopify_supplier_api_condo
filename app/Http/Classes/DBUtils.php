<?php

namespace App\Http\Classes;

class DBUtils {
    public static function SaveProductsDB(array $products, array $data, $class)
    {
        if(count($products) > 0 ) {
            foreach($products as $record) {
                $inItemID = $record['variants']['edges'][0]['node']['inventoryItem']['id'];
                $arrId = explode("/", $inItemID);
                $intID = end($arrId);
                dd($intID);
            }
        } else {
            return false;
        }
    }

}