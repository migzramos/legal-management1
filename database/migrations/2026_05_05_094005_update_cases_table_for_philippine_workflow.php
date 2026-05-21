<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->date('next_hearing_date')->nullable()->after('hearing_date');
        });

        // Update the enum values for status
        DB::statement("ALTER TABLE cases MODIFY COLUMN status ENUM('intake', 'barangay_mediation', 'escalation_to_court', 'active_case', 'resolution') DEFAULT 'intake'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('next_hearing_date');
        });

        // Revert the enum values for status
        DB::statement("ALTER TABLE cases MODIFY COLUMN status ENUM('open', 'ongoing', 'closed', 'won', 'lost', 'dismissed') DEFAULT 'open'");
    }
};
