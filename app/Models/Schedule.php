<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_id', 'created_by', 'title',
        'description', 'type', 'scheduled_at',
        'location', 'status', 'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
                     ->where('status', 'upcoming');
    }
}