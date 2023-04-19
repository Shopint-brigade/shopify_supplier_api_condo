<?php

namespace App\Http\Classes;

/**
 * Handle all graphQl stuff(queries, mutations ....etc)
 * To be used inside other classes.
 * This class will not use any internal classes
 */
class GraphQLMain
{
    /**
     * query graphql
     */
    public static function query($qql)
    {
        $query = <<<QUERY
            query {
                $qql
            }
          QUERY;
        return $query;
    }
    
}
