<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'action', 'model_type',
        'model_id', 'old_values', 'new_values',
        'ip_address', 'user_agent', 'description',
        'is_read',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'is_read'   => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where(function ($query) {
            $query->where('is_read', false)
                  ->orWhereNull('is_read');
        });
    }

    public function getAlertType(): string
    {
        $description = strtolower($this->description);

        if (str_contains($description, 'payment received')) {
            return 'success'; // green
        } elseif (str_contains($description, 'appointment')) {
            return 'warning'; // yellow
        } elseif (str_contains($description, 'overdue')) {
            return 'danger'; // red
        } elseif (str_contains($description, 'user') || str_contains($description, 'profile')) {
            return 'info'; // blue
        } else {
            return 'info'; // default blue
        }
    }
}