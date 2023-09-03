<?php

namespace App\Http\Controllers;

use App\Http\Classes\EnterenueUtils;
use App\Http\Classes\Shopify;
use App\Http\Classes\Helpers;
use App\Models\Enterenue;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Response as HttpResponse;

use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;


class EnterenueController extends Controller
{

    private string  $username;
    private  string $password;
    private  string $shop;
    private  string $api;
    private string $location_name;
    private string $collection_id;
    private  string $honey_account;
    private  string $honey_password;
    // DI Shopify
    public Shopify $shopify;

    /**
     * Init required data
     */
    public function __construct()
    {
        $this->username =  env('ENTERENUE_SHOPIFY_KEY'); // env('FARES_API_KEY');ENTERENUE_SHOPIFY_KEY
        $this->password = env('ENTERENUE_SHOPIFY_SECRET'); // env('FARES_API_TOKEN');ENTERENUE_SHOPIFY_SECRET
        $this->shop = env('ENTERENUE_SHOPIFY_STORE'); //env('FARES_STORE');ENTERENUE_SHOPIFY_STORE
        $this->api = env('ENTERENUE_SHOPIFY_API'); //env('FARES_API');ENTERENUE_SHOPIFY_API
        $this->location_name = env('ENTRENUE_LOCATION_NAME'); //env('FARES_LOCATION_NAME');ENTRENUE_LOCATION_NAME
        $this->collection_id = env('ENTRENUE_COLLECTION_ID'); //env('FARES_COLLECTION_ID');ENTRENUE_COLLECTION_ID
        $this->honey_account = env('HONEY_ACCOUNT');
        $this->honey_password = env("HONEY_PASSWORD");
        $this->shopify = new Shopify('https://' . $this->username . ':' . $this->password . '@' . $this->shop . '.myshopify.com/admin/' . $this->api);
    }

    public function getShopifyLocationByName()
    {
        return $this->shopify->getLocation($this->location_name, $this->password);
    }

    /**
     * Save all honey products from site in DB
     * to be used for sync products on Shopify
     * WILL use as cron job(LATER) and run each 2 hours
     */
    public function saveProductsinDB()
    {
        $products = $this->shopify->getProductsOfCollectionWithPrice($this->password, $this->collection_id);
        if (count($products) > 0) {
            foreach ($products as $product) {
                EnterenueUtils::SaveProductsDB($product, Enterenue::class);
            }
            info(count($products) . " products saved in DB");
        } else {
            info("Enternue save products job, products count is: " . count($products) . ' Menas NO products foud');
        }
    }

    /**
     * Use as cron job to update the Qty and the Price(msrp, map) from Enternue on Db
     * then we can use the DB as cache to update the qty and the price on
     * shopify.
     */
    public function syncQtyAndPriceEnternueDB()
    {
        // get products from DB
        $dbProducts = Enterenue::all();
        // find each product on Enternue 
        foreach ($dbProducts as $dbPr) {
            $entrenueResponse = EnterenueUtils::findProductOnEntrenue($dbPr['upc']);
            if (!is_null($entrenueResponse['error'])) {
                info($entrenueResponse['error']);
            } else {
                if (!empty($entrenueResponse['data']['data'])) {
                    $qty = intval($entrenueResponse['data']['data'][0]['quantity']) - 3;
                    $price = $entrenueResponse['data']['data'][0]['price'];
                    $msrp = $entrenueResponse['data']['data'][0]['msrp'];
                    $map = $entrenueResponse['data']['data'][0]['map'];
                    // update qty on DB
                    $dbPr->qty = $qty;
                    $dbPr->price = $price;
                    $dbPr->msrp = $msrp;
                    $dbPr->map = $map;
                    $dbPr->save();
                    info("qty and price updated in DB");
                } else {
                    info("product with upc: " . $dbPr['upc'] . " not found on Enternue !");
                }
            }
        }
    }



    /**
     * This method will be used as cron job to update the qty from DB 
     * on shopify store for each product nand updates the synced_at
     * attr on DB.
     */
    public function syncProductStockWithShopify()
    {
        // product location id
        $locationID = Helpers::getShopifyIntIDFromStr($this->getShopifyLocationByName());
        // get products from DB
        $dbProducts = Enterenue::where('upc', '!=', '')->get();
        // for each product login and find the product, update the QTY in DB and update the qty on shopify
        $client = new Client();
        $url = $this->shopify->url . '/inventory_levels/set.json';

        $promises = (function () use ($client, $dbProducts, $locationID, $url) {
            foreach ($dbProducts as $dbPr) {
                usleep(500000);
                if (!is_null($dbPr['qty'])) {
                    $data = [
                        'location_id' => $locationID,
                        'inventory_item_id' => $dbPr['inventory_item_id'],
                        'available' => $dbPr['qty'],
                    ];
                    $data_string = json_encode($data);
                    $request = new GuzzleRequest(
                        'POST',
                        $url,
                        [
                            'Content-Type' => 'application/json',
                            'Content-Length' => strlen($data_string),
                        ],
                        $data_string
                    );
                    yield $client->sendAsync($request);
                }
            }
        })();
        $eachPromise = new EachPromise($promises, [
            'concurrency' => 10,
            'fulfilled' => function (Response $response) {
                info($response->getStatusCode());
            },
            'rejected' => function ($reason) {
                // handle promise rejected here
                info($reason->getMessage());
            }
        ]);
        $eachPromise->promise()->wait();

        // update in DB
        foreach ($dbProducts as $dbPr) {
            $dbPr->synced_at = Carbon::now();
            $dbPr->save();
        }
    }

    /**
     * This method will be used as cron job to update the price from DB 
     * on shopify store for each product, adn updates the synced_at attr on DB.
     */
    public function syncProductPricekWithShopify()
    {
        // get products from DB
        $dbProducts = Enterenue::where('upc', '!=', '')->get();
        // for each product login and find the product, update the price on shopify
        $baseUrl = $this->shopify->url;
        // $promises = [];
        $client = new Client();
        $promises = (function () use ($client, $dbProducts, $baseUrl) {
            foreach ($dbProducts as $dbPr) {
                usleep(1100000);
                if (!is_null($dbPr['price'])) {
                    $pricesData = EnterenueUtils::calculatePriceMapMsrp($dbPr);
                    
                    $data = [
                        'product' => [
                            'id' => $dbPr['shopify_id'],
                            'variants' => [
                                [
                                    'id' => $dbPr['variant_id'],
                                    'price' => $pricesData['map'],
                                    'compare_at_price' => $pricesData['compare']
                                ],
                            ],
                        ]
                    ];
                    $data_string = json_encode($data);
                    $request = new GuzzleRequest(
                        'PUT',
                        $baseUrl . '/products/' . $dbPr['shopify_id'] . '.json',
                        [
                            'Content-Type' => 'application/json',
                            'Content-Length' => strlen($data_string),
                        ],
                        $data_string
                    );
                    yield $client->sendAsync($request);
                    $costData = [
                        'inventory_item' =>  ['inventory_item_id' =>   $dbPr['inventory_item_id'], 'cost' =>  $dbPr['price']]
                    ];
                    $costDataStaring = json_encode($costData);
                    $costRequest = new GuzzleRequest(
                        'PUT',
                        $baseUrl . '/inventory_items/' . $dbPr['inventory_item_id'] . '.json',
                        [
                            'Content-Type' => 'application/json',
                            'Content-Length' => strlen($costDataStaring),
                        ],
                        $costDataStaring
                    );
                    yield $client->sendAsync($costRequest);
                }
            }
        })();

        $eachPromise = new EachPromise($promises, [
            'concurrency' => 20,
            'fulfilled' => function (Response $response) {
                info($response->getStatusCode());
            },
            'rejected' => function ($reason) {
                // handle promise rejected here
                info($reason->getMessage());
            }
        ]);

        $eachPromise->promise()->wait();

        // update in DB
        foreach ($dbProducts as $dbPr) {
            $dbPr->synced_at = Carbon::now();
            $dbPr->save();
        }
    
    }
}
