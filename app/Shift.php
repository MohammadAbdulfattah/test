<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the shift.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for the translated day name
     */
    public function getTranslatedDayAttribute()
    {
        return __("days.{$this->day}");
    }

    /**
     * Scope to get shifts by day
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day', $day);
    }
}
