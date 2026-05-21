<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'case_id')) {
                $table->dropForeign(['case_id']);
                $table->dropColumn('case_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('case_id')->nullable()->after('lawyer_id')->constrained('cases')->onDelete('set null');
        });
    }
};
