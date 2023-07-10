<?php

namespace App\Http\Classes;

use App\Models\Honey;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Response as HttpResponse;
use voku\helper\HtmlDomParser;


class HoneyUtils
{
    /**
     * Save products in DB
     */
    public static function saveProductsFirstTime(array $products)
    {
        foreach ($products as $product) {
            $inItemID = $product['variants']['edges'][0]['node']['inventoryItem']['id'];
            $arrId = explode("/", $inItemID);
            $intID = end($arrId);
            Honey::create([
                'shopify_id' => $product['id'],
                'title' => $product['title'],
                'intID' => $product['intID'],
                'sku' => $product['variants']['edges'][0]['node']['sku'],
                'first_var_id' => $product['variants']['edges'][0]['node']['id'],
                'barcode' => $product['variants']['edges'][0]['node']['barcode'],
                'stock' => $product['variants']['edges'][0]['node']['inventoryQuantity'],
                'inv_item_id' => $product['variants']['edges'][0]['node']['inventoryItem']['id'],
                'inv_int_id' => $intID,
            ]);
        }
    }

    /**
     * 1- Login to honey(xml) and get data for specific product(via sku)
     * 2- get product quantity qty 
     * 3- update DB with the new qty and update the updated field(yes)
     */
    public static function connectToHoneyAndGetUpdateQTY(string $honey_account, string $honey_password, array $skus)
    {
        // qty delta
        $deleta = intval(env("HONEY_QTY_DELTLA")) != 0 ? intval(env("HONEY_QTY_DELTLA")) : 3;

        // create Guzzel client instance
        $client = new Client(['base_uri' => 'https://www.honeysplace.com/ws']);
        // promises to habdle the concurrent requests
        $promises = (function () use ($skus, $honey_account, $honey_password, $client) {
            foreach ($skus as $sku) {
                $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <HPEnvelope>
                    <account>' . $honey_account . '</account>
                    <password>' . $honey_password . '</password>
                    <stockcheck>
                        <sku>' . $sku . '</sku>
                    </stockcheck>
                </HPEnvelope>';
                // don't forget using generator
                yield $client->getAsync('https://www.honeysplace.com/ws', [
                    'query' => ['xmldata' => $xml],
                ]);
            }
        })();
        $eachPromise = new EachPromise($promises, [
            // how many concurrency we are use
            'concurrency' => 10,
            // promise fulfilled
            'fulfilled' => function (Response $response) use ($deleta) {
                if ($response->getStatusCode() == HttpResponse::HTTP_OK) {
                    // the response
                    $res = simplexml_load_string((string)$response->getBody());
                    if($res) {
                        // echo "<pre>";
                        // var_dump($res->stock->item);
                        // echo "</pre>";
                        if ($res->code != 501 && !is_null($res->stock->item)){
                            // extract needed data from the response
                            $honeySku = $res->stock->item->sku;
                            $honeyQTY = $res->stock->item->qty - $deleta;
                            // update Honey model(DB) with required data
                            $product = Honey::where("sku", $honeySku)->first();
                            if ($product) {
                                $product->stock = $honeyQTY;
                                $product->synced_at = Carbon::now();
                                $product->save();
                            }
                        }
                    }
                    // echo $res->stock->item . "<br>";
                }
            },
            'rejected' => function ($reason) {
                // handle promise rejected here
                dd($reason);
            }
        ]);
        // wait till all promises fulfilled
        $eachPromise->promise()->wait();
    }

    /**
     * 
     */
    public static function fetchDBProductsSKUS(): array
    {
        // get all products from DB
        return  Honey::pluck("sku")->all();
    }
    /*
    public static function updateProductStatusonDB($data)
    {
        // update the updated field in DB for inventory_item_id
        foreach ($data as $response) {
            if ($response->getStatusCode() == HttpResponse::HTTP_OK) {
                $iiID = json_decode($response->getBody()->getContents(), true)['inventory_level']['inventory_item_id'];
                Honey::where('inv_int_id', $iiID)->update(['updated' => 'yes']);
            }
        }
    }
*/

    /**
     * Verify the webhook credentials
     */
    public static function verifyWebhook($data, $hmac_header)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, env('SHOPIFY_API_SECRET_KEY'), true));
        return hash_equals($hmac_header, $calculated_hmac);
    }

    /**
     * Honey product images scraper
     */

    public static function parsingProductImages(string $url)
    {
        // init all product images
        $images = [];
        // init parser from url 
        $html = HtmlDomParser::file_get_html($url);
        // find product images
        $productImages = $html->find('.owl_carousel img');
        // if we have images
        if($productImages->length != 0) {
            foreach ($productImages as $img) {
                if ($img->hasAttribute('data-zoom-image')) {
                    $link = "https://www.honeysplace.com/" . $img->getAttribute('data-zoom-image');
                    $new_img = [['src' => $link]];
                    $images =  array_merge($images, $new_img);
                }
            };
        }
        return $images;
    }
}
