<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterenue extends Model
{
    use HasFactory;

    protected $fillable  = [
        'title',
        'upc',
        'price',
        'qty',
        'shopify_id',
        'inventory_item_id',
        'synced_at',
        'variant_id'
    ];
}
