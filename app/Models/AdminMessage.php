<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'admin_messages';

    protected $fillable = [
        'lawyer_id',
        'admin_id',
        'body',
        'category',
        'priority',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeConversation($query, User $lawyer, User $admin)
    {
        return $query->where(function ($q) use ($lawyer, $admin) {
            $q->where('lawyer_id', $lawyer->id)
                ->where('admin_id', $admin->id)
                ->orWhere(function ($q2) use ($lawyer, $admin) {
                    $q2->where('lawyer_id', $lawyer->id)
                        ->where(function ($q3) use ($admin) {
                            $q3->where('admin_id', $admin->id)
                                ->orWhereNull('admin_id');
                        });
                });
        });
    }
}
