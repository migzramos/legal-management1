<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'case_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['case_id']);
                $table->dropColumn('case_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'case_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('case_id')->nullable()->constrained('cases')->onDelete('set null');
            });
        }
    }
};
