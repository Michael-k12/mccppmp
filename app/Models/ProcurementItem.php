<?php

// app/Models/ProcurementItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    protected $fillable = [
        'classification',
        'description',
        'quantity',
        'unit',
        'price',
    ];
}

