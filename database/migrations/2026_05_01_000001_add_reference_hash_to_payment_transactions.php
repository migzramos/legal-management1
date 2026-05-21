<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_transactions', 'reference_hash')) {
                $table->string('reference_hash')->nullable()->after('reference_number');
                $table->index('reference_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payment_transactions', 'reference_hash')) {
                $table->dropIndex(['reference_hash']);
                $table->dropColumn('reference_hash');
            }
        });
    }
};
