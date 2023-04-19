//// TODO
// every hour get product qty from honey(by sku <=> upc) and update the qty in DB and shopify store   //// DONE
// add logs on DB to show the last time the products were synced   //// DONE
// webhook to notify when a new product created
// images (url ignore thelast TS, alt tag from the title)




- 1: get all products from shopify(use pagination to have more than 250) //////// DONE 

- 2: save in local(laravel) DB       //////// DONE
    - fields go here

- 3: need to login to honey place site   ////////DONE
- 4: from honey we need quantity and UPC //////// DONE
- 5: update DB qty by the qty from honey place site with concurrent(simulation by changing by hand)    ////// DONE
- 6: sync shopify products qty with DB products qty and update the updated field in DB with yes      ////// DONE


// JOBS //
- run /products route (get all products from shopify store and save in the DB)
- 

