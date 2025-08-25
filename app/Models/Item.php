<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
    'classification',
    'description',
    'unit',
    'price',
];
}
