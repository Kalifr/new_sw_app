<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_name',
        'organization_type',
        'country',
        'phone',
        'looking_for',
        'categories',
        'profile_completed'
    ];

    protected $casts = [
        'categories' => 'array',
        'profile_completed' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
