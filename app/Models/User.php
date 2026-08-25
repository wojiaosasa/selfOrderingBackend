<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'openid',
        'role',
        'nickname',
        'avatar_url',
        'phone',
        'api_token',
    ];

    protected $hidden = [
        'api_token',
    ];

    protected $casts = [
        'role' => UserRole::class,
    ];

    public function chefProfile(): HasOne
    {
        return $this->hasOne(ChefProfile::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'chef_id');
    }

    public function chefOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'chef_id');
    }

    public function dinerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'diner_id');
    }

    public function boundDiners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chef_diner_bindings', 'chef_id', 'diner_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function boundChefs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chef_diner_bindings', 'diner_id', 'chef_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}
