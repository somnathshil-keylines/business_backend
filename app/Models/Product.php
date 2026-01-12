<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
                'seller_id',
                'category_id',
                'sub_category_id',
                'name',
                'description',
                'price',
                'stock',
                'unit',
                'image',
                'status',
    ];
     use SoftDeletes;
}
