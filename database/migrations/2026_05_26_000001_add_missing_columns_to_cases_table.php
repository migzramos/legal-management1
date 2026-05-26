<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            if (!Schema::hasColumn('cases', 'court_name')) {
                $table->string('court_name')->nullable()->after('court_type_id');
            }
            if (!Schema::hasColumn('cases', 'judge_name')) {
                $table->string('judge_name')->nullable()->after('court_name');
            }
            if (!Schema::hasColumn('cases', 'opposing_party')) {
                $table->string('opposing_party')->nullable()->after('judge_name');
            }
            if (!Schema::hasColumn('cases', 'opposing_counsel')) {
                $table->string('opposing_counsel')->nullable()->after('opposing_party');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['court_name', 'judge_name', 'opposing_party', 'opposing_counsel']);
        });
    }
};