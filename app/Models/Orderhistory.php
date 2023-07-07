<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderhistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'initial_points',
        'points_adjustment',
        'current_points'
    ];
}
