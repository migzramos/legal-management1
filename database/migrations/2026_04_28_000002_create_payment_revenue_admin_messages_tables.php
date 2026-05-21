<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lawyer_id')->constrained('users')->onDelete('cascade');
            $table->string('gateway'); // gcash, paymaya, paypal, bank_transfer, cash
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('reference_number')->nullable()->unique();
            $table->longText('qr_payload')->nullable();
            $table->string('qr_image_url')->nullable();
            $table->json('payment_details')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_id');
            $table->index('client_id');
            $table->index('lawyer_id');
            $table->index('gateway');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            $table->date('revenue_date');
            $table->string('category')->default('appointment'); // appointment, case, service
            $table->text('description')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamps();

            $table->index('lawyer_id');
            $table->index('revenue_date');
            $table->index('category');
        });

        Schema::create('admin_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('subject');
            $table->longText('body');
            $table->enum('category', ['general', 'error_report', 'concern', 'billing_issue', 'appointment_issue', 'other'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('lawyer_id');
            $table->index('admin_id');
            $table->index('is_read');
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_messages');
        Schema::dropIfExists('revenues');
        Schema::dropIfExists('payment_transactions');
    }
};
