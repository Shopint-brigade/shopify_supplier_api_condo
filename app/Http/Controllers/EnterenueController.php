<?php

namespace App\Http\Controllers;

use App\Http\Classes\DBUtils;
use Illuminate\Http\Request;
use App\Http\Classes\Shopify;
use App\Http\Classes\Helpers;
use App\Models\Enterenue;

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
        $this->username = env('SHOPIFY_USER_NAME');
        $this->password = env('SHOPIFY_PASSWORD_TOKEN');
        $this->shop = env('SHOPIFY_SHOP');
        $this->api = env('SHOPIFY_API');
        $this->location_name = env('ENTRENUE_LOCATION_NAME');
        $this->collection_id = env('ENTRENUE_COLLECTION_ID');
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
     * to be used for sync products on SHopify
     * WILL use as cron job(LATER) and run each 2 hours
     */
    public function saveProductsinDB(Request $request)
    {

        $products = $this->shopify->getProductsOfCollection($this->password, $this->collection_id);
        dd(DBUtils::SaveProductsDB($products, [], Enterenue::class));
    }
    public function syncProductPriceAndStock()
    {
        $locationID = Helpers::getShopifyIntIDFromStr($this->getShopifyLocationByName()); 
    }
}
