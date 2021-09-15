<?php

namespace App\Models;

use App\Models\Image;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Office extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const APPROVAL_PENDING = 1;
    public const APPROVAL_APPROVED = 2;
    public const APPROVAL_REJECTED = 3;

    protected $casts = [
        'lng'=> 'decimal:8',
        'lat'=> 'decimal:8',
        'approval_status'=> 'integer',
        'price_per_day'=> 'decimal:2',
        'monthly_discount'=>  'decimal:2',
        'hidden'=> 'bool',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'resource');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'office_tags');
    }

    public function scopeNearestTo(Builder $builder, $lat, $lng)
    {
        return $builder->select()->selectRaw(
            'SQRT(
            POW(69.1 * (lat - ?), 2) +
            POW(69.1 * (? - lng) * COS(lat / 57.3), 2)
            ) AS distance',
            [$lat, $lng]
        )->orderBy('distance');
    }
}
