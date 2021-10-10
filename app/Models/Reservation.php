<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 1;
    public const STATUS_CANCELLED = 2;

    protected $casts = [
        'price' => 'integer',
        'status' => 'integer',
        'start_date' => 'immutable_date',
        'end_date' => 'immutable_date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query
            ->where(function ($query) use ($startDate, $endDate) {
                return $query
                    ->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(
                        fn ($query) => $query
                            ->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate)
                    );
            });
    }
}
