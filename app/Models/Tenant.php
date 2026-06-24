<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug', 'subdomain', 'company_name', 'business_type',
        'owner_name', 'phone', 'email', 'password', 'status',
        'trial_ends_at', 'current_package_id', 'timezone',
        'currency', 'theme', 'logo_path', 'referral_code',
        'referred_by_tenant_id',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->latestOfMany();
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'firma_sahibi');
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }
}
