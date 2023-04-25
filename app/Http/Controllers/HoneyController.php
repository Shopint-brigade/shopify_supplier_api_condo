<?php

namespace App\Http\Controllers;

use App\Http\Classes\HoneyUtils;
use App\Http\Classes\Shopify;
use App\Models\Honey;
use Illuminate\Http\Request;
use Carbon\Carbon;



/**
 * Handle all Honey's functionality
 * this class uses Shopify class injected(constuctor DI)
 */
class HoneyController extends Controller
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
    $this->location_name = env('SHOPIFY_LOCATION_NAME');
    $this->collection_id = env('SHOPIFY_COLLECTION_ID');
    $this->honey_account = env('HONEY_ACCOUNT');
    $this->honey_password = env("HONEY_PASSWORD");
    $this->shopify = new Shopify('https://' . $this->username . ':' . $this->password . '@' . $this->shop . '.myshopify.com/admin/' . $this->api);
  }

  /**
   * Get specific shopify location id by name
   */
  public function getShopifyLocationByName()
  {
    return $this->shopify->getLocation($this->location_name, $this->password);
  }

  /**
   * Get all shopify locations
   */
  public function getShopifyCollections()
  {
    return $this->shopify->getCollections($this->password);
  }

  /**
   * Get products from shopify store for specific collection
   * Save the products in DB
   * Used for one time(first time to seed the DB)
   */
  public function getShopifyProducts()
  {
    $products = $this->shopify->getProductsOfCollection($this->password, $this->collection_id);
    // save in DB first time
    HoneyUtils::saveProductsFirstTime($products);
  }


  /**
   * Login to honey
   * get the product qty
   * Update DB product with the new qty
   */
  public function connectHoney()
  {
    // bring in prooducts from DB
    $dbProductsSkus =  HoneyUtils::fetchDBProductsSKUS();
    // login to honey and get qty then update DB
    HoneyUtils::connectToHoneyAndGetUpdateQTY($this->honey_account, $this->honey_password, $dbProductsSkus);
  }

  /**
   * Get products from DB with the required fileds
   * Update the product qty on shopify store
   */
  public function updateQtyOnShopifyStore()
  {
    // get products from DB
    $dbProducts = Honey::select('inv_int_id', 'stock')->get();
    // update the product qty on the store and give back the response to be used
    $data = $this->shopify->updateProductQty($this->honey_password, $this->location_name, $dbProducts);
    // update the product (updated filed) in DB
    // HoneyUtils::updateProductStatusonDB($data);
  }

  /**
   * - get products sku from DB
   * - connect to honey place via credentials and ge the product qty
   * - update the product qty on DB
   * - update the product qty on shopify store
   */
  public function syncQtyHoneyDbStore()
  {
    // bring in prooducts from DB
    $dbProductsSkus =  HoneyUtils::fetchDBProductsSKUS();
    // connect honey => get product qty => update DB
    HoneyUtils::connectToHoneyAndGetUpdateQTY($this->honey_account, $this->honey_password, $dbProductsSkus);
    // bring the products again
    $dbProducts = Honey::all();
    // update synced_at field
    foreach($dbProducts as $product) {
      $product->synced_at = Carbon::now();
      $product->save();
    }
    // update the product qty on the store and give back the response to be used
    $this->shopify->updateProductQty($this->honey_password, $this->location_name, $dbProducts, false);
  }

  /**
   * New product webhook receiver
   */
  public function receiveShopifyWebhookNewProduct(Request $request)
  {
    // get webhook posted data
    $resData = $request->getContent();
    // verify the webhook 
    $verified = HoneyUtils::verifyWebhook($resData, $request->header('X-Shopify-Hmac-Sha256'));
    // verification passed
    if ($verified) {
      // prepare to extract data
      $data = json_decode($resData, true);
      // extract needed data
      $id = $data['id'];
      $title = $data['title'];
      $admin_graphql_api_id = $data['admin_graphql_api_id'];
      $status = $data['status'];
      $variantID = $data['variants'][0]['id'];
      $inv_int_id = $data['variants'][0]['inventory_item_id'];
      $sku = $data['variants'][0]['sku'];
      $barcode = $data['variants'][0]['barcode'];
      $inventory_quantity = $data['variants'][0]['inventory_quantity'];
      if (!is_null($inv_int_id)) {
        $inv_item_id = "gid://shopify/InventoryItem/" . $inv_int_id;
      }
      // if the product status is active save the product
      if ($status == "active") {
        $product = new Honey();
        $product->shopify_id = $admin_graphql_api_id;
        $product->title = $title;
        $product->intID = $id;
        $product->sku = $sku;
        $product->first_var_id = $variantID;
        $product->barcode = $barcode;
        $product->stock = $inventory_quantity;
        $product->inv_int_id = $inv_int_id;
        $product->inv_item_id = $inv_item_id;
        $product->newProduct = "yes";
        $product->save();
      }
    } else {
      return response()->json([
        'success' => false,
        'message' => 'Webhook not verified'
      ], 401);
    }
  }
}
