<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChefDinerBinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'chef_id',
        'diner_id',
        'status',
    ];

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function diner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diner_id');
    }
}
