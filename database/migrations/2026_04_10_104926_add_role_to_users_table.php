<?php
// database/migrations/2024_01_01_000001_add_role_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'lawyer', 'client'])->default('client')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->text('address')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('address');
        });

        Schema::create('case_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('court_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('jurisdiction')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('billing_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('hourly_rate', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->date('effective_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_rates');
        Schema::dropIfExists('court_types');
        Schema::dropIfExists('case_categories');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'address', 'is_active']);
        });
    }
};