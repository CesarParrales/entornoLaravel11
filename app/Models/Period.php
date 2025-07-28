<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Aquí se podrían añadir relaciones en el futuro, por ejemplo:
    // public function userPeriodRanks()
    // {
    //     return $this->hasMany(UserPeriodRank::class);
    // }
}
