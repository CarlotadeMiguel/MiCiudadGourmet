<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favorite extends Pivot
{
    use HasFactory;
    protected $table = 'favorites';
    protected $fillable = [
        'user_id',
        'restaurant_id'
    ];
}
