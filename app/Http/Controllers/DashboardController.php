<?php

namespace App\Http\Controllers;

use App\Http\Classes\HoneyUtils;
use App\Http\Requests\ImagesSyncRequest;
use App\Models\Honey;
use App\Http\Classes\Shopify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class DashboardController extends Controller
{
    // DI Shopify
    public Shopify $shopify;
    private string  $username;
    private  string $password;
    private  string $shop;
    private  string $api;

    public function __construct()
    {
        // only admin
        $this->middleware('auth');
        // shopify creds
        $this->username = env('SHOPIFY_USER_NAME');
        $this->password = env('SHOPIFY_PASSWORD_TOKEN');
        $this->shop = env('SHOPIFY_SHOP');
        $this->api = env('SHOPIFY_API');
        $this->shopify = new Shopify('https://' . $this->username . ':' . $this->password . '@' . $this->shop . '.myshopify.com/admin/' . $this->api);
    }

    /**
     * Display the last time products updated
     */
    public function logs()
    {
        $productSynced = false;
        $syncedDate = null;
        if (!is_null(Honey::first()) && !is_null(Honey::first()->synced_at)) {
            $syncedDate = Carbon::parse(Honey::first()->synced_at)->toDayDateTimeString();
            $productSynced = true;
        }

        return view("honey_place.logs", ['lastUpdated' => $syncedDate, 'productSynced' => $productSynced]);
    }

    /**
     * Display product created by shopify store
     */
    public function newProducts()
    {
        $newProducts = Honey::where("newProduct", "yes")->get();
        return view("honey_place.newProducts", ['newProducts' => $newProducts]);
    }
    /**
     * sync product images between honey place and shopify store
     */

    public function syncImages()
    {
        // get products(title and shopify id)
        $products = Honey::select('title', 'intID')->get();
        return view("honey_place.sync_images", compact('products'));
    }

    /**
     * Get products from DB via ajax(uses auto-complete)
     * Search by product title or id
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('search');
        $products = Honey::select('title', 'intID')->where('title', 'like', '%' . $search . '%')
            ->orWhere('intID', 'like', '%' . $search . '%')->get();
        return response()->json($products);
    }

    /**
     * Handle syncing images post  
     */
    public function postSyncImages(ImagesSyncRequest $request)
    {
        // get data from form request
        $honeyProductUrl = $request->input('product_url');
        $shopifyProductID = $request->input('product_id');
        // scrap product images from honey
        $honeyProductImages = HoneyUtils::parsingProductImages($honeyProductUrl);
        // if we have images
        if (count($honeyProductImages) > 0) {
            // update shopify prtoduct images and get back the response status  
            $status = $this->shopify->updateProductImages($shopifyProductID,  $this->password, $honeyProductImages);
            if ($status == Response::HTTP_OK) {
                // make imagesSynced field yes 
                Honey::where('intID', $shopifyProductID)->update(['imagesSynced' => 'yes']);;
                return redirect(route('home'))->with(['success' => 'images updated']);
            } else {
                return back()->with(['error' => $status . ' :Something went wrong !!!!']);
            }
        } else {
            return back()->with(['info' => 'product has no images']);
        }
    }

    /**
     * Display last 10 synced products(image synced)
     */

    public function displaySyncedProducts()
    {
        $products = Honey::select('title', 'intID', 'sku', 'stock')->where('imagesSynced', 'yes')->latest()->take(10)->get();
        return view("honey_place.list_products", compact('products'));
    }
}
