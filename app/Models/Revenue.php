<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'lawyer_id',
        'invoice_id',
        'payment_transaction_id',
        'amount',
        'currency',
        'revenue_date',
        'category',
        'description',
        'is_reconciled',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'revenue_date' => 'date',
        'is_reconciled' => 'boolean',
    ];

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('revenue_date', now()->month)
                     ->whereYear('revenue_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('revenue_date', now()->year);
    }

    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }
}
