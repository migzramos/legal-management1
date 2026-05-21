<?php
// database/migrations/xxxx_create_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('category', [
                'pleading',
                'evidence',
                'contract',
                'court_order',
                'correspondence',
                'invoice',
                'requested',
                'other'
            ])->default('other');
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('parent_document_id')
                ->nullable()
                ->constrained('documents')
                ->onDelete('set null');
            $table->boolean('is_visible_to_client')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};