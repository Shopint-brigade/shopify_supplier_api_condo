<?php

namespace App\Http\Classes;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

/**
 * Handle all shopify functionality
 */
class Shopify
{
    // store admin base url
    public string $url;
    // compose GraphQLMain class to be used in this class(Shopify)
    public $graphQL = GraphQLMain::class;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Headers for shopify graphQL
     */
    public function generateGraphQLHeaders(string $pass): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-Shoify-Access-Token' => $pass
        ];
    }

    /**
     * Send graphQL request to shopify admin
     */
    public function sendGraphQLRequest(string $pass, string $query)
    {
        $res = Http::withHeaders($this->generateGraphQLHeaders($pass))
            ->post($this->url . '/graphql.json', ['query' => $query]);    
        return $res;    
    }

    /**
     * shopify collections
     */

    public function getCollections(string $pass, int $first = 250)
    {
        $data = "
            collections(first: " . $first . "){
                edges{
                    node{
                        id
                        title
                    }
                }
            }
        ";
        $response = $this->sendGraphQLRequest($pass, $this->graphQL::query($data));
        return $response->json();
    }


    /**
     * shopify location id by location name
     */
    public function getLocation(string $locationName, string $pass, int $first = 250): string
    {
        $itemLocationId = "";
        $data = "
            locations(first:" . $first . "){
                edges{
                    node{
                        name
                        id
                    }
                }
            }      
        ";

        $response = $this->sendGraphQLRequest($pass, $this->graphQL::query($data));
        $bodyJSONArr = $response->json()['data']['locations']['edges'];
        if (count($bodyJSONArr) > 0) {
            foreach ($bodyJSONArr as $location) {
                if ($location['node']['name'] === $locationName) {
                    $itemLocationId = $location['node']['id'];
                }
            }
        }
        return $itemLocationId;
    }

    /**
     * Fetch products from shopify store via graphQL
     * more than 250 since we use pagination
     */
    public function getProductsOfCollection(string $pass, $collection_id): array
    {
        // initial products
        $products = [];
        // cursor for tracking pagination
        $cursor = null;

        do {
            $args = [
                "first: 5"
            ];
            if (!empty($cursor)) {
                $args[] = "after: \"$cursor\"";
            }
            $args = implode(', ', $args);
            $data = "
                collection(id:\"gid://shopify/Collection/" . $collection_id . "\"){
                    products(" . $args . "){
                        pageInfo{
                            hasNextPage
                        },
                        edges{
                            cursor
                            node{
                                id
                                title
                                publishedAt
                                variants(first:1){
                                    edges{
                                        node{
                                            id
                                            sku
                                            barcode
                                            inventoryQuantity
                                            inventoryItem{
                                                id
                                            }
                                        }
                                    }
                                }
                            }
                        }  
                    }
                }
                ";
            $response = $this->sendGraphQLRequest($pass, $this->graphQL::query($data));
            if (!empty($response->json()['data']['collection']['products']['edges'])) {
                $resEdges = $response->json()['data']['collection']['products']['edges'];
                foreach ($resEdges as $edge) {
                    $cursor = $edge['cursor'];
                    if (!is_null($edge['node']['publishedAt'])) {
                        $strId = explode("/", $edge['node']['id']);
                        $intID = end($strId);
                        $edge['node']['intID'] = $intID;
                        $products[] = $edge['node'];
                    }
                }
            }
        } while (!is_null($response->json()['data']['collection']) && $response->json()['data']['collection']['products']['pageInfo']['hasNextPage']);
        return $products;
    }

    /**
     * Update product qty on shopify store
     */
    public function updateProductQty(string $pass, string $locationName, $dbProducts, bool $returnData = true)
    {
        $locationIDStr = explode("/", $this->getLocation($locationName, $pass));
        $locationID = end($locationIDStr);
        // guzzel promises to be handled
        $promises = [];
        // headers
        $headers = $this->generateGraphQLHeaders($pass);

        // init Guzzel client
        $client = new Client([
            'base_uri' => $this->url,
            'headers' => $headers
        ]);
        // update(async) product qty on shopify
        foreach ($dbProducts as $product) {
            $promises[] = $client->postAsync($this->url . "/inventory_levels/set.json", [
                'json' => [
                    'location_id' => $locationID,
                    'inventory_item_id' => $product->inv_int_id,
                    'available' => $product->stock
                ],
            ]);
        }
        // handle the promises and get results
        // $results = Promise\Utils::unwrap($promises);
        $results = Promise\Utils::settle($promises)->wait();
        if ($returnData) {
            return $results;
        }
    }

    /**
     * Update product images
     */
    public function updateProductImages($id, $pass, $images): int
    {
        // needed headers
        $headers = [
            'X-Shopify-Access-Token' => $pass,
        ];
        // data to be updated
        $data = ['product' =>  ['id' =>   $id, 'images' =>  $images]];
        // send the update request
        $jsonData = json_encode($data);
        $res = Http::withHeaders($headers)
            ->put($this->url . '/products/' . $id . '.json', $data);
        return $res->status();
    }

    /**
     * General method to interact with shopify store
     * U can provide the method and data and the target url
     * Use it to handle Admin RestAPI requests
     */
    public function makeApiRequest(string $method, string $urlSeg, array $data)
    {
        $data_string = json_encode($data);
        $pattern = '/^https?:\/\/(.*):(.*)@(.*?)\/.*/';
        preg_match($pattern, $this->url, $matches);
        $key = $matches[1];
        $secret = $matches[2];
        $url = $this->url .'/' . $urlSeg . '.json';
        $data_string = json_encode($data);
        $response = Http::withBasicAuth($key, $secret)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($data_string),
            ])
            ->{$method}($url, $data);
        if($response->successful()) {
            return $response->json();
        }  else {
            if ($response->clientError()) {
                return response()->json(['error' => __('Client error !')]);
            } elseif ($response->serverError()) {
                return response()->json(['error' => __('Server error !')]);
            } else {
                return response()->json(['error' => __('Something went wrong !')]);
            }
        }

    }

    public function getProductsOfCollectionWithPrice(string $pass, $collection_id): array
    {
        // initial products
        $products = [];
        // cursor for tracking pagination
        $cursor = null;

        do {
            $args = [
                "first: 5"
            ];
            if (!empty($cursor)) {
                $args[] = "after: \"$cursor\"";
            }
            $args = implode(', ', $args);
            $data = "
                collection(id:\"gid://shopify/Collection/" . $collection_id . "\"){
                    products(" . $args . "){
                        pageInfo{
                            hasNextPage
                        },
                        edges{
                            cursor
                            node{
                                id
                                title
                                variants(first:1){
                                    edges{
                                        node{
                                            id
                                            barcode
                                            price
                                            inventoryQuantity
                                            inventoryItem{
                                                id
                                            }
                                        }
                                    }
                                }
                            }
                        }  
                    }
                }
                ";
            $response = $this->sendGraphQLRequest($pass, $this->graphQL::query($data));
            info($response->status());
            if (!empty($response->json()['data']['collection']['products']['edges'])) {
                $resEdges = $response->json()['data']['collection']['products']['edges'];
                foreach ($resEdges as $edge) {
                    $cursor = $edge['cursor'];
                    $products[] = $edge['node'];
                }
            }
        } while (!is_null($response->json()) && !is_null($response->json()['data']['collection']) && $response->json()['data']['collection']['products']['pageInfo']['hasNextPage']);
        return $products;
    }

    public function setQuantity()
    {
        
    }
}
