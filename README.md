# Laravel Honey and Shopify Intergration
## Functionality:
- Init DB: get all products from shopify store and save in DB.
- Connect to honey place and update the product qty in DB and shopify store
## Admin:
- Display logs: last time product qty synced.
- Display new products(created on shopfi and saved in DB via webhook).
- Sync product images form(enter honey product url and choose product from the drop down menu).
- Display Last 10 synced prpducts (images synced).


# Cron jobs:
- Feed DB with products from shopify store(Run once)
- Sync product qty between honey place, DB and shopify store(Interval run)


# Enternue Cron Jobs
- saveProductsinDB: get products from shopify and save in db(except the qty and price), will be used as cache
so we can update it each 2h( if the product exists it will do nothing)
- syncQtyAndPriceEnternueDB: update DB products price and Qty with data from Enternue(run each ! h)
- syncProductStockWithShopify: update product qty on shopify(run each ? h)
- syncProductPricekWithShopify: update product price on shopify(run each ? h)



- change the locatioin name in enterenuePushProduct method on EntrenueDashboardController
line 57

TODO:
- switch to real store:
    - change creds in EnterenueDashboardController, remove all FARES creds stuff

TODO:
- need to know the time for jobs(need changes in Kernal.php)

TODO:
- merge branches 

// good product Exsens Lubricant 70ml - Appletini

price, compare at price => at varianlt level
qty and cost at invintory level

// $gross = $product['price'];
            // $price = $product['price'] * 2;
            // $compare = $price * 0.2;
            // $compare = $compare + $price;
            // if (!empty($product['msrp'])) {
            //     $compare = $product['msrp'];
            // } else {
            //     $compare = '';
            // }
            // $map = $product['map'];
            // if (empty($map)) {
            //     $map = $price;
            //     if (!empty($product['msrp'])) {
            //         if ($map  >=  $product['msrp']) {
            //             $map = $product['msrp'];
            //         }
            //     }
            // }
            // if ($map == $compare) {
            //     $compare = '';
            // }