<?php

namespace App\Http\Classes;

use App\Models\Enterenue;
use Illuminate\Support\Facades\Http;
use voku\helper\HtmlDomParser;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use  GuzzleHttp\Client;
use Carbon\Carbon;

class EnterenueUtils
{
    /**
     * api login with some params(product ups , product name ...ect)
     */
    private static function loginGetApiRequest(string $urlSegment, string $paramName, string $paramValue)
    {
        try {
            $queryParams = [
                'email' =>  env('ENTERENUE_EMAIL'),
                'apikey' => env('ENTERENUE_KEY'),
                $paramName => $paramValue,
            ];

            $res = Http::get(env('ENTERENUE_API_URL') . $urlSegment, $queryParams);

            if ($res->failed()) {
                info($res->body());
                $err = new \Exception('Check credentials');
                return ['error' => $err->getMessage(), 'data' => null];
            }

            return ['error' => null, 'data' => $res->json()];
        } catch (RequestException $e) {
            info($e->getMessage());
            return  ['error' => $e->getMessage(), 'data' => null];
        } catch (\Exception $e) {
            info($e->getMessage());
            return ['error' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Search products by name
     */
    public static function search(string $term)
    {
        return self::loginGetApiRequest('/products', 'name', $term);
    }

    public static function pushProductToShopify(string $upc, string $location, $request, $shopify)
    {
        $theError = null;
        $itemLocationID = substr($location, strrpos($location, '/') + 1);
        // Login creds
        $url = env('ENTERENUE_LOGIN_URL');
        $login = env('ENTERENUE_LOGIN');
        $password = env('ENTERENUE_PASSWORD');
        // login and get product via upc
        ['error' => $error, 'data' => $data] = self::loginGetApiRequest('/products', 'upc', $upc);
        /*
          - generate product from data to be pushed to shopify store(all data except images)
          - login to frontend and get parse product images
        */
        if (is_null($error)) {
            // product to push to shopify store
            $productToPushData = self::PrepareProductToPush($data);
            // login
            $ch = self::accountLogin($url, $login, $password);
            $result = curl_exec($ch);
            curl_exec($ch);
            // search for product after loged in
            $searchUrl = env('ENTERENUE_SEARCH_PRODUCT_URL') . $upc . '&description=true';
            $result = self::logedINSerachProduct($ch, $searchUrl);
            // get the product link from the main image href
            $html = HtmlDomParser::str_get_html($result);
            $productink = $html->find('.product-img')[0]->href;
            //login again
            $ch = self::accountLogin($url, $login, $password);
            $result = curl_exec($ch);
            // single product page to get images
            $url = $productink;
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            // Parse other images(not the main one)
            [$x, $images] = self::parseProductImages($result);
            // get product data ready to push to shopify store
            $readyProductToPush = self::prpareProductData($x, $images, $productToPushData);
            // push the ready product to shopify store
            $createdProductResponse = $shopify->makeApiRequest('post', 'products', $readyProductToPush);
            // init product to be saved in DB
            $dbProduct = new Enterenue();
            if (isset($createdProduct['error'])) {
                $theError = $createdProductResponse['error'];
            }
            $dbProduct->title = $createdProductResponse['product']['title'];
            $dbProduct->upc = $upc;
            $dbProduct->shopify_id = $createdProductResponse['product']['id'];
            // posting qty levels
            $inventoryItemId = $createdProductResponse['product']['variants'][0]['inventory_item_id'];
            $quantity = $data['data'][0]['quantity'] - 3;
            $data = ['location_id' => $itemLocationID, 'inventory_item_id' =>   $createdProductResponse['product']['variants'][0]['inventory_item_id'], 'available' =>  $quantity];
            $qtyLevelResponse = $shopify->makeApiRequest('post', 'inventory_levels/set', $data);
            if (isset($qtyLevelReesult['error'])) {
                $theError = $createdProductResponse['error'];
            }
            $dbProduct->qty = $quantity;
            $dbProduct->synced;
            $dbProduct->inventory_item_id = $createdProductResponse['product']['variants'][0]['inventory_item_id'];
            // posting item Gross Cost 
            $data = ['inventory_item' =>  ['inventory_item_id' =>   $inventoryItemId, 'cost' =>  $productToPushData['gross']]];
            $itemCostResponse = $shopify->makeApiRequest('put', 'inventory_items/' . $inventoryItemId, $data);
            if (isset($itemCostResponse['error'])) {
                $theError = $itemCostResponse['error'];
            }
            $dbProduct->price =  $productToPushData['price'];
            $dbProduct->pushed = true;
            // all good ! save in DB
            $dbProduct->save();
        } else {
            $theError = $error;
        }
        return $theError;
    }

    /**
     * Prepare product to be pushed to shopify store
     * here we collect all data except images
     */
    private static function PrepareProductToPush(array $data): array
    {
        $productToPush = null;
        foreach ($data['data'] as $product) {
            $upc = $product['upc'];
            $name = $product['name'];
            $image = $product['image'];
            $description = strip_tags($product['description']);
            $description = preg_replace('/<[^>]*>/', ' ', $description);
            $description = html_entity_decode($description);
            $tags = $product['categories'] . ',entrenue';
            $gross = $product['price'];
            $price = $product['price'] * 2;
            $compare = $price * 0.2;
            $compare = $compare + $price;
            if (!empty($product['msrp'])) {
                $compare = $product['msrp'];
            } else {
                $compare = '';
            }
            $map = $product['map'];
            if (empty($map)) {
                $map = $price;
                if (!empty($product['msrp'])) {
                    if ($map  >=  $product['msrp']) {
                        $map = $product['msrp'];
                    }
                }
            }
            if ($map == $compare) {
                $compare = '';
            }
            $manufacturer = $product['manufacturer'];
        }
        $productToPush['upc'] = $upc;
        $productToPush['name'] = $name;
        $productToPush['description'] = $description;
        $productToPush['tags'] = $tags;
        $productToPush['gross'] = $gross;
        $productToPush['price'] = $price;
        $productToPush['compare'] = $compare;
        $productToPush['map'] = $map;
        $productToPush['manufacturer'] = $manufacturer;
        $productToPush['image'] = $image;
        return $productToPush;
    }

    /**
     * Login with admin creds (not api login but site frontend login)
     */
    private static function accountLogin(string $url, string $login, string $password)
    {
        $encodedEmail = urlencode($login);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $cookie = 'cookies.txt';
        $timeout = 60;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,         10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,  $timeout);
        curl_setopt($ch, CURLOPT_COOKIEJAR,       $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE,      $cookie);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "email=" . $encodedEmail  . "&password=" . $password);
        return $ch;
    }

    public static function logedINSerachProduct($ch, $searchProductUrl)
    {
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $searchProductUrl);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private static function parseProductImages($data): array
    {
        $html = HtmlDomParser::str_get_html($data);
        $images = [];
        $x = 0;
        foreach ($html->find('.additional-image img') as $e) {
            $x++;
            $link =  $e->src;
            $link = str_replace('80x80', '1100x1100', $link);
            $new_img = [['src' => $link]];
            $images =  array_merge($images, $new_img);
        }
        return [$x, $images];
    }

    private static function prpareProductData(int $x, array $images, $product)
    {
        if ($x == 0) {
            return array('product' =>  [
                'title' => $product['name'], 'body_html' => $product['description'],
                'vendor' => $product['manufacturer'],  'sku' => $product['upc'],
                'tags' => $product['tags'], 'images' => [["src" =>  $product['image']]],
                'product_type' => 'Adult Toys',
                'variants' => [[
                    'sku' => $product['upc'], 'fulfillment_service' => 'Manual',
                    'inventory_management' => 'shopify', 'compare_at_price' =>  $product['compare'],
                    'price' => $product['map'], 'barcode' => $product['upc']
                ]]
            ]);
        }
        return array('product' =>  [
            'title' => $product['name'], 'body_html' => $product['description'],
            'vendor' => $product['manufacturer'], 'product_type' => 'Adult Toys', 'sku' => $product['upc'],
            'tags' => $product['tags'], 'images' => $images,
            'variants' => [[
                'sku' => $product['upc'], 'fulfillment_service' => 'Manual',
                'inventory_management' => 'shopify', 'compare_at_price' =>  $product['compare'],
                'price' => $product['map'],  'barcode' => $product['upc']
            ]]
        ]);
    }

    public static function SaveProductsDB($product, $class)
    {
        $upc = $product['variants']['edges'][0]['node']['sku'] ?? '';
        $variantID = Helpers::getShopifyIntIDFromStr($product['variants']['edges'][0]['node']['id']);
        $invintory_item_id = Helpers::getShopifyIntIDFromStr($product['variants']['edges'][0]['node']['inventoryItem']['id']);
        $res = $class::updateOrCreate(
            ['shopify_id' => Helpers::getShopifyIntIDFromStr($product['id'])],
            [
                'title' => $product['title'],
                'upc' => $upc,
                'inventory_item_id' => $invintory_item_id,
                'variant_id' => $variantID
            ]
        );
        if (!is_null($res)) return "OK";
        info("Error while saving products in DB");
    }
    public static function findProductOnEntrenue(string $upc)
    {
        return self::loginGetApiRequest('/products', 'upc', $upc);
    }

    /*
    public static function updatesingleFieldOnShopifyProduct($product, array $data = [], array $promises, $client, string $url, string $method)
    {
        $data_string = json_encode($data);
        $request = new GuzzleRequest(
            $method,
            $url,
            [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($data_string),
            ],
            $data_string
        );
        $promise = $client->sendAsync($request)->then(
            // all is good
            function (Response $response) use ($product) {
                $product->synced_at = Carbon::now();
                $product->save();
                $result = $response->getBody()->getContents();
                info("Product with title: " . $product . " qty updated");
            },
            // error happened
            function (Exception $exception) {
                info('Error: ' . $exception->getMessage());
            }
        );
        $promises[] = $promise;
    }
    */
}
