<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'lawyer_id',
        'appointment_at', 'duration_minutes', 'hourly_rate',
        'purpose', 'status', 'notes',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
        'hourly_rate'    => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    /**
     * Messages tied to this appointment (appointment-based thread).
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'appointment_id')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Auto-generated invoice created on appointment confirmation.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'appointment_id');
    }

    /**
     * case_id was removed from appointments. Return null-safe guard
     * so views with @if($appointment->case) do not crash.
     */
    public function getCaseAttribute(): ?object
    {
        return null;
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function getScheduledDateAttribute()
    {
        return $this->appointment_at?->toDateString();
    }

    public function getScheduledTimeAttribute()
    {
        return $this->appointment_at?->format('H:i');
    }

    public function getEndTimeAttribute()
    {
        return $this->appointment_at?->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Computed total cost: hourly_rate × (duration_minutes / 60)
     * Used in views and API responses.
     */
    public function getTotalCostAttribute(): float
    {
        return round((float) ($this->hourly_rate ?? 0) * ($this->duration_minutes / 60), 2);
    }
}