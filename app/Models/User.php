<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; // Added for Str::squish
use App\Models\Province; // Added for relation
use App\Models\City; // Added for relation
use App\Models\Rank; // Added for rank relation

class User extends Authenticatable // implements MustVerifyEmail // Consider implementing if email verification is used
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Kept for compatibility, but prefer first_name/last_name
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'birth_date',
        'gender',
        'phone',
        'dni', // Matches migration: add_mlm_and_profile_fields_to_users_table
        'address_street', // Matches migration
        'address_city', // Matches migration
        'address_state', // Matches migration
        'address_postal_code', // Matches migration
        'address_country_id', // Matches migration
        'address_province_id', // New
        'address_city_id', // New
        'mlm_level',
        'sponsor_id',
        'referrer_id', // Changed from invitador_id for consistency
        'placement_id',
        'status',
        'profile_completed',
        'agreed_to_terms',
        'email_verified_at', // Allow mass assignment if set through admin
        'two_factor_secret', // From Fortify
        'two_factor_recovery_codes', // From Fortify
        'two_factor_confirmed_at', // From Fortify
        'first_activation_date', // Para Bono Reconocimiento
        'rank_id', // Added for rank
        // Campos del modelo original que se mapean o ya existen:
        // 'country_id', // Mapeado a address_country_id
        // 'dni_ruc', // Mapeado a dni
        // 'activated_at', // Considerar si se usa o se reemplaza por status/email_verified_at
        // 'archived_at', // Considerar si se usa o se reemplaza por status
        // 'avatar_path', // No está en el form actual
        // 'civil_status', // No está en el form actual
        // 'address_line_1', // Mapeado a address_street
        // 'province', // Reemplazado por address_province_id
        // 'city', // Reemplazado por address_city_id
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date:Y-m-d', // Ensure format consistency
            'agreed_to_terms' => 'boolean',
            'profile_completed' => 'boolean',
            'mlm_level' => 'integer',
            'two_factor_confirmed_at' => 'datetime',
            'address_province_id' => 'integer', // New
            'address_city_id' => 'integer', // New
            'first_activation_date' => 'date:Y-m-d', // Para Bono Reconocimiento
            // 'activated_at' => 'datetime',
            // 'archived_at' => 'datetime',
        ];
    }

    // ACCESSORS & MUTATORS

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return Str::squish("{$this->first_name} {$this->last_name}");
    }

    /**
     * Determine if the user's profile is complete.
     * Define your own logic for what constitutes a complete profile.
     */
    public function getProfileCompletedAttribute($value): bool
    {
        // If already set in DB (e.g. by an admin), return that value.
        if (isset($this->attributes['profile_completed'])) {
            return (bool) $this->attributes['profile_completed'];
        }

        // Otherwise, calculate it.
        // Example: Check if essential profile fields are filled.
        $requiredFields = [
            'first_name',
            'last_name',
            'username',
            'email',
            'birth_date',
            'phone',
            'dni',
            'address_street',
            'address_city',
            'address_state',
            'address_postal_code',
            'address_country_id',
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }
        return true;
    }

    // Automatically update 'name' field if first_name or last_name changes.
    // This is optional and depends on how you want to handle the 'name' field.
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::saving(function ($user) {
    //         if ($user->isDirty('first_name') || $user->isDirty('last_name')) {
    //             $user->name = $user->getFullNameAttribute();
    //         }
    //         // Calculate profile_completed on saving
    //         // $user->profile_completed = $user->getProfileCompletedAttribute(null); // Pass null to force recalculation
    //     });
    // }


    // RELATIONSHIPS

    /**
     * Get the country of this user's address.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'address_country_id');
    }

    /**
     * Get the province of this user's address.
     */
    public function addressProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'address_province_id');
    }

    /**
     * Get the city of this user's address.
     */
    public function addressCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'address_city_id');
    }

    /**
     * Get the sponsor of this user.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    /**
     * Get the users sponsored by this user (direct downline).
     */
    public function sponsoredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'sponsor_id');
    }

    /**
     * Get the referrer of this user.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the users referred by this user.
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /**
     * Get the placement parent of this user.
     */
    public function placement(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placement_id');
    }

    /**
     * Get the users placed under this user (direct downline in placement tree).
     */
    public function placementChildren(): HasMany
    {
        return $this->hasMany(User::class, 'placement_id');
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the rank of this user.
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    // SCOPES

    // Example scope:
    // public function scopeActive(Builder $query): Builder
    // {
    //     return $query->where('status', 'active');
    // }
}
