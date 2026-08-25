<?php

namespace App\Models;

use App\Enums\DishCategory;
use App\Enums\DishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dish extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'chef_id',
        'name',
        'image_url',
        'category',
        'recipe',
        'taste_note',
        'price',
        'portion_note',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'category' => DishCategory::class,
        'status' => DishStatus::class,
        'sort_order' => 'integer',
    ];

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_id');
    }
}
