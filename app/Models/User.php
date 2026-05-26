<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'is_active',
        'hourly_rate',
        'lawyer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ─── Role Helpers ───────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLawyer(): bool
    {
        return $this->role === 'lawyer';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // ─── Relationships ───────────────────────────────────────────

    // Cases where this user is the assigned lawyer
    public function lawyerCases()
    {
        return $this->hasMany(LegalCase::class, 'lawyer_id');
    }

    // Cases where this user is the client
    public function clientCases()
    {
        return $this->hasMany(LegalCase::class, 'client_id');
    }

    // Assigned lawyer for this client
    public function assignedLawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    // All case assignments (co-counsel, paralegal, etc.)
    public function caseAssignments()
    {
        return $this->hasMany(CaseAssignment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class, 'lawyer_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'client_id');
    }

    public function billingRate()
    {
    return $this->hasOne(BillingRate::class, 'lawyer_id')
                ->latestOfMany('effective_date');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}