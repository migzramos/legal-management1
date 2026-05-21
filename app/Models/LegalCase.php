<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'case_number',
        'title',
        'description',
        'category_id',
        'court_type_id',
        'lawyer_id',
        'client_id',
        'status',
        'filed_date',
        'hearing_date',
        'next_hearing_date',
        'closed_date',
        'notes',
    ];

    protected $casts = [
        'filed_date'        => 'date',
        'hearing_date'      => 'date',
        'next_hearing_date' => 'date',
        'closed_date'       => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(CaseCategory::class);
    }

    public function courtType()
    {
        return $this->belongsTo(CourtType::class);
    }

    public function assignments()
    {
        return $this->hasMany(CaseAssignment::class, 'case_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'case_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'case_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'case_id');
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class, 'case_id');
    }

    // FIX BUG 3: Removed appointments() relationship — case_id was permanently
    // dropped from the appointments table in migration:
    // 2026_04_28_000003_remove_case_id_from_appointments.php
    // Appointments are now linked to clients/lawyers directly, not cases.
    // To find appointments related to a case, query via the case's client:
    //   Appointment::where('client_id', $case->client_id)->get()

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'case_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeIntake($query)
    {
        return $query->where('status', 'intake');
    }

    public function scopeBarangayMediation($query)
    {
        return $query->where('status', 'barangay_mediation');
    }

    public function scopeEscalationToCourt($query)
    {
        return $query->where('status', 'escalation_to_court');
    }

    public function scopeActiveCase($query)
    {
        return $query->where('status', 'active_case');
    }

    public function scopeResolution($query)
    {
        return $query->where('status', 'resolution');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            'intake',
            'barangay_mediation',
            'escalation_to_court',
            'active_case',
        ]);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'resolution');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Returns labeled progress steps for the Philippine legal workflow.
     * Each step is marked completed up to and including the current status.
     */
    public function getProgressSteps(): array
    {
        $steps = [
            'intake'               => ['label' => 'Intake',                'completed' => false],
            'barangay_mediation'   => ['label' => 'Barangay Mediation',    'completed' => false],
            'escalation_to_court'  => ['label' => 'Escalation to Court',   'completed' => false],
            'active_case'          => ['label' => 'Active Case',           'completed' => false],
            'resolution'           => ['label' => 'Resolution',            'completed' => false],
        ];

        $statusOrder  = array_keys($steps);
        $currentIndex = array_search($this->status, $statusOrder);

        if ($currentIndex !== false) {
            for ($i = 0; $i <= $currentIndex; $i++) {
                $steps[$statusOrder[$i]]['completed'] = true;
            }
        }

        return $steps;
    }
}