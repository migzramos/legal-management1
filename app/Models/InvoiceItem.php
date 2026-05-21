<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'time_entry_id',
        'description', 'quantity', 'unit_price', 'total',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function timeEntry()
    {
        return $this->belongsTo(TimeEntry::class);
    }
}