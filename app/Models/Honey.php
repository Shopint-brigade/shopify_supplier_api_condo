<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Honey extends Model
{
    use HasFactory;
    // convert date to Carbon
    protected $dates = ['synced_at'];
    // change table name
    public $table = 'honey_products';

    protected $fillable = [
        'sku',
        'inv_item_id',
        'first_var_id',
        'shopify_id',
        'stock',
        'barcode',
        'intID',
        'inv_int_id'
    ];
}
