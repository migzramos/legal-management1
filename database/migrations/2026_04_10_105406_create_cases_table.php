<?php
// database/migrations/xxxx_create_cases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('case_categories')->onDelete('restrict');
            $table->foreignId('court_type_id')->constrained('court_types')->onDelete('restrict');
            $table->foreignId('lawyer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('client_id')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['open', 'ongoing', 'closed', 'won', 'lost', 'dismissed'])->default('open');
            $table->date('filed_date')->nullable();
            $table->date('hearing_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('case_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['lawyer', 'co_counsel', 'paralegal', 'client']);
            $table->date('assigned_at');
            $table->timestamps();

            $table->unique(['case_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_assignments');
        Schema::dropIfExists('cases');
    }
};