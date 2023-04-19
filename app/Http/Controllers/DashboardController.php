<?php

namespace App\Http\Controllers;

use App\Models\Honey;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the last time products updated
     */
    public function logs()
    {
        $syncedDate = Carbon::parse(Honey::first()->synced_at)->toDayDateTimeString();
        return view("honey_place.logs", ['lastUpdated' => $syncedDate]);
    }

    /**
     * Display product created by shopify store
     */
    public function newProducts()
    {
        $newProducts = Honey::where("newProduct", "yes")->get();
        return view("honey_place.newProducts", ['newProducts' => $newProducts]);
    }
}
