<?php
// app/Models/CaseAssignment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaseAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'user_id',
        'role',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'date',
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}