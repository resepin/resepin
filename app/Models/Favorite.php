<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'spoonacular_id',
        'title',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
