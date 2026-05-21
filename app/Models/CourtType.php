<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourtType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'jurisdiction', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function cases()
    {
        return $this->hasMany(LegalCase::class, 'court_type_id');
    }
}