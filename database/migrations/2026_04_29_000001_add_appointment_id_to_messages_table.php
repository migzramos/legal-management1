<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->after('case_id')->constrained('appointments')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropColumn('appointment_id');
        });
    }
};
