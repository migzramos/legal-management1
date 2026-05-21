<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LawyerAvailability extends Model
{
    protected $fillable = [
        'lawyer_id', 'available_date', 'start_time', 'end_time', 'is_booked'
    ];

    protected $casts = [
        'available_date' => 'date',
        'is_booked' => 'boolean',
    ];

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_booked', false)
                     ->where('available_date', '>=', today());
    }
}
