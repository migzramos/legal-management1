<?php
// app/Models/Document.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_id',
        'uploaded_by',
        'title',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'version',
        'parent_document_id',
        'is_visible_to_client',
        'notes',
    ];

    protected $casts = [
        'is_visible_to_client' => 'boolean',
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function parentDocument()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id');
    }
}