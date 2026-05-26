<?php
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class PaymentTransaction extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'invoice_id',
        'appointment_id',
        'client_id',
        'lawyer_id',
        'gateway',
        'amount',
        'currency',
        'status',
        'reference_number',
        'reference_hash',
        'qr_payload',
        'qr_image_url',
        'payment_details',
        'metadata',
        'confirmed_by',
        'confirmed_at',
        'proof_image',
    ];
 
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'metadata' => 'array',
        'confirmed_at' => 'datetime',
    ];
 
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
 
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
 
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
 
    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }
 
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
 
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
 
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }
 
    public static function generateReferenceNumber($gateway, $lawyer = null)
    {
        $format = config("payment.gateways.{$gateway}.reference_format");
        
        return strtr($format, [
            '{timestamp}' => now()->timestamp,
            '{random}' => strtoupper(bin2hex(random_bytes(4))),
            '{lawyer_initials}' => $lawyer ? strtoupper(substr($lawyer->name, 0, 1)) : 'XX',
        ]);
    }
}
 