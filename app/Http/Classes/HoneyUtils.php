<?php

namespace App\Http\Classes;

use App\Models\Honey;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Http;
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
                    if (!is_null($res->stock->item)) {
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
    public static function parsingSearchResult()
    {
        // require_once("simple_html_dom.php");
        $upc = '812024032741';
        $url = "http://api.scraperapi.com?api_key=bff67a41fc7541f5ef08cd1bf93e6c32&url=https://www.honeysplace.com/search?q=" . $upc . "&render=true&wait_for_selector=div.sku_section";
        // $url = "http://api.scraperapi.com?api_key=bff67a41fc7541f5ef08cd1bf93e6c32&url=https://www.honeysplace.com/search?q=" . $upc . "&render=true";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $html = HtmlDomParser::str_get_html($response);
        $nod = $html->find('p');
        dd($nod->text());
        
        $html =  HtmlDomParser::file_get_html($url);
        dd($html->find('.description'));
        // $honeyUrl = "https://www.honeysplace.com/search?q=" . $upc . "&render=true";
        // $payload = json_encode(
        //     array(
        //         "apiKey"
        //         =>
        //         "e063fcd2475b9ce39ee45b47174d3bb6",
        //         "url"
        //         =>
        //         $honeyUrl
        //     )
        // );
        // $ch = curl_init();
        // curl_setopt(
        //     $ch,
        //     CURLOPT_URL,
        //     "https://async.scraperapi.com/jobs"
        // );
        // curl_setOpt(
        //     $ch,
        //     CURLOPT_POST,
        //     1
        // );
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        // curl_setopt(
        //     $ch,
        //     CURLOPT_HTTPHEADER,
        //     array(
        //         "Content-Type:application/json"
        //     )
        // );
        // curl_setopt(
        //     $ch,
        //     CURLOPT_RETURNTRANSFER,
        //     TRUE
        // );
        // $faresRES = null;
        // $response = curl_exec($ch);
        // curl_close($ch);
        // $statusUel = json_decode($response, true)['statusUrl'];
        // $res = Http::get($statusUel);
        // $response = $res->body();
        
        // do{
        //     Http::get($statusUel);
        // } while(json_decode($response, true)['status'] != "finished");
     
        // dd($faresRES);
        // $url =
        // "http://api.scraperapi.com?api_key=e063fcd2475b9ce39ee45b47174d3bb6&url=https://www.honeysplace.com/search?q=812024032741&render=true"; $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER,
        // TRUE); curl_setopt($ch, CURLOPT_HEADER,
        // FALSE); curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,
        // 0); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,
        // 0); $response = curl_exec($ch); curl_close($ch); 
        // // $dom = new simple_html_dom();
        // $html = str_get_html($response);
        // dd($linko = $html->find('#hits', 0));
        // $final_link = 'https://www.honeysplace.com/' . $linko;
        // $html =  HtmlDomParser::str_get_html($response);
        // dd($html->find('.description a'));

        // $url = "http://api.scraperapi.com?api_key=e063fcd2475b9ce39ee45b47174d3bb6&url=https://www.honeysplace.com/search?q=" . $upc . "&render=true";
        // // $url = "https://www.honeysplace.com/search?q=" . $upc . "&render=true";
        // // $response = Http::get($url);
        // // dd($response->body());

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // $response = curl_exec($ch);
        // curl_close($ch);
        // // echo $response;
        // // dd($response);
        // $html =  HtmlDomParser::file_get_html($url);

        // $linko = $html->find('.description');
        // dd($linko);

        // dd($html) ;
        // dd($html);
        // dd($html->xml());
        // $element = $html->find('.description', 0);
        // return $element->text;
        // $html = HtmlDomParser::str_get_html($response->body());
        // return $html->find('#hits', 0);

        // $data = $dom->find('div.results-wrapper #hits', 0);
        // return $data->find('.ais-hits');

    }

    public static function parsingProductImages()
    {
    }
}
