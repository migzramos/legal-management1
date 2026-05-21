<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comprehensive indexing for optimal query performance
     * Includes all tables used in the system for CRUD operations
     */
    public function up(): void
    {
        // Appointments indexing
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (!Schema::hasIndex('appointments', 'idx_appointments_client_lawyer_status')) {
                    $table->index(['client_id', 'lawyer_id', 'status'], 'idx_appointments_client_lawyer_status');
                }
                if (!Schema::hasIndex('appointments', 'idx_appointments_appointment_at')) {
                    $table->index('appointment_at', 'idx_appointments_appointment_at');
                }
                if (!Schema::hasIndex('appointments', 'idx_appointments_status')) {
                    $table->index('status', 'idx_appointments_status');
                }
            });
        }

        // Invoices indexing
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasIndex('invoices', 'idx_invoices_appointment_status')) {
                    $table->index(['appointment_id', 'status'], 'idx_invoices_appointment_status');
                }
                if (!Schema::hasIndex('invoices', 'idx_invoices_lawyer_client')) {
                    $table->index(['lawyer_id', 'client_id'], 'idx_invoices_lawyer_client');
                }
                if (!Schema::hasIndex('invoices', 'idx_invoices_status')) {
                    $table->index('status', 'idx_invoices_status');
                }
            });
        }

        // Payment transactions indexing
        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                if (!Schema::hasIndex('payment_transactions', 'idx_payment_transactions_invoice_status')) {
                    $table->index(['invoice_id', 'status'], 'idx_payment_transactions_invoice_status');
                }
                if (!Schema::hasIndex('payment_transactions', 'idx_payment_transactions_reference')) {
                    $table->index('reference_number', 'idx_payment_transactions_reference');
                }
                if (!Schema::hasIndex('payment_transactions', 'idx_payment_transactions_gateway')) {
                    $table->index('gateway', 'idx_payment_transactions_gateway');
                }
                if (!Schema::hasIndex('payment_transactions', 'idx_payment_transactions_lawyer_client')) {
                    $table->index(['lawyer_id', 'client_id'], 'idx_payment_transactions_lawyer_client');
                }
            });
        }

        // Cases indexing
        if (Schema::hasTable('cases')) {
            Schema::table('cases', function (Blueprint $table) {
                if (!Schema::hasIndex('cases', 'idx_cases_lawyer_client_status')) {
                    $table->index(['lawyer_id', 'client_id', 'status'], 'idx_cases_lawyer_client_status');
                }
                if (!Schema::hasIndex('cases', 'idx_cases_category')) {
                    $table->index('category_id', 'idx_cases_category');
                }
                if (!Schema::hasIndex('cases', 'idx_cases_status')) {
                    $table->index('status', 'idx_cases_status');
                }
            });
        }

        // Users indexing
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasIndex('users', 'idx_users_role_active')) {
                    $table->index(['role', 'is_active'], 'idx_users_role_active');
                }
                if (!Schema::hasIndex('users', 'idx_users_email')) {
                    $table->index('email', 'idx_users_email');
                }
            });
        }

        // Admin messages indexing
        if (Schema::hasTable('admin_messages')) {
            Schema::table('admin_messages', function (Blueprint $table) {
                if (!Schema::hasIndex('admin_messages', 'idx_admin_messages_lawyer_admin_read')) {
                    $table->index(['lawyer_id', 'admin_id', 'is_read'], 'idx_admin_messages_lawyer_admin_read');
                }
            });
        }

        // Audit logs indexing
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'created_at') && !Schema::hasIndex('audit_logs', 'idx_audit_logs_created_at')) {
                    $table->index('created_at', 'idx_audit_logs_created_at');
                }
            });
        }

        // Revenue indexing
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                if (!Schema::hasIndex('revenues', 'idx_revenues_lawyer_date')) {
                    $table->index(['lawyer_id', 'revenue_date'], 'idx_revenues_lawyer_date');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'appointments' => ['idx_appointments_client_lawyer_status', 'idx_appointments_appointment_at', 'idx_appointments_status'],
            'invoices' => ['idx_invoices_appointment_status', 'idx_invoices_lawyer_client', 'idx_invoices_status'],
            'payment_transactions' => ['idx_payment_transactions_invoice_status', 'idx_payment_transactions_reference', 'idx_payment_transactions_gateway', 'idx_payment_transactions_lawyer_client'],
            'cases' => ['idx_cases_lawyer_client_status', 'idx_cases_category', 'idx_cases_status'],
            'users' => ['idx_users_role_active', 'idx_users_email'],
            'admin_messages' => ['idx_admin_messages_lawyer_admin_read'],
            'audit_logs' => ['idx_audit_logs_created_at'],
            'revenues' => ['idx_revenues_lawyer_date'],
        ];

        foreach ($tables as $tableName => $indexes) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexes) {
                    foreach ($indexes as $indexName) {
                        try {
                            if (Schema::hasIndex($tableName, $indexName)) {
                                $table->dropIndex($indexName);
                            }
                        } catch (\Exception $e) {
                            // Index doesn't exist
                        }
                    }
                });
            }
        }
    }
};
