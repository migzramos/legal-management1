<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id', 'lawyer_id', 'date',
        'hours', 'hourly_rate', 'description', 'is_billed',
    ];

    protected $casts = [
        'date'      => 'date',
        'is_billed' => 'boolean',
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function scopeUnbilled($query)
    {
        return $query->where('is_billed', false);
    }
}