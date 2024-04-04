<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cityType(): BelongsTo
    {
        return $this->belongsTo(CityType::class);
    }
}
