<?php

namespace App\Models;

use App\Models\Rfq;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'profile_completed' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'buyer_id');
    }

    public function messageThreads(): BelongsToMany
    {
        return $this->belongsToMany(MessageThread::class, 'message_participants', 'user_id', 'thread_id')
            ->withPivot('last_read_at')
            ->withTimestamps()
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('messages')
                    ->whereColumn('thread_id', 'message_threads.id')
                    ->latest()
                    ->limit(1);
            });
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function unreadMessages()
    {
        return Message::whereHas('thread.participants', function ($query) {
            $query->where('user_id', $this->id);
        })->whereDoesntHave('status', function ($query) {
            $query->where('user_id', $this->id)
                ->where(function ($q) {
                    $q->where('read', true)
                        ->orWhere('email_sent', true);
                });
        });
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function hasCompletedProfile(): bool
    {
        return $this->profile()->exists() && 
               $this->profile->organization_name &&
               $this->profile->organization_type &&
               $this->profile->country &&
               $this->profile->phone &&
               $this->profile->looking_for &&
               $this->profile->categories;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('metadata')
            ->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function assignRole(string $role, array $metadata = []): void
    {
        $roleModel = Role::firstOrCreate(['name' => $role]);
        
        if (!$this->hasRole($role)) {
            $this->roles()->attach($roleModel->id, ['metadata' => json_encode($metadata)]);
        }
    }

    public function removeRole(string $role): void
    {
        $roleModel = Role::where('name', $role)->first();
        
        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }
    }

    public function isInspector(): bool
    {
        return $this->hasRole(Role::INSPECTOR);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isBuyer(): bool
    {
        return $this->hasRole(Role::BUYER);
    }

    public function isSeller(): bool
    {
        return $this->hasRole(Role::SELLER);
    }

    public function canInspect(Order $order): bool
    {
        if (!$this->isInspector()) {
            return false;
        }

        $inspectionRegions = $this->profile->inspection_regions ?? [];
        return in_array($order->shipping_details['origin_region'] ?? null, $inspectionRegions);
    }
}
