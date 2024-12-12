<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    const INSPECTOR = 'inspector';
    const ADMIN = 'admin';
    const BUYER = 'buyer';
    const SELLER = 'seller';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('metadata')
            ->withTimestamps();
    }

    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    public function getDisplayNameAttribute(): string
    {
        return match($this->name) {
            self::INSPECTOR => 'Inspector',
            self::ADMIN => 'Administrator',
            self::BUYER => 'Buyer',
            self::SELLER => 'Seller',
            default => ucfirst($this->name),
        };
    }
} 