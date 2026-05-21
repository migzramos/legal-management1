<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'case_id')) {
                // Already removed - skip
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->after('case_id')->constrained('appointments')->onDelete('set null');
            }
            if (!Schema::hasColumn('invoices', 'is_validated')) {
                $table->boolean('is_validated')->default(false)->after('status');
            }
            if (!Schema::hasColumn('invoices', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable()->after('is_validated');
            }
            if (!Schema::hasColumn('invoices', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->unique()->after('payment_gateway');
            }
            if (!Schema::hasColumn('invoices', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('payment_reference');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->after('case_id')->constrained('appointments')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeignIdFor('App\Models\Appointment', 'appointment_id');
            $table->dropColumn(['appointment_id', 'is_validated', 'payment_gateway', 'payment_reference', 'payment_details']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeignIdFor('App\Models\Appointment', 'appointment_id');
            $table->dropColumn('appointment_id');
        });
    }
};
