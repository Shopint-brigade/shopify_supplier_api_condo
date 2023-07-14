<?php

namespace App\Http\Controllers;

use App\Http\Classes\EnterenueUtils;
use Illuminate\Http\Request;
use App\Http\Classes\Shopify;
use App\Http\Classes\Helpers;
use App\Models\Enterenue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
        $this->username =  env('FARES_API_KEY'); //env('SHOPIFY_USER_NAME');
        $this->password = env('FARES_API_TOKEN'); //env('SHOPIFY_PASSWORD_TOKEN');
        $this->shop = env('FARES_STORE'); //env('SHOPIFY_SHOP');
        $this->api = env('FARES_API'); //env('SHOPIFY_API');
        $this->location_name = env('FARES_LOCATION_NAME'); //env('ENTRENUE_LOCATION_NAME');
        $this->collection_id = env('FARES_COLLECTION_ID'); //env('ENTRENUE_COLLECTION_ID');
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
    public function saveProductsinDB(Request $request)
    {
        $products = $this->shopify->getProductsOfCollectionWithPrice($this->password, $this->collection_id);
        if (count($products) > 0) {
            foreach ($products as $product) {
                EnterenueUtils::SaveProductsDB($product, Enterenue::class);
            }
        info("product saved in DB !");    
        } else {
            info("Enternue save products job, products count is: " . count($products) . ' Menas NO products foud');
        }
    }

    /**
     * Use as cron job to update the Qty and the Price from Enternue on Db
     * then we can use the DB as cache to update the qty and the price on
     * shopify.
     */
    public function syncQtyAndPriceEnternueDB()
    {
        // get products from DB
        $dbProducts = Enterenue::all();
        // find each product on Enternue 
        foreach($dbProducts as $dbPr) {
            $entrenueResponse = EnterenueUtils::findProductOnEntrenue($dbPr['upc']);
            if (!is_null($entrenueResponse['error'])) {
                info($entrenueResponse['error']);
            } else {
                if (!empty($entrenueResponse['data']['data'])) {
                    $qty = intval($entrenueResponse['data']['data'][0]['quantity']) - 3;
                    $price = $entrenueResponse['data']['data'][0]['price'];
                    // update qty on DB
                    $dbPr->qty = $qty;
                    $dbPr->price = $price;
                    $dbPr->save();
                    info("qty and price updated in DB");
                } else {
                    info("product with upc: " . $dbPr['upc'] . " not found on Enternue !");
                }
            }
        }
        return "success";
    }



    /**
     * This method will be used as cron job to update the qty from DB 
     * on shopify store for each product nand updates the synced_at
     * attr on DB.
     */
    public function syncProductStockWithShopify()
    {
        // dd($this->shopify->url);
        // product location id
        $locationID = Helpers::getShopifyIntIDFromStr($this->getShopifyLocationByName());
        // get products from DB
        $dbProducts = Enterenue::where('upc', '!=', '')->get();
        // for each product login and find the product, update the QTY in DB and update the qty on shopify
        $promises = [];
        $client = new Client();
        foreach ($dbProducts as $dbPr) {
            // prepare data for shopify(qty)
            $data = [
                'location_id' => $locationID,
                'inventory_item_id' => $dbPr['inventory_item_id'],
                'available' => $dbPr['qty'],
            ];
            // $url = $this->shopify->url . '/inventory_levels/set.json';
            // EnterenueUtils::updatesingleFieldOnShopifyProduct($dbProducts, $data, $promises, $client, $url, 'POST');
            $data_string = json_encode($data);
               // update the qty on shopify
               $request = new GuzzleRequest(
                'POST',
                $this->shopify->url . '/inventory_levels/set.json',
                [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($data_string),
                ],
                $data_string
            );
         
            $promise = $client->sendAsync($request)->then(
                // all is good
                function (Response $response) use ($dbPr) {
                    $dbPr->synced_at = Carbon::now();
                    $dbPr->save();
                    $result = $response->getBody()->getContents();
                    info("Product with title: " . $dbPr . " qty updated");
                },
                // error happened
                function (Exception $exception) {
                    info('Error: ' . $exception->getMessage());
                }
            );

            $promises[] = $promise;
        }
        // wait for all promises to
        $eachPromise = new EachPromise($promises);
        $eachPromise->promise()->wait();
        // all good other code goes here ....
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
        $promises = [];
        $client = new Client();
        foreach ($dbProducts as $dbPr) {
            // prepare data for shopify(price)
            $data = [
                'product' => [
                    'id' => $dbPr['shopify_id'],
                    'variants' => [
                        [
                            'id' => $dbPr['variant_id'],
                            'price' => $dbPr['price'],
                        ],
                    ],
                ]
            ];
            $data_string = json_encode($data);
               // update the qty on shopify
               $request = new GuzzleRequest(
                'PUT',
                $this->shopify->url . '/products/'.$dbPr['shopify_id'].'.json',
                [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($data_string),
                ],
                $data_string
            );
          
            $promise = $client->sendAsync($request)->then(
                // all is good
                function (Response $response) use ($dbPr) {
                    $dbPr->synced_at = Carbon::now();
                    $dbPr->save();
                    $result = $response->getBody()->getContents();
                    info("Product with title: " . $dbPr . " price updated");
                },
                // error happened
                function (Exception $exception) {
                    info('Error: ' . $exception->getMessage());
                }
            );

            $promises[] = $promise;
        }
        // wait for all promises to
        $eachPromise = new EachPromise($promises);
        $eachPromise->promise()->wait();
        // all good other code goes here ....
    }
    // TODO
    // and test all agian
    // push to github and merge with main branch
}
