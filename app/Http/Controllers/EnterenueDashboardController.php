<?php

namespace App\Http\Controllers;

use App\Http\Classes\Shopify;
use Illuminate\Http\Request;
use App\Http\Classes\EnterenueUtils;
use App\Models\Enterenue;
class EnterenueDashboardController extends DashboardController
{
    private string $password;
    public function __construct()
    {
        $this->password = env('FARES_API_TOKEN');
        $storeUrl = "https://" . env('FARES_API_KEY') . ":" . env('FARES_API_TOKEN') . "@" . env('FARES_STORE') . ".myshopify.com/admin/" . env('FARES_API');
        $this->shopify = new Shopify($storeUrl);
    }

    /**
     * Enterenue
     */

    public function enterenueSearchForm()
    {
        return view('entrenue.search');
    }

    public function enterenueSearch(Request $request)
    {
        // validation
        $request->validate([
            'term' => 'required|min:3'
        ]);

        // serach request
        $res =  EnterenueUtils::search($request->term);
        // handle errors
        if (is_null($res['error'])) {
            $dbProductsUpcs = [];
            $total = $res['data']['total'];
            $title = $request->term;
            $products = $res['data']['data'];
            $dbProducts = Enterenue::select('upc')->get();
            foreach($dbProducts as $product) {
                array_push($dbProductsUpcs, $product->upc);
            }
            return view('entrenue.serach_result', compact(['title', 'total', 'products', 'dbProductsUpcs']));
        }
        return  redirect()->back()->with('error', $res['error']);
    }

    public function enterenuePushProduct(Request $request, string $upc)
    {
        $locationID = $this->shopify->getLocation("Honey's Fulfilment", $this->password);
        $error = EnterenueUtils::pushProductToShopify($upc,$locationID, $request, $this->shopify) ;
        if(is_null($error)) {
            return redirect()->route('admin.enterenue.products')->with('success', __('Product with upc: '.$upc.' pushed'));
        }
        return redirect()->route('admin.enterenue.products')->with('error', $error . " while pushing product with upc: " . $upc);
    }

    public function displayPushedProducts(Request $request)
    {
        $products = Enterenue::where('pushed', 1)->orderBy('created_at', 'desc')->simplePaginate(10);
        return view('entrenue.products', compact('products'));
    }

    public function destroy(Enterenue $product)
    {
        $product->delete();
        return redirect()->back()->with('success', __('Product deleted from DB'));
    }

    public function showAllShopifyProducts()
    {
        $products = Enterenue::select(['qty', 'price', 'upc', 'title', 'updated_at'])->simplePaginate(10);
        return view('entrenue.shopify_products', compact('products'));
    }
}
