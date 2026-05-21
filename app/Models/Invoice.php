<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    // FIX BUG 4: Added 'case_id' to $fillable — it was missing, causing
    // Invoice::create(['case_id' => ...]) to silently drop the value due
    // to Laravel's mass assignment protection. The column exists in the DB
    // (re-added in migration 2026_05_05_154823_add_case_id_back_to_invoices_table).
    protected $fillable = [
        'invoice_number',
        'or_number',
        'case_id',           // ← FIXED: was missing
        'appointment_id',
        'client_id',
        'lawyer_id',
        'subtotal',
        'tax',
        'total',
        'amount_paid',
        'balance',
        'status',
        'is_validated',
        'payment_gateway',
        'payment_reference',
        'payment_details',
        'issued_date',
        'due_date',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'issued_date'     => 'date',
        'due_date'        => 'date',
        'paid_date'       => 'date',
        'is_validated'    => 'boolean',
        'payment_details' => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereNotIn('status', ['paid', 'cancelled']);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Generates a unique Official Receipt number in the format OR-{YEAR}-{000001}.
     * Uses a DB transaction with row-level lock to prevent race conditions
     * when multiple invoices are confirmed simultaneously.
     */
    public static function generateOrNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;

            $latestOrNumber = self::where('or_number', 'like', "OR-{$year}-%")
                ->orderBy('or_number', 'desc')
                ->lockForUpdate()
                ->value('or_number');

            $nextSequence = 1;

            if ($latestOrNumber) {
                $parts        = explode('-', $latestOrNumber);
                $nextSequence = intval($parts[2] ?? 0) + 1;
            }

            $orNumber = sprintf('OR-%s-%06d', $year, $nextSequence);

            // Collision guard in case of concurrent inserts
            while (self::where('or_number', $orNumber)->exists()) {
                $nextSequence++;
                $orNumber = sprintf('OR-%s-%06d', $year, $nextSequence);
            }

            return $orNumber;
        });
    }

    // ─── Accessors ───────────────────────────────────────────────

    /**
     * Alias: $invoice->amount === $invoice->total
     */
    public function getAmountAttribute()
    {
        return $this->total;
    }
}