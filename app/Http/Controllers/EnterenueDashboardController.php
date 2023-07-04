<?php

namespace App\Http\Controllers;

use App\Http\Classes\Shopify;
use Illuminate\Http\Request;
use App\Http\Classes\EnterenueUtils;

class EnterenueDashboardController extends DashboardController
{
    private string $password;
    public function __construct()
    {
        $this->password = env('FARES_API_TOKEN');
        $storeUrl = "https://" . env('FARES_API_KEY') . ":" . env('FARES_API_TOKEN') . "@" . env('FARES_STORE') . "/admin/" . env('FARES_API');
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
            $total = $res['data']['total'];
            $title = $request->term;
            $products = $res['data']['data'];
            return view('entrenue.serach_result', compact(['title', 'total', 'products']));
        }
        return  redirect()->back()->with('error', $res['error']);
    }

    public function enterenuePushProduct(Request $request, string $upc)
    {

        $locationID = $this->shopify->getLocation("Honey's Fulfilment", $this->password);
        $error = EnterenueUtils::pushProductToShopify($upc,$locationID, $request, $this->shopify) ;
        if(is_null($error)) {
            return redirect()->route('admin.enterenue.search.form')->with('success', __('Product with upc: '.$upc.' pushed'));
        }
        return redirect()->route('admin.enterenue.search.form')->with('error', $error . " while pushing product with upc: " . $upc);
    }
}
