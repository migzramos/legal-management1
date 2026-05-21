<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'lawyer_id', 'hourly_rate',
        'currency', 'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }
}