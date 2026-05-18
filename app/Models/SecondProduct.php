<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecondProduct extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',
        /*
        |--------------------------------------------------------------------------
        | PURCHASE INVOICE
        |--------------------------------------------------------------------------
        */

        'purchase_invoice_id',
        'purchase_invoice_number',

        /*
        |--------------------------------------------------------------------------
        | ITEM
        |--------------------------------------------------------------------------
        */

        'item_id',
        'item_no',
        'item_name',

        /*
        |--------------------------------------------------------------------------
        | SERIAL NUMBER
        |--------------------------------------------------------------------------
        */

        'serial_number',

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER
        |--------------------------------------------------------------------------
        */

        'customer_id',
        'customer_no',
        'customer_name',

        /*
        |--------------------------------------------------------------------------
        | SALES ORDER
        |--------------------------------------------------------------------------
        */

        'sales_order_id',
        'sales_order_number',

        /*
        |--------------------------------------------------------------------------
        | WORKFLOW
        |--------------------------------------------------------------------------
        */

        'status',
        'type_garansi',
        'tanggal_real',
        'tanggal_fake',

        /*
        |--------------------------------------------------------------------------
        | WEBSITE
        |--------------------------------------------------------------------------
        */

        'selling_price',
        'is_publish',
        'description',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'is_publish' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function images()
    {
        return $this->hasMany(SecondProductImage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
