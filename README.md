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


# TODO:
- change push to shopify route name