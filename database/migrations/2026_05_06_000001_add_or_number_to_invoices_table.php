<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'or_number')) {
                $table->string('or_number')->nullable()->unique()->after('invoice_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'or_number')) {
                $table->dropUnique(['or_number']);
                $table->dropColumn('or_number');
            }
        });
    }
};
