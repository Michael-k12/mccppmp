<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppmp extends Model
{
    protected $fillable = [
        'classification',
        'description',
        'unit',
        'price',
        'quantity',
        'estimated_budget',
        'mode_of_procurement',
        'department',
        'milestone_date',
    ];
    protected $casts = [
        'milestone_date' => 'date',
    ];
}
