<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "user_id",
        "subtotal",
        "tax_amount",
        "shipping_charge",
        "discount_amount",
        "total_amount",
        "payment_method",
        "payment_status",
        "order_status",
        "shipping_address",
        "billing_address",
        "notes",
    ];
}
