<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Optimize message retrieval by appointment
            if (!Schema::hasIndex('messages', 'messages_appointment_id_created_at_index')) {
                $table->index(['appointment_id', 'created_at'], 'messages_appointment_id_created_at_index');
            }

            // Optimize unread message queries
            if (!Schema::hasIndex('messages', 'messages_receiver_id_is_read_index')) {
                $table->index(['receiver_id', 'is_read'], 'messages_receiver_id_is_read_index');
            }

            // Optimize full-text search on body
            if (!Schema::hasIndex('messages', 'messages_body_fulltext')) {
                $table->fullText('body', 'messages_body_fulltext');
            }

            // Optimize appointment + receiver queries
            if (!Schema::hasIndex('messages', 'messages_appointment_receiver_index')) {
                $table->index(['appointment_id', 'receiver_id'], 'messages_appointment_receiver_index');
            }

            // Sender queries
            if (!Schema::hasIndex('messages', 'messages_sender_id_index')) {
                $table->index('sender_id', 'messages_sender_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_appointment_id_created_at_index');
            $table->dropIndex('messages_receiver_id_is_read_index');
            $table->dropIndex('messages_body_fulltext');
            $table->dropIndex('messages_appointment_receiver_index');
            $table->dropIndex('messages_sender_id_index');
        });
    }
};
