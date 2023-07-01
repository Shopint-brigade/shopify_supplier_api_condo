<?php

namespace App\Http\Classes;
use Illuminate\Support\Facades\Http;


class EnterenueUtils
{
    public static function search(string $term)
    {
        // login with creds
        $res = Http::get(env('ENTERENUE_API_URL') .'/products', [
            'email' => env('ENTERENUE_EMAIL'),
            'apikey' => env('ENTERENUE_KEY'),
            'name' => $term,
        ]);
        if ($res->failed()) {
            log('Error: ' . $res->body());
        }

        return $res->json();
    }

    public static function pushProductToShopify(string $upc)
    {
        return 'push to shopify' . $upc;
    }
}