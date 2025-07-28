<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rank_order',
        'required_group_volume',
        'required_direct_sponsors_count',
        'required_direct_sponsor_rank_id',
        'compression_depth_level',
        'instant_qualification_personal_points',
        'leg_alpha_min_percentage_vg',
        'leg_beta_min_percentage_vg',
        'is_active',
        'color_badge',
        'slug', // Añadido slug
    ];

    protected $casts = [
        'rank_order' => 'integer',
        'required_group_volume' => 'integer',
        'required_direct_sponsors_count' => 'integer',
        'required_direct_sponsor_rank_id' => 'integer',
        'compression_depth_level' => 'integer',
        'instant_qualification_personal_points' => 'integer',
        'leg_alpha_min_percentage_vg' => 'decimal:2',
        'leg_beta_min_percentage_vg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the rank required for direct sponsors.
     * This defines a self-referential relationship.
     */
    public function requiredDirectSponsorRank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'required_direct_sponsor_rank_id');
    }

    /**
     * Get the users who have this rank.
     */
    // public function users(): HasMany
    // {
    //     // Assuming you add a 'rank_id' to your User model
    //     return $this->hasMany(User::class);
    // }
}
